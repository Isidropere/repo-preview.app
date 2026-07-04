import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import '../widgets/item_image.dart';
import 'negociacion_detalle_screen.dart';

/// Pantalla "Mis Intercambios" — lista negociaciones activas del usuario
class MisIntercambiosScreen extends StatefulWidget {
  const MisIntercambiosScreen({super.key});
  @override
  State<MisIntercambiosScreen> createState() => _MisIntercambiosScreenState();
}

class _MisIntercambiosScreenState extends State<MisIntercambiosScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabs;
  List _comoEmisor   = [];
  List _comoReceptor = [];
  bool _loading      = true;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 2, vsync: this);
    _load();
  }

  @override
  void dispose() {
    _tabs.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await ApiClient.get('/negociaciones', auth: true, useCache: false);
    if (res.statusCode == 200) {
      final body = jsonDecode(res.body);
      setState(() {
        _comoEmisor   = body['como_emisor']   as List? ?? [];
        _comoReceptor = body['como_receptor'] as List? ?? [];
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
        title: const Text('Mis Intercambios'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _load),
        ],
        bottom: TabBar(
          controller: _tabs,
          labelColor: kPrimary,
          unselectedLabelColor: kTextGray,
          indicatorColor: kPrimary,
          tabs: [
            Tab(text: 'Propuestos (${_comoEmisor.length})'),
            Tab(text: 'Recibidos (${_comoReceptor.length})'),
          ],
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : TabBarView(
              controller: _tabs,
              children: [
                _NegList(negociaciones: _comoEmisor,   emptyMsg: 'No has propuesto intercambios aún', rol: 'emisor', onRefresh: _load),
                _NegList(negociaciones: _comoReceptor, emptyMsg: 'No has recibido propuestas aún', rol: 'receptor', onRefresh: _load),
              ],
            ),
    );
  }
}

class _NegList extends StatelessWidget {
  final List negociaciones;
  final String emptyMsg;
  final String rol;
  final VoidCallback onRefresh;

  const _NegList({
    required this.negociaciones,
    required this.emptyMsg,
    required this.rol,
    required this.onRefresh,
  });

  Future<void> _ejecutarAccion(BuildContext context, int id, String path) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => PopScope(
        canPop: false,
        child: Dialog(
          backgroundColor: Colors.transparent,
          elevation: 0,
          child: Center(
            child: Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: kPrimary),
                  SizedBox(height: 12),
                  Text('Procesando...', style: TextStyle(fontSize: 13)),
                ],
              ),
            ),
          ),
        ),
      ),
    );

    try {
      final res = await ApiClient.post(path, {}, auth: true);
      if (context.mounted) {
        Navigator.of(context, rootNavigator: false).pop(); // close only the dialog
        if (res.statusCode == 200 || res.statusCode == 201) {
          final body = jsonDecode(res.body);
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(body['message'] ?? 'Acción realizada con éxito'),
            backgroundColor: Colors.green,
          ));
          onRefresh();
        } else {
          final body = jsonDecode(res.body);
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(body['message'] ?? 'Error al realizar la acción'),
            backgroundColor: Colors.red,
          ));
        }
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.of(context, rootNavigator: false).pop();
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text('Error de conexión: $e'),
          backgroundColor: Colors.red,
        ));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (negociaciones.isEmpty) {
      return Center(
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Icon(Icons.swap_horiz_outlined, size: 56, color: Colors.grey.shade300),
          const SizedBox(height: 12),
          Text(emptyMsg, style: TextStyle(color: kTextGray, fontSize: 14)),
        ]),
      );
    }

    return RefreshIndicator(
      color: kPrimary,
      onRefresh: () async => onRefresh(),
      child: ListView.builder(
        padding: const EdgeInsets.all(12),
        itemCount: negociaciones.length,
        itemBuilder: (_, i) {
          final neg = negociaciones[i];
          final item = neg['item'] as Map? ?? {};
          final receptor = neg['usuario_receptor'] as Map? ?? neg['usuario'] as Map? ?? {};
          final estado = neg['estado'] ?? 'Inicial';

          return InkWell(
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => NegociacionDetalleScreen(
                  negociacionId: neg['id_negociacion'],
                ),
              ),
            ),
            borderRadius: BorderRadius.circular(10),
            child: Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.grey.shade200),
                boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 6)],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Container(
                        width: 52, height: 52,
                        color: Colors.grey.shade100,
                        child: ItemImage(item: item),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Text(
                          item['item'] ?? 'Artículo #${neg['id_negociacion']}',
                          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Con: ${receptor['nombres'] ?? ''} ${receptor['apellidos'] ?? ''}',
                          style: TextStyle(fontSize: 11, color: kTextGray),
                        ),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            Builder(builder: (context) {
                              final esServServ = neg['es_servicio_servicio'] == true;
                              final esProdServ = neg['es_producto_servicio'] == true;
                              final label = esServServ
                                  ? '🤝 Servicio ↔ Servicio'
                                  : esProdServ
                                      ? '📦🔧 Producto ↔ Servicio'
                                      : '📦📦 Producto ↔ Producto';
                              final color = esServServ
                                  ? Colors.blue
                                  : esProdServ
                                      ? Colors.orange
                                      : Colors.green;
                              return Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: color.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(4),
                                  border: Border.all(color: color.withOpacity(0.3)),
                                ),
                                child: Text(label, style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: color.shade700)),
                              );
                            }),
                            if (neg['monto_oferta'] != null && (neg['monto_oferta'] as num) > 0) ...[
                              const SizedBox(width: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: kPrimary.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(4),
                                  border: Border.all(color: kPrimary.withOpacity(0.3)),
                                ),
                                child: Text(
                                  '+ RD\$ ${neg['monto_oferta']}',
                                  style: const TextStyle(fontSize: 9, color: kPrimary, fontWeight: FontWeight.bold),
                                ),
                              ),
                            ],
                          ],
                        ),
                        if (neg['items_ofrecidos_detalles'] != null && (neg['items_ofrecidos_detalles'] as List).isNotEmpty) ...[
                          const SizedBox(height: 8),
                          const Text('Ofrece a cambio:', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: kTextGray)),
                          const SizedBox(height: 4),
                          Wrap(
                            spacing: 4,
                            runSpacing: 4,
                            children: (neg['items_ofrecidos_detalles'] as List).map((offeredItem) {
                              final cantidadOfrecida = offeredItem['cantidad_ofrecida'] ?? 1;
                              return Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade50,
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(color: Colors.grey.shade200),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(3),
                                      child: SizedBox(
                                        width: 16, height: 16,
                                        child: ItemImage(item: offeredItem),
                                      ),
                                    ),
                                    const SizedBox(width: 6),
                                    Flexible(
                                      child: Text(
                                        offeredItem['item'] ?? '',
                                        style: const TextStyle(fontSize: 10, color: kTextDark, fontWeight: FontWeight.w500),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    if (cantidadOfrecida > 1) ...[
                                      const SizedBox(width: 4),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                                        decoration: BoxDecoration(
                                          color: kPrimary,
                                          borderRadius: BorderRadius.circular(10),
                                        ),
                                        child: Text(
                                          '× $cantidadOfrecida',
                                          style: const TextStyle(fontSize: 9, color: Colors.white, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              );
                            }).toList(),
                          ),
                        ],
                      ]),
                    ),
                    const SizedBox(width: 8),
                    _EstatusBadge(estado: estado),
                  ]),

                  // --- BOTONES DE ACCION RAPIDA ---
                  if (_buildActions(context, neg, estado).isNotEmpty) ...[
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 8.0),
                      child: Divider(height: 1, color: Color(0xFFEEEEEE)),
                    ),
                    Wrap(
                      spacing: 8,
                      runSpacing: 4,
                      children: _buildActions(context, neg, estado),
                    )
                  ],
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  List<Widget> _buildActions(BuildContext context, Map neg, String estado) {
    final int id = neg['id_negociacion'];
    final miConfirmado = rol == 'emisor' ? (neg['emisor_confirmado'] == 1) : ((neg['receptor_confirmado'] == 1) || (neg['receptor_confirmado'] == true));
    List<Widget> actions = [];

    // Receptor: Inicial o contraoferta
    if (rol == 'receptor' && (estado == 'Inicial' || estado == 'contraoferta' || estado == 'pendiente')) {
      actions.add(_actionButton('Aceptar', Colors.green, () => _ejecutarAccion(context, id, '/negociaciones/$id/aceptar')));
      actions.add(_actionButton('Rechazar', Colors.red, () => _ejecutarAccion(context, id, '/negociaciones/$id/rechazar'), isOutlined: true));
    }
    // Emisor: contraoferta
    else if (rol == 'emisor' && estado == 'contraoferta') {
      actions.add(_actionButton('Aceptar Contraoferta', Colors.green, () => _ejecutarAccion(context, id, '/negociaciones/$id/aceptar-como-emisor')));
      actions.add(_actionButton('Rechazar', Colors.red, () => _ejecutarAccion(context, id, '/negociaciones/$id/rechazar'), isOutlined: true));
    }
    // Ambos: Aceptado y no confirmado (Aprobar intercambio)
    else if (estado == 'aceptado' && !miConfirmado) {
      final path = rol == 'emisor' ? '/negociaciones/$id/confirmar-emisor' : '/negociaciones/$id/confirmar-receptor';
      actions.add(_actionButton('Aprobar Intercambio', Colors.orange, () => _ejecutarAccion(context, id, path)));
    }
    // Si hay que pagar, mostrar "Pagar Envío" (esto navega al detalle)
    else if (estado == 'aprobado' || estado == 'aceptado') {
      if (miConfirmado) {
         // Ver si el otro ha confirmado
         final otroConfirmado = rol == 'emisor' ? ((neg['receptor_confirmado'] == 1) || (neg['receptor_confirmado'] == true)) : (neg['emisor_confirmado'] == 1);
         if (otroConfirmado) {
            // Ya ambos confirmaron, mostrar botón genérico para ir al detalle (donde está la lógica compleja)
            actions.add(_actionButton('Ver detalles para continuar', kPrimary, () {
              Navigator.push(context, MaterialPageRoute(builder: (_) => NegociacionDetalleScreen(negociacionId: id))).then((_) => onRefresh());
            }));
         } else {
           actions.add(const Text('Esperando a la otra parte...', style: TextStyle(fontSize: 12, color: Colors.orange, fontStyle: FontStyle.italic)));
         }
      }
    }

    // Completar intercambio (servicios)
    if (estado == 'en_envio' || (estado == 'aceptado' && miConfirmado)) {
        actions.add(_actionButton('Ver Detalles', Colors.grey.shade700, () {
           Navigator.push(context, MaterialPageRoute(builder: (_) => NegociacionDetalleScreen(negociacionId: id))).then((_) => onRefresh());
        }, isOutlined: true));
    }

    return actions;
  }

  Widget _actionButton(String text, Color color, VoidCallback onTap, {bool isOutlined = false}) {
    if (isOutlined) {
      return OutlinedButton(
        onPressed: onTap,
        style: OutlinedButton.styleFrom(
          foregroundColor: color,
          side: BorderSide(color: color),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
          minimumSize: const Size(0, 32),
          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
        ),
        child: Text(text, style: const TextStyle(fontSize: 12)),
      );
    }
    return ElevatedButton(
      onPressed: onTap,
      style: ElevatedButton.styleFrom(
        backgroundColor: color,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
        minimumSize: const Size(0, 32),
        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
      ),
      child: Text(text, style: const TextStyle(fontSize: 12)),
    );
  }
}

class _EstatusBadge extends StatelessWidget {
  final String estado;
  const _EstatusBadge({required this.estado});

  @override
  Widget build(BuildContext context) {
    Color bg, fg;
    switch (estado.toLowerCase()) {
      case 'aceptado': case 'aprobado': case 'completado': case 'entregado':
        bg = const Color(0xFFDCFCE7); fg = const Color(0xFF15803D); break;
      case 'en_envio': case 'enviado':
        bg = const Color(0xFFDBEAFE); fg = const Color(0xFF1D4ED8); break;
      case 'contraoferta':
        bg = const Color(0xFFFEF9C3); fg = const Color(0xFFB45309); break;
      case 'inicial': case 'pendiente':
        bg = const Color(0xFFF3F4F6); fg = const Color(0xFF374151); break;
      default:
        bg = const Color(0xFFFEE2E2); fg = const Color(0xFFB91C1C);
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
      child: Text(
        estado[0].toUpperCase() + estado.substring(1),
        style: TextStyle(fontSize: 10, color: fg, fontWeight: FontWeight.bold),
      ),
    );
  }
}
