import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import '../widgets/item_image.dart';
import 'checkout_screen.dart';
import 'login_screen.dart';
import 'publicar_articulo_screen.dart';
import 'items_list_screen.dart';
import '../widgets/footer_widget.dart';
// import 'negociacion_detalle_screen.dart';

class CarritoScreen extends StatefulWidget {
  const CarritoScreen({super.key});
  @override
  State<CarritoScreen> createState() => _CarritoScreenState();
}

class _CarritoScreenState extends State<CarritoScreen> {
  Map<String, dynamic>? _data;
  bool  _loading   = true;
  bool  _loggedIn  = false;
  bool  _vaciando  = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    _loggedIn = await AuthService.isLoggedIn();
    if (!mounted) return;
    if (!_loggedIn) {
      setState(() => _loading = false);
      final loggedIn = await Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
      if (loggedIn == true) {
        _load();
      } else {
        if (mounted && Navigator.canPop(context)) {
          Navigator.pop(context);
        }
      }
      return;
    }
    
    try {
      final res = await ApiClient.get('/carrito', auth: true, useCache: false);
      if (!mounted) return;
      if (res.statusCode == 200) {
        final parsed = jsonDecode(res.body);
        setState(() { _data = parsed; _loading = false; });
        try {
          final todosItems = (parsed['todosLosItems'] as List?) ?? [];
          ApiClient.cartCountNotifier.value = todosItems.length;
        } catch (_) {}
      } else {
        setState(() => _loading = false);
      }
    } catch (e) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _eliminar(int idItem) async {
    await ApiClient.delete('/carrito/$idItem', auth: true);
    ApiClient.clearCache('/carrito');
    _load();
  }

  Future<void> _vaciar() async {
    final ok = await _confirmar('¿Vaciar carrito?', '¿Seguro que deseas eliminar todos los artículos?');
    if (!ok) return;
    setState(() => _vaciando = true);
    await ApiClient.delete('/carrito/vaciar', auth: true);
    ApiClient.clearCache('/carrito');
    await _load();
    if (mounted) setState(() => _vaciando = false);
  }

