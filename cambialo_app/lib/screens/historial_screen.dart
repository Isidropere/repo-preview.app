import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

/// Historial de compras, ventas e intercambios
/// Fiel al diseño web con tabs
class HistorialScreen extends StatefulWidget {
  const HistorialScreen({super.key});
  @override
  State<HistorialScreen> createState() => _HistorialScreenState();
}

class _HistorialScreenState extends State<HistorialScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabs;
  List _compras      = [];
  List _ventas       = [];
  List _intercambios = [];
  bool _loading      = true;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 3, vsync: this);
    _load();
  }

  @override
  void dispose() {
    _tabs.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await ApiClient.get('/historial', auth: true);
    if (res.statusCode == 200) {
      final body = jsonDecode(res.body);
      setState(() {
        _compras      = body['compras']      ?? [];
        _ventas       = body['ventas']       ?? [];
        _intercambios = body['intercambios'] ?? [];
        _loading      = false;
      });
    } else {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Historial general'),
        bottom: TabBar(
          controller: _tabs,
          labelColor: kPrimary,
          unselectedLabelColor: kTextGray,
          indicatorColor: kPrimary,
          tabs: const [
            Tab(text: 'Compras'),
            Tab(text: 'Ventas'),
            Tab(text: 'Intercambios'),
          ],
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : TabBarView(
              controller: _tabs,
              children: [
                _ComprasTab(compras: _compras),
                _VentasTab(ventas: _ventas),
                _IntercambiosTab(intercambios: _intercambios),
              ],
            ),
    );
  }
}

// ── Tab Compras ──────────────────────────────────────────────────────────
class _ComprasTab extends StatelessWidget {
  final List compras;
  const _ComprasTab({required this.compras});

  @override
  Widget build(BuildContext context) {
    if (compras.isEmpty) return _empty('No tienes compras registradas aún', Icons.shopping_cart_outlined);
    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: compras.length,
      itemBuilder: (_, i) {
        final c = compras[i];
        final estatus = c['estatus'] ?? 'pendiente';
        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.shade200),
            boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 6)],
          ),
          child: Column(children: [
            // Cabecera
            Padding(
              padding: const EdgeInsets.all(12),
              child: Row(children: [
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    Text('Orden #${(c['id_pago_compra'] ?? '').toString().substring(0, 8)}...',
                        style: const TextStyle(fontSize: 12, color: kTextGray)),
                    const SizedBox(width: 8),
                    _EstatusBadge(estatus: estatus),
                  ]),
                  const SizedBox(height: 4),
                  Text(c['fecha'] ?? '', style: TextStyle(fontSize: 11, color: kTextGray)),
                ])),
                Text('RD\$ ${c['total'] ?? 0}',
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: kPrimary)),
              ]),
            ),
            // Items
            if ((c['pago_items'] as List? ?? []).isNotEmpty)
              ...((c['pago_items'] as List).map((pi) => Padding(
                padding: const EdgeInsets.fromLTRB(12, 0, 12, 8),
                child: Row(children: [
                  Container(width: 40, height: 40, color: Colors.grey.shade100,
                      child: const Icon(Icons.inventory_2_outlined, color: Colors.grey, size: 20)),
                  const SizedBox(width: 10),
                  Expanded(child: Text(pi['nombre_item'] ?? '',
                      style: const TextStyle(fontSize: 12, color: kTextDark))),
                  Text('x${pi['cantidad']}', style: TextStyle(fontSize: 12, color: kTextGray)),
                ]),
              ))),
          ]),
        );
      },
    );
  }
}

// ── Tab Ventas ───────────────────────────────────────────────────────────
class _VentasTab extends StatelessWidget {
  final List ventas;
  const _VentasTab({required this.ventas});

  @override
  Widget build(BuildContext context) {
    if (ventas.isEmpty) return _empty('No tienes ventas registradas aún', Icons.sell_outlined);
    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: ventas.length,
      itemBuilder: (_, i) {
        final v = ventas[i];
        final item = v['item'] as Map? ?? {};
        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Row(children: [
            Container(width: 48, height: 48, color: Colors.grey.shade100,
                child: const Icon(Icons.inventory_2_outlined, color: Colors.grey)),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(item['item'] ?? 'Artículo',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: kTextDark)),
              Text('Cantidad: ${v['cantidad']}', style: TextStyle(fontSize: 12, color: kTextGray)),
              if (item['valor'] != null)
                Text('RD\$ ${item['valor']}',
                    style: const TextStyle(fontSize: 12, color: kPrimary, fontWeight: FontWeight.bold)),
            ])),
          ]),
        );
      },
    );
  }
}

// ── Tab Intercambios ─────────────────────────────────────────────────────
class _IntercambiosTab extends StatelessWidget {
  final List intercambios;
  const _IntercambiosTab({required this.intercambios});

  @override
  Widget build(BuildContext context) {
    if (intercambios.isEmpty) return _empty('No tienes intercambios registrados aún', Icons.swap_horiz_outlined);
    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: intercambios.length,
      itemBuilder: (_, i) {
        final neg = intercambios[i];
        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Negociación #${neg['id_negociacion']}',
                  style: TextStyle(fontSize: 11, color: kTextGray)),
              const SizedBox(height: 4),
              Text(neg['mensaje_inicial'] ?? '',
                  maxLines: 2, overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 13, color: kTextDark)),
              if (neg['monto_oferta'] != null)
                Text('RD\$ ${neg['monto_oferta']}',
                    style: const TextStyle(fontSize: 12, color: kPrimary, fontWeight: FontWeight.bold)),
            ])),
            const SizedBox(width: 12),
            _EstatusBadge(estatus: neg['estado'] ?? 'Inicial'),
          ]),
        );
      },
    );
  }
}

// ── Helpers ──────────────────────────────────────────────────────────────
Widget _empty(String msg, IconData icon) => Center(
  child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
    Icon(icon, size: 56, color: Colors.grey.shade300),
    const SizedBox(height: 12),
    Text(msg, style: TextStyle(color: kTextGray, fontSize: 14)),
  ]),
);

class _EstatusBadge extends StatelessWidget {
  final String estatus;
  const _EstatusBadge({required this.estatus});

  @override
  Widget build(BuildContext context) {
    Color bg, fg;
    switch (estatus.toLowerCase()) {
      case 'aprobado': case 'aceptada': case 'entregado':
        bg = const Color(0xFFDCFCE7); fg = const Color(0xFF15803D); break;
      case 'enviado':
        bg = const Color(0xFFDBEAFE); fg = const Color(0xFF1D4ED8); break;
      case 'pendiente': case 'inicial':
        bg = const Color(0xFFFEF9C3); fg = const Color(0xFFB45309); break;
      default:
        bg = const Color(0xFFFEE2E2); fg = const Color(0xFFB91C1C);
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
      child: Text(estatus[0].toUpperCase() + estatus.substring(1),
          style: TextStyle(fontSize: 11, color: fg, fontWeight: FontWeight.w600)),
    );
  }
}
