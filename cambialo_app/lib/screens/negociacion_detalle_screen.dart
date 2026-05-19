import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

class NegociacionDetalleScreen extends StatefulWidget {
  final int negociacionId;
  const NegociacionDetalleScreen({super.key, required this.negociacionId});

  @override
  State<NegociacionDetalleScreen> createState() => _NegociacionDetalleScreenState();
}

class _NegociacionDetalleScreenState extends State<NegociacionDetalleScreen> {
  Map<String, dynamic>? _neg;
  List _mensajes = [];
  List _tarjetas = [];
  bool _loading = true;
  bool _actionLoading = false;
  String? _selectedTarjetaId;
  final _cvvCtrl = TextEditingController();
  final _msgCtrl = TextEditingController();
  final _contraOfertaCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();

  int? _userId;

  @override
  void initState() {
    super.initState();
    _loadAll();
  }

  @override
  void dispose() {
    _cvvCtrl.dispose();
    _msgCtrl.dispose();
    _contraOfertaCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadAll() async {
    setState(() => _loading = true);
    await _loadCurrentUser();
    await _loadNegociacion();
    await _loadMensajes();
    await _loadTarjetas();
    setState(() => _loading = false);
    _scrollToBottom();
  }

  Future<void> _loadCurrentUser() async {
    final res = await ApiClient.get('/auth/me', auth: true);
    if (res.statusCode == 200) {
      final body = jsonDecode(res.body);
      _userId = body['id'] as int?;
    }
  }

  Future<void> _loadNegociacion() async {
    final res = await ApiClient.get('/negociaciones/${widget.negociacionId}', auth: true);
    if (res.statusCode == 200) {
      _neg = jsonDecode(res.body);
    }
  }

  Future<void> _loadMensajes() async {
    final res = await ApiClient.get('/negociaciones/${widget.negociacionId}/mensajes', auth: true);
    if (res.statusCode == 200) {
      _mensajes = jsonDecode(res.body) as List;
    }
  }

  Future<void> _loadTarjetas() async {
    final res = await ApiClient.get('/tarjetas', auth: true);
    if (res.statusCode == 200) {
      _tarjetas = jsonDecode(res.body) as List;
      if (_tarjetas.isNotEmpty) {
        _selectedTarjetaId = _tarjetas[0]['id_tarjeta'].toString();
      }
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollCtrl.hasClients) {
        _scrollCtrl.animateTo(
          _scrollCtrl.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _enviarMensaje() async {
    if (_msgCtrl.text.trim().isEmpty) return;
    final text = _msgCtrl.text.trim();
    _msgCtrl.clear();

    final res = await ApiClient.post(
      '/negociaciones/${widget.negociacionId}/mensajes',
      {'mensaje': text},
      auth: true,
    );

    if (res.statusCode == 200) {
      await _loadMensajes();
      setState(() {});
      _scrollToBottom();
    }
  }

  Future<void> _ejecutarAccion(String path, {Map<String, dynamic>? body}) async {
    setState(() => _actionLoading = true);
    final res = await ApiClient.post(path, body ?? {}, auth: true);
    setState(() => _actionLoading = false);

    final responseBody = jsonDecode(res.body);
    if (res.statusCode == 200 || res.statusCode == 201) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(responseBody['message'] ?? 'Operación realizada con éxito.'),
        backgroundColor: Colors.green,
      ));
      _loadAll();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(responseBody['message'] ?? 'Error al procesar la acción.'),
        backgroundColor: Colors.red,
      ));
    }
  }

  void _mostrarContraofertaDialog() {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Enviar Contraoferta'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextFormField(
                controller: _contraOfertaCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Monto adicional (RD$)',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              const Text(
                'Nota: Esto enviará un mensaje al emisor proponiendo un ajuste en el valor en efectivo.',
                style: TextStyle(fontSize: 11, color: kTextGray),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancelar'),
            ),
            ElevatedButton(
              onPressed: () {
                final amt = double.tryParse(_contraOfertaCtrl.text.trim()) ?? 0.0;
                Navigator.pop(context);
                _ejecutarAccion(
                  '/negociaciones/${widget.negociacionId}/contraoferta',
                  body: {'monto_contra_oferta': amt, 'mensaje': 'He realizado una contraoferta por RD\$ $amt'},
                );
              },
              style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
              child: const Text('Enviar', style: TextStyle(color: Colors.white)),
            ),
          ],
        );
      },
    );
  }

  void _mostrarPagoDialog() {
    if (_tarjetas.isEmpty) {
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('No tienes tarjetas registradas'),
          content: const Text('Por favor, registra una tarjeta de crédito en la sección de Checkout o Mi Cuenta antes de continuar.'),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cerrar')),
          ],
        ),
      );
      return;
    }

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: const Text('Pagar costo de envío'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Selecciona una tarjeta para proceder con el cobro del envío:'),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    value: _selectedTarjetaId,
                    items: _tarjetas.map((t) {
                      return DropdownMenuItem<String>(
                        value: t['id_tarjeta'].toString(),
                        child: Text('${t['marca'] ?? 'Tarjeta'} **** ${t['ultimos_cuatro'] ?? ''}'),
                      );
                    }).toList(),
                    onChanged: (val) {
                      setDialogState(() => _selectedTarjetaId = val);
                    },
                    decoration: const InputDecoration(border: OutlineInputBorder()),
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _cvvCtrl,
                    obscureText: true,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'CVV',
                      hintText: '123',
                      border: OutlineInputBorder(),
                    ),
                  ),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Cancelar'),
                ),
                ElevatedButton(
                  onPressed: () {
                    final cvv = _cvvCtrl.text.trim();
                    Navigator.pop(context);
                    _ejecutarAccion(
                      '/negociaciones/${widget.negociacionId}/pago',
                      body: {
                        'id_tarjeta': _selectedTarjetaId,
                        'cvv': cvv,
                      },
                    );
                  },
                  style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
                  child: const Text('Pagar', style: TextStyle(color: Colors.white)),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Widget _buildStatusBadge(String status) {
    Color bg, fg;
    switch (status.toLowerCase()) {
      case 'aprobado':
      case 'aceptado':
      case 'entregado':
      case 'completado':
        bg = const Color(0xFFDCFCE7);
        fg = const Color(0xFF15803D);
        break;
      case 'enviado':
      case 'en_envio':
        bg = const Color(0xFFDBEAFE);
        fg = const Color(0xFF1D4ED8);
        break;
      case 'pendiente':
      case 'inicial':
      case 'contraoferta':
        bg = const Color(0xFFFEF9C3);
        fg = const Color(0xFFB45309);
        break;
      default:
        bg = const Color(0xFFFEE2E2);
        fg = const Color(0xFFB91C1C);
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
      child: Text(
        status.toUpperCase(),
        style: TextStyle(fontSize: 10, color: fg, fontWeight: FontWeight.bold),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator(color: kPrimary)));
    }

    if (_neg == null) {
      return const Scaffold(body: Center(child: Text('Error al cargar la negociación.')));
    }

    final emisor = _neg!['usuario'] as Map? ?? {};
    final receptor = _neg!['usuario_receptor'] as Map? ?? {};
    final item = _neg!['item'] as Map? ?? {};
    final estado = _neg!['estado'] ?? 'Inicial';
    final itemsOfrecidos = _neg!['items_ofrecidos'] as List? ?? [];

    final isEmisor = _userId == emisor['id'];
    final isReceptor = _userId == receptor['id'];

    final emisorConfirmado = _neg!['emisor_confirmado'] == 1 || _neg!['emisor_confirmado'] == true;
    final receptorConfirmado = _neg!['receptor_confirmado'] == 1 || _neg!['receptor_confirmado'] == true;
    final pagoEmisor = _neg!['pago_emisor'] == 1 || _neg!['pago_emisor'] == true;
    final pagoReceptor = _neg!['pago_receptor'] == 1 || _neg!['pago_receptor'] == true;

    final itemSolicitadoEsServicio = item['id_categoria_item'] == 29;

    return Scaffold(
      appBar: AppBar(
        title: Text('Intercambio #${_neg!['id_negociacion']}'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadAll,
          ),
        ],
      ),
      body: Column(
        children: [
          // Info de negociación & Estado
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Con: ${isEmisor ? (receptor['nombres'] ?? '') : (emisor['nombres'] ?? '')}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                    _buildStatusBadge(estado),
                  ],
                ),
                const SizedBox(height: 6),
                Text(
                  'Solicita: ${item['item'] ?? ''}',
                  style: const TextStyle(fontSize: 12, color: kTextDark),
                ),
                if (itemsOfrecidos.isNotEmpty)
                  Text(
                    'Ofrece: ${itemsOfrecidos.length} artículo(s)/servicio(s)',
                    style: const TextStyle(fontSize: 11, color: kTextGray),
                  ),
                if (_neg!['monto_oferta'] != null && _neg!['monto_oferta'] > 0)
                  Text(
                    'Monto adicional: RD\$ ${_neg!['monto_oferta']}',
                    style: const TextStyle(fontSize: 12, color: kPrimary, fontWeight: FontWeight.bold),
                  ),
                if (_neg!['monto_contra_oferta'] != null && _neg!['monto_contra_oferta'] > 0)
                  Text(
                    'Monto Contraoferta: RD\$ ${_neg!['monto_contra_oferta']}',
                    style: const TextStyle(fontSize: 12, color: Colors.orange, fontWeight: FontWeight.bold),
                  ),
              ],
            ),
          ),

          // Área de Acciones Contextuales
          if (_actionLoading)
            const LinearProgressIndicator(color: kPrimary)
          else if (estado.toString().toLowerCase() != 'cancelado' && estado.toString().toLowerCase() != 'rechazado')
            Container(
              padding: const EdgeInsets.all(12),
              color: Colors.orange.shade50,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Acciones pendientes:',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: kTextDark),
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      // Acciones Estado Inicial
                      if (estado == 'Inicial' && isReceptor) ...[
                        ElevatedButton(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/aceptar'),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                          child: const Text('Aceptar', style: TextStyle(color: Colors.white)),
                        ),
                        ElevatedButton(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/rechazar'),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                          child: const Text('Rechazar', style: TextStyle(color: Colors.white)),
                        ),
                        ElevatedButton(
                          onPressed: _mostrarContraofertaDialog,
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
                          child: const Text('Contraoferta', style: TextStyle(color: Colors.white)),
                        ),
                      ],

                      // Acciones Contraoferta
                      if (estado == 'contraoferta' && isEmisor) ...[
                        ElevatedButton(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/aceptar-como-emisor'),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                          child: const Text('Aceptar Contraoferta', style: TextStyle(color: Colors.white)),
                        ),
                        ElevatedButton(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/rechazar'),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                          child: const Text('Rechazar', style: TextStyle(color: Colors.white)),
                        ),
                      ],

                      // Cancelar (solo Emisor en Inicial/contraoferta)
                      if ((estado == 'Inicial' || estado == 'contraoferta') && isEmisor) ...[
                        OutlinedButton(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/cancelar'),
                          style: OutlinedButton.styleFrom(side: const BorderSide(color: Colors.red)),
                          child: const Text('Cancelar Propuesta', style: TextStyle(color: Colors.red)),
                        ),
                      ],

                      // Confirmación mutua (Aceptado pero no confirmado por este usuario)
                      if (estado == 'aceptado') ...[
                        if (isEmisor && !emisorConfirmado)
                          ElevatedButton(
                            onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/confirmar-emisor'),
                            style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
                            child: const Text('Aprobar Intercambio', style: TextStyle(color: Colors.white)),
                          ),
                        if (isReceptor && !receptorConfirmado)
                          ElevatedButton(
                            onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/confirmar-receptor'),
                            style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
                            child: const Text('Aprobar Intercambio', style: TextStyle(color: Colors.white)),
                          ),

                        // Selección Modo de Entrega (Mixto: Producto vs Servicio)
                        // Si ambos confirmaron, el dueño del producto físico selecciona el modo
                        if (emisorConfirmado && receptorConfirmado && _neg!['modo_entrega'] == null) ...[
                          // Receptor es dueño del producto si solicitado no es servicio y ofrecidos son servicios
                          // Emisor es dueño del producto si solicitado es servicio y ofrecidos son productos
                          if ((isReceptor && !itemSolicitadoEsServicio) || (isEmisor && itemSolicitadoEsServicio)) ...[
                            ElevatedButton.icon(
                              onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/modo-entrega', body: {'modo': 'envio'}),
                              icon: const Icon(Icons.local_shipping, color: Colors.white),
                              label: const Text('Elegir Envío', style: TextStyle(color: Colors.white)),
                              style: ElevatedButton.styleFrom(backgroundColor: Colors.blue),
                            ),
                            ElevatedButton.icon(
                              onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/modo-entrega', body: {'modo': 'retiro'}),
                              icon: const Icon(Icons.handshake, color: Colors.white),
                              label: const Text('Retiro en Persona', style: TextStyle(color: Colors.white)),
                              style: ElevatedButton.styleFrom(backgroundColor: Colors.indigo),
                            ),
                          ] else ...[
                            const Text(
                              'Esperando que el dueño del producto elija el modo de entrega...',
                              style: TextStyle(fontSize: 11, fontStyle: FontStyle.italic, color: kTextGray),
                            ),
                          ]
                        ],

                        // Pago de envío si requiere envío físico
                        // Producto vs Producto requiere pago de ambos
                        // Mixto (Producto vs Servicio) requiere pago del dueño del producto (si eligió envio)
                        if (emisorConfirmado && receptorConfirmado) ...[
                          if (isEmisor && !pagoEmisor) ...[
                            ElevatedButton.icon(
                              onPressed: _mostrarPagoDialog,
                              icon: const Icon(Icons.payment, color: Colors.white),
                              label: const Text('Pagar costo de envío', style: TextStyle(color: Colors.white)),
                              style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                            ),
                            ElevatedButton(
                              onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/pago', body: {'sin_pago': true}),
                              style: ElevatedButton.styleFrom(backgroundColor: Colors.grey),
                              child: const Text('Aprobar sin costo', style: TextStyle(color: Colors.white)),
                            ),
                          ],
                          if (isReceptor && !pagoReceptor) ...[
                            ElevatedButton.icon(
                              onPressed: _mostrarPagoDialog,
                              icon: const Icon(Icons.payment, color: Colors.white),
                              label: const Text('Pagar costo de envío', style: TextStyle(color: Colors.white)),
                              style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                            ),
                            ElevatedButton(
                              onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/pago', body: {'sin_pago': true}),
                              style: ElevatedButton.styleFrom(backgroundColor: Colors.grey),
                              child: const Text('Aprobar sin costo', style: TextStyle(color: Colors.white)),
                            ),
                          ],
                        ]
                      ],

                      // Confirmar entrega en ruta (en_envio)
                      if (estado == 'en_envio') ...[
                        ElevatedButton.icon(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/confirmar-entrega'),
                          icon: const Icon(Icons.check_circle_outline, color: Colors.white),
                          label: const Text('Confirmar Recepción / Entrega', style: TextStyle(color: Colors.white)),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),

          // Chat de mensajes
          Expanded(
            child: Container(
              color: Colors.grey.shade50,
              child: _mensajes.isEmpty
                  ? const Center(
                      child: Text(
                        'No hay mensajes aún. Escribe algo para iniciar la conversación.',
                        style: TextStyle(color: kTextGray, fontSize: 13),
                      ),
                    )
                  : ListView.builder(
                      controller: _scrollCtrl,
                      padding: const EdgeInsets.all(12),
                      itemCount: _mensajes.length,
                      itemBuilder: (context, idx) {
                        final msg = _mensajes[idx];
                        final senderId = msg['usuario_emisor_id'] ?? msg['emisor_id'];
                        final me = senderId == _userId;
                        final bodyMsg = msg['mensaje'] ?? '';
                        final fechaMsg = msg['fecha'] ?? '';

                        return Align(
                          alignment: me ? Alignment.centerRight : Alignment.centerLeft,
                          child: Container(
                            maxWidth: MediaQuery.of(context).size.width * 0.75,
                            margin: const EdgeInsets.only(bottom: 10),
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: me ? kPrimary : Colors.white,
                              borderRadius: BorderRadius.only(
                                topLeft: const Radius.circular(12),
                                topRight: const Radius.circular(12),
                                bottomLeft: me ? const Radius.circular(12) : Radius.zero,
                                bottomRight: me ? Radius.zero : const Radius.circular(12),
                              ),
                              border: me ? null : Border.all(color: Colors.grey.shade200),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  bodyMsg,
                                  style: TextStyle(
                                    fontSize: 13,
                                    color: me ? Colors.white : kTextDark,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Align(
                                  alignment: Alignment.bottomRight,
                                  child: Text(
                                    fechaMsg,
                                    style: TextStyle(
                                      fontSize: 9,
                                      color: me ? Colors.white70 : kTextGray,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
            ),
          ),

          // Enviar Mensaje Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(top: BorderSide(color: Colors.grey.shade200)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _msgCtrl,
                    decoration: const InputDecoration(
                      hintText: 'Escribe un mensaje...',
                      border: InputBorder.none,
                      contentPadding: EdgeInsets.symmetric(horizontal: 10),
                    ),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.send, color: kPrimary),
                  onPressed: _enviarMensaje,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
