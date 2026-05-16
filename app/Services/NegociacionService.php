<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Message;
use App\Models\Negociacion;
use App\Models\Paquete;
use App\Models\PredefinedMessage;
use App\Services\ERPService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================
 * NegociacionService — Lógica de negocio de intercambios
 * ============================================================
 *
 * Flujo de estados:
 *   Inicial → aceptado → completado
 *           → contraoferta → aceptado / rechazado
 *           → rechazado
 *           → cancelado (por el emisor)
 *
 * Reglas:
 *   - Solo se puede negociar items con stock > 0
 *   - No se permiten negociaciones duplicadas (mismo emisor + mismo item)
 *   - Cada transición de estado valida el estado actual
 *   - Aceptar descuenta inventario dentro de una transacción DB
 *   - El paquete ofrecido debe pertenecer al emisor
 * ============================================================
 */
class NegociacionService
{
    public function __construct(
        private ERPService $erpService,
    ) {}

    // Estados que permiten cada acción
    private const ESTADOS_ACEPTAR     = ['Inicial', 'contraoferta'];
    private const ESTADOS_RECHAZAR    = ['Inicial', 'contraoferta'];
    private const ESTADOS_CONTRAOFERTA = ['Inicial'];
    private const ESTADOS_CANCELAR    = ['Inicial', 'contraoferta'];

    /**
     * Crea una nueva negociación.
     */
    public function crear(int $emisorId, array $datos): array
    {
        $receptorItem = Item::with('inventarios')->find($datos['item_id']);

        if (!$receptorItem) {
            return $this->error('El artículo no existe.');
        }

        if ($receptorItem->estatus != 1) {
            return $this->error('Este artículo no está disponible (pausado o inactivo).');
        }

        if ($receptorItem->id_user === $emisorId) {
            return $this->error('No puedes negociar contigo mismo.');
        }

        // Validar stock — Reinstaurado para todos los ítems (incluyendo servicios) por lógica de cobro
        $stock = $receptorItem->inventarios?->cantidad ?? 0;
        if ($stock <= 0) {
            return $this->error('Este artículo está agotado y no se puede negociar.');
        }

        // Validar que no exista negociación activa del mismo emisor por el mismo item
        $existente = Negociacion::where('usuario_emisor_id', $emisorId)
            ->where('receptor_item_id', $receptorItem->id_item)
            ->whereNotIn('estado', ['rechazado', 'cancelado', 'completado'])
            ->exists();

        if ($existente) {
            return $this->error('Ya tienes una negociación activa por este artículo.');
        }

        // Validar que el paquete pertenezca al emisor
        $emisorPaquete = null;
        if (!empty($datos['paquete_id'])) {
            $emisorPaquete = Paquete::where('id_paquete', $datos['paquete_id'])
                ->where('id_user', $emisorId)
                ->first();

            if (!$emisorPaquete) {
                return $this->error('El paquete seleccionado no te pertenece.');
            }
        }

        $negociacion = Negociacion::create([
            'receptor_item_id'    => $receptorItem->id_item,
            'id_color'            => $datos['id_color'] ?? null,
            'emisor_paquete_id'   => $emisorPaquete?->id_paquete,
            'usuario_emisor_id'   => $emisorId,
            'usuario_receptor_id' => $receptorItem->id_user,
            'mensaje_inicial'     => $datos['mensaje'],
            'monto_oferta'        => $datos['monto_oferta'] ?? null,
            'estado'              => 'Inicial',
            'fecha_creacion'      => now(),
            'items_ofrecidos'     => !empty($datos['items_ofrecidos']) ? $datos['items_ofrecidos'] : null,
        ]);

        $this->crearMensaje(
            $emisorId,
            $receptorItem->id_user,
            $receptorItem->id_item,
            $emisorPaquete?->id_paquete,
            $datos['mensaje']
        );

        // Notificar al receptor
        $emisor = \App\Models\User::find($emisorId);
        $textoNotif = "[Intercambio] Tienes una nueva propuesta de intercambio por tu producto \"{$receptorItem->item}\" de {$emisor->nombres}.";
        $this->notificar($receptorItem->id_user, $textoNotif);

        return $this->ok('Negociación enviada correctamente.');
    }

