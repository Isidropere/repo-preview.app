import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import 'login_screen.dart';
import 'item_detail_screen.dart';
import 'items_list_screen.dart';
import 'carrito_screen.dart';
import 'publicar_articulo_screen.dart';

/// Pantalla principal — fiel al diseño web de Cambialord
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List _intercambio = [];
  List _venta       = [];
  bool _loading     = true;
  bool _error       = false;
  Map<String, dynamic>? _user;
  final _searchCtrl = TextEditingController();

  // Categorías populares (igual que la web)
  final _categorias = [
    {'id': 26, 'label': 'Damas',       'icon': Icons.woman},
    {'id': 27, 'label': 'Caballeros',  'icon': Icons.man},
    {'id': 20, 'label': 'Niños',       'icon': Icons.child_care},
    {'id': 19, 'label': 'Teléfonos',   'icon': Icons.phone_android},
    {'id': 16, 'label': 'Hogar',       'icon': Icons.home_outlined},
    {'id': 4,  'label': 'Gamer',       'icon': Icons.sports_esports_outlined},
    {'id': 29, 'label': 'Talentos',    'icon': Icons.star_outline},
    {'id': 10, 'label': 'Otras',       'icon': Icons.category_outlined},
  ];

  @override
  void initState() {
    super.initState();
    _loadUser();
    _loadHome();
  }

  Future<void> _loadUser() async {
    final u = await AuthService.me();
    if (mounted) setState(() => _user = u);
  }

  Future<void> _loadHome() async {
    setState(() { _loading = true; _error = false; });
    try {
      final results = await Future.wait([
        ApiClient.get('/items?tipo=2&page=1'),
        ApiClient.get('/items?tipo=1&page=1'),
      ]);
      if (mounted) {
        setState(() {
          if (results[0].statusCode == 200) _intercambio = jsonDecode(results[0].body)['data'] ?? [];
          if (results[1].statusCode == 200) _venta       = jsonDecode(results[1].body)['data'] ?? [];
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() { _loading = false; _error = true; });
    }
  }

  Future<void> _logout() async {
    await AuthService.logout();
    if (!mounted) return;
    Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
  }

  void _search() {
    final q = _searchCtrl.text.trim();
    if (q.isEmpty) return;
    Navigator.push(context, MaterialPageRoute(
      builder: (_) => ItemsListScreen(query: q),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: _buildAppBar(),
      body: RefreshIndicator(
        color: kPrimary,
        onRefresh: _loadHome,
        child: _error
            ? Center(
                child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                  const Icon(Icons.wifi_off, size: 56, color: Colors.grey),
                  const SizedBox(height: 12),
                  const Text('No se pudo conectar al servidor',
                      style: TextStyle(color: kTextGray, fontSize: 15)),
                  const SizedBox(height: 4),
                  Text('Verifica que el servidor esté corriendo',
                      style: TextStyle(color: Colors.grey.shade400, fontSize: 12)),
                  const SizedBox(height: 20),
                  ElevatedButton.icon(
                    onPressed: _loadHome,
                    icon: const Icon(Icons.refresh, color: Colors.white),
                    label: const Text('Reintentar', style: TextStyle(color: Colors.white)),
                    style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
                  ),
                ]),
              )
            : SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Banner superior — "Encuentra lo que deseas cambiar"
                    Container(
                      width: double.infinity,
                      color: kPrimary,
                      padding: const EdgeInsets.symmetric(vertical: 6),
                      child: const Text(
                        'Encuentra lo que deseas cambiar',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w500),
                      ),
                    ),

                    // Buscador
                    Padding(
                      padding: const EdgeInsets.all(12),
                      child: Row(children: [
                        Expanded(
                          child: TextField(
                            controller: _searchCtrl,
                            onSubmitted: (_) => _search(),
                            decoration: InputDecoration(
                              hintText: 'Buscar Productos, Marcas y más...',
                              prefixIcon: const Icon(Icons.search, color: kPrimary),
                              filled: true,
                              fillColor: Colors.white,
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(8),
                                borderSide: BorderSide(color: Colors.grey.shade300),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(8),
                                borderSide: BorderSide(color: Colors.grey.shade300),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(8),
                                borderSide: const BorderSide(color: kSecondary),
                              ),
                              contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        ElevatedButton(
                          onPressed: _search,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: kPrimary,
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          child: const Text('Buscar', style: TextStyle(color: Colors.white)),
                        ),
                      ]),
                    ),

                    // Categorías populares
                    _sectionTitle('Categorías Populares'),
                    SizedBox(
                      height: 100,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemCount: _categorias.length,
                        itemBuilder: (_, i) => _CategoriaChip(cat: _categorias[i]),
                      ),
                    ),

                    // Frase principal — "Si no puedes venderlo ¡Cámbialo!"
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
                      child: Column(children: [
                        RichText(
                          textAlign: TextAlign.center,
                          text: const TextSpan(
                            style: TextStyle(fontSize: 22, color: kTextDark),
                            children: [
                              TextSpan(text: 'Si no puedes venderlo\n'),
                              TextSpan(text: '¡Cámbialo!',
                                  style: TextStyle(fontWeight: FontWeight.bold, color: kSecondary)),
                            ],
                          ),
                        ),
                        const SizedBox(height: 12),
                        ElevatedButton(
                          onPressed: () => Navigator.push(context,
                              MaterialPageRoute(builder: (_) => const ItemsListScreen(tipo: 2))),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: kSecondary,
                            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 10),
                          ),
                          child: const Text('Solicitar cambio', style: TextStyle(color: Colors.white, fontSize: 16)),
                        ),
                      ]),
                    ),

                    // Sección intercambio
                    _sectionHeader('Productos de intercambio', 'Intercambia lo que tienes por algo que quieres', tipo: 2),
                    _ProductosSlider(items: _intercambio, loading: _loading),

                    // Sección venta
                    _sectionHeader('Productos de venta', 'Aquí puedes vender lo que quieras', tipo: 1),
                    _ProductosSlider(items: _venta, loading: _loading),

                    // CTA final — igual que la web (fondo naranja)
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
                    const SizedBox(height: 24),
                  ],
                ),
              ),
      ),
    );
  }

  AppBar _buildAppBar() {
    return AppBar(
      backgroundColor: Colors.white,
      elevation: 2,
      title: Image.network(
        'http://10.0.2.2:8000/imgs/logoTypes/header-logo.png',
        height: 36,
        errorBuilder: (_, __, ___) => const Text('Cambialord',
            style: TextStyle(color: kPrimary, fontWeight: FontWeight.bold, fontSize: 18)),
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.shopping_cart_outlined, color: kPrimary),
          onPressed: () => Navigator.push(context,
              MaterialPageRoute(builder: (_) => const CarritoScreen())),
        ),
        if (_user != null)
          Padding(
            padding: const EdgeInsets.only(right: 4),
            child: GestureDetector(
              onTap: () => _showUserMenu(),
              child: CircleAvatar(
                radius: 18,
                backgroundColor: kPrimary,
                backgroundImage: CachedNetworkImageProvider(_user!['profile_photo_url']),
              ),
            ),
          )
        else
          IconButton(
            icon: const Icon(Icons.person_outline, color: kPrimary),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen())),
          ),
        const SizedBox(width: 4),
      ],
    );
  }

  void _showUserMenu() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => Padding(
        padding: const EdgeInsets.all(20),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          CircleAvatar(radius: 28, backgroundColor: kPrimary,
              backgroundImage: CachedNetworkImageProvider(_user!['profile_photo_url'])),
          const SizedBox(height: 8),
          Text('${_user!['nombres']} ${_user!['apellidos']}',
              style: const TextStyle(fontWeight: FontWeight.w600)),
          Text(_user!['email'], style: TextStyle(color: kTextGray, fontSize: 12)),
          const Divider(height: 24),
          ListTile(leading: const Icon(Icons.person_outline, color: kPrimary),
              title: const Text('Mi cuenta'), onTap: () => Navigator.pop(context)),
          ListTile(leading: const Icon(Icons.logout, color: Colors.red),
              title: const Text('Cerrar sesión', style: TextStyle(color: Colors.red)),
              onTap: () { Navigator.pop(context); _logout(); }),
        ]),
      ),
    );
  }

  Widget _sectionTitle(String title) => Padding(
    padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
    child: Text(title, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: kTextDark)),
  );

  Widget _sectionHeader(String title, String subtitle, {required int tipo}) => Container(
    color: kBgGray,
    padding: const EdgeInsets.fromLTRB(16, 20, 16, 8),
    child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
      Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: kTextDark)),
        const SizedBox(height: 4),
        Text(subtitle, style: TextStyle(fontSize: 13, color: kTextGray)),
      ])),
      TextButton(
        onPressed: () => Navigator.push(context, MaterialPageRoute(
          builder: (_) => ItemsListScreen(tipo: tipo))),
        child: const Text('Ver todos', style: TextStyle(color: kPrimary)),
      ),
    ]),
  );
}

