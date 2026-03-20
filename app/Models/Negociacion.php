<?php

/**
 * ============================================================
 * MODELO: Negociacion
 * ============================================================
 * Representa una negociación (intercambio) entre dos usuarios.
 * Un usuario emisor propone intercambiar su paquete de artículos
 * por un artículo específico que pertenece al usuario receptor.
 *
 * Tabla BD: negociaciones
 * Clave primaria: id_negociacion
 *
 * Estados posibles del campo `estado`:
 *   - 'Inicial'      → Negociación recién creada
 *   - 'pendiente'    → Esperando respuesta del receptor
 *   - 'contraoferta' → El receptor hizo una contraoferta
 *   - 'aceptado'     → Ambas partes acordaron
 *   - 'completado'   → Intercambio finalizado
 *   - 'rechazado'    → Fue rechazado por alguna parte
 *   - 'cancelado'    → Cancelado por admin u otra razón
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Negociacion extends Model
{
    use HasFactory;

    /** Nombre real de la tabla en la base de datos */
    protected $table = 'negociaciones';

    /** Clave primaria personalizada (no es 'id' por defecto) */
    protected $primaryKey = 'id_negociacion';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     * Solo estos campos pueden ser llenados con create() o fill().
     */
    protected $fillable = [
        'receptor_item_id',       // ID del artículo que el emisor quiere obtener
        'emisor_paquete_id',      // ID del paquete que el emisor ofrece a cambio
        'usuario_emisor_id',      // ID del usuario que inicia la negociación
        'usuario_receptor_id',    // ID del usuario dueño del artículo solicitado
        'mensaje_inicial',        // Mensaje de presentación del emisor
        'monto_oferta',           // Monto en dinero que ofrece el emisor (opcional)
        'monto_contra_oferta',    // Monto de contraoferta del receptor (opcional)
        'estado',                 // Estado actual de la negociación
        'fecha_creacion',         // Fecha manual (no usa created_at)
    ];

    /**
     * Desactivamos timestamps automáticos (created_at / updated_at)
     * porque la tabla usa el campo manual `fecha_creacion`.
     */
    public $timestamps = false;

    // ============================================================
    // RELACIONES ELOQUENT
    // ============================================================

    /**
     * Usuario que inició la negociación (emisor).
     * Relación: negociaciones.usuario_emisor_id → users.id
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_emisor_id');
    }

    /**
     * Usuario que recibe la propuesta (receptor / dueño del artículo).
     * Relación: negociaciones.usuario_receptor_id → users.id
     */
    public function usuarioReceptor()
    {
        return $this->belongsTo(User::class, 'usuario_receptor_id');
    }

    /**
     * Artículo que el emisor desea obtener.
     * Relación: negociaciones.receptor_item_id → items.id_item
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'receptor_item_id');
    }

    /**
     * Artículos que el emisor ofrece a cambio (tabla pivot).
     *
     * NOTA: Esta tabla pivot (negociacion_items_oferta) aún no existe
     * en la base de datos. La relación está definida pero no debe
     * usarse hasta que se cree la migración correspondiente.
     */
    public function itemsOferta()
    {
        // Tabla pivot no existe en BD — relación deshabilitada
        return $this->belongsToMany(Item::class, 'negociacion_items_oferta', 'negociacion_id_negociacion', 'item_id_item')
            ->withPivot('cantidad')
            ->withTimestamps();
    }

    /**
     * Artículos que el receptor propone como contraoferta (tabla pivot).
     *
     * NOTA: Esta tabla pivot (negociacion_items_contraoferta) aún no existe
     * en la base de datos. La relación está definida pero no debe
     * usarse hasta que se cree la migración correspondiente.
     */
    public function itemsContraOferta()
    {
        // Tabla pivot no existe en BD — relación deshabilitada
        return $this->belongsToMany(Item::class, 'negociacion_items_contraoferta', 'negociacion_id_negociacion', 'item_id_item')
            ->withPivot('cantidad')
            ->withTimestamps();
    }
}
