import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:path_provider/path_provider.dart';
import 'package:share_plus/share_plus.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import 'negociacion_detalle_screen.dart';

/// Historial de compras, ventas e intercambios
/// Fiel al diseño web con tabs
class HistorialScreen extends StatefulWidget {
  final int initialTabIndex;
  const HistorialScreen({super.key, this.initialTabIndex = 0});
  @override
  State<HistorialScreen> createState() => _HistorialScreenState();
}

class _HistorialScreenState extends State<HistorialScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabs;
  List _compras      = [];
  List _ventas       = [];
  List _intercambios = [];
  List _motivos      = [];
  bool _loading      = true;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 3, vsync: this, initialIndex: widget.initialTabIndex);
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
        _motivos      = body['motivos']      ?? [];
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
                _ComprasTab(compras: _compras, motivos: _motivos, onRefresh: _load),
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
  final List motivos;
  final VoidCallback onRefresh;
  const _ComprasTab({required this.compras, required this.motivos, required this.onRefresh});

  Future<void> _confirmDevolucion(BuildContext context, String id) async {
    final result = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (ctx) => _DevolucionBottomSheet(motivos: motivos),
    );

    if (result != null) {
      if (!context.mounted) return;
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => const Center(child: CircularProgressIndicator(color: kPrimary)),
      );

      try {
        final res = await ApiClient.post(
          '/historial/devolucion/$id',
          {
            'id_motivo_devolucion': result['id_motivo_devolucion'],
            'comentario_devolucion': result['comentario_devolucion'],
          },
          auth: true,
        );
        if (!context.mounted) return;
        Navigator.pop(context); // Quitar loader

        final body = jsonDecode(res.body);
        if (res.statusCode == 200 && (body['success'] ?? false)) {
          ApiClient.clearCache('/historial');
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(body['message'] ?? 'Devolución procesada correctamente.'),
              backgroundColor: Colors.green,
            ),
          );
          onRefresh();
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(body['message'] ?? 'Ocurrió un error al procesar la devolución.'),
              backgroundColor: Colors.red,
            ),
          );
        }
      } catch (e) {
        if (!context.mounted) return;
        Navigator.pop(context); // Quitar loader
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error de red: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _descargarFactura(BuildContext context, String id) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator(color: kPrimary)),
    );

    try {
      final res = await ApiClient.get('/historial/compra/$id/invoice', auth: true, useCache: false);
      if (!context.mounted) return;
      Navigator.pop(context); // Quitar loader

      if (res.statusCode == 200) {
        final bytes = res.bodyBytes;
        final tempDir = await getTemporaryDirectory();
        final file = File('${tempDir.path}/factura-compra-$id.pdf');
        await file.writeAsBytes(bytes);

        await SharePlus.instance.share(
          ShareParams(
            files: [XFile(file.path, mimeType: 'application/pdf')],
            subject: 'Factura Compra $id',
            text: 'Descarga tu factura de Cambialord de la orden #$id',
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error al descargar la factura: Código ${res.statusCode}'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.pop(context); // Quitar loader
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error al descargar: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 11, color: kTextGray)),
          Text(value, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500, color: kTextDark)),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (compras.isEmpty) return _empty('No tienes compras registradas aún', Icons.shopping_cart_outlined);
    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: compras.length,
      itemBuilder: (_, i) {
        final c = compras[i];
        final estatus = c['estatus'] ?? 'pendiente';
        final bool editable = estatus.toLowerCase() == 'pendiente' || estatus.toLowerCase() == 'aprobado';
        final bool hasInvoice = estatus.toLowerCase() == 'aprobado' || estatus.toLowerCase() == 'enviado' || estatus.toLowerCase() == 'entregado';
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

            // Detalles de Pago y descarga de factura
            if (hasInvoice) ...[
              const Divider(height: 1, color: Color(0xFFEEEEEE)),
              Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade50,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.grey.shade200),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: const [
                              Icon(Icons.payment, size: 16, color: kSecondary),
                              SizedBox(width: 6),
                              Text(
                                'Detalles del Pago',
                                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: kTextDark),
                              ),
                            ],
                          ),
                          const Divider(height: 12, color: Color(0xFFEEEEEE)),
                          if (c['tarjeta'] != null) ...[
                            _infoRow('Tarjeta:', '${c['tarjeta']['tipo_tarjeta'] ?? 'Tarjeta'} (*** ${c['tarjeta']['last4'] ?? ''})'),
                            if (c['tarjeta']['nombre_titular'] != null)
                              _infoRow('Titular:', c['tarjeta']['nombre_titular']),
                          ],
                          if (c['autorizacion_pago'] != null)
                            _infoRow('Autorización:', c['autorizacion_pago']),
                          if (c['transaction_id'] != null)
                            _infoRow('Transacción ID:', c['transaction_id']),
                        ],
                      ),
                    ),
                    const SizedBox(height: 10),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        if (editable) ...[
                          TextButton.icon(
                            style: TextButton.styleFrom(
                              foregroundColor: Colors.red.shade700,
                              backgroundColor: Colors.red.shade50,
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                                side: BorderSide(color: Colors.red.shade100),
                              ),
                            ),
                            icon: const Icon(Icons.undo, size: 16),
                            label: const Text('Solicitar Devolución', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                            onPressed: () => _confirmDevolucion(context, c['id_pago_compra'] ?? ''),
                          ),
                          const SizedBox(width: 8),
                        ],
                        ElevatedButton.icon(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFFF58634), // Web orange #f58634
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                            elevation: 0,
                          ),
                          icon: const Icon(Icons.picture_as_pdf, size: 16),
                          label: const Text('Factura PDF', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                          onPressed: () => _descargarFactura(context, c['id_pago_compra'] ?? ''),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ] else if (editable) ...[
              const Divider(height: 1, color: Color(0xFFEEEEEE)),
              Padding(
                padding: const EdgeInsets.all(8),
                child: Align(
                  alignment: Alignment.centerRight,
                  child: TextButton.icon(
                    style: TextButton.styleFrom(
                      foregroundColor: Colors.red.shade700,
                      backgroundColor: Colors.red.shade50,
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                        side: BorderSide(color: Colors.red.shade100),
                      ),
                    ),
                    icon: const Icon(Icons.undo, size: 16),
                    label: const Text('Solicitar Devolución', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                    onPressed: () => _confirmDevolucion(context, c['id_pago_compra'] ?? ''),
                  ),
                ),
              ),
            ]
          ]),
        );
      },
    );
  }
}

