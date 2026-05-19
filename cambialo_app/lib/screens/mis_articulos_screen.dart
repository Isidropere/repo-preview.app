import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import 'item_detail_screen.dart';
import 'publicar_articulo_screen.dart';

/// Lista de artículos propios del usuario autenticado
class MisArticulosScreen extends StatefulWidget {
  const MisArticulosScreen({super.key});
  @override
  State<MisArticulosScreen> createState() => _MisArticulosScreenState();
}

class _MisArticulosScreenState extends State<MisArticulosScreen> {
  List _items   = [];
  bool _loading = true;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await ApiClient.get('/mis-items', auth: true, useCache: false);
    if (res.statusCode == 200) {
      setState(() { _items = jsonDecode(res.body); _loading = false; });
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
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancelar')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Eliminar', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    ) ?? false;
    if (!ok) return;
    await ApiClient.delete('/items/$idItem', auth: true);
    ApiClient.clearCache('/mis-items');
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mis Artículos')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          await Navigator.push(context, MaterialPageRoute(builder: (_) => const PublicarArticuloScreen()));
          _load();
        },
        backgroundColor: kPrimary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Nuevo', style: TextStyle(color: Colors.white)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : _items.isEmpty
              ? Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                  Icon(Icons.inventory_2_outlined, size: 56, color: Colors.grey.shade300),
                  const SizedBox(height: 12),
                  Text('No tienes artículos publicados', style: TextStyle(color: kTextGray)),
                ]))
              : RefreshIndicator(
                  onRefresh: _load, color: kPrimary,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(12),
                    itemCount: _items.length,
                    itemBuilder: (_, i) {
                      final item   = _items[i];
                      final imgUrl = item['image_url'] as String?;
                      final activo = item['estatus'] == 1;
                      return GestureDetector(
                        onTap: () => Navigator.push(context, MaterialPageRoute(
                            builder: (_) => ItemDetailScreen(itemId: item['id_item']))),
                        child: Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          decoration: BoxDecoration(
                            color: Colors.white, borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: Colors.grey.shade200),
                          ),
                          child: Row(children: [
                            ClipRRect(
                              borderRadius: const BorderRadius.horizontal(left: Radius.circular(10)),
                              child: imgUrl != null
                                  ? Image.network(imgUrl, width: 90, height: 90, fit: BoxFit.cover)
                                  : Container(width: 90, height: 90, color: Colors.grey.shade100,
                                      child: const Icon(Icons.image_not_supported, color: Colors.grey)),
                            ),
                            Expanded(child: Padding(
                              padding: const EdgeInsets.all(10),
                              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                                Text(item['item'] ?? '', maxLines: 1, overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                const SizedBox(height: 4),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: (activo ? Colors.green : Colors.orange).withOpacity(0.15),
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                  child: Text(activo ? 'Activo' : 'Pendiente aprobación',
                                      style: TextStyle(fontSize: 10,
                                          color: activo ? Colors.green : Colors.orange,
                                          fontWeight: FontWeight.w600)),
                                ),
                                const SizedBox(height: 4),
                                Text('RD\$ ${item['valor'] ?? 0}',
                                    style: const TextStyle(fontSize: 13, color: kPrimary, fontWeight: FontWeight.bold)),
                              ]),
                            )),
                            IconButton(
                              icon: const Icon(Icons.delete_outline, color: Colors.red, size: 20),
                              onPressed: () => _eliminar(item['id_item'], item['item'] ?? ''),
                            ),
                          ]),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