class _CategoriaChip extends StatelessWidget {
  final Map cat;
  const _CategoriaChip({required this.cat});
  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => Navigator.push(context, MaterialPageRoute(
        builder: (_) => ItemsListScreen(categoriaId: cat['id']))),
      child: Container(
        width: 72,
        margin: const EdgeInsets.only(right: 8),
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Container(
            width: 56, height: 56,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 6)],
            ),
            child: Icon(cat['icon'] as IconData, color: kPrimary, size: 28),
          ),
          const SizedBox(height: 4),
          Text(cat['label'] as String,
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500)),
        ]),
      ),
    );
  }
}

class _ProductosSlider extends StatelessWidget {
  final List items;
  final bool loading;
  const _ProductosSlider({required this.items, this.loading = false});
  @override
  Widget build(BuildContext context) {
    if (loading) {
      // Skeleton placeholder mientras carga
      return Container(
        color: kBgGray,
        height: 240,
        child: ListView.builder(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
          itemCount: 4,
          itemBuilder: (_, __) => Container(
            width: 160,
            margin: const EdgeInsets.only(right: 12),
            decoration: BoxDecoration(
              color: Colors.grey.shade200,
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        ),
      );
    }
    if (items.isEmpty) {
      return Container(
        color: kBgGray,
        padding: const EdgeInsets.all(16),
        child: Text('No hay productos disponibles.', style: TextStyle(color: kTextGray)),
      );
    }
    return Container(
      color: kBgGray,
      height: 240,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
        itemCount: items.length,
        itemBuilder: (ctx, i) => _ProductCard(item: items[i]),
      ),
    );
  }
}

class _ProductCard extends StatelessWidget {
  final Map item;
  const _ProductCard({required this.item});
  @override
  Widget build(BuildContext context) {
    // La API ya devuelve image_url resuelta
    final imgUrl = item['image_url'] as String?;

    return GestureDetector(
      onTap: () => Navigator.push(context,
          MaterialPageRoute(builder: (_) => ItemDetailScreen(itemId: item['id_item']))),
      child: Container(
        width: 160,
        margin: const EdgeInsets.only(right: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.07), blurRadius: 6)],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          ClipRRect(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(10)),
            child: imgUrl != null
                ? CachedNetworkImage(
                    imageUrl: imgUrl, height: 130, width: double.infinity, fit: BoxFit.cover,
                    placeholder: (_, __) => Container(height: 130, color: Colors.grey.shade100),
                    errorWidget: (_, __, ___) => Container(height: 130, color: Colors.grey.shade100,
                        child: const Icon(Icons.image_not_supported, color: Colors.grey)),
                  )
                : Container(height: 130, color: Colors.grey.shade100,
                    child: const Icon(Icons.image_not_supported, color: Colors.grey)),
          ),
          Padding(
            padding: const EdgeInsets.all(8),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(item['item'] ?? '', maxLines: 2, overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: kTextDark)),
              const SizedBox(height: 4),
              if (item['valor'] != null)
                Text('RD\$ ${item['valor']}',
                    style: const TextStyle(fontSize: 12, color: kTextGray)),
            ]),
          ),
        ]),
      ),
    );
  }
}
