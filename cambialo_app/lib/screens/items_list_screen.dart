import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:share_plus/share_plus.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import '../widgets/item_image.dart';
import 'item_detail_screen.dart';
import 'login_screen.dart';
import 'propuesta_intercambio_screen.dart';
import 'publicar_articulo_screen.dart';
import '../widgets/footer_widget.dart';

/// Listado de productos — Intercambio o Venta
class ItemsListScreen extends StatefulWidget {
  final int? tipo;
  final int? categoriaId;
  final String? query;
  final String? title;

  const ItemsListScreen({super.key, this.tipo, this.categoriaId, this.query, this.title});
  @override
  State<ItemsListScreen> createState() => _ItemsListScreenState();
}

class _ItemsListScreenState extends State<ItemsListScreen> {
  List _items    = [];
  bool _loading  = true;
  bool _error    = false;
  int  _page     = 1;
  bool _hasMore  = true;
  int? _currentUserId;

  @override
  void initState() {
    super.initState();
    _load();
    _loadUser();
  }

  Future<void> _loadUser() async {
    try {
      final user = await AuthService.me();
      if (user != null && mounted) {
        setState(() {
          _currentUserId = ApiClient.parseInt(user['id']);
        });
      }
    } catch (_) {}
  }

  Future<void> _load({bool reset = false}) async {
    if (reset) { _page = 1; _hasMore = true; _items = []; }
    if (!_hasMore) return;
    setState(() {
      _loading = true;
      _error = false;
    });

    String path;
    if (widget.query != null) {
      path = '/items/buscar?q=${Uri.encodeComponent(widget.query!)}';
    } else {
      final params = <String>['page=$_page'];
      if (widget.tipo != null)       params.add('tipo=${widget.tipo}');
      if (widget.categoriaId != null) params.add('categoria=${widget.categoriaId}');
      path = '/items?${params.join('&')}';
    }

    try {
      final res = await ApiClient.get(path);
      if (!mounted) return;
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        final newItems = widget.query != null ? (body as List) : (body['data'] as List);
        setState(() {
          _items = reset ? newItems : [..._items, ...newItems];
          _hasMore = widget.query == null && body['current_page'] < body['last_page'];
          if (_hasMore) _page++;
          _loading = false;
        });
      } else {
        setState(() {
          _loading = false;
          _error = true;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _loading = false;
          _error = true;
        });
      }
    }
  }

  String get _title {
    if (widget.title != null) return widget.title!;
    if (widget.query != null) return 'Resultados: "${widget.query}"';
    if (widget.tipo == 1) return 'Productos de venta';
    if (widget.tipo == 2 || widget.tipo == 3) return 'Productos de intercambio';
    if (widget.tipo == null && widget.categoriaId == null) return 'Todos los productos';
    return 'Productos';
  }

  @override
  Widget build(BuildContext context) {
    final double screenWidth = MediaQuery.of(context).size.width;
    final double cardWidth = (screenWidth - 34) / 2;
    final double targetHeight = 215.0;
    final double computedAspectRatio = cardWidth / targetHeight;

    return Scaffold(
      appBar: AppBar(
        title: Text(_title),
        leading: Navigator.canPop(context)
            ? IconButton(
                icon: const Icon(Icons.arrow_back),
                onPressed: () => Navigator.pop(context),
              )
            : null,
      ),
      body: _loading && _items.isEmpty
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : _error && _items.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.cloud_off, size: 80, color: Colors.grey),
                      const SizedBox(height: 16),
                      const Text(
                        'Error de conexión',
                        style: TextStyle(fontSize: 16, color: kTextDark, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 24),
                        child: Text(
                          'Verifique su conexión a la red',
                          style: TextStyle(fontSize: 13, color: kTextGray),
                          textAlign: TextAlign.center,
                        ),
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () => _load(reset: true),
                        style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
                        child: const Text('Reintentar', style: TextStyle(color: Colors.white)),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  color: kPrimary,
                  onRefresh: () => _load(reset: true),
                  child: _items.isEmpty
                      ? ListView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          children: [
                            SizedBox(height: MediaQuery.of(context).size.height * 0.15),
                            Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.inventory_2_outlined, size: 80, color: Colors.grey.shade400),
                                const SizedBox(height: 16),
                                Text(
                                  widget.tipo == 1
                                      ? 'No hay productos de venta disponibles'
                                      : (widget.tipo == 2 || widget.tipo == 3
                                          ? 'No hay productos de intercambio disponibles'
                                          : (widget.query != null
                                              ? 'No se encontraron resultados'
                                              : 'Aún no hay productos en esta categoría')),
                                  textAlign: TextAlign.center,
                                  style: const TextStyle(fontSize: 16, color: Colors.grey, fontWeight: FontWeight.w500),
                                ),
                              ],
                            ),
                            SizedBox(height: MediaQuery.of(context).size.height * 0.15),
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
                        )
                      : CustomScrollView(
                          slivers: [
                            SliverPadding(
                              padding: const EdgeInsets.all(12),
                              sliver: SliverGrid(
                                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                                  crossAxisCount: 2,
                                  childAspectRatio: computedAspectRatio,
                                  crossAxisSpacing: 10,
                                  mainAxisSpacing: 10,
                                ),
                                delegate: SliverChildBuilderDelegate(
                                  (ctx, i) {
                                    return _ItemGridCard(item: _items[i], currentUserId: _currentUserId);
                                  },
                                  childCount: _items.length,
                                ),
                              ),
                            ),
                            if (_hasMore)
                              SliverToBoxAdapter(
                                child: Builder(
                                  builder: (context) {
                                    WidgetsBinding.instance.addPostFrameCallback((_) {
                                      if (!_loading) {
                                        _load();
                                      }
                                    });
                                    return const Padding(
                                      padding: EdgeInsets.symmetric(vertical: 16.0),
                                      child: Center(child: CircularProgressIndicator(color: kPrimary)),
                                    );
                                  },
                                ),
                              ),
                            SliverToBoxAdapter(
                              child: Container(
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
                            ),
                            const SliverToBoxAdapter(
                              child: FooterWidget(),
                            ),
                          ],
                        ),
                ),
    );
  }
}

