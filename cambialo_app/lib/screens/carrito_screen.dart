import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import 'login_screen.dart';

class CarritoScreen extends StatefulWidget {
  const CarritoScreen({super.key});
  @override
  State<CarritoScreen> createState() => _CarritoScreenState();
}

class _CarritoScreenState extends State<CarritoScreen> {
  Map?  _carrito;
  bool  _loading  = true;
  bool  _loggedIn = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    _loggedIn = await AuthService.isLoggedIn();
    if (!_loggedIn) { setState(() => _loading = false); return; }

    final res = await ApiClient.get('/carrito', auth: true);
    if (res.statusCode == 200) {
      setState(() { _carrito = jsonDecode(res.body); _loading = false; });
    } else {
      setState(() => _loading = false);
    }
  }

  Future<void> _eliminar(int idItem) async {
    await ApiClient.delete('/carrito/$idItem', auth: true);
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Carrito'),
        actions: [
          if (_carrito != null && (_carrito!['items'] as List).isNotEmpty)
            TextButton(
              onPressed: () {},
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
      const Text('Inicia sesión para ver tu carrito',
          style: TextStyle(color: kTextGray, fontSize: 15)),
      const SizedBox(height: 16),
      ElevatedButton(
        onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen())),
        child: const Text('Iniciar sesión', style: TextStyle(color: Colors.white)),
      ),
    ]),
  );

  Widget _buildCarrito() {
    final items = (_carrito?['items'] as List?) ?? [];
    final total = _carrito?['total'] ?? 0;

    if (items.isEmpty) {
      return Center(
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          const Icon(Icons.shopping_cart_outlined, size: 64, color: Colors.grey),
          const SizedBox(height: 16),
          const Text('Tu carrito está vacío', style: TextStyle(color: kTextGray, fontSize: 15)),
        ]),
      );
    }

    return Column(children: [
      Expanded(
        child: RefreshIndicator(
          color: kPrimary,
          onRefresh: _load,
          child: ListView.separated(
            padding: const EdgeInsets.all(12),
            itemCount: items.length,
            separatorBuilder: (_, __) => const SizedBox(height: 8),
            itemBuilder: (_, i) => _CarritoItem(
              item: items[i],
              onEliminar: () => _eliminar(items[i]['id_item']),
            ),
          ),
        ),
      ),

      // Resumen total — igual que la web
      Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 8, offset: const Offset(0, -2))],
        ),
        child: Column(children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            const Text('Total seleccionado', style: TextStyle(fontSize: 15, color: kTextGray)),
            Text('RD\$ $total',
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: kPrimary)),
          ]),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () {},
              style: ElevatedButton.styleFrom(
                backgroundColor: kSecondary,
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
              child: const Text('Proceder al pago', style: TextStyle(color: Colors.white, fontSize: 15)),
            ),
          ),
        ]),
      ),
    ]);
  }
}

class _CarritoItem extends StatelessWidget {
  final Map item;
  final VoidCallback onEliminar;
  const _CarritoItem({required this.item, required this.onEliminar});

  @override
  Widget build(BuildContext context) {
    final itemData = item['item'] as Map? ?? {};
    final imagenes = (itemData['imagenes'] as List?) ?? [];
    final imgUrl = imagenes.isNotEmpty
        ? 'http://10.0.2.2:8000/storage/imgs/articulos/items/${imagenes[0]['nombre']}'
        : null;
    final subtotal = ((itemData['valor'] ?? 0) * (item['cantidad'] ?? 1)) - (item['descuento'] ?? 0);

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 6)],
      ),
      child: Row(children: [
        // Imagen
        ClipRRect(
          borderRadius: const BorderRadius.horizontal(left: Radius.circular(10)),
          child: imgUrl != null
              ? CachedNetworkImage(imageUrl: imgUrl, width: 90, height: 90, fit: BoxFit.cover,
                  errorWidget: (_, __, ___) => Container(width: 90, height: 90, color: Colors.grey.shade100))
              : Container(width: 90, height: 90, color: Colors.grey.shade100,
                  child: const Icon(Icons.image_not_supported, color: Colors.grey)),
        ),
        // Info
        Expanded(
          child: Padding(
            padding: const EdgeInsets.all(10),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(itemData['item'] ?? '', maxLines: 2, overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: kTextDark)),
              const SizedBox(height: 4),
              Text('Cantidad: ${item['cantidad']}', style: TextStyle(fontSize: 12, color: kTextGray)),
              const SizedBox(height: 4),
              Text('RD\$ $subtotal',
                  style: const TextStyle(fontSize: 13, color: kPrimary, fontWeight: FontWeight.bold)),
            ]),
          ),
        ),
        // Eliminar
        IconButton(
          icon: const Icon(Icons.delete_outline, color: Colors.red),
          onPressed: onEliminar,
        ),
      ]),
    );
  }
}
