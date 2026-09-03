import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:share_plus/share_plus.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:carousel_slider/carousel_slider.dart';
import 'package:http/http.dart' as http;
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import 'login_screen.dart';
import 'item_detail_screen.dart';
import 'items_list_screen.dart';
import 'main_screen.dart';
import 'carrito_screen.dart';
import 'publicar_articulo_screen.dart';
import 'publicar_talento_screen.dart';
import 'notificaciones_screen.dart';
import 'cuenta_screen.dart';
import 'otras_categorias_screen.dart';
import 'mis_intercambios_screen.dart';
import 'propuesta_intercambio_screen.dart';
import '../widgets/item_image.dart';
import '../widgets/footer_widget.dart';
import '../widgets/categorias_drawer.dart';
import '../widgets/ticker_banner_widget.dart';
import '../widgets/adulto_verification_dialog.dart';
import '../core/analytics_service.dart';

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
  int _cartCount = 0;
  int _intercambiosCount = 0;
  int _notifCount = 0;
  final _searchCtrl = TextEditingController();

  // Strip de categorías dinámico
  List<dynamic> _stripCategorias = [];
  bool _stripLoading = true;

  // Carrusel
  final PageController _carouselCtrl = PageController();
  int _carouselIndex = 0;
  Timer? _carouselTimer;
  final List<String> _carouselImages = ['1.jpg', '2.jpg', '3.jpg'];

  // Categorías populares (igual que la web)
  final _categorias = [
    {'id': 26, 'label': 'Damas',       'icon': Icons.woman},
    {'id': 27, 'label': 'Caballeros',  'icon': Icons.man},
    {'id': 20, 'label': 'Niños',       'icon': Icons.child_care},
    {'id': 19, 'label': 'Teléfonos',   'icon': Icons.phone_android},
    {'id': 16, 'label': 'Hogar',       'icon': Icons.home_outlined},
    {'id': 4,  'label': 'Gamer',       'icon': Icons.sports_esports_outlined},
    {'id': 29, 'label': 'Talentos-Servicios', 'icon': Icons.star_outline},
    {'id': 24, 'label': 'Tecnología',  'icon': Icons.computer_outlined},
    {'id': 6,  'label': 'Vehículos',   'icon': Icons.directions_car_outlined},
    {'id': 2,  'label': 'Electrodom.', 'icon': Icons.kitchen_outlined},
    {'id': 10, 'label': 'Otras',       'icon': Icons.category_outlined},
  ];

  @override
  void initState() {
    super.initState();
    _user = AuthService.currentUser;
    _initAll();
    _startCarouselTimer();
    _loadStripCategorias();
    AnalyticsService.trackEvent(
      _user != null ? 'home_logged_in_view' : 'home_public_view',
      params: {'user_status': _user != null ? 'authenticated' : 'guest'},
    );
  }

  Future<void> _loadStripCategorias() async {
    try {
      final res = await ApiClient.get('/categorias');
      if (res.statusCode == 200 && mounted) {
        final List<dynamic> data = jsonDecode(res.body);
        final isLoggedIn = await AuthService.isLoggedIn();
        final filtered = data.where((c) {
          final id = ApiClient.parseInt(c['id_categoria_item']) ?? 0;
          if (id == 11 && !isLoggedIn) return false;
          return true;
        }).toList();
        filtered.sort((a, b) =>
            (a['categoria'] ?? '')
                .toString()
                .toLowerCase()
                .compareTo((b['categoria'] ?? '').toString().toLowerCase()));
        if (mounted) {
          setState(() {
            _stripCategorias = filtered;
            _stripLoading = false;
          });
        }
      } else {
        if (mounted) setState(() => _stripLoading = false);
      }
    } catch (_) {
      if (mounted) setState(() => _stripLoading = false);
    }
  }

  void _startCarouselTimer() {
    _carouselTimer = Timer.periodic(const Duration(seconds: 4), (timer) {
      if (_carouselCtrl.hasClients && _carouselCtrl.position.hasContentDimensions) {
        try {
          final page = _carouselCtrl.page;
          if (page != null) {
            int next = (page.round() + 1) % _carouselImages.length;
            _carouselCtrl.animateToPage(next, duration: const Duration(milliseconds: 500), curve: Curves.easeInOut);
          }
        } catch (_) {
          // Ignorar si el layout no está listo
        }
      }
    });
  }

  @override
  void dispose() {
    _carouselTimer?.cancel();
    _carouselCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  /// Carga usuario e items de forma óptima
  Future<void> _initAll() async {
    setState(() { _loading = true; _error = false; });
    try {
      final loggedIn = await AuthService.isLoggedIn();
      
      // 1. Cargar el usuario de la cache/SharedPreferences de inmediato
      if (loggedIn) {
        final cachedUser = await AuthService.me();
        if (cachedUser != null && mounted) {
          setState(() {
            _user = cachedUser;
          });
        }
      }

      // 2. Cargar los items en paralelo y refrescar el usuario de fondo si es necesario
      final results = await Future.wait([
        ApiClient.get('/items?tipo=2&page=1', auth: loggedIn),
        ApiClient.get('/items?tipo=1&page=1', auth: loggedIn),
        if (loggedIn) AuthService.me(forceRefresh: true) else Future.value(null),
      ]);
      if (!mounted) return;

      final intercambioRes = results[0] as http.Response;
      final ventaRes       = results[1] as http.Response;
      final user           = results[2] as Map<String, dynamic>?;

      setState(() {
        if (intercambioRes.statusCode == 200) {
          _intercambio = jsonDecode(intercambioRes.body)['data'] ?? [];
        }
        if (ventaRes.statusCode == 200) {
          _venta = jsonDecode(ventaRes.body)['data'] ?? [];
        }
        if (user != null) {
          _user = user;
        }
        _loading = false;
      });

      // Notificaciones en background — no bloquean la UI
      if (_user != null) _loadBadges();
    } catch (e) {
      if (mounted) setState(() { _loading = false; _error = true; });
    }
  }

  Future<void> _loadHome() => _initAll();

  Future<void> _loadBadges() async {
    try {
      final res = await ApiClient.get('/auth/badges', auth: true, useCache: false);
      if (res.statusCode == 200 && mounted) {
        final data = jsonDecode(res.body);
        setState(() {
          _cartCount = data['cart'] ?? 0;
          _intercambiosCount = data['intercambios'] ?? 0;
          _notifCount = data['notificaciones'] ?? 0;
        });
      }
    } catch (_) {}
  }

  Future<void> _logout() async {
    await AuthService.logout();
    if (!mounted) return;
    Navigator.of(context, rootNavigator: true).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const MainScreen()),
      (route) => false,
    );
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
      drawer: const CategoriasDrawer(),
      body: RefreshIndicator(
        color: kPrimary,
        onRefresh: _loadHome,
        child: _error
            ? Center(
                child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                  const Icon(Icons.wifi_off, size: 56, color: Colors.grey),
                  const SizedBox(height: 12),
                  const Text('Error de conexión',
                      style: TextStyle(color: kTextGray, fontSize: 15, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Text('Verifique su conexión a la red',
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
                    const TickerBannerWidget(),
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
                      padding: const EdgeInsets.all(16),
                      child: SizedBox(
                        height: 40,
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Expanded(
                              child: TextField(
                                controller: _searchCtrl,
                                onSubmitted: (_) => _search(),
                                decoration: InputDecoration(
                                  hintText: 'Buscar Productos, Marcas y más...',
                                  prefixIcon: const Icon(Icons.search, color: kPrimary, size: 20),
                                  filled: true,
                                  fillColor: Colors.white,
                                  isDense: true,
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 12),
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
                                ),
                                style: const TextStyle(fontSize: 13),
                              ),
                            ),
                            const SizedBox(width: 8),
                            ElevatedButton(
                              onPressed: _search,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: kPrimary,
                                padding: const EdgeInsets.symmetric(horizontal: 16),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                elevation: 0,
                              ),
                              child: const Text(
                                'Buscar',
                                style: TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                    // Strip de categorías
                    _CategoriasStrip(
                      categorias: _stripCategorias,
                      loading: _stripLoading,
                    ),

                    // Carrusel
                    _buildCarousel(),

                    // Categorías populares
                    _sectionTitle('Categorías Populares'),
                    _CategoriesGlobe(categorias: _categorias),

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
                        const SizedBox(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Expanded(
                              child: ElevatedButton.icon(
                                onPressed: () async {
                                  final loggedIn = await AuthService.isLoggedIn();
                                  if (!loggedIn) {
                                    if (!mounted) return;
                                    final result = await Navigator.of(context, rootNavigator: true).push(
                                      MaterialPageRoute(builder: (_) => const LoginScreen()),
                                    );
                                    if (result != true) return;
                                  }
                                  if (!mounted) return;
                                  Navigator.of(context, rootNavigator: true).push(
                                    MaterialPageRoute(builder: (_) => const PublicarArticuloScreen()),
                                  );
                                },
                                icon: const Icon(Icons.add_shopping_cart, color: Colors.white, size: 16),
                                label: const Text('Crear producto', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: kPrimary,
                                  padding: const EdgeInsets.symmetric(vertical: 12),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                  elevation: 2,
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: ElevatedButton.icon(
                                onPressed: () async {
                                  final loggedIn = await AuthService.isLoggedIn();
                                  if (!loggedIn) {
                                    if (!mounted) return;
                                    final result = await Navigator.of(context, rootNavigator: true).push(
                                      MaterialPageRoute(builder: (_) => const LoginScreen()),
                                    );
                                    if (result != true) return;
                                  }
                                  if (!mounted) return;
                                  Navigator.of(context, rootNavigator: true).push(
                                    MaterialPageRoute(builder: (_) => const PublicarTalentoScreen()),
                                  );
                                },
                                icon: const Icon(Icons.psychology, color: Colors.white, size: 16),
                                label: const Text('Crear talento', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: kSecondary,
                                  padding: const EdgeInsets.symmetric(vertical: 12),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                  elevation: 2,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ]),
                    ),

                    // Sección intercambio
                    _sectionHeader('Productos de intercambio', 'Intercambia lo que tienes por algo que quieres', tipo: 2),
                    _ProductosSlider(items: _intercambio, loading: _loading, currentUserId: ApiClient.parseInt(_user?['id'])),

                    // Sección venta
                    _sectionHeader('Productos de venta', 'Aquí puedes vender lo que quieras', tipo: 1),
                    _ProductosSlider(items: _venta, loading: _loading, currentUserId: ApiClient.parseInt(_user?['id'])),

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
                    const FooterWidget(),
                  ],
                ),
              ),
      ),
    );
  }

  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      automaticallyImplyLeading: false,
      backgroundColor: Colors.white,
      elevation: 0,
      leading: Builder(
        builder: (context) => IconButton(
          icon: const Icon(Icons.menu, color: kPrimary),
          onPressed: () => Scaffold.of(context).openDrawer(),
        ),
      ),
      title: Image.network(
        '${kBaseUrl.replaceAll('/api', '')}/imgs/logoTypes/header-logo.png',
        height: 36,
        errorBuilder: (_, __, ___) => const Text('Cambialord',
            style: TextStyle(color: kPrimary, fontWeight: FontWeight.bold, fontSize: 18)),
      ),
      actions: [
        if (_user != null) ...[
          IconButton(
            padding: const EdgeInsets.symmetric(horizontal: 6),
            constraints: const BoxConstraints(),
            icon: Badge(isLabelVisible: _intercambiosCount > 0, label: Text('$_intercambiosCount'), child: const Icon(Icons.swap_horiz_outlined, color: kPrimary)),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MisIntercambiosScreen())),
          ),
          IconButton(
            padding: const EdgeInsets.symmetric(horizontal: 6),
            constraints: const BoxConstraints(),
            icon: Badge(isLabelVisible: _notifCount > 0, label: Text('$_notifCount'), child: const Icon(Icons.notifications_none, color: kPrimary)),
            onPressed: () async {
              await Navigator.push(context, MaterialPageRoute(builder: (_) => const NotificacionesScreen()));
              _loadBadges();
            },
          ),
          const SizedBox(width: 8),
        ],
        if (_user != null)
          Padding(
            padding: const EdgeInsets.only(right: 8),
            child: GestureDetector(
              onTap: () => _showUserMenu(),
              child: _buildAvatarWidget(18),
            ),
          )
        else
          IconButton(
            icon: const Icon(Icons.person_outline, color: kPrimary),
            onPressed: () async {
              final result = await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
              if (result == true && mounted) {
                _loadHome(); // Recargar inicio si inició sesión
              }
            },
          ),
        const SizedBox(width: 4),
      ],
    );
  }

  Widget _buildAvatarWidget(double radius) {
    final photoUrl = _user?['profile_photo_url']?.toString();
    final isPlaceholder = photoUrl == null ||
                          photoUrl.trim().isEmpty ||
                          photoUrl.contains('default') ||
                          photoUrl.contains('.svg') ||
                          photoUrl.contains('via.placeholder.com');

    return CircleAvatar(
      radius: radius,
      backgroundColor: kPrimary,
      child: ClipOval(
        child: isPlaceholder
            ? Icon(Icons.person, size: radius * 1.1, color: Colors.white)
            : Image.network(
                ApiClient.fixImageUrl(photoUrl),
                width: radius * 2,
                height: radius * 2,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => Icon(Icons.person, size: radius * 1.1, color: Colors.white),
              ),
      ),
    );
  }

  void _showUserMenu() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => Padding(
        padding: const EdgeInsets.all(20),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          _buildAvatarWidget(28),
          const SizedBox(height: 8),
          Text('${_user!['nombres']} ${_user!['apellidos']}',
              style: const TextStyle(fontWeight: FontWeight.w600)),
          Text(_user!['email'], style: TextStyle(color: kTextGray, fontSize: 12)),
          const Divider(height: 24),
          ListTile(
            leading: const Icon(Icons.person_outline, color: kPrimary),
            title: const Text('Mi cuenta'), 
            onTap: () {
              Navigator.pop(context);
              Navigator.push(context, MaterialPageRoute(builder: (_) => const CuentaScreen()));
            },
          ),
          ListTile(leading: const Icon(Icons.logout, color: Colors.red),
              title: const Text('Cerrar sesión', style: TextStyle(color: Colors.red)),
              onTap: () { Navigator.pop(context); _logout(); }),
        ]),
      ),
    );
  }

  Widget _sectionTitle(String t) => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        child: Text(t, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: kTextDark)),
      );

  Widget _buildCarousel() {
    final String baseUrl = kBaseUrl.replaceAll('/api', '');
    return Container(
      height: 180,
      margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Stack(
        alignment: Alignment.bottomCenter,
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: PageView.builder(
              controller: _carouselCtrl,
              onPageChanged: (i) => setState(() => _carouselIndex = i),
              itemCount: _carouselImages.length,
              itemBuilder: (ctx, i) {
                return Image.network(
                  '$baseUrl/imgs/${_carouselImages[i]}',
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(color: Colors.grey.shade200, child: const Icon(Icons.image_not_supported, color: Colors.grey)),
                );
              },
            ),
          ),
          Positioned(
            bottom: 10,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(_carouselImages.length, (i) {
                return AnimatedContainer(
                  duration: const Duration(milliseconds: 300),
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  height: 8,
                  width: _carouselIndex == i ? 16 : 8,
                  decoration: BoxDecoration(
                    color: _carouselIndex == i ? kPrimary : Colors.white.withOpacity(0.5),
                    borderRadius: BorderRadius.circular(4),
                  ),
                );
              }),
            ),
          ),
        ],
      ),
    );
  }

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

