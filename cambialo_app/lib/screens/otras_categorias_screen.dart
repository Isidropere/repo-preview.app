import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import 'items_list_screen.dart';
import '../core/auth_service.dart';
import '../widgets/adulto_verification_dialog.dart';

class OtrasCategoriasScreen extends StatefulWidget {
  const OtrasCategoriasScreen({Key? key}) : super(key: key);

  @override
  State<OtrasCategoriasScreen> createState() => _OtrasCategoriasScreenState();
}

class _OtrasCategoriasScreenState extends State<OtrasCategoriasScreen> {
  bool _loading = true;
  List _categorias = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadCategorias();
  }

  Future<void> _loadCategorias() async {
    try {
      final res = await ApiClient.get('/categorias');
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body) as List;
        final isLoggedIn = await AuthService.isLoggedIn();
        // Excluir las que ya se muestran en el home
        final idsHome = [26, 27, 20, 19, 16, 4, 29, 24, 6, 2, 10];
        
        final filtered = data.where((c) {
          final id = ApiClient.parseInt(c['id_categoria_item']) ?? 0;
          if (idsHome.contains(id)) return false;
          if (id == 11 && !isLoggedIn) return false;
          return true;
        }).toList();

        // Ordenar alfabéticamente
        filtered.sort((a, b) => (a['categoria'] ?? '').toString().compareTo((b['categoria'] ?? '').toString()));

        setState(() {
          _categorias = filtered;
          _loading = false;
        });
      } else {
        setState(() {
          _error = 'Error al cargar categorías';
          _loading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Error de conexión';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgGray,
      appBar: AppBar(
        backgroundColor: kPrimary,
        title: const Text('Otras Categorías'),
        centerTitle: true,
      ),
      body: _buildBody(),
    );
  }

  static const Map<int, IconData> _icons = {
    1: Icons.music_note,
    2: Icons.kitchen,
    3: Icons.electrical_services,
    5: Icons.chair,
    6: Icons.directions_car,
    7: Icons.handyman,
    8: Icons.diamond,
    9: Icons.menu_book,
    10: Icons.category,
    11: Icons.eighteen_up_rating,
    13: Icons.spa,
    14: Icons.color_lens,
    15: Icons.sports_soccer,
    17: Icons.yard,
    21: Icons.account_balance,
    22: Icons.child_care,
    23: Icons.pets,
    24: Icons.computer,
    25: Icons.library_books,
    28: Icons.business_center,
  };

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator(color: kPrimary));
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, color: Colors.grey, size: 48),
            const SizedBox(height: 16),
            Text(
              _error == 'Error de conexión' ? 'Error de conexión' : _error!,
              style: const TextStyle(color: kTextDark, fontSize: 16, fontWeight: FontWeight.bold),
            ),
            if (_error == 'Error de conexión') ...[
              const SizedBox(height: 4),
              const Text(
                'Verifique su conexión a la red',
                style: TextStyle(color: kTextGray, fontSize: 13),
              ),
            ],
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () {
                setState(() {
                  _loading = true;
                  _error = null;
                });
                _loadCategorias();
              },
              style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
              child: const Text('Reintentar'),
            )
          ],
        ),
      );
    }

    if (_categorias.isEmpty) {
      return const Center(
        child: Text('No hay más categorías disponibles', style: TextStyle(color: Colors.grey, fontSize: 16)),
      );
    }

    return GridView.builder(
      padding: const EdgeInsets.all(12),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 4,
        mainAxisSpacing: 10,
        crossAxisSpacing: 10,
        childAspectRatio: 1.0,
      ),
      itemCount: _categorias.length,
      itemBuilder: (context, i) {
        final cat = _categorias[i];
        final idCat = ApiClient.parseInt(cat['id_categoria_item']) ?? 0;
        return InkWell(
          onTap: () async {
            if (idCat == 11) {
              final ok = await AdultoVerificationDialog.showVerification(context);
              if (!ok) return;
            }

            if (context.mounted) {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => ItemsListScreen(
                    categoriaId: idCat,
                    title: cat['categoria']?.toString() ?? '',
                  ),
                ),
              );
            }
          },
          borderRadius: BorderRadius.circular(10),
          child: Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: Colors.grey.shade200),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  blurRadius: 3,
                  offset: const Offset(0, 2),
                )
              ],
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  _icons[idCat] ?? Icons.category_outlined,
                  size: 24,
                  color: kPrimary,
                ),
                const SizedBox(height: 6),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 2),
                  child: Text(
                    cat['categoria'] ?? '',
                    textAlign: TextAlign.center,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: kTextDark,
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
