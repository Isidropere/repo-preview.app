import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import 'login_screen.dart';
import 'item_detail_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List _items = [];
  bool _loading = true;
  int _page = 1;
  bool _hasMore = true;
  final _searchCtrl = TextEditingController();
  Map<String, dynamic>? _user;

  @override
  void initState() {
    super.initState();
    _loadUser();
    _loadItems();
  }

  Future<void> _loadUser() async {
    final u = await AuthService.me();
    if (mounted) setState(() => _user = u);
  }

  Future<void> _loadItems({bool reset = false}) async {
    if (reset) { _page = 1; _hasMore = true; _items = []; }
    if (!_hasMore) return;

    setState(() => _loading = true);
    final q = _searchCtrl.text.trim();
    final path = q.isNotEmpty
        ? '/items/buscar?q=${Uri.encodeComponent(q)}'
        : '/items?page=$_page';

    final res = await ApiClient.get(path);
    if (res.statusCode == 200) {
      final body = jsonDecode(res.body);
      final newItems = q.isNotEmpty ? (body as List) : (body['data'] as List);
      setState(() {
        _items = reset ? newItems : [..._items, ...newItems];
        _hasMore = q.isEmpty && body['current_page'] < body['last_page'];
        if (_hasMore) _page++;
        _loading = false;
      });
    } else {
      setState(() => _loading = false);
    }
  }

  Future<void> _logout() async {
    await AuthService.logout();
    if (!mounted) return;
    Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 1,
        title: const Text('Cambialord', style: TextStyle(color: Color(0xFFF58634), fontWeight: FontWeight.bold)),
        actions: [
          if (_user != null)
            Padding(
              padding: const EdgeInsets.only(right: 8),
              child: CircleAvatar(
                radius: 18,
                backgroundImage: CachedNetworkImageProvider(_user!['profile_photo_url']),
              ),
            ),
          IconButton(icon: const Icon(Icons.logout, color: Colors.grey), onPressed: _logout),
        ],
      ),
      body: Column(
        children: [
          // Buscador
          Padding(
            padding: const EdgeInsets.all(12),
            child: TextField(
              controller: _searchCtrl,
              decoration: InputDecoration(
                hintText: 'Buscar productos...',
                prefixIcon: const Icon(Icons.search, color: Color(0xFFF58634)),
                suffixIcon: _searchCtrl.text.isNotEmpty
                    ? IconButton(icon: const Icon(Icons.clear), onPressed: () { _searchCtrl.clear(); _loadItems(reset: true); })
                    : null,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                filled: true,
                fillColor: Colors.white,
              ),
              onSubmitted: (_) => _loadItems(reset: true),
            ),
          ),

          // Grid de productos
          Expanded(
            child: _loading && _items.isEmpty
                ? const Center(child: CircularProgressIndicator(color: Color(0xFFF58634)))
                : RefreshIndicator(
                    color: const Color(0xFFF58634),
                    onRefresh: () => _loadItems(reset: true),
                    child: GridView.builder(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 2,
                        childAspectRatio: 0.75,
                        crossAxisSpacing: 10,
                        mainAxisSpacing: 10,
                      ),
                      itemCount: _items.length + (_hasMore ? 1 : 0),
                      itemBuilder: (ctx, i) {
                        if (i == _items.length) {
                          _loadItems();
                          return const Center(child: CircularProgressIndicator(color: Color(0xFFF58634)));
                        }
                        return _ItemCard(item: _items[i]);
                      },
                    ),
                  ),
          ),
        ],
      ),
    );
  }
}

class _ItemCard extends StatelessWidget {
  final Map item;
  const _ItemCard({required this.item});

  @override
  Widget build(BuildContext context) {
    final imagenes = item['imagenes'] as List? ?? [];
    final imgUrl = imagenes.isNotEmpty
        ? '${ApiClient.kBaseUrl.replaceAll('/api', '')}/${imagenes[0]['ruta']}/${imagenes[0]['nombre']}'
        : 'https://via.placeholder.com/200';

    return GestureDetector(
      onTap: () => Navigator.push(context,
          MaterialPageRoute(builder: (_) => ItemDetailScreen(itemId: item['id_item']))),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 6)],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
              child: CachedNetworkImage(
                imageUrl: imgUrl,
                height: 130,
                width: double.infinity,
                fit: BoxFit.cover,
                placeholder: (_, __) => Container(height: 130, color: Colors.grey[200]),
                errorWidget: (_, __, ___) => Container(height: 130, color: Colors.grey[200],
                    child: const Icon(Icons.image_not_supported, color: Colors.grey)),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(item['item'] ?? '', maxLines: 2, overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                  const SizedBox(height: 4),
                  if (item['valor'] != null)
                    Text('RD\$ ${item['valor']}',
                        style: const TextStyle(color: Color(0xFFF58634), fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: item['tipo_trans'] == 1 ? Colors.blue[50] : Colors.green[50],
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      item['tipo_trans'] == 1 ? 'Venta' : 'Intercambio',
                      style: TextStyle(
                        fontSize: 10,
                        color: item['tipo_trans'] == 1 ? Colors.blue[700] : Colors.green[700],
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