  Future<void> _actualizarCantidad(int itemIntencionId, String accion) async {
    final res = await ApiClient.put('/carrito/$itemIntencionId/cantidad', {'accion': accion}, auth: true);
    if (res.statusCode == 200) {
      try {
        final body = jsonDecode(res.body);
        final msg = body['message'] ?? 'Cantidad actualizada';
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.white),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    msg,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                ),
              ],
            ),
            backgroundColor: Colors.green.shade600,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            margin: const EdgeInsets.all(16),
            duration: const Duration(seconds: 2),
          ));
        }
      } catch (e) {
        // Ignorar
      }
      _load();
    } else {
      try {
        final msg = jsonDecode(res.body)['message'] ?? 'Error al actualizar cantidad';
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Row(
              children: [
                const Icon(Icons.error_outline, color: Colors.white),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    msg,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                ),
              ],
            ),
            backgroundColor: Colors.red.shade600,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            margin: const EdgeInsets.all(16),
            duration: const Duration(seconds: 3),
          ));
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: const Row(
              children: [
                Icon(Icons.error_outline, color: Colors.white),
                SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'Error al actualizar cantidad',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                ),
              ],
            ),
            backgroundColor: Colors.red.shade600,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            margin: const EdgeInsets.all(16),
            duration: const Duration(seconds: 3),
          ));
        }
      }
    }
  }

  Future<void> _marcarSeleccionado(Map item, bool estado) async {
    setState(() {
      item['es_seleccionado'] = estado ? 1 : 0;
    });
    final res = await ApiClient.put('/carrito/${item['id_item_intencion_compra']}/seleccion', {'estado': estado}, auth: true);
    if (res.statusCode == 200) {
      _load();
    } else {
      if (mounted) {
        setState(() {
          item['es_seleccionado'] = !estado ? 1 : 0;
        });
      }
    }
  }

  Future<void> _toggleSeleccionarTodos(bool seleccionar) async {
    setState(() => _loading = true);
    final items = (_data?['todosLosItems'] as List?) ?? [];
    final List<Future> requests = [];
    for (var item in items) {
       bool esSel = ApiClient.parseBool(item['es_seleccionado']);
       final itemData = item['item'] as Map? ?? {};
       final isServicio = (itemData['id_categoria_item'] == 29);
       final estadoSolicitud = item['estado_solicitud']?.toString();
       if (isServicio && estadoSolicitud != 'aprobada') {
         continue; // Saltar servicios no aprobados
       }
       if (esSel != seleccionar) {
          requests.add(ApiClient.put('/carrito/${item['id_item_intencion_compra']}/seleccion', {'estado': seleccionar}, auth: true));
       }
    }
    if (requests.isNotEmpty) {
      await Future.wait(requests);
    }
    _load();
  }

  Future<void> _gestionarSolicitudServicio(Map item, String? estadoActual) async {
    final itemData = item['item'] as Map? ?? {};
    final int itemId = int.tryParse(itemData['id_item'].toString()) ?? 0;

    if (estadoActual == 'pendiente_aprobacion') {
      showDialog(
        context: context,
        builder: (_) => AlertDialog(
          title: const Text('⏳ Solicitud en espera'),
          content: const Text(
            'Ya has enviado la solicitud de aprobación para este servicio. '
            'Debes esperar a que el proveedor responda antes de proceder al pago.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Entendido'),
            ),
          ],
        ),
      );
      return;
    }

    // Si está aprobada, se puede seleccionar de manera normal
    if (estadoActual == 'aprobada') {
      return;
    }

    // Si es null o rechazada, mostrar selector de fecha
    final DateTime? fechaSel = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      helpText: 'SELECCIONA LA FECHA DEL SERVICIO',
    );

    if (fechaSel == null) return;

    final String fechaStr = "${fechaSel.year}-${fechaSel.month.toString().padLeft(2, '0')}-${fechaSel.day.toString().padLeft(2, '0')}";

    if (!mounted) return;

    final bool? confirmar = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Solicitar aprobación'),
        content: Text(
          '¿Deseas enviar la solicitud de aprobación al proveedor para la fecha: $fechaStr?\n\n'
          'El proveedor de este servicio debe aprobar la fecha antes de realizar el pago.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
            child: const Text('Enviar solicitud', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirmar != true) return;

    setState(() => _loading = true);

    try {
      final res = await ApiClient.post('/solicitudes-servicio/enviar', {
        'id_item': itemId,
        'fecha_servicio': fechaStr,
      }, auth: true);

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(body['message'] ?? 'Solicitud enviada con éxito.'),
            backgroundColor: Colors.green,
          ));
        }
      } else {
        final body = jsonDecode(res.body);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(body['message'] ?? 'Error al enviar solicitud.'),
            backgroundColor: Colors.red,
          ));
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Error de conexión al enviar la solicitud.'),
          backgroundColor: Colors.red,
        ));
      }
    } finally {
      _load();
    }
  }

  Future<bool> _confirmar(String titulo, String msg) async {
    return await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: Text(titulo),
        content: Text(msg),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancelar')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Confirmar', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    ) ?? false;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: const Text('Carrito'),
        actions: [
          if (_data != null && (_data!['todosLosItems'] as List).isNotEmpty)
            _vaciando
                ? const Padding(padding: EdgeInsets.all(12), child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.red)))
                : TextButton(
                    onPressed: _vaciar,
                    child: const Text('Vaciar', style: TextStyle(color: Colors.red)),
                  ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : !_loggedIn
              ? _notLoggedIn()
              : _buildCarrito(),
    );
  }

  Widget _notLoggedIn() => Center(
    child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
      const Icon(Icons.shopping_cart_outlined, size: 64, color: Colors.grey),
      const SizedBox(height: 16),
      const Text('Inicia sesión para ver tu carrito', style: TextStyle(color: kTextGray, fontSize: 15)),
      const SizedBox(height: 16),
      ElevatedButton(
        onPressed: () async {
          await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
          _load();
        },
        child: const Text('Iniciar sesión', style: TextStyle(color: Colors.white)),
      ),
    ]),
  );

  Widget _buildCarrito() {
    final todosLosItems = (_data?['todosLosItems'] as List?) ?? [];
    if (todosLosItems.isEmpty) {
      return Center(
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          const Icon(Icons.shopping_cart_outlined, size: 64, color: Colors.grey),
          const SizedBox(height: 16),
          const Text('Tu carrito está vacío', style: TextStyle(color: kTextGray, fontSize: 15)),
        ]),
      );
    }

    final carritos = _data?['carritos'] as List? ?? [];
    
    // Agrupar items por tipo usando los carritos si es posible, o filtrando todosLosItems
    final Map<String, dynamic>? carritoProducto = carritos.cast<Map<String,dynamic>?>().firstWhere((c) => c?['tipo'] == 'producto', orElse: () => null);
    final Map<String, dynamic>? carritoServicio = carritos.cast<Map<String,dynamic>?>().firstWhere((c) => c?['tipo'] == 'servicio', orElse: () => null);

    final itemsProducto = carritoProducto?['items_intencion_compra'] as List? ?? [];
    final itemsServicio = carritoServicio?['items_intencion_compra'] as List? ?? [];

    final totales = _data?['totales'] ?? {};
    final double totalArticulos = double.tryParse((totales['total_articulos'] ?? 0).toString()) ?? 0.0;
    final double totalDescuento = double.tryParse((totales['total_descuento'] ?? 0).toString()) ?? 0.0;
    final double totalImpuestos = double.tryParse((totales['total_impuestos'] ?? 0).toString()) ?? 0.0;
    final double totalEstimado = double.tryParse((totales['total_estimado'] ?? 0).toString()) ?? 0.0;
    final double envio = 0.0; // Todo: integrar calculo de envio
    final double granTotal = totalEstimado + totalImpuestos + envio;

    // Verificar si todos están seleccionados (para servicios, solo consideramos los aprobados)
    bool todosSeleccionados = todosLosItems.isNotEmpty && todosLosItems.every((i) {
      final itemData = i['item'] as Map? ?? {};
      final isServicio = (itemData['id_categoria_item'] == 29);
      if (isServicio) {
        return i['estado_solicitud']?.toString() != 'aprobada' || ApiClient.parseBool(i['es_seleccionado']);
      }
      return ApiClient.parseBool(i['es_seleccionado']);
    });
    
    int totalSeleccionadosProductos = itemsProducto.where((i) => ApiClient.parseBool(i['es_seleccionado'])).length;
    int totalSeleccionadosServicios = itemsServicio.where((i) => ApiClient.parseBool(i['es_seleccionado'])).length;

    return Column(children: [
      Expanded(
        child: RefreshIndicator(
          color: kPrimary,
          onRefresh: _load,
          child: ListView(
            padding: const EdgeInsets.all(12),
            children: [
              // Checkbox Seleccionar todos
              Row(
                children: [
                  Checkbox(
                    value: todosSeleccionados,
                    activeColor: kPrimary,
                    onChanged: (val) => _toggleSeleccionarTodos(val ?? false),
                  ),
                  const Text('Seleccionar todos', style: TextStyle(fontWeight: FontWeight.w500)),
                ],
              ),
              const SizedBox(height: 12),

              // Sección Productos
              if (itemsProducto.isNotEmpty) ...[
                Row(
                  children: [
                    const Icon(Icons.inventory_2_outlined, color: kPrimary, size: 20),
                    const SizedBox(width: 8),
                    Text('Productos (${itemsProducto.length})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: kTextDark)),
                  ],
                ),
                const SizedBox(height: 8),
                ...itemsProducto.map((item) => _buildItemCard(item, esServicio: false)).toList(),
                const SizedBox(height: 24),
              ],

              // Sección Servicios
              if (itemsServicio.isNotEmpty) ...[
                Row(
                  children: [
                    const Icon(Icons.star_outline, color: kSecondary, size: 20),
                    const SizedBox(width: 8),
                    Text('Servicios / Talentos (${itemsServicio.length})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: kTextDark)),
                  ],
                ),
                const SizedBox(height: 8),
                ...itemsServicio.map((item) => _buildItemCard(item, esServicio: true)).toList(),
                const SizedBox(height: 24),
              ],
              Container(
                color: kPrimary,
                padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
                child: Column(children: [
                  const Text(
                    '¿Quieres intercambiar o vender un producto?\n¡Hazlo con nosotros!',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.white, fontSize: 16),
                  ),
                  const SizedBox(height: 16),
                  Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                    ElevatedButton(
                      onPressed: () => Navigator.push(context,
                          MaterialPageRoute(builder: (_) => const PublicarArticuloScreen())),
                      style: ElevatedButton.styleFrom(backgroundColor: kSecondary),
                      child: const Text('Vender', style: TextStyle(color: Colors.white)),
                    ),
                    const SizedBox(width: 12),
                    ElevatedButton(
                      onPressed: () => Navigator.push(context,
                          MaterialPageRoute(builder: (_) => const ItemsListScreen(tipo: 2))),
                      style: ElevatedButton.styleFrom(backgroundColor: kSecondary),
                      child: const Text('Cambiar', style: TextStyle(color: Colors.white)),
                    ),
                  ]),
                ]),
              ),
              const FooterWidget(),
            ],
          ),
        ),
      ),
      
      // Resumen del Pedido (Bottom Sheet fijo)
      Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 8, offset: const Offset(0, -2))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Resumen del Pedido', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('Total de artículos:', style: TextStyle(color: kTextGray)),
              Text(totalArticulos.toStringAsFixed(2)),
            ]),
            const SizedBox(height: 4),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('Descuento:', style: TextStyle(color: kTextGray)),
              Text('-${totalDescuento.toStringAsFixed(2)}', style: const TextStyle(color: Colors.red)),
            ]),
            const SizedBox(height: 4),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('Envío:', style: TextStyle(color: kTextGray)),
              Text('RD\$ ${envio.toStringAsFixed(2)}', style: const TextStyle(color: kTextGray)),
            ]),
            const SizedBox(height: 4),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('Impuestos:', style: TextStyle(color: kTextGray)),
              Text('RD\$ ${totalImpuestos.toStringAsFixed(2)}', style: const TextStyle(color: kTextGray)),
            ]),
            const Divider(height: 24),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('Total estimado:', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              Text('RD\$ ${granTotal.toStringAsFixed(2)}', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: kPrimary)),
            ]),
            const SizedBox(height: 16),
            const Text('¿Qué deseas pagar?', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                icon: const Icon(Icons.check_circle_outline, color: Colors.white, size: 18),
                onPressed: totalSeleccionadosProductos > 0 ? () {
                  Navigator.push(context, MaterialPageRoute(
                    builder: (_) => CheckoutScreen(carrito: carritoProducto!),
                  ));
                } : null,
                style: ElevatedButton.styleFrom(
                  backgroundColor: kPrimary,
                  disabledBackgroundColor: Colors.grey.shade300,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
                label: Text('Pagar Productos ($totalSeleccionadosProductos)', style: const TextStyle(color: Colors.white, fontSize: 14)),
              ),
            ),
            const SizedBox(height: 8),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                icon: Icon(
                  totalSeleccionadosServicios > 0 ? Icons.payment : Icons.access_time,
                  color: totalSeleccionadosServicios > 0 ? Colors.white : kTextGray,
                  size: 18,
                ),
                onPressed: totalSeleccionadosServicios > 0 ? () {
                  Navigator.push(context, MaterialPageRoute(
                    builder: (_) => CheckoutScreen(carrito: carritoServicio!),
                  ));
                } : null,
                style: ElevatedButton.styleFrom(
                  backgroundColor: kSecondary,
                  disabledBackgroundColor: Colors.grey.shade100,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
                label: Text(
                  totalSeleccionadosServicios > 0
                      ? 'Pagar Servicios aprobados ($totalSeleccionadosServicios)'
                      : 'Servicios pendientes de aprobación',
                  style: TextStyle(
                    color: totalSeleccionadosServicios > 0 ? Colors.white : kTextGray,
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          ]
        ),
      ),
    ]);
  }

  Widget _buildItemCard(Map item, {required bool esServicio}) {
    final itemData = item['item'] as Map? ?? {};
    final double valor = double.tryParse((itemData['valor'] ?? 0).toString()) ?? 0.0;
    final int cantidad = int.tryParse((item['cantidad'] ?? 1).toString()) ?? 1;
    final double descuento = double.tryParse((item['descuento'] ?? 0).toString()) ?? 0.0;
    
    // Categoría badge
    final catNombre = itemData['categoria']?['nombre'] ?? '';

    final String? imgUrl = itemData['image_url']?.toString();

    final String? estadoSolicitud = item['estado_solicitud']?.toString();
    bool esSeleccionado = ApiClient.parseBool(item['es_seleccionado']);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 4)],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Checkbox
          Checkbox(
            value: esServicio ? (estadoSolicitud == 'aprobada' && esSeleccionado) : esSeleccionado,
            activeColor: esServicio ? kSecondary : kPrimary,
            onChanged: (val) {
              if (esServicio) {
                if (estadoSolicitud == 'aprobada') {
                  _marcarSeleccionado(item, val ?? false);
                } else {
                  _gestionarSolicitudServicio(item, estadoSolicitud);
                }
              } else {
                _marcarSeleccionado(item, val ?? false);
              }
            },
          ),
          
          // Image
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: ItemImage(
              item: itemData,
              imageUrl: imgUrl,
              width: 80,
              height: 80,
            ),
          ),
          const SizedBox(width: 12),

          // Details
          Expanded(
            child: Padding(
              padding: const EdgeInsets.only(top: 8, right: 8, bottom: 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Titulo y Categoria
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Text(itemData['item'] ?? '', maxLines: 2, overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: kTextDark)),
                      ),
                      if (catNombre.isNotEmpty && !esServicio)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(color: Colors.orange.shade100, borderRadius: BorderRadius.circular(4)),
                          child: Text(catNombre.toUpperCase(), style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.orange.shade800)),
                        ),
                    ],
                  ),
                  
                  if (esServicio) ...[
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        const Icon(Icons.star, color: kSecondary, size: 12),
                        const SizedBox(width: 4),
                        const Text('Servicio / Talento', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w500)),
                      ],
                    ),
                  ],

                  const SizedBox(height: 6),
                  
                  // Precio
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('RD\$ ${valor.toStringAsFixed(2)}', style: const TextStyle(fontSize: 14, color: kPrimary, fontWeight: FontWeight.bold)),
                      if (descuento > 0)
                        Text('Ahorras: \$${descuento.toStringAsFixed(2)}', style: const TextStyle(fontSize: 10, color: Colors.red)),
                    ],
                  ),

                  if (esServicio) ...[
                    const SizedBox(height: 4),
                    Text('Cantidad: $cantidad', style: const TextStyle(fontSize: 11, color: kTextGray)),
                    const SizedBox(height: 6),
                    InkWell(
                      onTap: () => _gestionarSolicitudServicio(item, estadoSolicitud),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: estadoSolicitud == 'pendiente_aprobacion'
                              ? Colors.amber.shade50
                              : estadoSolicitud == 'aprobada'
                                  ? Colors.green.shade50
                                  : estadoSolicitud == 'rechazada'
                                      ? Colors.red.shade50
                                      : Colors.grey.shade100,
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: estadoSolicitud == 'pendiente_aprobacion'
                                ? Colors.amber.shade200
                                : estadoSolicitud == 'aprobada'
                                    ? Colors.green.shade200
                                    : estadoSolicitud == 'rechazada'
                                        ? Colors.red.shade200
                                        : Colors.grey.shade300,
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              estadoSolicitud == 'pendiente_aprobacion'
                                  ? Icons.hourglass_empty
                                  : estadoSolicitud == 'aprobada'
                                      ? Icons.check_circle_outline
                                      : estadoSolicitud == 'rechazada'
                                          ? Icons.error_outline
                                          : Icons.add_circle_outline,
                              size: 14,
                              color: estadoSolicitud == 'pendiente_aprobacion'
                                  ? Colors.amber.shade800
                                  : estadoSolicitud == 'aprobada'
                                      ? Colors.green.shade800
                                      : estadoSolicitud == 'rechazada'
                                          ? Colors.red.shade800
                                          : Colors.grey.shade700,
                            ),
                            const SizedBox(width: 6),
                            Flexible(
                              child: Text(
                                estadoSolicitud == 'pendiente_aprobacion'
                                    ? 'Solicitud pendiente de aprobación'
                                    : estadoSolicitud == 'aprobada'
                                        ? 'Aprobado — puedes proceder al pago'
                                        : estadoSolicitud == 'rechazada'
                                            ? 'Solicitud rechazada (pulsar para reenviar)'
                                            : 'Marca para solicitar aprobación al proveedor',
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: estadoSolicitud == 'pendiente_aprobacion'
                                      ? Colors.amber.shade900
                                      : estadoSolicitud == 'aprobada'
                                          ? Colors.green.shade900
                                          : estadoSolicitud == 'rechazada'
                                              ? Colors.red.shade900
                                              : Colors.grey.shade800,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],

                  const SizedBox(height: 12),

                  // Acciones inferiores
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      // Botón negociar
                      if (itemData['intercambio'] == 1 || esServicio)
                        ElevatedButton.icon(
                          onPressed: () {
                            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Ir a negociar')));
                          },
                          icon: const Icon(Icons.handshake, color: Colors.white, size: 14),
                          label: const Text('Negociar', style: TextStyle(color: Colors.white, fontSize: 11)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: kSecondary,
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 0),
                            minimumSize: const Size(0, 28),
                          ),
                        )
                      else 
                        const SizedBox(), // Espacio vacío

                      Row(
                        children: [
                          if (!esServicio) ...[
                            // Controles de cantidad
                            Container(
                              height: 26,
                              decoration: BoxDecoration(
                                color: Colors.grey.shade100,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Row(
                                children: [
                                  InkWell(
                                    onTap: () => _actualizarCantidad(item['id_item_intencion_compra'], 'decrementar'),
                                    child: const Padding(padding: EdgeInsets.symmetric(horizontal: 8), child: Text('-', style: TextStyle(fontWeight: FontWeight.bold))),
                                  ),
                                  Container(color: Colors.white, padding: const EdgeInsets.symmetric(horizontal: 8), alignment: Alignment.center, child: Text('$cantidad', style: const TextStyle(fontSize: 12))),
                                  InkWell(
                                    onTap: () => _actualizarCantidad(item['id_item_intencion_compra'], 'incrementar'),
                                    child: const Padding(padding: EdgeInsets.symmetric(horizontal: 8), child: Text('+', style: TextStyle(fontWeight: FontWeight.bold))),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 8),
                          ],
                          
                          // Eliminar
                          InkWell(
                            onTap: () => _eliminar(itemData['id_item']),
                            child: const Text('Eliminar', style: TextStyle(color: Colors.red, fontSize: 11, fontWeight: FontWeight.w500)),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