// ── Strip horizontal de categorías ────────────────────────────────────────────
class _CategoriasStrip extends StatefulWidget {
  final List<dynamic> categorias;
  final bool loading;

  const _CategoriasStrip({required this.categorias, required this.loading});

  @override
  State<_CategoriasStrip> createState() => _CategoriasStripState();
}

class _CategoriasStripState extends State<_CategoriasStrip> {
  final ScrollController _scrollCtrl = ScrollController();

  @override
  void dispose() {
    _scrollCtrl.dispose();
    super.dispose();
  }

  IconData _icon(int id) {
    switch (id) {
      case 1:  return Icons.music_note_outlined;
      case 2:  return Icons.kitchen_outlined;
      case 3:  return Icons.electrical_services_outlined;
      case 4:  return Icons.sports_esports_outlined;
      case 5:  return Icons.chair_outlined;
      case 6:  return Icons.directions_car_outlined;
      case 7:  return Icons.handyman_outlined;
      case 8:  return Icons.diamond_outlined;
      case 9:  return Icons.menu_book_outlined;
      case 10: return Icons.category_outlined;
      case 11: return Icons.eighteen_up_rating_outlined;
      case 13: return Icons.spa_outlined;
      case 14: return Icons.color_lens_outlined;
      case 15: return Icons.sports_soccer_outlined;
      case 16: return Icons.home_outlined;
      case 17: return Icons.yard_outlined;
      case 19: return Icons.phone_android_outlined;
      case 20: return Icons.child_care_outlined;
      case 21: return Icons.museum_outlined;
      case 22: return Icons.baby_changing_station_outlined;
      case 23: return Icons.pets_outlined;
      case 24: return Icons.computer_outlined;
      case 25: return Icons.library_books_outlined;
      case 26: return Icons.woman_2_outlined;
      case 27: return Icons.man_2_outlined;
      case 28: return Icons.business_center_outlined;
      case 29: return Icons.star_outline;
      default: return Icons.category_outlined;
    }
  }