class _ItemGridCard extends StatelessWidget {
  final Map item;
  final int? currentUserId;
  const _ItemGridCard({required this.item, this.currentUserId});
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
        try {
          final data = jsonDecode(res.body);
          if (data['success'] == true) {
            isSuccess = true;
            message = data['message'] ?? '¡Agregado al carrito!';
            if (data['cart_count'] != null) {
              ApiClient.cartCountNotifier.value = int.tryParse(data['cart_count'].toString()) ?? (ApiClient.cartCountNotifier.value + 1);
            } else {
              ApiClient.cartCountNotifier.value++;
            }
          } else {
            message = data['message'] ?? 'Error al agregar';
          }
        } catch (_) {
          isSuccess = true;
          ApiClient.cartCountNotifier.value++;
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
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 6)],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Stack(
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(10)),
                child: ItemImage(
                  item: item,
                  height: 115,
                  width: double.infinity,
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
                      Text(item['item'] ?? '', maxLines: 2, overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: kTextDark)),
                      const SizedBox(height: 6),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          if (item['valor'] != null)
                            Expanded(
                              child: Text(
                                'RD\$ ${item['valor']}',
                                style: const TextStyle(color: kPrimary, fontWeight: FontWeight.bold, fontSize: 12),
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
                                fontSize: 8.5,
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
                              color: yaEnCarrito ? const Color(0xFFEFF6FF) : const Color(0xFFF5F3FF),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: yaEnCarrito ? const Color(0xFFBFDBFE) : const Color(0xFFC7D2FE),
                                width: 1,
                              ),
                            ),
                            child: Text(
                              yaEnCarrito ? 'EN CARRITO' : 'EN NEGOCIACIÓN',
                              style: TextStyle(
                                fontSize: 9,
                                fontWeight: FontWeight.bold,
                                color: yaEnCarrito ? const Color(0xFF1D4ED8) : const Color(0xFF4F46E5),
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
