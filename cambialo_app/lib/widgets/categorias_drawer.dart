import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import '../screens/items_list_screen.dart';

/// Drawer de Categorías dinámico con filtro de búsqueda en tiempo real
class CategoriasDrawer extends StatefulWidget {
  const CategoriasDrawer({super.key});

  @override
  State<CategoriasDrawer> createState() => _CategoriasDrawerState();
}

class _CategoriasDrawerState extends State<CategoriasDrawer> {
  bool _loading = true;
  List<dynamic> _allCategorias = [];
  List<dynamic> _filteredCategorias = [];
  final _searchCtrl = TextEditingController();
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadCategorias();
    _searchCtrl.addListener(_onSearchChanged);
  }

  @override
  void dispose() {
    _searchCtrl.removeListener(_onSearchChanged);
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadCategorias() async {
    try {
      final res = await ApiClient.get('/categorias');
      if (res.statusCode == 200) {
        final List<dynamic> data = jsonDecode(res.body);
        
        // Ordenar alfabéticamente
        data.sort((a, b) => (a['categoria'] ?? '')
            .toString()
            .toLowerCase()
            .compareTo((b['categoria'] ?? '').toString().toLowerCase()));

        if (mounted) {
          setState(() {
            _allCategorias = data;
            _filteredCategorias = data;
            _loading = false;
          });
        }
      } else {
        if (mounted) {
          setState(() {
            _error = 'Error al cargar categorías';
            _loading = false;
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'Error de conexión';
          _loading = false;
        });
      }
    }
  }

  void _onSearchChanged() {
    final query = _searchCtrl.text.toLowerCase().trim();
    setState(() {
      if (query.isEmpty) {
        _filteredCategorias = _allCategorias;
      } else {
        _filteredCategorias = _allCategorias.where((c) {
          final nombre = (c['categoria'] ?? '').toString().toLowerCase();
          return nombre.contains(query);
        }).toList();
      }
    });
  }

  IconData _getCategoryIcon(int idCat) {
    switch (idCat) {
      case 1: return Icons.music_note_outlined;
      case 2: return Icons.kitchen_outlined;
      case 3: return Icons.electrical_services_outlined;
      case 4: return Icons.sports_esports_outlined;
      case 5: return Icons.chair_outlined;
      case 6: return Icons.directions_car_outlined;
      case 7: return Icons.handyman_outlined;
      case 8: return Icons.diamond_outlined;
      case 9: return Icons.menu_book_outlined;
      case 10: return Icons.category_outlined;
      case 11: return Icons.eighteen_up_rating_outlined;
      case 13: return Icons.spa_outlined;
      case 14: return Icons.color_lens_outlined;
      case 15: return Icons.sports_soccer_outlined;
      case 16: return Icons.home_outlined;
      case 17: return Icons.yard_outlined;
      case 19: return Icons.phone_android_outlined;
      case 20: return Icons.child_care_outlined;
      case 21: return Icons.museum_outlined; // Antigüedades
      case 22: return Icons.baby_changing_station_outlined;
      case 23: return Icons.pets_outlined;
      case 24: return Icons.computer_outlined;
      case 25: return Icons.library_books_outlined;
      case 26: return Icons.woman_2_outlined; // Damas
      case 27: return Icons.man_2_outlined; // Caballeros
      case 28: return Icons.business_center_outlined;
      case 29: return Icons.star_outline; // Talentos
      default: return Icons.category_outlined;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Drawer(
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.zero,
      ),
      child: Column(
        children: [
          // Header de Categorías color naranja
          Container(
            padding: EdgeInsets.only(
              top: MediaQuery.of(context).padding.top + 12,
              bottom: 16,
              left: 16,
              right: 16,
            ),
            color: kPrimary,
            child: Row(
              children: [
                const Icon(Icons.folder_open_outlined, color: Colors.white, size: 24),
                const SizedBox(width: 12),
                const Text(
                  'Categorías',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const Spacer(),
                GestureDetector(
                  onTap: () => Navigator.pop(context),
                  child: const Icon(Icons.close, color: Colors.white, size: 24),
                ),
              ],
            ),
          ),

          // Buscador de Categorías
          Padding(
            padding: const EdgeInsets.all(12.0),
            child: TextField(
              controller: _searchCtrl,
              decoration: InputDecoration(
                hintText: 'Buscar categoría...',
                hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 14),
                prefixIcon: Icon(Icons.search, color: Colors.grey.shade400, size: 20),
                filled: true,
                fillColor: Colors.grey.shade100,
                contentPadding: const EdgeInsets.symmetric(vertical: 10),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: BorderSide(color: Colors.grey.shade200),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: const BorderSide(color: kPrimary),
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
          ),

          // Lista de Categorías
          Expanded(
            child: _buildCategoryList(),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryList() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator(color: kPrimary));
    }

    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, color: Colors.grey, size: 40),
              const SizedBox(height: 12),
              Text(
                _error!,
                style: const TextStyle(color: kTextGray, fontSize: 14),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 12),
              ElevatedButton(
                onPressed: () {
                  setState(() {
                    _loading = true;
                    _error = null;
                  });
                  _loadCategorias();
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: kPrimary,
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                ),
                child: const Text('Reintentar', style: TextStyle(color: Colors.white)),
              ),
            ],
          ),
        ),
      );
    }

    if (_filteredCategorias.isEmpty) {
      return const Center(
        child: Text(
          'No se encontraron categorías',
          style: TextStyle(color: kTextGray, fontSize: 14),
        ),
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.only(bottom: 24),
      itemCount: _filteredCategorias.length,
      separatorBuilder: (_, __) => Divider(height: 1, color: Colors.grey.shade100),
      itemBuilder: (context, i) {
        final cat = _filteredCategorias[i];
        final idCat = ApiClient.parseInt(cat['id_categoria_item']) ?? 0;
        final nombre = cat['categoria']?.toString() ?? '';

        return ListTile(
          leading: Icon(
            _getCategoryIcon(idCat),
            color: kPrimary,
            size: 22,
          ),
          title: Text(
            nombre,
            style: const TextStyle(
              color: kTextDark,
              fontSize: 14,
              fontWeight: FontWeight.w600,
            ),
          ),
          trailing: const Icon(Icons.chevron_right, color: Colors.grey, size: 18),
          onTap: () {
            // Cerrar el drawer
            Navigator.pop(context);
            
            // Navegar a los artículos de esa categoría
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => ItemsListScreen(
                  categoriaId: idCat,
                  title: nombre,
                ),
              ),
            );
          },
        );
      },
    );
  }
}