  @override
  Widget build(BuildContext context) {
    // Construir lista: "Todo" primero + categorías de la API
    final items = <Map<String, dynamic>>[
      {'id': null, 'label': 'Todo', 'icon': Icons.apps_rounded},
      if (!widget.loading)
        ...widget.categorias.map((c) => {
              'id': ApiClient.parseInt(c['id_categoria_item']),
              'label': (c['categoria'] ?? '').toString(),
              'icon': _icon(ApiClient.parseInt(c['id_categoria_item']) ?? 0),
            }),
    ];

    // Chips skeleton si está cargando
    final skeletonCount = widget.loading ? 7 : 0;

    return SizedBox(
      height: 46,
      child: GestureDetector(
        // Drag horizontal manual — bypassa completamente el conflicto de gestos
        // con el SingleChildScrollView vertical padre
        onHorizontalDragUpdate: (details) {
          if (!_scrollCtrl.hasClients) return;
          final newOffset = (_scrollCtrl.offset - details.delta.dx)
              .clamp(0.0, _scrollCtrl.position.maxScrollExtent);
          _scrollCtrl.jumpTo(newOffset);
        },
        child: ListView(
          controller: _scrollCtrl,
          scrollDirection: Axis.horizontal,
          // NeverScrollable: el GestureDetector de arriba controla el scroll
          physics: const NeverScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 12),
          children: [
            // Skeletons mientras carga
            if (skeletonCount > 0)
              ...List.generate(
                skeletonCount,
                (_) => Container(
                  margin: const EdgeInsets.only(right: 8),
                  width: 80,
                  height: 34,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade200,
                    borderRadius: BorderRadius.circular(20),
                  ),
                ),
              )
            else
              // Chips reales
              ...items.map((item) {
                final bool isTodo = item['id'] == null;
                return GestureDetector(
                  onTap: () async {
                    if (isTodo) {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const ItemsListScreen()),
                      );
                    } else {
                      final int catId = item['id'] as int;
                      // Categoría adulto (id==11): requiere verificación igual que el drawer
                      if (catId == 11) {
                        final ok = await AdultoVerificationDialog.showVerification(context);
                        if (!ok) return;
                      }
                      if (!context.mounted) return;
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => ItemsListScreen(
                            categoriaId: catId,
                            title: item['label'] as String,
                          ),
                        ),
                      );
                    }
                  },
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 180),
                    margin: const EdgeInsets.only(right: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    height: 34,
                    decoration: BoxDecoration(
                      color: isTodo ? kPrimary : Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: isTodo ? kPrimary : Colors.grey.shade300,
                        width: 1,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.06),
                          blurRadius: 4,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          item['icon'] as IconData,
                          size: 14,
                          color: isTodo ? Colors.white : kPrimary,
                        ),
                        const SizedBox(width: 5),
                        Text(
                          item['label'] as String,
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: isTodo ? Colors.white : kTextDark,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }),
          ],
        ),
      ),
    );
  }
}

