import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import '../widgets/item_image.dart';
import 'checkout_screen.dart';
import 'login_screen.dart';
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
      _load();
    } else {
      try {
        final msg = jsonDecode(res.body)['message'] ?? 'Error al actualizar cantidad';
        if(mounted){
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg), backgroundColor: Colors.red));
        }
      } catch (e) {
        if(mounted){
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Error al actualizar cantidad'), backgroundColor: Colors.red));
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
    for (var item in items) {
       bool esSel = item['es_seleccionado'] == 1 || item['es_seleccionado'] == true;
       if (esSel != seleccionar) {
          await ApiClient.put('/carrito/${item['id_item_intencion_compra']}/seleccion', {'estado': seleccionar}, auth: true);
       }
    }
    _load();
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
    final double totalEstimado = double.tryParse((totales['total_estimado'] ?? 0).toString()) ?? 0.0;
    final double envio = 0.0; // Todo: integrar calculo de envio
    final double granTotal = totalEstimado + envio;

    // Verificar si todos están seleccionados
    bool todosSeleccionados = todosLosItems.every((i) => i['es_seleccionado'] == 1 || i['es_seleccionado'] == true);
    
    int totalSeleccionadosProductos = itemsProducto.where((i) => i['es_seleccionado'] == 1 || i['es_seleccionado'] == true).length;

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
                  // Mismo comportamiento actual, pero enviando el carrito de productos
                  // checkout_screen.dart necesitará ajustarse luego para usar el carrito filtrado.
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
                icon: const Icon(Icons.access_time, color: kTextGray, size: 18),
                onPressed: null, // Los servicios dependen de aprobación
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.grey.shade100,
                  disabledBackgroundColor: Colors.grey.shade100,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
                label: const Text('Servicios pendientes de aprobación', style: TextStyle(color: kTextGray, fontSize: 14)),
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

    final String? imgUrl = itemData['image_url'] as String? ??
        ((itemData['imagenes'] != null && (itemData['imagenes'] as List).isNotEmpty)
            ? (itemData['imagenes'][0]['ruta']?.toString().startsWith('http') == true
                ? itemData['imagenes'][0]['ruta'].toString()
                : '${kBaseUrl.replaceAll('/api', '')}/${itemData['imagenes'][0]['ruta'].toString().trim().replaceAll(RegExp(r'^/'), '')}/${itemData['imagenes'][0]['nombre']}')
            : null);

    bool esSeleccionado = item['es_seleccionado'] == 1 || item['es_seleccionado'] == true;

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
            value: esSeleccionado,
            activeColor: kPrimary,
            onChanged: (val) => _marcarSeleccionado(item, val ?? false),
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
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(color: Colors.amber.shade50, borderRadius: BorderRadius.circular(4)),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.hourglass_empty, size: 10, color: Colors.amber.shade800),
                          const SizedBox(width: 4),
                          Text('Solicitud pendiente de aprobación', style: TextStyle(fontSize: 10, color: Colors.amber.shade800)),
                        ],
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
                            // Acción para negociar con el vendedor
                            // Navigator.push(context, MaterialPageRoute(builder: (_) => NegociacionDetalleScreen(negociacionId: ...)));
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
