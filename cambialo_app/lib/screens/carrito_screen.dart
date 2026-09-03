import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import '../widgets/item_image.dart';
import 'checkout_screen.dart';
import 'main_screen.dart';
import 'login_screen.dart';
import 'publicar_articulo_screen.dart';
import 'items_list_screen.dart';
import '../widgets/footer_widget.dart';
import '../widgets/negociaciones_modal.dart';
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
  
  double _costoEnvio = 0.0;
  String _diasEnvio = "";
  bool _calculandoEnvio = false;

  @override
  void initState() {
    super.initState();
    ApiClient.cartCountNotifier.addListener(_onCartCountChanged);
    _load();
  }

  @override
  void didUpdateWidget(covariant CarritoScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    _load();
  }

  @override
  void dispose() {
    ApiClient.cartCountNotifier.removeListener(_onCartCountChanged);
    super.dispose();
  }

  void _onCartCountChanged() {
    if (mounted) {
      _load();
    }
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
      // Intentar cargar desde caché rápido para pintar UI de inmediato
      if (ApiClient.hasCache('/carrito', auth: true)) {
        final cached = ApiClient.getCache('/carrito', auth: true);
        if (cached != null) {
          final parsed = jsonDecode(cached);
          setState(() { _data = parsed; _loading = false; });
          try {
            final todosItems = (parsed['todosLosItems'] as List?) ?? [];
            ApiClient.cartCountNotifier.value = todosItems.length;
          } catch (_) {}
        }
      }

      // Consulta de fondo para datos frescos (sin bloquear si ya hay cache)
      final res = await ApiClient.get('/carrito', auth: true, useCache: false);
      if (!mounted) return;
      if (res.statusCode == 200) {
        final parsed = jsonDecode(res.body);
        setState(() { _data = parsed; _loading = false; });
        try {
          final todosItems = (parsed['todosLosItems'] as List?) ?? [];
          ApiClient.cartCountNotifier.value = todosItems.length;
        } catch (_) {}
        // Actualizar cache manualmente para la próxima vez
        ApiClient.setCache('/carrito', res.body, auth: true);
        
        // Recalcular envío después de cargar
        _recalcularEnvio();
      } else {
        setState(() => _loading = false);
      }
    } catch (e) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _recalcularEnvio() async {
    final todosItems = (_data?['todosLosItems'] as List?) ?? [];
    final totales = _data?['totales'] as Map<String, dynamic>? ?? {};
    final totalEstimado = double.tryParse(totales['total_estimado']?.toString() ?? '0') ?? 0.0;
    final municipio = _data?['municipioDefault']?.toString() ?? '';

    if (municipio.isEmpty || totalEstimado <= 0) {
      setState(() { _costoEnvio = 0.0; _diasEnvio = ""; });
      return;
    }

    double maxPeso = 0;
    double maxAlto = 0;
    double maxAncho = 0;
    double maxProfundo = 0;

    for (final itemIntencion in todosItems) {
      if (itemIntencion['es_seleccionado'] == 1 || itemIntencion['es_seleccionado'] == true) {
        final item = itemIntencion['item'];
        if (item != null) {
          final cantidad = int.tryParse(itemIntencion['cantidad']?.toString() ?? '1') ?? 1;
          final peso = double.tryParse(item['peso_lbs']?.toString() ?? '0') ?? 0;
          final alto = double.tryParse(item['alto_cm']?.toString() ?? '0') ?? 0;
          final ancho = double.tryParse(item['ancho_cm']?.toString() ?? '0') ?? 0;
          final profundo = double.tryParse(item['profundo_cm']?.toString() ?? '0') ?? 0;

          maxPeso += peso * cantidad;
          if (alto * cantidad > maxAlto) maxAlto = alto * cantidad;
          if (ancho > maxAncho) maxAncho = ancho;
          if (profundo > maxProfundo) maxProfundo = profundo;
        }
      }
    }

    setState(() => _calculandoEnvio = true);
    try {
      final qs = '?pueblo=${Uri.encodeComponent(municipio)}&valor_articulo=$totalEstimado&peso_lbs=$maxPeso&alto_cm=$maxAlto&ancho_cm=$maxAncho&profundo_cm=$maxProfundo';
      final res = await ApiClient.get('/delivery/calcular$qs'); // It uses API prefix by default
      if (res.statusCode == 200) {
        final d = jsonDecode(res.body);
        final costo = double.tryParse(d['costo_envio_total']?.toString() ?? '0') ?? 0.0;
        if (mounted) {
          setState(() {
            _costoEnvio = costo;
            if (d['dias_habiles'] != null) {
              _diasEnvio = '🚚 Entrega estimada: ~${d['dias_habiles']} días hábiles';
            } else {
              _diasEnvio = "";
            }
          });
        }
      }
    } catch (e) {
      if (mounted) setState(() { _costoEnvio = 0.0; _diasEnvio = ""; });
    } finally {
      if (mounted) setState(() => _calculandoEnvio = false);
    }
  }

  Future<void> _eliminar(int idItem) async {
    // Actualizar badge inmediatamente
    if (ApiClient.cartCountNotifier.value > 0) {
      ApiClient.cartCountNotifier.value--;
    }
    await ApiClient.delete('/carrito/$idItem', auth: true);
    ApiClient.clearCache('/carrito');
    _load();
  }

  Future<void> _vaciar() async {
    final ok = await _confirmar('¿Vaciar carrito?', '¿Seguro que deseas eliminar todos los artículos?');
    if (!ok) return;
    setState(() => _vaciando = true);
    // Vaciar badge inmediatamente
    ApiClient.cartCountNotifier.value = 0;
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
       final isServicio = (itemData['id_categoria_item'].toString() == '29');
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

    if (['aprobada', 'rechazada', 'pendiente'].contains(estadoActual)) {
      await showDialog(
        context: context,
        builder: (BuildContext dialogContext) => AlertDialog(
          title: const Text('Estado de tu solicitud'),
          content: Text('La solicitud para este servicio se encuentra: ${(estadoActual ?? '').toUpperCase()}'),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
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
      builder: (BuildContext dialogContext) => AlertDialog(
        title: const Text('Solicitar aprobación'),
        content: Text(
          '¿Deseas enviar la solicitud de aprobación al proveedor para la fecha: $fechaStr?\n\n'
          'El proveedor de este servicio debe aprobar la fecha antes de realizar el pago.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(dialogContext, true),
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
      builder: (BuildContext dialogContext) => AlertDialog(
        title: Text(titulo),
        content: Text(msg),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('Cancelar')),
          ElevatedButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Confirmar', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    ) ?? false;
  }

  @override
  Widget build(BuildContext context) {
    final itemCount = (_data?['todosLosItems'] as List?)?.length ?? 0;
    return Scaffold(
      backgroundColor: Colors.grey.shade100,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        title: Text(
          'Carrito ($itemCount)',
          style: const TextStyle(color: Colors.black, fontWeight: FontWeight.bold, fontSize: 18),
        ),
        leading: Navigator.canPop(context)
            ? IconButton(
                icon: const Icon(Icons.arrow_back, color: Colors.black),
                onPressed: () => Navigator.pop(context),
              )
            : (itemCount > 0
                ? InkWell(
                    onTap: () {
                      final todosLosItems = (_data?['todosLosItems'] as List?) ?? [];
                      final todosSeleccionados = todosLosItems.isNotEmpty && todosLosItems.every((i) {
                        if (i['item']?['id_categoria_item'].toString() == '29' && i['estado_solicitud'] != 'aprobada') return true; 
                        return ApiClient.parseBool(i['es_seleccionado']);
                      });
                      _toggleSeleccionarTodos(!todosSeleccionados);
                    },
                    child: Row(
                      children: [
                        const SizedBox(width: 12),
                        Builder(
                          builder: (context) {
                            final todosLosItems = (_data?['todosLosItems'] as List?) ?? [];
                            final todosSeleccionados = todosLosItems.isNotEmpty && todosLosItems.every((i) {
                              if (i['item']?['id_categoria_item'].toString() == '29' && i['estado_solicitud'] != 'aprobada') return true; 
                              return ApiClient.parseBool(i['es_seleccionado']);
                            });
                            return Icon(todosSeleccionados ? Icons.check_circle : Icons.circle_outlined, 
                              color: todosSeleccionados ? kPrimary : Colors.grey.shade400, size: 22);
                          }
                        ),
                        const SizedBox(width: 4),
                        const Text('Todos', style: TextStyle(color: Colors.black, fontSize: 13, fontWeight: FontWeight.w500)),
                      ],
                    ),
                  )
                : null),
        leadingWidth: 100,
        actions: [
          if (_data != null && (_data!['todosLosItems'] as List).isNotEmpty)
            _vaciando
                ? const Padding(padding: EdgeInsets.all(12), child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.red)))
                : IconButton(
                    icon: const Icon(Icons.delete_outline, color: Colors.black, size: 26),
                    onPressed: _vaciar,
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
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.orange.shade50,
                  shape: BoxShape.circle,
                ),
                child: Icon(Icons.shopping_cart_outlined, size: 64, color: Colors.orange.shade400),
              ),
              const SizedBox(height: 16),
              const Text(
                'Tu carrito está vacío',
                style: TextStyle(color: kTextDark, fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              const Text(
                'Explora nuestros productos y servicios para agregar lo que te guste.',
                textAlign: TextAlign.center,
                style: TextStyle(color: kTextGray, fontSize: 14),
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: () {
                  if (Navigator.canPop(context)) {
                    Navigator.pop(context);
                  } else {
                    MainScreen.switchTab(context, 2);
                  }
                },
                icon: const Icon(Icons.storefront, color: Colors.white),
                label: const Text('Explorar productos', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: kPrimary,
                  padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                  elevation: 2,
                ),
              ),
            ],
          ),
        ),
      );
    }

    final carritos = _data?['carritos'] as List? ?? [];
    
    // Para el botón de checkout (CheckoutScreen) necesitamos el objeto carrito
    final Map<String, dynamic>? carritoProducto = carritos.cast<Map<String,dynamic>?>().firstWhere((c) => c?['tipo'] == 'producto', orElse: () => null);
    final Map<String, dynamic>? carritoServicio = carritos.cast<Map<String,dynamic>?>().firstWhere((c) => c?['tipo'] == 'servicio', orElse: () => null);

    // Filtrar todosLosItems en lugar de usar solo el primer carrito (para soportar múltiples vendedores)
    final List<dynamic> itemsProducto = todosLosItems.where((i) {
      final itemData = i['item'] as Map? ?? {};
      return itemData['id_categoria_item'].toString() != '29';
    }).toList();
    
    final List<dynamic> itemsServicio = todosLosItems.where((i) {
      final itemData = i['item'] as Map? ?? {};
      return itemData['id_categoria_item'].toString() == '29';
    }).toList();

    final totales = _data?['totales'] ?? {};
    final double totalArticulos = double.tryParse((totales['total_articulos'] ?? 0).toString()) ?? 0.0;
    final double totalDescuento = double.tryParse((totales['total_descuento'] ?? 0).toString()) ?? 0.0;
    final double totalImpuestos = double.tryParse((totales['total_impuestos'] ?? 0).toString()) ?? 0.0;
    final double totalEstimado = double.tryParse((totales['total_estimado'] ?? 0).toString()) ?? 0.0;
    final numTotalItems = (totales['total_articulos'] as num?)?.toInt() ?? 0;
    
    // El granTotal incluye impuestos pero ahora le sumamos _costoEnvio
    final double granTotal = totalEstimado + totalImpuestos + _costoEnvio;

    // Verificar si todos están seleccionados (para servicios, solo consideramos los aprobados)
    bool todosSeleccionados = todosLosItems.isNotEmpty && todosLosItems.every((i) {
      final itemData = i['item'] as Map? ?? {};
      final isServicio = (itemData['id_categoria_item'].toString() == '29');
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
              const SizedBox(height: 12),
            ],
          ),
        ),
      ),
      
      // Barra inferior Temu Style
      Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: Colors.grey.shade200)),
        ),
        child: SafeArea(
          top: false,
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start, // Alineado a la izquierda
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        const Padding(
                          padding: EdgeInsets.only(bottom: 2),
                          child: Text('Total: ', style: TextStyle(fontSize: 12)),
                        ),
                        Flexible(
                          child: Text('\$${granTotal.toStringAsFixed(2)}', 
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: kPrimary),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                    if (totalImpuestos > 0)
                      Text('Impuestos: \$${totalImpuestos.toStringAsFixed(2)}', style: const TextStyle(color: Colors.grey, fontSize: 11)),
                    if (_costoEnvio > 0)
                      Text('Envío: \$${_costoEnvio.toStringAsFixed(2)}', style: const TextStyle(color: Colors.grey, fontSize: 11)),
                    if (_diasEnvio.isNotEmpty)
                      Text(_diasEnvio, style: const TextStyle(color: kPrimary, fontSize: 10)),
                    if (totalDescuento > 0)
                      Text('Ahorras \$${totalDescuento.toStringAsFixed(2)}', style: const TextStyle(color: kPrimary, fontSize: 11)),
                  ],
                ),
              ),
              const SizedBox(width: 12),
              
              Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  // Botón Productos
                  if (itemsProducto.isNotEmpty || itemsServicio.isEmpty)
                    ElevatedButton(
                      onPressed: totalSeleccionadosProductos > 0 ? () {
                        Navigator.push(context, MaterialPageRoute(builder: (_) => CheckoutScreen(carrito: carritoProducto!)));
                      } : null,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: kPrimary,
                        disabledBackgroundColor: kPrimary.withOpacity(0.5),
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                        elevation: 0,
                      ),
                      child: Text(
                        totalSeleccionadosProductos > 0 ? 'Pagar Productos ($totalSeleccionadosProductos)' : 'Hacer pedido (0)', 
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)
                      ),
                    ),
                  
                  if (itemsProducto.isNotEmpty && itemsServicio.isNotEmpty)
                    const SizedBox(height: 8),

                  // Botón Servicios
                  if (itemsServicio.isNotEmpty)
                    ElevatedButton(
                      onPressed: totalSeleccionadosServicios > 0 ? () {
                        Navigator.push(context, MaterialPageRoute(builder: (_) => CheckoutScreen(carrito: carritoServicio!)));
                      } : null,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: kPrimary,
                        disabledBackgroundColor: kPrimary.withOpacity(0.5),
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                        elevation: 0,
                      ),
                      child: Text(
                        totalSeleccionadosServicios > 0 ? 'Pago Servicio ($totalSeleccionadosServicios)' : 'Pago Servicio (0)', 
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)
                      ),
                    ),
                ],
              ),
            ],
          ),
        ),
      ),
    ]);
  }


  Widget _buildPillTab(String text, bool isSelected) {
    return Container(
      margin: const EdgeInsets.only(right: 8),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      decoration: BoxDecoration(
        color: isSelected ? Colors.transparent : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: isSelected ? Colors.black : Colors.grey.shade300),
      ),
      child: Text(text, style: TextStyle(color: isSelected ? Colors.black : Colors.grey.shade700, fontWeight: isSelected ? FontWeight.bold : FontWeight.normal, fontSize: 13)),
    );
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
          // Checkbox circular estilo Temu
          InkWell(
            onTap: () {
               final val = !esSeleccionado;
               if (esServicio) {
                 if (estadoSolicitud == 'aprobada') {
                   _marcarSeleccionado(item, val);
                 } else {
                   _gestionarSolicitudServicio(item, estadoSolicitud);
                 }
               } else {
                 _marcarSeleccionado(item, val);
               }
            },
            child: Padding(
              padding: const EdgeInsets.only(left: 12, top: 32, right: 8, bottom: 32),
              child: Icon(
                esServicio 
                  ? (estadoSolicitud == 'aprobada' && esSeleccionado ? Icons.check_circle : Icons.circle_outlined)
                  : (esSeleccionado ? Icons.check_circle : Icons.circle_outlined),
                color: esServicio 
                  ? (estadoSolicitud == 'aprobada' && esSeleccionado ? kSecondary : Colors.grey.shade400)
                  : (esSeleccionado ? kPrimary : Colors.grey.shade400),
                size: 22,
              ),
            ),
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
                            showModalBottomSheet(
                              context: context,
                              isScrollControlled: true,
                              backgroundColor: Colors.transparent,
                              builder: (_) => NegociacionesModal(
                                itemId: int.tryParse(itemData['id_item']?.toString() ?? '0') ?? 0,
                                itemName: itemData['item'] ?? 'Artículo',
                              ),
                            );
                          },
                          icon: const Icon(Icons.handshake, color: Colors.white, size: 14),
                          label: const Text('Negociar', style: TextStyle(color: Colors.white, fontSize: 11)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: kPrimary,
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