class _CategoriesGlobe extends StatefulWidget {
  final List categorias;
  const _CategoriesGlobe({required this.categorias});

  @override
  State<_CategoriesGlobe> createState() => _CategoriesGlobeState();
}
class _CategoriesGlobeState extends State<_CategoriesGlobe> {
  int _currentIndex = 0;

  @override
  Widget build(BuildContext context) {
    return CarouselSlider.builder(
      itemCount: widget.categorias.length,
      itemBuilder: (context, index, realIndex) {
        final isCenter = index == _currentIndex;
        return Center(
          child: AnimatedScale(
            scale: isCenter ? 1.35 : 0.9,
            duration: const Duration(milliseconds: 250),
            curve: Curves.easeOutBack,
            child: _CategoriaChip(cat: widget.categorias[index]),
          ),
        );
      },
      options: CarouselOptions(
        height: 130, // Ligeramente mayor para evitar recortes por escala
        viewportFraction: 0.28,          // Muestra unas 3-4 en pantalla de forma balanceada
        initialPage: 0,
        enableInfiniteScroll: true,     // Scroll infinito continuo
        reverse: false,
        autoPlay: false,
        scrollDirection: Axis.horizontal,
        onPageChanged: (index, reason) {
          setState(() {
            _currentIndex = index;
          });
        },
      ),
    );
  }
}

