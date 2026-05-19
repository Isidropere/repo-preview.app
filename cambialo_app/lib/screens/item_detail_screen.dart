import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import 'propuesta_intercambio_screen.dart';

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
    final res = await ApiClient.get('/items/${widget.itemId}');
    if (res.statusCode == 200) {
      setState(() { _item = jsonDecode(res.body); _loading = false; });
    } else {
      setState(() => _loading = false);
    }
  }

  Future<void> _addToCart() async {
    final loggedIn = await AuthService.isLoggedIn();
    if (!loggedIn) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Inicia sesión para agregar al carrito'),
        backgroundColor: Colors.red,
      ));
      return;
    }
    setState(() => _addingToCart = true);
    final res = await ApiClient.post('/carrito/agregar',
        {'id_item': widget.itemId, 'cantidad': 1}, auth: true);
    setState(() => _addingToCart = false);
    if (!mounted) return;
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
    final condicion = switch (_item!['condicion']) {
      1 => 'Nuevo',
      2 => 'Como nuevo',
      _ => 'Usado',
    };
    final tipoTrans = _item!['tipo_trans'] == 1 ? 'Venta' : 'Intercambio';

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: Text(_item!['item'] ?? '', overflow: TextOverflow.ellipsis),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          IconButton(icon: const Icon(Icons.shopping_cart_outlined, color: kPrimary), onPressed: () {}),
        ],
      ),
      body: SingleChildScrollView(
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

          // Galería de imágenes con indicador de página
          Stack(children: [
            SizedBox(
              height: 280,
              child: imagenes.isEmpty
                  ? Container(color: Colors.grey.shade100,
                      child: const Icon(Icons.image, size: 80, color: Colors.grey))
                  : PageView.builder(
                      itemCount: imagenes.length,
                      onPageChanged: (i) => setState(() => _imgIndex = i),
                      itemBuilder: (_, i) => CachedNetworkImage(
                        imageUrl: imagenes[i]['image_url'] ?? '',
                        fit: BoxFit.cover,
                        placeholder: (_, __) => Container(color: Colors.grey.shade100),
                        errorWidget: (_, __, ___) => Container(color: Colors.grey.shade100,
                            child: const Icon(Icons.image_not_supported, color: Colors.grey)),
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
                _badge(tipoTrans, tipoTrans == 'Venta' ? const Color(0xFF1D4ED8) : const Color(0xFF15803D),
                    tipoTrans == 'Venta' ? const Color(0xFFEFF6FF) : const Color(0xFFF0FDF4)),
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

              // Botón agregar al carrito
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: _addingToCart ? null : _addToCart,
                  icon: _addingToCart
                      ? const SizedBox(width: 18, height: 18,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Icon(Icons.shopping_cart_outlined, color: Colors.white),
                  label: const Text('Agregar al carrito',
                      style: TextStyle(fontSize: 15, color: Colors.white)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: kSecondary,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                ),
              ),
              const SizedBox(height: 12),

              // Botón negociar (intercambio)
              if (_item!['tipo_trans'] != 1)
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: () async {
                      final loggedIn = await AuthService.isLoggedIn();
                      if (!loggedIn) {
                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                          content: Text('Inicia sesión para proponer un intercambio'),
                          backgroundColor: Colors.red,
                        ));
                        return;
                      }
                      Navigator.push(context, MaterialPageRoute(
                        builder: (_) => PropuestaIntercambioScreen(
                          receptorItemId:  _item!['id_item'],
                          nombreArticulo:  _item!['item'] ?? '',
                          idCategoriaItem: _item!['id_categoria_item'],
                        ),
                      ));
                    },
                    icon: const Icon(Icons.swap_horiz, color: kPrimary),
                    label: const Text('Proponer intercambio',
                        style: TextStyle(color: kPrimary, fontSize: 15)),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: kPrimary),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                ),
            ]),
          ),
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
