import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import '../widgets/item_image.dart';
import 'item_detail_screen.dart';

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
  int  _page     = 1;
  bool _hasMore  = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load({bool reset = false}) async {
    if (reset) { _page = 1; _hasMore = true; _items = []; }
    if (!_hasMore) return;
    setState(() => _loading = true);

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
        setState(() => _loading = false);
      }
    } catch (e) {
      if (mounted) setState(() => _loading = false);
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
    return Scaffold(
      appBar: AppBar(
        title: Text(_title),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: _loading && _items.isEmpty
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : RefreshIndicator(
              color: kPrimary,
              onRefresh: () => _load(reset: true),
              child: _items.isEmpty
                  ? ListView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      children: [
                        SizedBox(height: MediaQuery.of(context).size.height * 0.3),
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
                      ],
                    )
                  : GridView.builder(
                      padding: const EdgeInsets.all(12),
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 2,
                        childAspectRatio: 0.72,
                        crossAxisSpacing: 10,
                        mainAxisSpacing: 10,
                      ),
                      itemCount: _items.length + (_hasMore ? 1 : 0),
                      itemBuilder: (ctx, i) {
                        if (i == _items.length) {
                          WidgetsBinding.instance.addPostFrameCallback((_) {
                            if (!_loading) {
                              _load();
                            }
                          });
                          return const Center(child: CircularProgressIndicator(color: kPrimary));
                        }
                        return _ItemGridCard(item: _items[i]);
                      },
                    ),
            ),
    );
  }
}

class _ItemGridCard extends StatelessWidget {
  final Map item;
  const _ItemGridCard({required this.item});
  @override
  Widget build(BuildContext context) {
    final imgUrl = ApiClient.fixImageUrl(item['image_url'] as String?);
    final int itemId = int.tryParse(item['id_item']?.toString() ?? '') ?? 0;
    final int transVal = int.tryParse(item['tipo_trans']?.toString() ?? '') ?? 0;

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
          ClipRRect(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(10)),
            child: ItemImage(
              item: item,
              height: 140,
              width: double.infinity,
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(8),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(item['item'] ?? '', maxLines: 2, overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: kTextDark)),
              const SizedBox(height: 4),
              if (item['valor'] != null)
                Text('RD\$ ${item['valor']}',
                    style: const TextStyle(color: kPrimary, fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 4),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: transVal == 1 ? const Color(0xFFEFF6FF) : const Color(0xFFF0FDF4),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  transVal == 1 ? 'Venta' : (transVal == 2 ? 'Intercambio' : 'Ambos'),
                  style: TextStyle(
                    fontSize: 10,
                    color: transVal == 1 ? const Color(0xFF1D4ED8) : const Color(0xFF15803D),
                  ),
                ),
              ),
            ]),
          ),
        ]),
      ),
    );
  }
}
