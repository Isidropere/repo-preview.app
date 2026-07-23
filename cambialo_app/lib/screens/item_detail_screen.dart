import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import '../widgets/item_image.dart';
import '../widgets/full_screen_image_viewer.dart';
import 'propuesta_intercambio_screen.dart';
import 'mis_intercambios_screen.dart';
import 'login_screen.dart';
import 'publicar_articulo_screen.dart';
import 'items_list_screen.dart';
import '../widgets/footer_widget.dart';

/// Detalle de producto — fiel al diseño web de Cambialord
class ItemDetailScreen extends StatefulWidget {
  final int itemId;
  const ItemDetailScreen({super.key, required this.itemId});
  @override
  State<ItemDetailScreen> createState() => _ItemDetailScreenState();
}

class _ItemDetailScreenState extends State<ItemDetailScreen> {
  Map?  _item;
  bool  _loading      = true;
  bool  _addingToCart = false;
  int   _imgIndex     = 0;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final res = await ApiClient.get('/items/${widget.itemId}', auth: true);
    if (res.statusCode == 200) {
      setState(() { _item = jsonDecode(res.body); _loading = false; });
    } else {
      setState(() => _loading = false);
    }
  }

  Future<void> _addToCart() async {
    final loggedIn = await AuthService.isLoggedIn();
    if (!loggedIn) {
      if (!mounted) return;
      final result = await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
      if (result != true) return;
    }
    setState(() => _addingToCart = true);
    
    // Actualización optimista rápida del badge
    ApiClient.cartCountNotifier.value++;

    final res = await ApiClient.post('/carrito/agregar',
        {'id_item': widget.itemId, 'cantidad': 1}, auth: true);
    setState(() => _addingToCart = false);
    
    if (!mounted) return;
    if (res.statusCode == 200) {
      try {
        final data = jsonDecode(res.body);
        if (data['cart_count'] != null) {
          ApiClient.cartCountNotifier.value = int.tryParse(data['cart_count'].toString()) ?? ApiClient.cartCountNotifier.value;
        }
      } catch (_) {}
    } else {
      // Revertir en caso de error
      if (ApiClient.cartCountNotifier.value > 0) {
        ApiClient.cartCountNotifier.value--;
      }
    }
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res.statusCode == 200 ? '¡Agregado al carrito!' : 'Error al agregar'),
      backgroundColor: res.statusCode == 200 ? kPrimary : Colors.red,
    ));
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator(color: kPrimary)));
    if (_item == null) return const Scaffold(body: Center(child: Text('Producto no encontrado')));

    final imagenes = _item!['imagenes'] as List? ?? [];
    final condVal = int.tryParse(_item!['condicion']?.toString() ?? '') ?? 0;
    final condicion = switch (condVal) {
      1 => 'Nuevo',
      2 => 'Como nuevo',
      _ => 'Usado',
    };
    final tipoTransRaw = int.tryParse(_item!['tipo_trans'].toString()) ?? 0;
    final esVenta       = tipoTransRaw == 1;
    final esIntercambio = tipoTransRaw == 2;
    final esMixto       = tipoTransRaw == 3;
    final tipoTrans     = esVenta ? 'Venta' : (esMixto ? 'Venta+Intercambio' : 'Intercambio');

    final yaEnCarrito = _item!['ya_en_carrito'] == true;
    final conNegociacionActiva = _item!['con_negociacion_activa'] == true;

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: Text(_item!['item'] ?? '', overflow: TextOverflow.ellipsis),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

          // Galería de imágenes con indicador de página
          Stack(children: [
            GestureDetector(
              onTap: imagenes.isEmpty
                  ? null
                  : () {
                      final urls = imagenes
                          .map((img) => ApiClient.fixImageUrl(img['image_url'] as String?))
                          .toList();
                      Navigator.push(
                        context,
                        PageRouteBuilder(
                          opaque: false,
                          barrierDismissible: true,
                          barrierColor: Colors.black.withOpacity(0.5),
                          pageBuilder: (context, _, __) => FullScreenImageViewer(
                            imageUrls: urls,
                            initialIndex: _imgIndex,
                          ),
                        ),
                      );
                    },
              child: SizedBox(
                height: 280,
                child: imagenes.isEmpty
                    ? ItemImage(item: _item!, width: double.infinity, height: 280)
                    : PageView.builder(
                        itemCount: imagenes.length,
                        onPageChanged: (i) => setState(() => _imgIndex = i),
                        itemBuilder: (_, i) {
                          return ItemImage(
                            item: _item!,
                            imageUrl: imagenes[i]['image_url'],
                            width: double.infinity,
                            height: 280,
                          );
                        },
                      ),
              ),
            ),
            if (imagenes.length > 1)
              Positioned(
                bottom: 10, left: 0, right: 0,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(imagenes.length, (i) => Container(
                    width: 8, height: 8,
                    margin: const EdgeInsets.symmetric(horizontal: 3),
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: i == _imgIndex ? kPrimary : Colors.white.withOpacity(0.7),
                    ),
                  )),
                ),
              ),
          ]),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

              // Nombre
              Text(_item!['item'] ?? '',
                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: kTextDark)),
              const SizedBox(height: 8),

              // Precio + badges
              Row(children: [
                if (_item!['valor'] != null)
                  Text('RD\$ ${_item!['valor']}',
                      style: const TextStyle(fontSize: 20, color: kPrimary, fontWeight: FontWeight.bold)),
                const SizedBox(width: 12),
                _badge(
                  tipoTrans,
                  esVenta ? const Color(0xFF1D4ED8) : (esMixto ? Colors.deepPurple : const Color(0xFF15803D)),
                  esVenta ? const Color(0xFFEFF6FF) : (esMixto ? const Color(0xFFF3E8FF) : const Color(0xFFF0FDF4)),
                ),
                const SizedBox(width: 6),
                _badge(condicion, kTextGray, kBgGray),
              ]),
              const SizedBox(height: 16),

              // Ubicación
              if (_item!['usuario'] != null) ...[
                Row(children: [
                  const Icon(Icons.person_outline, size: 16, color: kTextGray),
                  const SizedBox(width: 4),
                  Text(
                    '${_item!['usuario']['nombres']} ${_item!['usuario']['apellidos']}',
                    style: TextStyle(fontSize: 13, color: kTextGray),
                  ),
                ]),
                const SizedBox(height: 8),
              ],

              // Descripción
              if (_item!['presentacion'] != null && _item!['presentacion'].toString().isNotEmpty) ...[
                const Divider(),
                const SizedBox(height: 8),
                const Text('Descripción',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: kTextDark)),
                const SizedBox(height: 6),
                Text(_item!['presentacion'],
                    style: TextStyle(fontSize: 14, color: kTextGray, height: 1.5)),
                const SizedBox(height: 16),
              ],

              const Divider(),
              const SizedBox(height: 16),

              // Botón agregar al carrito (solo para Venta o Mixto)
              if (esVenta || esMixto)
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: (yaEnCarrito || conNegociacionActiva || _addingToCart) ? null : _addToCart,
                    icon: _addingToCart
                        ? const SizedBox(width: 18, height: 18,
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : Icon(
                            yaEnCarrito ? Icons.check : (conNegociacionActiva ? Icons.lock_outline : Icons.shopping_cart_outlined),
                            color: Colors.white,
                          ),
                    label: Text(
                      yaEnCarrito
                          ? 'Ya en el carrito'
                          : (conNegociacionActiva ? 'Negociación activa' : 'Agregar al carrito'),
                      style: const TextStyle(fontSize: 15, color: Colors.white),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: (yaEnCarrito || conNegociacionActiva) ? Colors.grey : kSecondary,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                ),
              if (esVenta || esMixto) const SizedBox(height: 12),

              // Botón proponer intercambio (para Intercambio o Mixto)
              if (esIntercambio || esMixto)
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: (yaEnCarrito || conNegociacionActiva)
                        ? null
                        : () async {
                            final loggedIn = await AuthService.isLoggedIn();
                            if (!loggedIn) {
                              if (!mounted) return;
                              final result = await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
                              if (result != true) return;
                            }
                            Navigator.push(context, MaterialPageRoute(
                              builder: (_) => PropuestaIntercambioScreen(
                                receptorItemId:  int.tryParse(_item!['id_item']?.toString() ?? '') ?? 0,
                                nombreArticulo:  _item!['item'] ?? '',
                                idCategoriaItem: int.tryParse(_item!['id_categoria_item']?.toString() ?? '') ?? 0,
                              ),
                            ));
                          },
                    icon: Icon(
                      Icons.swap_horiz,
                      color: (yaEnCarrito || conNegociacionActiva) ? Colors.grey : kPrimary,
                    ),
                    label: Text(
                      yaEnCarrito
                          ? 'Ya en el carrito'
                          : (conNegociacionActiva ? 'Negociación activa' : 'Proponer intercambio'),
                      style: TextStyle(
                        color: (yaEnCarrito || conNegociacionActiva) ? Colors.grey : kPrimary,
                        fontSize: 15,
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: BorderSide(
                        color: (yaEnCarrito || conNegociacionActiva) ? Colors.grey.shade300 : kPrimary,
                      ),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                ),
              const SizedBox(height: 16),
              Center(
                child: TextButton.icon(
                  onPressed: () async {
                    final loggedIn = await AuthService.isLoggedIn();
                    if (!loggedIn) {
                      if (!mounted) return;
                      final result = await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
                      if (result != true) return;
                    }
                    if (!mounted) return;
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const MisIntercambiosScreen()));
                  },
                  icon: const Icon(Icons.swap_horiz, color: kPrimary, size: 16),
                  label: const Text('Ver mis intercambios',
                      style: TextStyle(color: kPrimary, fontSize: 13)),
                ),
              ),
            ]),
          ),
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
        ]),
      ),
    );
  }

  Widget _badge(String text, Color textColor, Color bgColor) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
    decoration: BoxDecoration(color: bgColor, borderRadius: BorderRadius.circular(4)),
    child: Text(text, style: TextStyle(fontSize: 11, color: textColor, fontWeight: FontWeight.w500)),
  );
}
