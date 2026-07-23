import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import 'historial_screen.dart';
import 'mis_intercambios_screen.dart';
import 'mis_ventas_talentos_screen.dart';

class NotificacionesScreen extends StatefulWidget {
  const NotificacionesScreen({super.key});

  @override
  State<NotificacionesScreen> createState() => _NotificacionesScreenState();
}

class _NotificacionesScreenState extends State<NotificacionesScreen> {
  List _notificaciones = [];
  bool _loading = true;
  int _selectedTab = 0; // 0 = Todas, 1 = Sin leer

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiClient.get('/notificaciones/todas', auth: true, useCache: false);
      if (res.statusCode == 200) {
        if (mounted) {
          setState(() {
            _notificaciones = jsonDecode(res.body)['mensajes'] ?? [];
            _loading = false;
          });
        }
      } else {
        if (mounted) setState(() => _loading = false);
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _marcarTodasLeidas() async {
    setState(() => _loading = true);
    try {
      await ApiClient.post('/notificaciones/leer-todas', {}, auth: true);
      _load();
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _marcarLeidaYNavegar(Map<String, dynamic> notif) async {
    final id = notif['id'];
    final msg = (notif['mensaje'] ?? '').toString();
    final idOferta = notif['id_oferta'];

    try {
      await ApiClient.post('/notificaciones/$id/leido', {}, auth: true);
    } catch (_) {}

    if (!mounted) return;

    final msgLower = msg.toLowerCase();
    final bool esServicio = msg.contains('[Servicio]') || msgLower.contains('talento') || msgLower.contains('servicio');
    final bool esIntercambio = msg.contains('[Intercambio]') || msgLower.contains('intercambio') || msgLower.contains('negociaci') || msgLower.contains('propuesta');
    final bool esCompra = msg.contains('[Compra]') || msgLower.contains('tu orden #');
    final bool esVenta = msg.contains('[Venta]') || (msgLower.contains('orden #') && !msg.contains('[Compra]'));

    if (esServicio) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const MisVentasTalentosScreen()),
      );
    } else if (esIntercambio) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const MisIntercambiosScreen()),
      );
    } else if (esCompra) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const HistorialScreen(initialTabIndex: 0)),
      );
    } else if (esVenta) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const HistorialScreen(initialTabIndex: 1)),
      );
    } else {
      _load();
    }
  }

  String _formatDate(String? dateStr) {
    if (dateStr == null) return '';
    final parsed = DateTime.tryParse(dateStr);
    if (parsed == null) return '';
    
    final local = parsed.toLocal();
    final now = DateTime.now();
    final difference = now.difference(local);

    if (difference.inMinutes < 60) {
      if (difference.inMinutes <= 1) return 'Ahora';
      return 'Hace ${difference.inMinutes} min';
    } else if (difference.inHours < 24) {
      return 'Hace ${difference.inHours} ${difference.inHours == 1 ? 'hora' : 'horas'}';
    } else if (difference.inDays < 7) {
      return 'Hace ${difference.inDays} ${difference.inDays == 1 ? 'día' : 'días'}';
    } else {
      return '${local.day.toString().padLeft(2, '0')}/${local.month.toString().padLeft(2, '0')}/${local.year}';
    }
  }

  @override
  Widget build(BuildContext context) {
    final unreadCount = _notificaciones.where((n) => n['leido'] == 0).length;
    final filtered = _selectedTab == 0
        ? _notificaciones
        : _notificaciones.where((n) => n['leido'] == 0).toList();

    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: const Text(
          'Notificaciones',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 20),
        ),
        actions: [
          if (unreadCount > 0)
            TextButton.icon(
              onPressed: _marcarTodasLeidas,
              icon: const Icon(Icons.done_all, size: 16, color: kPrimary),
              label: const Text(
                'Marcar leídas',
                style: TextStyle(color: kPrimary, fontSize: 13, fontWeight: FontWeight.bold),
              ),
            ),
          IconButton(
            icon: const Icon(Icons.refresh, color: kTextDark),
            onPressed: _load,
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : Column(
              children: [
                // Filtros de pestaña
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  color: Colors.white,
                  child: Row(
                    children: [
                      _buildTabButton(0, 'Todas', _notificaciones.length),
                      const SizedBox(width: 10),
                      _buildTabButton(1, 'Sin Leer', unreadCount),
                    ],
                  ),
                ),
                
                // Lista de notificaciones
                Expanded(
                  child: filtered.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(24),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade100,
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  _selectedTab == 0 
                                      ? Icons.notifications_off_outlined 
                                      : Icons.mark_chat_read_outlined,
                                  size: 48,
                                  color: Colors.grey.shade400,
                                ),
                              ),
                              const SizedBox(height: 16),
                              Text(
                                _selectedTab == 0 
                                    ? 'No tienes notificaciones' 
                                    : '¡Estás al día! No hay alertas sin leer',
                                style: const TextStyle(
                                  color: kTextDark,
                                  fontWeight: FontWeight.w600,
                                  fontSize: 15,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                _selectedTab == 0 
                                    ? 'Te avisaremos cuando haya novedades.' 
                                    : 'Todas tus alertas han sido leídas.',
                                style: const TextStyle(
                                  color: kTextGray,
                                  fontSize: 13,
                                ),
                              ),
                            ],
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: _load,
                          color: kPrimary,
                          child: ListView.builder(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                            itemCount: filtered.length,
                            itemBuilder: (_, i) {
                              final n = filtered[i];
                              final msg = (n['mensaje'] ?? '').toString();
                              final isUnread = n['leido'] == 0;
                              final senderName = n['sender'] != null ? n['sender']['nombres'] : 'Sistema';

                              IconData icon = Icons.notifications_outlined;
                              Color accentColor = kPrimary;
                              String typeTitle = 'Notificación';

                              if (msg.contains('[Servicio]') || msg.toLowerCase().contains('talento') || msg.toLowerCase().contains('servicio')) {
                                icon = Icons.star_outline;
                                accentColor = const Color(0xFF2196F3);
                                typeTitle = 'Servicio';
                              } else if (msg.contains('[Intercambio]') || msg.toLowerCase().contains('intercambio') || msg.toLowerCase().contains('negociaci') || msg.toLowerCase().contains('propuesta')) {
                                icon = Icons.swap_horiz_outlined;
                                accentColor = const Color(0xFF4CAF50);
                                typeTitle = 'Intercambio';
                              } else if (msg.contains('[Compra]') || msg.toLowerCase().contains('tu orden #')) {
                                icon = Icons.credit_card_outlined;
                                accentColor = const Color(0xFFFF9800);
                                typeTitle = 'Compra';
                              } else if (msg.contains('[Venta]') || (msg.toLowerCase().contains('orden #') && !msg.contains('[Compra]'))) {
                                icon = Icons.storefront_outlined;
                                accentColor = const Color(0xFF9C27B0);
                                typeTitle = 'Venta';
                              }

                              return Container(
                                margin: const EdgeInsets.only(bottom: 12),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: isUnread ? accentColor.withOpacity(0.2) : Colors.grey.shade100,
                                  ),
                                  boxShadow: [
                                    BoxShadow(
                                      color: isUnread 
                                          ? accentColor.withOpacity(0.06) 
                                          : Colors.black.withOpacity(0.01),
                                      blurRadius: 10,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                child: ClipRRect(
                                  borderRadius: BorderRadius.circular(12),
                                  child: InkWell(
                                    onTap: () => _marcarLeidaYNavegar(n),
                                    child: Stack(
                                      children: [
                                        Positioned(
                                          left: 0,
                                          top: 0,
                                          bottom: 0,
                                          width: 4,
                                          child: Container(
                                            color: isUnread ? accentColor : accentColor.withOpacity(0.35),
                                          ),
                                        ),
                                        Padding(
                                          padding: const EdgeInsets.all(16),
                                          child: Row(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              const SizedBox(width: 6),
                                              CircleAvatar(
                                                radius: 20,
                                                backgroundColor: accentColor.withOpacity(0.08),
                                                child: Icon(icon, color: accentColor, size: 20),
                                              ),
                                              const SizedBox(width: 14),
                                              Expanded(
                                                child: Column(
                                                  crossAxisAlignment: CrossAxisAlignment.start,
                                                  children: [
                                                    Row(
                                                      children: [
                                                        Text(
                                                          typeTitle,
                                                          style: TextStyle(
                                                            fontSize: 12,
                                                            fontWeight: FontWeight.bold,
                                                            color: accentColor,
                                                            letterSpacing: 0.5,
                                                          ),
                                                        ),
                                                        const SizedBox(width: 8),
                                                        Container(
                                                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                          decoration: BoxDecoration(
                                                            color: n['sender'] != null ? Colors.blue.shade50 : Colors.grey.shade100,
                                                            borderRadius: BorderRadius.circular(6),
                                                          ),
                                                          child: Text(
                                                            senderName,
                                                            style: TextStyle(
                                                              fontSize: 9,
                                                              fontWeight: FontWeight.w600,
                                                              color: n['sender'] != null ? Colors.blue.shade700 : Colors.grey.shade700,
                                                            ),
                                                          ),
                                                        ),
                                                        const Spacer(),
                                                        Text(
                                                          _formatDate(n['created_at']?.toString()),
                                                          style: TextStyle(fontSize: 10, color: Colors.grey.shade400),
                                                        ),
                                                      ],
                                                    ),
                                                    const SizedBox(height: 6),
                                                    Text(
                                                      msg,
                                                      style: const TextStyle(
                                                        fontSize: 13.5,
                                                        fontWeight: FontWeight.w500,
                                                        color: kTextDark,
                                                        height: 1.3,
                                                      ),
                                                    ),
                                                  ],
                                                ),
                                              ),
                                              if (isUnread) ...[
                                                const SizedBox(width: 8),
                                                Container(
                                                  margin: const EdgeInsets.only(top: 4),
                                                  width: 8,
                                                  height: 8,
                                                  decoration: BoxDecoration(
                                                    color: accentColor,
                                                    shape: BoxShape.circle,
                                                  ),
                                                ),
                                              ],
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
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

  Widget _buildTabButton(int index, String label, int count) {
    final isSelected = _selectedTab == index;
    return GestureDetector(
      onTap: () => setState(() => _selectedTab = index),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? kPrimary : Colors.transparent,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? kPrimary : Colors.grey.shade300,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              label,
              style: TextStyle(
                color: isSelected ? Colors.white : kTextDark,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                fontSize: 13,
              ),
            ),
            const SizedBox(width: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: isSelected ? Colors.white24 : (index == 1 ? Colors.red.shade50 : Colors.grey.shade100),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                '$count',
                style: TextStyle(
                  color: isSelected ? Colors.white : (index == 1 ? Colors.red : kTextGray),
                  fontSize: 10,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
