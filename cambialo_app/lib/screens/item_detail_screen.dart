import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/api_client.dart';

class ItemDetailScreen extends StatefulWidget {
  final int itemId;
  const ItemDetailScreen({super.key, required this.itemId});
  @override
  State<ItemDetailScreen> createState() => _ItemDetailScreenState();
}

class _ItemDetailScreenState extends State<ItemDetailScreen> {
  Map? _item;
  bool _loading = true;
  bool _addingToCart = false;

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
    setState(() => _addingToCart = true);
    final res = await ApiClient.post('/carrito/agregar',
        {'id_item': widget.itemId, 'cantidad': 1}, auth: true);
    setState(() => _addingToCart = false);

    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res.statusCode == 200 ? 'Agregado al carrito' : 'Inicia sesión para agregar al carrito'),
      backgroundColor: res.statusCode == 200 ? const Color(0xFFF58634) : Colors.red,
    ));
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator(color: Color(0xFFF58634))));
    if (_item == null) return const Scaffold(body: Center(child: Text('Producto no encontrado')));

    final imagenes = _item!['imagenes'] as List? ?? [];
    final baseUrl = ApiClient.kBaseUrl.replaceAll('/api', '');

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: Text(_item!['item'] ?? '', overflow: TextOverflow.ellipsis),
        backgroundColor: const Color(0xFFF58634),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Imágenes
            SizedBox(
              height: 260,
              child: imagenes.isEmpty
                  ? Container(color: Colors.grey[200], child: const Icon(Icons.image, size: 80, color: Colors.grey))
                  : PageView.builder(
                      itemCount: imagenes.length,
                      itemBuilder: (_, i) => CachedNetworkImage(
                        imageUrl: '$baseUrl/${imagenes[i]['ruta']}/${imagenes[i]['nombre']}',
                        fit: BoxFit.cover,
                        errorWidget: (_, __, ___) => Container(color: Colors.grey[200]),
                      ),
                    ),
            ),

            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(_item!['item'] ?? '', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  if (_item!['valor'] != null)
                    Text('RD\$ ${_item!['valor']}',
                        style: const TextStyle(fontSize: 20, color: Color(0xFFF58634), fontWeight: FontWeight.bold)),
                  const SizedBox(height: 12),
                  if (_item!['presentacion'] != null) ...[
                    const Text('Descripción', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
                    const SizedBox(height: 4),
                    Text(_item!['presentacion'], style: TextStyle(color: Colors.grey[700])),
                  ],
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _addingToCart ? null : _addToCart,
                      icon: _addingToCart
                          ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                          : const Icon(Icons.shopping_cart_outlined),
                      label: const Text('Agregar al carrito', style: TextStyle(fontSize: 16)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFF58634),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
