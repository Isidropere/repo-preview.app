import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import '../widgets/item_image.dart';
import 'item_detail_screen.dart';
import 'publicar_articulo_screen.dart';

/// Lista de artículos propios del usuario autenticado
class MisArticulosScreen extends StatefulWidget {
  const MisArticulosScreen({super.key});
  @override
  State<MisArticulosScreen> createState() => _MisArticulosScreenState();
}

class _MisArticulosScreenState extends State<MisArticulosScreen> {
  List _items = [];
  bool _loading = true;

  // Filtros de búsqueda (idénticos a la web)
  String _searchQuery = '';
  String _selectedStatus = 'all';
  String _selectedType = 'all';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await ApiClient.get('/mis-items', auth: true, useCache: false);
    if (res.statusCode == 200) {
      final list = jsonDecode(res.body) as List;
      setState(() {
        _items = list.where((item) {
          final tipo = int.tryParse(item['id_tipo_item']?.toString() ?? '');
          final cat = int.tryParse(item['id_categoria_item']?.toString() ?? '');
          return tipo != 2 && cat != 29;
        }).toList();
        _loading = false;
      });
    } else {
      setState(() => _loading = false);
    }
  }

  Future<void> _eliminar(int idItem, String nombre) async {
    final ok = await showDialog<bool>(
          context: context,
          builder: (_) => AlertDialog(
            title: const Text('Eliminar artículo'),
            content: Text('¿Seguro que deseas eliminar "$nombre"?'),
            actions: [
              TextButton(
                  onPressed: () => Navigator.pop(context, false),
                  child: const Text('Cancelar')),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, true),
                style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                child: const Text('Eliminar', style: TextStyle(color: Colors.white)),
              ),
            ],
          ),
        ) ??
        false;
    if (!ok) return;

    setState(() => _loading = true);

    try {
      final res = await ApiClient.delete('/items/$idItem', auth: true);
      if (res.statusCode == 200 || res.statusCode == 204) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Artículo eliminado exitosamente.'),
          backgroundColor: Colors.green,
        ));
        ApiClient.clearCache('/mis-items');
        _load();
      } else {
        setState(() => _loading = false);
        if (!mounted) return;
        final body = jsonDecode(res.body);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(body['message'] ?? 'Error al eliminar el artículo.'),
          backgroundColor: Colors.red,
        ));
      }
    } catch (e) {
      setState(() => _loading = false);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('Error al conectar con el servidor: $e'),
        backgroundColor: Colors.red,
      ));
    }
  }

  String _formatDate(String? dateStr) {
    if (dateStr == null || dateStr.isEmpty) return '';
    try {
      final date = DateTime.parse(dateStr);
      final day = date.day.toString().padLeft(2, '0');
      final month = date.month.toString().padLeft(2, '0');
      final year = date.year;
      return '$day/$month/$year';
    } catch (_) {
      return '';
    }
  }

  String _formatPrice(dynamic price) {
    if (price == null) return '0.00';
    final numVal = double.tryParse(price.toString());
    if (numVal == null) return price.toString();

    final parts = numVal.toStringAsFixed(2).split('.');
    final RegExp reg = RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))');
    String matchFunc(Match match) => '${match[1]},';
    final formattedInt = parts[0].replaceAllMapped(reg, matchFunc);
    return '$formattedInt.${parts[1]}';
  }

  void _showLightbox(String? imageUrl, String title) {
    if (imageUrl == null || imageUrl.isEmpty) return;
    showDialog(
      context: context,
      barrierColor: Colors.black.withOpacity(0.9),
      builder: (context) => GestureDetector(
        onTap: () => Navigator.pop(context),
        child: Dialog(
          backgroundColor: Colors.transparent,
          insetPadding: const EdgeInsets.all(12),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Align(
                alignment: Alignment.topRight,
                child: IconButton(
                  icon: const Icon(Icons.close, color: Colors.white, size: 30),
                  onPressed: () => Navigator.pop(context),
                ),
              ),
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: InteractiveViewer(
                  child: Image.network(
                    imageUrl,
                    fit: BoxFit.contain,
                    errorBuilder: (_, __, ___) => const Icon(
                        Icons.broken_image,
                        color: Colors.white,
                        size: 100),
                  ),
                ),
              ),
              const SizedBox(height: 15),
              Text(
                title,
                textAlign: TextAlign.center,
                style: const TextStyle(
                    color: Colors.white,
                    fontSize: 15,
                    fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    // Aplicar filtros locales de la misma forma que en Javascript de la web
    final filteredItems = _items.where((item) {
      // 1. Filtro por nombre
      final name = (item['item'] ?? '').toString().toLowerCase();
      if (_searchQuery.isNotEmpty &&
          !name.contains(_searchQuery.toLowerCase())) {
        return false;
      }
      // 2. Filtro por estado
      final status = (item['estatus'] ?? '').toString();
      if (_selectedStatus != 'all' && status != _selectedStatus) {
        return false;
      }
      // 3. Filtro por tipo de transacción
      final int transVal = int.tryParse(item['tipo_trans']?.toString() ?? '') ?? 0;
      if (_selectedType == '1') {
        if (transVal != 1 && transVal != 3) return false;
      } else if (_selectedType == '2') {
        if (transVal != 2 && transVal != 3) return false;
      } else if (_selectedType == '3') {
        if (transVal != 3) return false;
      }
      return true;
    }).toList();

    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(title: const Text('Mis Artículos')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          await Navigator.push(
              context,
              MaterialPageRoute(
                  builder: (_) => const PublicarArticuloScreen()));
          _load();
        },
        backgroundColor: kPrimary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Nuevo', style: TextStyle(color: Colors.white)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : Column(
              children: [
                // Sección de Filtros (Replica de la Web)
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    border: Border(
                        bottom: BorderSide(color: Colors.grey.shade200)),
                  ),
                  child: Column(
                    children: [
                      // Buscador por nombre
                      TextField(
                        onChanged: (val) => setState(() => _searchQuery = val),
                        decoration: InputDecoration(
                          hintText: 'Buscar producto...',
                          prefixIcon: const Icon(Icons.search, color: Colors.grey),
                          contentPadding: const EdgeInsets.symmetric(
                              vertical: 8, horizontal: 12),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: BorderSide(color: Colors.grey.shade300),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: BorderSide(color: Colors.grey.shade200),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: kPrimary),
                          ),
                          fillColor: Colors.grey.shade50,
                          filled: true,
                        ),
                      ),
                      const SizedBox(height: 10),
                      // Dropdowns
                      Row(
                        children: [
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              value: _selectedStatus,
                              items: const [
                                DropdownMenuItem(
                                    value: 'all',
                                    child: Text('Todos (Estado)',
                                        style: TextStyle(fontSize: 12))),
                                DropdownMenuItem(
                                    value: '1',
                                    child: Text('Activos',
                                        style: TextStyle(fontSize: 12))),
                                DropdownMenuItem(
                                    value: '2',
                                    child: Text('Inactivos',
                                        style: TextStyle(fontSize: 12))),
                              ],
                              onChanged: (val) => setState(
                                  () => _selectedStatus = val ?? 'all'),
                              decoration: InputDecoration(
                                contentPadding: const EdgeInsets.symmetric(
                                    horizontal: 8, vertical: 8),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide:
                                      BorderSide(color: Colors.grey.shade200),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              value: _selectedType,
                              items: const [
                                DropdownMenuItem(
                                    value: 'all',
                                    child: Text('Tipo',
                                        style: TextStyle(fontSize: 12))),
                                DropdownMenuItem(
                                    value: '1',
                                    child: Text('Venta',
                                        style: TextStyle(fontSize: 12))),
                                DropdownMenuItem(
                                    value: '2',
                                    child: Text('Intercambio',
                                        style: TextStyle(fontSize: 12))),
                                DropdownMenuItem(
                                    value: '3',
                                    child: Text('Ambos',
                                        style: TextStyle(fontSize: 12))),
                              ],
                              onChanged: (val) =>
                                  setState(() => _selectedType = val ?? 'all'),
                              decoration: InputDecoration(
                                contentPadding: const EdgeInsets.symmetric(
                                    horizontal: 8, vertical: 8),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide:
                                      BorderSide(color: Colors.grey.shade200),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                // Listado de Artículos
                Expanded(
                  child: filteredItems.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.inventory_2_outlined,
                                  size: 56, color: Colors.grey.shade300),
                              const SizedBox(height: 12),
                              Text('No se encontraron artículos',
                                  style: TextStyle(color: kTextGray)),
                            ],
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: _load,
                          color: kPrimary,
                          child: ListView.builder(
                            padding: const EdgeInsets.all(12),
                            itemCount: filteredItems.length,
                            itemBuilder: (_, i) {
                              final item = filteredItems[i];
                               final int itemId = int.tryParse(item['id_item']?.toString() ?? '') ?? 0;
                               final int statusVal = int.tryParse(item['estatus']?.toString() ?? '') ?? 0;

                               // Mapeo exacto de estados de la web
                               final String statusText = statusVal == 1
                                   ? 'Activo'
                                   : (statusVal == 2 ? 'Inactivo' : 'Pausado');
                               final Color badgeColor = statusVal == 1
                                   ? Colors.green
                                   : (statusVal == 2
                                       ? Colors.red
                                       : Colors.yellow.shade800);

                               final int transVal = int.tryParse(item['tipo_trans']?.toString() ?? '') ?? 0;
                               final String transText = transVal == 1
                                   ? 'Venta'
                                   : (transVal == 2
                                       ? 'Intercambio'
                                       : 'Ambos');
                               final String pubDate = _formatDate(item['fecha']);

                              return GestureDetector(
                                onTap: () => Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                        builder: (_) => ItemDetailScreen(
                                            itemId: itemId))),
                                child: Container(
                                  margin: const EdgeInsets.only(bottom: 10),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(
                                        color: Colors.grey.shade200),
                                  ),
                                  child: Row(
                                    children: [
                                      // Imagen con Lightbox (Al presionar abre visor)
                                      GestureDetector(
                                        onTap: () {
                                          final rawUrl =
                                              item['image_url']?.toString();
                                          if (rawUrl != null &&
                                              rawUrl.isNotEmpty) {
                                            _showLightbox(
                                                ApiClient.fixImageUrl(rawUrl),
                                                item['item'] ?? '');
                                          }
                                        },
                                        child: ClipRRect(
                                          borderRadius:
                                              const BorderRadius.horizontal(
                                                  left: Radius.circular(10)),
                                          child: ItemImage(
                                            item: item,
                                            width: 90,
                                            height: 90,
                                          ),
                                        ),
                                      ),
                                      // Información Completa
                                      Expanded(
                                        child: Padding(
                                          padding: const EdgeInsets.all(10),
                                          child: Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              // Título
                                              Text(
                                                item['item'] ?? '',
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                                style: const TextStyle(
                                                    fontSize: 13,
                                                    fontWeight: FontWeight.w600),
                                              ),
                                              const SizedBox(height: 5),
                                              // Estado y Vistas
                                              Row(
                                                children: [
                                                  Container(
                                                    padding: const EdgeInsets
                                                        .symmetric(
                                                        horizontal: 6,
                                                        vertical: 2),
                                                    decoration: BoxDecoration(
                                                      color: badgeColor
                                                          .withOpacity(0.15),
                                                      borderRadius:
                                                          BorderRadius.circular(
                                                              4),
                                                    ),
                                                    child: Text(
                                                      statusText,
                                                      style: TextStyle(
                                                          fontSize: 10,
                                                          color: badgeColor,
                                                          fontWeight:
                                                              FontWeight.w600),
                                                    ),
                                                  ),
                                                  const SizedBox(width: 8),
                                                  Icon(
                                                      Icons
                                                          .remove_red_eye_outlined,
                                                      size: 13,
                                                      color:
                                                          Colors.grey.shade500),
                                                  const SizedBox(width: 3),
                                                  Text(
                                                    '${item['views_count'] ?? 0}',
                                                    style: TextStyle(
                                                        fontSize: 11,
                                                        color: Colors
                                                            .grey.shade600),
                                                  ),
                                                ],
                                              ),
                                              const SizedBox(height: 5),
                                              // Precio, Tipo de Transacción y Fecha
                                              Row(
                                                mainAxisAlignment:
                                                    MainAxisAlignment
                                                        .spaceBetween,
                                                children: [
                                                  Text(
                                                    'RD\$ ${_formatPrice(item['valor'])}',
                                                    style: const TextStyle(
                                                        fontSize: 13,
                                                        color: kPrimary,
                                                        fontWeight:
                                                            FontWeight.bold),
                                                  ),
                                                  Text(
                                                    '$transText  |  $pubDate',
                                                    style: TextStyle(
                                                        fontSize: 10,
                                                        color: Colors
                                                            .grey.shade500),
                                                  ),
                                                ],
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                      // Acciones (Editar/Eliminar)
                                      IconButton(
                                        icon: const Icon(Icons.edit_outlined,
                                            color: kPrimary, size: 20),
                                        onPressed: () async {
                                          final updated = await Navigator.push(
                                            context,
                                            MaterialPageRoute(
                                              builder: (_) =>
                                                  PublicarArticuloScreen(
                                                      itemId: itemId),
                                            ),
                                          );
                                          if (updated == true) {
                                            _load();
                                          }
                                        },
                                      ),
                                      IconButton(
                                        icon: const Icon(Icons.delete_outline,
                                            color: Colors.red, size: 20),
                                        onPressed: () => _eliminar(
                                            itemId,
                                            item['item'] ?? ''),
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
                        ),
                ),
              ],
            ),
    );
  }
}
