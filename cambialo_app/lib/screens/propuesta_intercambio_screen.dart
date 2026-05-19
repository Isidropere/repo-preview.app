import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

/// Pantalla para proponer un intercambio — selecciona uno o varios artículos propios + mensaje
class PropuestaIntercambioScreen extends StatefulWidget {
  final int receptorItemId;
  final String nombreArticulo;
  final int idCategoriaItem;

  const PropuestaIntercambioScreen({
    super.key,
    required this.receptorItemId,
    required this.nombreArticulo,
    required this.idCategoriaItem,
  });

  @override
  State<PropuestaIntercambioScreen> createState() => _PropuestaIntercambioScreenState();
}

class _PropuestaIntercambioScreenState extends State<PropuestaIntercambioScreen> {
  final _mensajeCtrl = TextEditingController();
  final _montoCtrl   = TextEditingController();

  List _misItems = [];
  final Set<int> _itemsSeleccionados = {};
  bool _loading = true;
  bool _enviando = false;
  String _error = '';

  @override
  void initState() {
    super.initState();
    _loadMisItems();
  }

  @override
  void dispose() {
    _mensajeCtrl.dispose();
    _montoCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadMisItems() async {
    final res = await ApiClient.get('/mis-items', auth: true, useCache: false);
    if (res.statusCode == 200) {
      setState(() {
        // Solo artículos disponibles para intercambio (tipo_trans 2 o 3)
        _misItems = (jsonDecode(res.body) as List)
            .where((i) => i['tipo_trans'] == 2 || i['tipo_trans'] == 3)
            .toList();
        _loading = false;
      });
    } else {
      setState(() => _loading = false);
    }
  }

  Future<void> _enviar() async {
    if (_mensajeCtrl.text.trim().isEmpty) {
      setState(() => _error = 'El mensaje es requerido.');
      return;
    }
    setState(() {
      _enviando = true;
      _error = '';
    });

    final body = <String, dynamic>{
      'item_id': widget.receptorItemId,
      'mensaje': _mensajeCtrl.text.trim(),
    };
    if (_montoCtrl.text.trim().isNotEmpty) {
      body['monto_oferta'] = double.tryParse(_montoCtrl.text.trim()) ?? 0;
    }
    if (_itemsSeleccionados.isNotEmpty) {
      body['items_ofrecidos'] = _itemsSeleccionados.toList();
    }

    final res = await ApiClient.post('/negociaciones', body, auth: true);
    setState(() => _enviando = false);

    if (res.statusCode == 201 || res.statusCode == 200) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('¡Propuesta enviada correctamente!'),
        backgroundColor: Colors.green,
      ));
      Navigator.pop(context);
    } else {
      final rb = jsonDecode(res.body);
      setState(() => _error = rb['message'] ?? 'Error al enviar la propuesta.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final reqEsServicio = widget.idCategoriaItem == 29;
    bool hasProduct = !reqEsServicio;
    bool hasService = reqEsServicio;

    for (var item in _misItems) {
      if (_itemsSeleccionados.contains(item['id_item'])) {
        final isServ = item['id_categoria_item'] == 29;
        if (isServ) {
          hasService = true;
        } else {
          hasProduct = true;
        }
      }
    }

    String matchType = '';
    String matchDesc = '';
    Color matchColor = kPrimary;

    if (hasProduct && hasService) {
      matchType = 'Intercambio Mixto (Producto ↔ Servicio)';
      matchDesc = 'El dueño del producto físico seleccionará el modo de entrega (envío o retiro presencial).';
      matchColor = Colors.orange;
    } else if (hasService && !hasProduct) {
      matchType = 'Intercambio de Servicios (Servicio ↔ Servicio)';
      matchDesc = 'Intercambio 100% digital o de prestación directa. No requiere costos de envío ni pasarela de pago.';
      matchColor = Colors.blue;
    } else {
      matchType = 'Intercambio Físico (Producto ↔ Producto)';
      matchDesc = 'Ambas partes deberán pagar su costo de envío correspondiente antes de proceder con el despacho.';
      matchColor = Colors.green;
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Proponer intercambio')),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                // Artículo que se quiere
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: kPrimary.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: kPrimary.withOpacity(0.3)),
                  ),
                  child: Row(children: [
                    const Icon(Icons.swap_horiz, color: kPrimary),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Text(
                                reqEsServicio ? 'SOLICITAS SERVICIO' : 'SOLICITAS PRODUCTO',
                                style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: kPrimary),
                              ),
                              const SizedBox(width: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                                decoration: BoxDecoration(
                                  color: reqEsServicio ? Colors.blue.shade50 : Colors.green.shade50,
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Text(
                                  reqEsServicio ? 'Servicio' : 'Producto',
                                  style: TextStyle(
                                    fontSize: 8,
                                    fontWeight: FontWeight.bold,
                                    color: reqEsServicio ? Colors.blue.shade800 : Colors.green.shade800,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          Text(widget.nombreArticulo,
                              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
                        ],
                      ),
                    ),
                  ]),
                ),
                const SizedBox(height: 20),

                // Tipo de intercambio dinámico
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: matchColor.withOpacity(0.05),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: matchColor.withOpacity(0.2)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        matchType,
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: matchColor),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        matchDesc,
                        style: const TextStyle(fontSize: 11, color: kTextGray),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Seleccionar artículos propios a ofrecer
                const Text('Artículos o Servicios que ofreces (puedes seleccionar varios)',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
                const SizedBox(height: 8),
                if (_misItems.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.orange.shade50,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Text(
                      'No tienes artículos ni servicios disponibles para intercambiar. Puedes proponer una oferta en efectivo.',
                      style: TextStyle(fontSize: 12, color: kTextGray),
                    ),
                  )
                else
                  Card(
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                      side: BorderSide(color: Colors.grey.shade200),
                    ),
                    child: ListView.separated(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: _misItems.length,
                      separatorBuilder: (_, __) => const Divider(height: 1),
                      itemBuilder: (context, idx) {
                        final item = _misItems[idx];
                        final id = item['id_item'] as int;
                        final isService = item['id_categoria_item'] == 29;
                        final isSelected = _itemsSeleccionados.contains(id);

                        return CheckboxListTile(
                          value: isSelected,
                          onChanged: (bool? val) {
                            setState(() {
                              if (val == true) {
                                _itemsSeleccionados.add(id);
                              } else {
                                _itemsSeleccionados.remove(id);
                              }
                            });
                          },
                          activeColor: kPrimary,
                          title: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: isService ? Colors.blue.shade50 : Colors.green.shade50,
                                  borderRadius: BorderRadius.circular(4),
                                  border: Border.all(color: isService ? Colors.blue.shade200 : Colors.green.shade200),
                                ),
                                child: Text(
                                  isService ? 'Servicio' : 'Producto',
                                  style: TextStyle(
                                    fontSize: 9,
                                    fontWeight: FontWeight.bold,
                                    color: isService ? Colors.blue.shade800 : Colors.green.shade800,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  item['item'] ?? '',
                                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                                ),
                              ),
                            ],
                          ),
                          subtitle: Text(
                            'Valor estimado: RD\$ ${item['valor'] ?? 0}',
                            style: const TextStyle(fontSize: 11, color: kPrimary),
                          ),
                        );
                      },
                    ),
                  ),
                const SizedBox(height: 20),

                // Monto adicional en efectivo
                const Text('Monto adicional en efectivo (opcional)',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _montoCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(
                    labelText: 'Ej: 500',
                    prefixText: 'RD\$ ',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 20),

                // Mensaje
                const Text('Tu mensaje *',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _mensajeCtrl,
                  maxLines: 4,
                  decoration: const InputDecoration(
                    hintText: 'Ej: Hola, me interesa tu servicio. Ofrezco mi artículo + RD\$500...',
                    border: OutlineInputBorder(),
                  ),
                ),

                if (_error.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  Text(_error, style: const TextStyle(color: Colors.red, fontSize: 13)),
                ],

                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _enviando ? null : _enviar,
                    icon: _enviando
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Icon(Icons.send, color: Colors.white),
                    label: Text(_enviando ? 'Enviando...' : 'Enviar propuesta',
                        style: const TextStyle(color: Colors.white, fontSize: 15)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: kPrimary,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                  ),
                ),
              ]),
            ),
    );
  }
}
