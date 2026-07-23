import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

class MisVentasTalentosScreen extends StatefulWidget {
  const MisVentasTalentosScreen({super.key});

  @override
  State<MisVentasTalentosScreen> createState() => _MisVentasTalentosScreenState();
}

class _MisVentasTalentosScreenState extends State<MisVentasTalentosScreen> with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  bool _loading = true;
  List _pendientes = [];
  List _procesadas = [];

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
    _load();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiClient.get('/solicitudes-servicio/mis-ventas-talentos', auth: true, useCache: false);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (mounted) {
          setState(() {
            _pendientes = data['pendientes'] ?? [];
            _procesadas = data['procesadas'] ?? [];
            _loading = false;
          });
        }
      } else {
        if (mounted) setState(() => _loading = false);
      }
    } catch (e) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _ejecutarAccion(int id, String url) async {
    setState(() => _loading = true);
    try {
      final res = await ApiClient.post(url, {}, auth: true);
      if (res.statusCode == 200 || res.statusCode == 302) {
        _load();
      } else {
        if (mounted) {
          setState(() => _loading = false);
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Error al ejecutar la acción.')));
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error de conexión: $e')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        title: const Text('Mis Ventas de Talentos'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        bottom: TabBar(
          controller: _tabCtrl,
          labelColor: kPrimary,
          unselectedLabelColor: kTextGray,
          indicatorColor: kPrimary,
          tabs: [
            Tab(text: 'Pendientes (${_pendientes.length})'),
            Tab(text: 'Procesadas (${_procesadas.length})'),
          ],
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tabCtrl,
              children: [
                _buildList(_pendientes, esPendiente: true),
                _buildList(_procesadas, esPendiente: false),
              ],
            ),
    );
  }

  Widget _buildList(List items, {required bool esPendiente}) {
    if (items.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.inbox, size: 48, color: Colors.grey.shade300),
            const SizedBox(height: 16),
            Text(esPendiente ? 'No tienes solicitudes pendientes.' : 'No tienes solicitudes procesadas.', style: const TextStyle(color: kTextGray)),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: items.length,
      itemBuilder: (context, i) {
        final item = items[i];
        return _SolicitudCard(
          solicitud: item,
          esPendiente: esPendiente,
          onAprobar: () => _ejecutarAccion(item['id_solicitud'], '/solicitudes-servicio/${item['id_solicitud']}/aprobar-json'),
          onRechazar: () => _ejecutarAccion(item['id_solicitud'], '/solicitudes-servicio/${item['id_solicitud']}/rechazar-json'),
        );
      },
    );
  }
}

class _SolicitudCard extends StatelessWidget {
  final Map solicitud;
  final bool esPendiente;
  final VoidCallback onAprobar;
  final VoidCallback onRechazar;

  const _SolicitudCard({
    required this.solicitud,
    required this.esPendiente,
    required this.onAprobar,
    required this.onRechazar,
  });

  @override
  Widget build(BuildContext context) {
    final comprador = solicitud['comprador'] ?? {};
    final item = solicitud['item'] ?? {};
    final itemName = item['item'] ?? 'Servicio';
    final fechaServicio = solicitud['fecha_servicio'] != null ? solicitud['fecha_servicio'].toString().split('T')[0] : '';
    final monto = solicitud['monto_total'] ?? 0;
    
    // Ubicación (Comprador)
    final direcciones = comprador['direcciones'] as List? ?? [];
    String ubicacion = 'No especificada';
    if (direcciones.isNotEmpty) {
      final dir = direcciones[0];
      final prov = dir['provincia']?['provincia'] ?? '';
      final mun = dir['municipio']?['municipio'] ?? '';
      if (prov.isNotEmpty || mun.isNotEmpty) {
        ubicacion = '$mun, $prov';
      }
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(child: Text(itemName, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold))),
                Text('RD\$ $monto', style: const TextStyle(fontSize: 16, color: kPrimary, fontWeight: FontWeight.bold)),
              ],
            ),
            const SizedBox(height: 8),
            Text('Comprador: ${comprador['nombres']} ${comprador['apellidos']}', style: const TextStyle(fontSize: 14)),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.calendar_today, size: 14, color: kTextGray),
                const SizedBox(width: 4),
                Text('Fecha: $fechaServicio', style: const TextStyle(fontSize: 13, color: kTextGray)),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.location_on, size: 14, color: kTextGray),
                const SizedBox(width: 4),
                Text('Lugar: $ubicacion', style: const TextStyle(fontSize: 13, color: kTextGray)),
              ],
            ),
            const SizedBox(height: 12),
            if (esPendiente)
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: onRechazar,
                      style: OutlinedButton.styleFrom(
                        foregroundColor: Colors.red,
                        side: const BorderSide(color: Colors.red),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      child: const Text('Rechazar'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: onAprobar,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      child: const Text('Aprobar', style: TextStyle(color: Colors.white)),
                    ),
                  ),
                ],
              )
            else
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: solicitud['estado'] == 'aprobada' || solicitud['estado'] == 'pagada' ? Colors.green.shade50 : Colors.red.shade50,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  solicitud['estado'].toString().toUpperCase(),
                  style: TextStyle(
                    color: solicitud['estado'] == 'aprobada' || solicitud['estado'] == 'pagada' ? Colors.green : Colors.red,
                    fontWeight: FontWeight.bold,
                    fontSize: 12,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