class _DevolucionBottomSheet extends StatefulWidget {
  final List motivos;
  const _DevolucionBottomSheet({required this.motivos});

  @override
  State<_DevolucionBottomSheet> createState() => _DevolucionBottomSheetState();
}

class _DevolucionBottomSheetState extends State<_DevolucionBottomSheet> {
  final _formKey = GlobalKey<FormState>();
  int? _selectedMotivoId;
  final _comentarioController = TextEditingController();

  @override
  void dispose() {
    _comentarioController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        left: 20,
        right: 20,
        top: 20,
        bottom: MediaQuery.of(context).viewInsets.bottom + 20,
      ),
      child: Form(
        key: _formKey,
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Solicitar Devolución',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: kTextDark,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: kTextGray),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              const Text(
                'Por favor, selecciona el motivo de tu devolución. Esta acción es obligatoria para procesar tu solicitud.',
                style: TextStyle(fontSize: 13, color: kTextGray),
              ),
              const SizedBox(height: 20),
              DropdownButtonFormField<int>(
                value: _selectedMotivoId,
                decoration: InputDecoration(
                  labelText: 'Motivo de devolución *',
                  labelStyle: const TextStyle(color: kTextGray, fontSize: 14),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                  focusedBorder: const OutlineInputBorder(
                    borderRadius: BorderRadius.all(Radius.circular(8)),
                    borderSide: BorderSide(color: kPrimary, width: 1.5),
                  ),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                ),
                items: widget.motivos.map<DropdownMenuItem<int>>((m) {
                  final id = m['id'] is int ? m['id'] as int : int.tryParse(m['id']?.toString() ?? '');
                  return DropdownMenuItem<int>(
                    value: id,
                    child: Text(
                      m['motivo'] ?? '',
                      style: const TextStyle(fontSize: 14, color: kTextDark),
                    ),
                  );
                }).toList(),
                validator: (val) {
                  if (val == null) {
                    return 'Debes seleccionar un motivo';
                  }
                  return null;
                },
                onChanged: (val) {
                  setState(() {
                    _selectedMotivoId = val;
                  });
                },
              ),
              const SizedBox(height: 20),
              TextFormField(
                controller: _comentarioController,
                maxLines: 3,
                decoration: InputDecoration(
                  labelText: 'Comentarios adicionales (opcional)',
                  labelStyle: const TextStyle(color: kTextGray, fontSize: 14),
                  hintText: 'Detalla el motivo si lo deseas...',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                  focusedBorder: const OutlineInputBorder(
                    borderRadius: BorderRadius.all(Radius.circular(8)),
                    borderSide: BorderSide(color: kPrimary, width: 1.5),
                  ),
                  contentPadding: const EdgeInsets.all(12),
                ),
                style: const TextStyle(fontSize: 14, color: kTextDark),
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: kTextGray,
                        side: BorderSide(color: Colors.grey.shade300),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Cancelar'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.red.shade600,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                        elevation: 0,
                      ),
                      onPressed: () {
                        if (_formKey.currentState!.validate()) {
                          Navigator.pop(context, {
                            'id_motivo_devolucion': _selectedMotivoId,
                            'comentario_devolucion': _comentarioController.text,
                          });
                        }
                      },
                      child: const Text('Confirmar'),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
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
        return InkWell(
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => NegociacionDetalleScreen(
                  negociacionId: neg['id_negociacion'],
                ),
              ),
            );
          },
          child: Container(
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
          ),
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