    /**
     * Receptor acepta la negociación.
     * Descuenta inventario dentro de una transacción.
     */
    public function aceptar(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_receptor_id) {
            return $this->error('No autorizado.');
        }

        if (!in_array($neg->estado, self::ESTADOS_ACEPTAR)) {
            return $this->error("No se puede aceptar una negociación en estado \"{$neg->estado}\".");
        }

        // Al aceptar un intercambio de servicio, notificar al receptor con la ubicación del emisor
        $itemNeg = Item::find($neg->receptor_item_id);
        if (!$itemNeg || $itemNeg->estatus != 1) {
            $neg->update(['estado' => 'cancelado']);
            return $this->error('El artículo ya no está disponible. Negociación cancelada.');
        }

        // Transacción: cambiar estado + descontar inventario
        try {
            DB::transaction(function () use ($neg) {
                // Lock para evitar doble aceptación
                $neg = Negociacion::where('id_negociacion', $neg->id_negociacion)
                    ->lockForUpdate()
                    ->first();

                if (!in_array($neg->estado, self::ESTADOS_ACEPTAR)) {
                    throw new \RuntimeException('Estado ya cambió.');
                }

                $neg->update(['estado' => 'aceptado']);

                // Descontar inventario (Reinstaurado para todos los ítems por lógica de cobro)
                $item = Item::with('inventarios')->find($neg->receptor_item_id);

                if ($item && $item->inventarios && $item->inventarios->cantidad > 0) {
                    $item->inventarios->cantidad -= 1;
                    $item->inventarios->save();

                    // Si el stock llegó a 0, cancelar otras negociaciones activas por este item
                    if ($item->inventarios->cantidad <= 0) {
                        Negociacion::where('receptor_item_id', $item->id_item)
                            ->where('id_negociacion', '!=', $neg->id_negociacion)
                            ->whereIn('estado', ['Inicial', 'contraoferta'])
                            ->update(['estado' => 'cancelado']);
                    }
                }
            });

            // Notificar al emisor que fue aceptado
            $receptor = \App\Models\User::find($userId);
            $this->notificar($neg->usuario_emisor_id, "[Intercambio] ¡Tu propuesta de intercambio fue aceptada por {$receptor->nombres}! Confirma para continuar.");

            return $this->ok('Negociación aceptada.');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Error al aceptar negociación', ['id' => $negociacionId, 'error' => $e->getMessage()]);
            return $this->error('Error al procesar la aceptación.');
        }
    }

    /**
     * Emisor acepta una contraoferta del receptor.
     */
    public function aceptarComoEmisor(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) return $this->error('Negociación no encontrada.');

        if ($userId != $neg->usuario_emisor_id) {
            return $this->error('Solo el emisor puede aceptar la contraoferta.');
        }

        if ($neg->estado !== 'contraoferta') {
            return $this->error('No hay contraoferta activa para aceptar.');
        }

        try {
            DB::transaction(function () use ($neg) {
                $neg = Negociacion::where('id_negociacion', $neg->id_negociacion)->lockForUpdate()->first();
                if ($neg->estado !== 'contraoferta') throw new \RuntimeException('Estado ya cambió.');
                $neg->update(['estado' => 'aceptado']);

                $item = Item::with('inventarios')->find($neg->receptor_item_id);
                if ($item && $item->inventarios && $item->inventarios->cantidad > 0) {
                    $item->inventarios->cantidad -= 1;
                    $item->inventarios->save();
                    if ($item->inventarios->cantidad <= 0) {
                        Negociacion::where('receptor_item_id', $item->id_item)
                            ->where('id_negociacion', '!=', $neg->id_negociacion)
                            ->whereIn('estado', ['Inicial', 'contraoferta'])
                            ->update(['estado' => 'cancelado']);
                    }
                }
            });

            $this->notificar($neg->usuario_receptor_id, "[Intercambio] El emisor aceptó tu contraoferta en el intercambio #{$neg->id_negociacion}. Confirma para continuar.");

            return $this->ok('Contraoferta aceptada. El intercambio está en estado aceptado.');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Error al aceptar contraoferta', ['id' => $negociacionId, 'error' => $e->getMessage()]);
            return $this->error('Error al procesar la aceptación.');
        }
    }

    /**
     * Receptor rechaza la negociación.
     */
    public function rechazar(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_receptor_id) {
            return $this->error('No autorizado.');
        }

        if (!in_array($neg->estado, self::ESTADOS_RECHAZAR)) {
            return $this->error("No se puede rechazar una negociación en estado \"{$neg->estado}\".");
        }

        $neg->update(['estado' => 'rechazado']);

        // Notificar al emisor
        $receptor = \App\Models\User::find($userId);
        $this->notificar($neg->usuario_emisor_id, "[Intercambio] Tu propuesta de intercambio fue rechazada por {$receptor->nombres}.");

        return $this->ok('Negociación rechazada.');
    }

    /**
     * Emisor cancela su propia negociación.
     */
    public function cancelar(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_emisor_id) {
            return $this->error('No autorizado. Solo el emisor puede cancelar.');
        }

        if (!in_array($neg->estado, self::ESTADOS_CANCELAR)) {
            return $this->error("No se puede cancelar una negociación en estado \"{$neg->estado}\".");
        }

        $neg->update(['estado' => 'cancelado']);
        $this->notificar($neg->usuario_receptor_id, "[Intercambio] El intercambio #{$neg->id_negociacion} ha sido cancelado por el emisor.");
        return $this->ok('Negociación cancelada.');
    }

    /**
     * Receptor envía contraoferta.
     */
    public function contraoferta(int $userId, int $negociacionId, array $datos): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_receptor_id) {
            return $this->error('No autorizado.');
        }

        if (!in_array($neg->estado, self::ESTADOS_CONTRAOFERTA)) {
            return $this->error("No se puede hacer contraoferta en estado \"{$neg->estado}\".");
        }

        $neg->update([
            'monto_contra_oferta' => $datos['monto_contra_oferta'] ?? null,
            'estado'              => 'contraoferta',
        ]);

        if (!empty($datos['mensaje'])) {
            $this->crearMensaje(
                $userId,
                $neg->usuario_emisor_id,
                $neg->receptor_item_id,
                null,
                $datos['mensaje']
            );
        }

        $this->notificar($neg->usuario_emisor_id, "[Intercambio] Tienes una contraoferta en el intercambio #{$neg->id_negociacion}.");

        return $this->ok('Contraoferta enviada.');
    }

    /**
     * Emisor confirma el intercambio después de que el receptor aceptó.
     * Una vez confirmado por ambos, se notifica a los administradores.
     */
    public function confirmarEmisor(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_emisor_id) {
            return $this->error('Solo el emisor puede confirmar.');
        }

        if ($neg->estado !== 'aceptado') {
            return $this->error('Solo se puede confirmar una negociación aceptada por el receptor.');
        }

        if ($neg->emisor_confirmado) {
            return $this->error('Ya confirmaste este intercambio.');
        }

        $neg->update(['emisor_confirmado' => true]);

        // Notificar al receptor que el emisor aprobó
        $this->notificar($neg->usuario_receptor_id, "[Intercambio] El emisor aprobó el intercambio #{$neg->id_negociacion}. Ahora aprueba tú para continuar.");

        // Solo notificar admins cuando AMBAS partes hayan confirmado
        $negFresh = $neg->fresh();
        if ($negFresh->emisor_confirmado && $negFresh->receptor_confirmado) {
            $this->notificarConfirmacionMutua($negFresh);
        }

        return $this->ok('Confirmación registrada. Esperando aprobación del receptor para continuar.');
    }

    /**
     * Receptor confirma el intercambio.
     */
    public function confirmarReceptor(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_receptor_id) {
            return $this->error('Solo el receptor puede confirmar.');
        }

        if ($neg->estado !== 'aceptado') {
            return $this->error('La negociación no está en estado aceptado.');
        }

        if ($neg->receptor_confirmado) {
            return $this->error('Ya confirmaste este intercambio.');
        }

        $neg->update(['receptor_confirmado' => true]);

        // Determinar si es servicio vs servicio para personalizar mensaje
        $itemSolicitado = Item::find($neg->receptor_item_id);
        $itemsOfrecidos = $neg->items_ofrecidos ? Item::whereIn('id_item', $neg->items_ofrecidos)->get() : collect();
        $esServicioServicio = ($itemSolicitado && $itemSolicitado->id_categoria_item == 29) && 
                             ($itemsOfrecidos->isNotEmpty() && $itemsOfrecidos->every(fn($i) => $i->id_categoria_item == 29));

        $msgNotif = $esServicioServicio 
            ? "El receptor aprobó el intercambio #{$neg->id_negociacion}. ¡Ambos confirmaron! Coordinen la prestación del servicio."
            : "El receptor aprobó el intercambio #{$neg->id_negociacion}. ¡Ambos confirmaron! Procede al pago de envío.";

        // Notificar al emisor que el receptor aprobó
        $this->notificar($neg->usuario_emisor_id, "[Intercambio] " . $msgNotif);

        // Si ambos confirmaron, notificar (una única vez)
        $negFresh = $neg->fresh();
        if ($negFresh->emisor_confirmado && $negFresh->receptor_confirmado) {
            $this->notificarConfirmacionMutua($negFresh);
        }

        return $this->ok('Has aprobado el intercambio. ¡Ambos han confirmado! Procede al pago del envío.');
    }

    /**
     * Dueño del producto selecciona modo de entrega (envio | retiro).
     * Solo aplica en intercambios producto↔servicio.
     */
    public function seleccionarModoEntrega(int $userId, int $negociacionId, string $modo): array
    {
        if (!in_array($modo, ['envio', 'retiro'])) {
            return $this->error('Modo de entrega inválido.');
        }

        $neg = Negociacion::find($negociacionId);
        if (!$neg) return $this->error('Negociación no encontrada.');

        if ($neg->estado !== 'aceptado') {
            return $this->error('Solo se puede seleccionar modo de entrega en negociaciones aceptadas.');
        }

        if (!$neg->emisor_confirmado || !$neg->receptor_confirmado) {
            return $this->error('Ambas partes deben aprobar antes de seleccionar el modo de entrega.');
        }

        // Solo el dueño del producto puede elegir el modo
        // En producto↔servicio: el receptor es dueño del producto solicitado
        // El emisor ofrece servicio y solicita el producto del receptor
        $itemSolicitado = Item::find($neg->receptor_item_id);
        $itemsOfrecidos = $neg->items_ofrecidos
            ? Item::whereIn('id_item', $neg->items_ofrecidos)->get()
            : collect();

        $solicitadoEsServicio = $itemSolicitado && $itemSolicitado->id_categoria_item == 29;
        $ofrecidosServicio    = $itemsOfrecidos->isNotEmpty() && $itemsOfrecidos->every(fn($i) => $i->id_categoria_item == 29);

        // Determinar quién es el dueño del producto físico
        if (!$solicitadoEsServicio && $ofrecidosServicio) {
            // El receptor tiene el producto, el emisor ofrece servicio
            $duenioProductoId = $neg->usuario_receptor_id;
        } elseif ($solicitadoEsServicio && !$ofrecidosServicio) {
            // El emisor tiene el producto, el receptor ofrece servicio
            $duenioProductoId = $neg->usuario_emisor_id;
        } else {
            return $this->error('Este modo de entrega solo aplica en intercambios producto↔servicio.');
        }

        if ($userId != $duenioProductoId) {
            return $this->error('Solo el dueño del producto puede seleccionar el modo de entrega.');
        }

        // Guardar modo_entrega solo si la columna existe
        if (\Illuminate\Support\Facades\Schema::hasColumn('negociaciones', 'modo_entrega')) {
            $neg->update(['modo_entrega' => $modo]);
        }

        // Notificar a la otra parte
        $otroId = $userId == $neg->usuario_emisor_id ? $neg->usuario_receptor_id : $neg->usuario_emisor_id;
        $textoModo = $modo === 'envio'
            ? "El dueño del producto eligió enviarlo. Se notificará a los administradores para gestionar el envío."
            : "El dueño del producto eligió entrega en persona. Coordinen el retiro directamente.";

        $this->notificar($otroId, "[Intercambio] " . $textoModo);

        if ($modo === 'envio') {
            $this->notificarAdminsEntrega($neg, 'envio');
        }

        return $this->ok($modo === 'envio'
            ? 'Seleccionaste envío. Los administradores gestionarán el envío.'
            : 'Seleccionaste retiro en persona. Coordina con la otra parte.');
    }

    /**
     * El receptor del producto confirma que lo recibió o retiró.
     */
    public function confirmarEntrega(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) return $this->error('Negociación no encontrada.');

        if ($neg->estado !== 'aceptado') {
            return $this->error('La negociación no está en estado aceptado.');
        }

        if (!$neg->modo_entrega) {
            // Si la columna no existe aún (migración pendiente), permitir continuar
            if (!\Illuminate\Support\Facades\Schema::hasColumn('negociaciones', 'modo_entrega')) {
                $neg->update(['entrega_confirmada' => true, 'estado' => 'completado']);
                $otroId = $userId == $neg->usuario_emisor_id ? $neg->usuario_receptor_id : $neg->usuario_emisor_id;
                $this->notificar($otroId, "[Intercambio] ✅ Intercambio #{$neg->id_negociacion} completado.");
                return $this->ok('Entrega confirmada. El intercambio está completado.');
            }
            return $this->error('El dueño del producto aún no ha seleccionado el modo de entrega.');
        }

        // Determinar quién recibe el producto
        $itemSolicitado    = Item::find($neg->receptor_item_id);
        $itemsOfrecidos    = $neg->items_ofrecidos
            ? Item::whereIn('id_item', $neg->items_ofrecidos)->get()
            : collect();
        $solicitadoEsServicio = $itemSolicitado && $itemSolicitado->id_categoria_item == 29;
        $ofrecidosServicio    = $itemsOfrecidos->isNotEmpty() && $itemsOfrecidos->every(fn($i) => $i->id_categoria_item == 29);

        if (!$solicitadoEsServicio && $ofrecidosServicio) {
            // El emisor recibe el producto (lo solicitó)
            $receptorProductoId = $neg->usuario_emisor_id;
        } elseif ($solicitadoEsServicio && !$ofrecidosServicio) {
            // El receptor recibe el producto (el emisor lo envía)
            $receptorProductoId = $neg->usuario_receptor_id;
        } else {
            return $this->error('Confirmación de entrega solo aplica en intercambios producto↔servicio.');
        }

        if ($userId != $receptorProductoId) {
            return $this->error('Solo quien recibe el producto puede confirmar la entrega.');
        }

        $entregaConfirmada = \Illuminate\Support\Facades\Schema::hasColumn('negociaciones', 'entrega_confirmada')
            ? (bool) $neg->entrega_confirmada
            : false;

        if ($entregaConfirmada) {
            return $this->error('La entrega ya fue confirmada.');
        }

        $updateData = ['estado' => 'completado'];
        if (\Illuminate\Support\Facades\Schema::hasColumn('negociaciones', 'entrega_confirmada')) {
            $updateData['entrega_confirmada'] = true;
        }
        $neg->update($updateData);

        // ERP: Registrar salidas del almacén por el intercambio completado
        $this->erpService->registrarSalidaIntercambio($neg);

        $otroId = $userId == $neg->usuario_emisor_id ? $neg->usuario_receptor_id : $neg->usuario_emisor_id;
        $this->notificar($otroId, "[Intercambio] ✅ El intercambio #{$neg->id_negociacion} fue completado. El receptor confirmó la entrega del producto.");

        $this->notificarAdminsEntrega($neg, 'completado');

        return $this->ok('Entrega confirmada. El intercambio está completado.');
    }

    private function notificarAdminsEntrega(Negociacion $neg, string $tipo): void
    {
        try {
            $admins = \App\Models\User::where('isAdmin', true)->get();
            $texto = match($tipo) {
                'envio'      => "📦 Intercambio #{$neg->id_negociacion}: el dueño eligió envío. Gestiona la logística.",
                'completado' => "✅ Intercambio #{$neg->id_negociacion}: entrega confirmada por el receptor. Intercambio completado.",
                default      => "Intercambio #{$neg->id_negociacion} actualizado.",
            };
            foreach ($admins as $admin) {
                $this->notificar($admin->id, "[Intercambio] " . $texto);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar a admins (entrega)', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Notifica a admins y usuarios cuando ambos han confirmado (antes del pago)
     */
    private function notificarConfirmacionMutua(Negociacion $neg): void
    {
        try {
            $esServicio = $this->esServicioServicio($neg);
            $esMixto    = $this->esProductoServicio($neg);
            $admins     = \App\Models\User::where('isAdmin', true)->get();

            if ($esServicio) {
                $texto = "🤝 Intercambio de SERVICIOS #{$neg->id_negociacion}: ambos usuarios confirmaron. Coordinarán directamente.";
            } elseif ($esMixto) {
                $texto = "🤝 Intercambio MIXTO #{$neg->id_negociacion}: ambos confirmaron. Esperando pago de envío del producto físico.";
            } else {
                $texto = "🤝 Intercambio de PRODUCTOS #{$neg->id_negociacion}: ambos confirmaron. Esperando pagos de envío.";
            }

            foreach ($admins as $admin) {
                $this->notificar($admin->id, "[Intercambio] " . $texto);
            }
        } catch (\Throwable $e) {
            Log::warning('Error en notificarConfirmacionMutua', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Verifica si una negociación es Producto vs Servicio (mixto)
     */
    public function esProductoServicio(Negociacion $neg): bool
    {
        $itemSolicitado = Item::find($neg->receptor_item_id);
        $itemsOfrecidos = $neg->items_ofrecidos ? Item::whereIn('id_item', $neg->items_ofrecidos)->get() : collect();

        $solicitadoEsServicio = $itemSolicitado && $itemSolicitado->id_categoria_item == 29;
        $ofrecidosServicio    = $itemsOfrecidos->isNotEmpty() && $itemsOfrecidos->every(fn($i) => $i->id_categoria_item == 29);

        // Mixto: uno servicio y el otro no
        return ($solicitadoEsServicio && !$ofrecidosServicio) || (!$solicitadoEsServicio && $ofrecidosServicio);
    }

    /**
     * Verifica si una negociación es Servicio vs Servicio
     */
    public function esServicioServicio(Negociacion $neg): bool
    {
        $itemSolicitado = Item::find($neg->receptor_item_id);
        $itemsOfrecidos = $neg->items_ofrecidos ? Item::whereIn('id_item', $neg->items_ofrecidos)->get() : collect();

        $solicitadoEsServicio = $itemSolicitado && $itemSolicitado->id_categoria_item == 29;
        $ofrecidosServicio    = $itemsOfrecidos->isNotEmpty() && $itemsOfrecidos->every(fn($i) => $i->id_categoria_item == 29);
        
        // Fallback para paquetes de servicio
        if (!$ofrecidosServicio && $itemsOfrecidos->isEmpty() && $solicitadoEsServicio) {
            $ofrecidosServicio = true;
        }

        return $solicitadoEsServicio && $ofrecidosServicio;
    }

    /**
     * Verifica si una negociación es Producto vs Producto
     */
    public function esProductoProducto(Negociacion $neg): bool
    {
        return !$this->esServicioServicio($neg) && !$this->esProductoServicio($neg);
    }

    public function notificarAdminsCompletado(Negociacion $neg): void
    {
        try {
            $admins = \App\Models\User::where('isAdmin', true)->get();
            $esMixto = $this->esProductoServicio($neg);
            
            if ($esMixto) {
                $texto = "📦 Intercambio MIXTO (Prod↔Serv) #{$neg->id_negociacion}: pago realizado. Gestiona el envío del producto físico.";
            } else {
                $texto = "📦 Intercambio #{$neg->id_negociacion}: ambos usuarios pagaron el envío. Procede a gestionar y despachar los productos. (Panel Admin → Intercambios Confirmados)";
            }

            foreach ($admins as $admin) {
                $this->notificar($admin->id, "[Intercambio] " . $texto);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar a admins (completado)', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Marca una negociación aceptada como completada (intercambio realizado).
     */
    public function completar(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        // Cualquiera de las dos partes puede marcar como completado
        if ($userId != $neg->usuario_emisor_id && $userId != $neg->usuario_receptor_id) {
            return $this->error('No autorizado.');
        }

        if ($neg->estado !== 'aceptado') {
            return $this->error('Solo se pueden completar negociaciones aceptadas.');
        }

        $neg->update(['estado' => 'completado']);

        // ERP: Registrar salidas del almacén por el intercambio completado
        $this->erpService->registrarSalidaIntercambio($neg);

        $otroId = $userId == $neg->usuario_emisor_id ? $neg->usuario_receptor_id : $neg->usuario_emisor_id;
        $this->notificar($otroId, "[Intercambio] El intercambio #{$neg->id_negociacion} ha sido marcado como completado.");

        return $this->ok('Intercambio marcado como completado.');
    }

    // ───────────────────────────────────────────────────────
    // Consultas
    // ───────────────────────────────────────────────────────

    /**
     * Historial de negociaciones de un artículo para el usuario.
     */
    public function obtenerNegociaciones(int $userId, int $itemId): array
    {
        // Obtener mensajes reales de la tabla messages (no de negociaciones)
        $mensajes = Message::where('id_oferta', $itemId)
            ->where(fn($q) => $q->where('id_emisor', $userId)->orWhere('id_receptor', $userId))
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'texto'  => $msg->mensaje,
                'fecha'  => optional($msg->created_at)->format('d/m/Y H:i'),
                'propio' => $msg->id_emisor == $userId,
            ]);

        $paquetes = DB::table('paquetes')
            ->where('id_user', $userId)
            ->select('id_paquete as id', 'nombre_paquete as nombre')
            ->get();

        return [
            'mensajes'             => $mensajes,
            'paquetes'             => $paquetes,
            'accion'               => PredefinedMessage::select('tipo')->distinct()->get(),
            'mensajesPredefinidos' => PredefinedMessage::select('titulo', 'mensaje', 'rol')->get(),
        ];
    }

    /**
     * Mensajes entre dos usuarios filtrados por item negociado.
     */
    public function obtenerMensajes(int $userId, int $idEmisor, int $idReceptor): array
    {
        $rawMensajes = Message::where(function ($q) use ($idEmisor, $idReceptor) {
                $q->where('id_emisor', $idEmisor)->where('id_receptor', $idReceptor);
            })
            ->orWhere(function ($q) use ($idEmisor, $idReceptor) {
                $q->where('id_emisor', $idReceptor)->where('id_receptor', $idEmisor);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $itemId = $rawMensajes->whereNotNull('id_oferta')->first()?->id_oferta;
        if ($itemId) {
            $rawMensajes = $rawMensajes->where('id_oferta', $itemId)->values();
        }

        $mensajes = $rawMensajes->map(fn($msg) => [
            'id'      => $msg->id,
            'mensaje' => $msg->mensaje,
            'fecha'   => optional($msg->created_at)->format('d/m/Y H:i'),
            'propio'  => $msg->id_emisor == $userId,
        ]);

        return [
            'mensajes'             => $mensajes,
            'mensajesPredefinidos' => PredefinedMessage::select('titulo', 'mensaje', 'rol')->get(),
            'accion'               => PredefinedMessage::select('tipo')->distinct()->get(),
            'item_id'              => $itemId,
        ];
    }

    // ───────────────────────────────────────────────────────
    // Helpers privados
    // ───────────────────────────────────────────────────────

    public function crearMensaje(int $emisorId, int $receptorId, ?int $itemId, ?int $paqueteId, string $texto): void
    {
        try {
            Message::create([
                'id_emisor'   => $emisorId,
                'id_receptor' => $receptorId,
                'id_oferta'   => $itemId,
                'id_paquete'  => $paqueteId,
                'mensaje'     => $texto,
                'leido'       => 0,
            ]);
        } catch (\Exception $e) {
            Log::warning('NegociacionService: no se pudo guardar mensaje', ['error' => $e->getMessage()]);
        }
    }

    private function ok(string $message): array
    {
        return ['success' => true, 'message' => $message];
    }

    private function error(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }

    private function notificar(int $userId, string $mensaje): void
    {
        try {
            Message::create([
                'id_emisor'   => null,
                'id_receptor' => $userId,
                'mensaje'     => $mensaje,
                'leido'       => false,
            ]);
            event(new \App\Events\NuevaNotificacion($mensaje, $userId));
        } catch (\Throwable $e) {
            Log::warning('Error al notificar en NegociacionService', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
    }
}