class _CategoriaChip extends StatelessWidget {
  final Map cat;
  const _CategoriaChip({required this.cat});
  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        if (cat['id'] == 10) {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const OtrasCategoriasScreen()));
        } else {
          Navigator.push(context, MaterialPageRoute(
            builder: (_) => ItemsListScreen(
              categoriaId: cat['id'],
              title: cat['label'] as String,
            )
          ));
        }
      },
      child: Center(
        child: Container(
          width: 72,
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
            const SizedBox(height: 6),
            Text(cat['label'] as String,
                textAlign: TextAlign.center,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600)),
          ]),
        ),
      ),
    );
  }
}

class _ProductosSlider extends StatefulWidget {
  final List items;
  final bool loading;
  final int? currentUserId;
  const _ProductosSlider({required this.items, required this.loading, this.currentUserId});

  @override
  _ProductosSliderState createState() => _ProductosSliderState();
}

class _ProductosSliderState extends State<_ProductosSlider> {
  final CarouselSliderController _controller = CarouselSliderController();

  @override
  Widget build(BuildContext context) {
    if (widget.loading) {
      return Container(
        color: kBgGray,
        height: 235,
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
    if (widget.items.isEmpty) {
      return Container(
        color: kBgGray,
        padding: const EdgeInsets.all(16),
        child: const Text('No hay productos disponibles.', style: TextStyle(color: kTextGray)),
      );
    }

    final double screenWidth = MediaQuery.of(context).size.width;
    // Cada tarjeta de producto tiene un ancho de 160 y un margen derecho de 12.
    // Esto hace que el ancho total de la celda sea de 172.
    double viewportFraction = 172.0 / screenWidth;
    if (viewportFraction > 0.95) {
      viewportFraction = 0.95;
    }

    return Container(
      color: kBgGray,
      height: 235,
      child: Stack(
        alignment: Alignment.center,
        children: [
          CarouselSlider.builder(
            carouselController: _controller,
            itemCount: widget.items.length,
            itemBuilder: (ctx, i, realIndex) => _ProductCard(item: widget.items[i], currentUserId: widget.currentUserId),
            options: CarouselOptions(
              height: 225,
              viewportFraction: viewportFraction,
              enableInfiniteScroll: false,
              autoPlay: false,
              padEnds: false,
            ),
          ),
          if (widget.items.length > 2)
            Positioned(
              left: 4,
              child: CircleAvatar(
                backgroundColor: Colors.white.withOpacity(0.9),
                radius: 20,
                child: IconButton(
                  icon: const Icon(Icons.chevron_left, color: kPrimary),
                  onPressed: () => _controller.previousPage(duration: const Duration(milliseconds: 300), curve: Curves.ease),
                ),
              ),
            ),
          if (widget.items.length > 2)
            Positioned(
              right: 4,
              child: CircleAvatar(
                backgroundColor: Colors.white.withOpacity(0.9),
                radius: 20,
                child: IconButton(
                  icon: const Icon(Icons.chevron_right, color: kPrimary),
                  onPressed: () => _controller.nextPage(duration: const Duration(milliseconds: 300), curve: Curves.ease),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _ProductCard extends StatelessWidget {
  final Map item;
  final int? currentUserId;
  const _ProductCard({required this.item, this.currentUserId});
  @override
  Widget build(BuildContext context) {
    final int itemId = int.tryParse(item['id_item']?.toString() ?? '') ?? 0;
    final int transVal = int.tryParse(item['tipo_trans']?.toString() ?? '') ?? 0;
    final int itemUserId = int.tryParse(item['id_user']?.toString() ?? '') ?? 0;
    final bool esVenta = transVal == 1 || transVal == 3;
    final bool esIntercambio = transVal == 2 || transVal == 3;
    final bool esMio = currentUserId != null && currentUserId == itemUserId;

    final bool yaEnCarrito = item['ya_en_carrito'] == true;
    final bool conNegociacionActiva = item['con_negociacion_activa'] == true;

    Future<void> handleIntercambio() async {
      final loggedIn = await AuthService.isLoggedIn();
      if (!loggedIn) {
        if (!context.mounted) return;
        final result = await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
        if (result != true) return;
      }
      if (!context.mounted) return;
      Navigator.push(context, MaterialPageRoute(
        builder: (_) => PropuestaIntercambioScreen(
          receptorItemId: itemId,
          nombreArticulo: item['item'] ?? '',
          idCategoriaItem: int.tryParse(item['id_categoria_item']?.toString() ?? '') ?? 0,
        ),
      ));
    }

    Future<void> handleAddToCart() async {
      final loggedIn = await AuthService.isLoggedIn();
      if (!loggedIn) {
        if (!context.mounted) return;
        final result = await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
        if (result != true) return;
      }
      if (!context.mounted) return;
      final res = await ApiClient.post('/carrito/agregar',
          {'id_item': itemId, 'cantidad': 1}, auth: true);
      String message = 'Error al agregar';
      bool isSuccess = false;
      if (res.statusCode == 200) {
        isSuccess = true;
        ApiClient.clearCache('/carrito');
        try {
          final data = jsonDecode(res.body);
          message = data['message'] ?? '¡Agregado al carrito!';
          if (data['cart_count'] != null) {
            ApiClient.cartCountNotifier.value = int.tryParse(data['cart_count'].toString()) ?? (ApiClient.cartCountNotifier.value + 1);
          } else {
            ApiClient.cartCountNotifier.value++;
          }
        } catch (_) {
          message = '¡Agregado al carrito!';
        }
      } else {
        try {
          final data = jsonDecode(res.body);
          message = data['message'] ?? 'Error al agregar';
        } catch (_) {}
      }
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(message),
          backgroundColor: isSuccess ? kPrimary : Colors.red,
        ));
      }
    }

    Future<void> handleShare() async {
      final baseUrl = kBaseUrl.replaceAll('/api', '');
      final slug = item['slug'] ?? itemId.toString();
      final itemUrl = '$baseUrl/items/producto/$slug';
      final itemTitle = item['item'] ?? 'Artículo';
      try {
        await SharePlus.instance.share(
          ShareParams(
            text: 'Mira este artículo en Cambialord: $itemTitle\n$itemUrl',
            subject: itemTitle,
          ),
        );
      } catch (e) {
        await Clipboard.setData(ClipboardData(text: itemUrl));
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
            content: Text('¡Enlace copiado al portapapeles!'),
            backgroundColor: Colors.green,
          ));
        }
      }
    }

    return GestureDetector(
      onTap: () => Navigator.push(context,
          MaterialPageRoute(builder: (_) => ItemDetailScreen(itemId: itemId))),
      child: Container(
        width: 160,
        margin: const EdgeInsets.only(right: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.07), blurRadius: 6)],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Stack(
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(10)),
                child: ItemImage(
                  item: item,
                  height: 110,
                  width: double.infinity,
                ),
              ),
              if (yaEnCarrito || conNegociacionActiva || (int.tryParse(item['stock']?.toString() ?? '') ?? 0) <= 0)
                Positioned(
                  top: 8,
                  left: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: ((int.tryParse(item['stock']?.toString() ?? '') ?? 0) <= 0 && !yaEnCarrito && !conNegociacionActiva)
                          ? const Color(0xFFFEE2E2)
                          : const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: ((int.tryParse(item['stock']?.toString() ?? '') ?? 0) <= 0 && !yaEnCarrito && !conNegociacionActiva)
                            ? const Color(0xFFFCA5A5)
                            : const Color(0xFFBFDBFE),
                        width: 1,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.05),
                          blurRadius: 2,
                          offset: const Offset(0, 1),
                        )
                      ],
                    ),
                    child: Text(
                      ((int.tryParse(item['stock']?.toString() ?? '') ?? 0) <= 0 && !yaEnCarrito && !conNegociacionActiva)
                          ? 'AGOTADO'
                          : (yaEnCarrito ? 'CARRITO' : 'NEGOCIACIÓN'),
                      style: TextStyle(
                        fontSize: 7.5,
                        fontWeight: FontWeight.bold,
                        color: ((int.tryParse(item['stock']?.toString() ?? '') ?? 0) <= 0 && !yaEnCarrito && !conNegociacionActiva)
                            ? const Color(0xFFEF4444)
                            : const Color(0xFF1D4ED8),
                        letterSpacing: 0.3,
                      ),
                    ),
                  ),
                ),
              Positioned(
                top: 8,
                right: 8,
                child: GestureDetector(
                  onTap: handleShare,
                  child: Container(
                    width: 28,
                    height: 28,
                    decoration: const BoxDecoration(
                      color: Color(0xFFF58634), // Web orange #f58634
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black26,
                          blurRadius: 4,
                          offset: Offset(0, 2),
                        )
                      ],
                    ),
                    child: const Icon(Icons.share, size: 14, color: Colors.white),
                  ),
                ),
              ),
            ],
          ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(item['item'] ?? '', maxLines: 1, overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: kTextDark)),
                      const SizedBox(height: 6),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          if (item['valor'] != null)
                            Expanded(
                              child: Text(
                                'RD\$ ${item['valor']}',
                                style: const TextStyle(color: kPrimary, fontWeight: FontWeight.bold, fontSize: 11.5),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          const SizedBox(width: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                            decoration: BoxDecoration(
                              color: transVal == 1 ? const Color(0xFFEFF6FF) : const Color(0xFFF0FDF4),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              transVal == 1 ? 'Venta' : (transVal == 2 ? 'Intercambio' : 'Ambos'),
                              style: TextStyle(
                                fontSize: 8,
                                fontWeight: FontWeight.bold,
                                color: transVal == 1 ? const Color(0xFF1D4ED8) : const Color(0xFF15803D),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  Row(
                    children: [
                      if (yaEnCarrito || conNegociacionActiva) ...[
                        Expanded(
                          child: Container(
                            height: 32,
                            alignment: Alignment.center,
                            margin: const EdgeInsets.only(right: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEFF6FF),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: const Color(0xFFBFDBFE),
                                width: 1,
                              ),
                            ),
                            child: Text(
                              yaEnCarrito ? 'EN CARRITO' : 'EN NEGOCIACIÓN',
                              style: const TextStyle(
                                fontSize: 9,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF1D4ED8),
                              ),
                            ),
                          ),
                        ),
                      ] else ...[
                        if (esVenta && !esMio) ...[
                          Expanded(
                            child: Container(
                              height: 32,
                              margin: const EdgeInsets.only(right: 4),
                              child: ElevatedButton(
                                onPressed: handleAddToCart,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF3B82F6), // Web blue #3b82f6
                                  foregroundColor: Colors.white,
                                  padding: EdgeInsets.zero,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                  elevation: 0,
                                  minimumSize: Size.zero,
                                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                ),
                                child: const Icon(Icons.shopping_cart, size: 16, color: Colors.white),
                              ),
                            ),
                          ),
                        ],
                        if (esIntercambio && !esMio) ...[
                          Expanded(
                            child: Container(
                              height: 32,
                              margin: const EdgeInsets.only(right: 4),
                              child: OutlinedButton(
                                onPressed: handleIntercambio,
                                style: OutlinedButton.styleFrom(
                                  side: const BorderSide(color: Color(0xFFFED7AA), width: 1), // Web border-orange-300 #fed7aa
                                  backgroundColor: const Color(0xFFFFF7ED), // Web #fff7ed
                                  foregroundColor: const Color(0xFFC2410C),
                                  padding: EdgeInsets.zero,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                  elevation: 0,
                                  minimumSize: Size.zero,
                                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                ),
                                child: const Icon(Icons.swap_horiz, size: 18, color: Color(0xFFC2410C)), // Web text-orange-700 #c2410c
                              ),
                            ),
                          ),
                        ],
                      ],
                      Expanded(
                        child: Container(
                          height: 32,
                          child: OutlinedButton(
                            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => ItemDetailScreen(itemId: itemId))),
                            style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: Color(0xFFE2E8F0), width: 1), // Web border-gray-200 #e2e8f0
                              backgroundColor: const Color(0xFFF8FAFC), // Web #f8fafc
                              foregroundColor: const Color(0xFF64748B),
                              padding: EdgeInsets.zero,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                              elevation: 0,
                              minimumSize: Size.zero,
                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                            ),
                            child: const Icon(Icons.visibility, size: 16, color: Color(0xFF64748B)), // Web text-gray-500 #64748b
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ]),
      ),
    );
  }
}
