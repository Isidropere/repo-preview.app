import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
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
  List _predefinidos = [];
  bool _loading = true;
  bool _actionLoading = false;
  final _msgCtrl = TextEditingController();
  final _contraOfertaCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();

  int? _userId;
  bool _tieneDireccion = false;
  String _municipioUsuario = '';

  @override
  void initState() {
    super.initState();
    _loadAll();
  }

  @override
  void dispose() {
    _msgCtrl.dispose();
    _contraOfertaCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadAll() async {
    setState(() => _loading = true);
    try {
      // Cargar todo en paralelo
      await Future.wait([
        _loadCurrentUser(),
        _loadNegociacion(),
        _loadMensajes(),
        _loadDirecciones(),
      ]);
    } catch (e) {
      debugPrint('Error en _loadAll: $e');
    } finally {
      if (mounted) {
        setState(() => _loading = false);
        _scrollToBottom();
      }
    }
  }

  Future<void> _loadCurrentUser() async {
    final res = await ApiClient.get('/auth/me', auth: true);
    if (res.statusCode == 200) {
      final body = jsonDecode(res.body);
      _userId = ApiClient.parseInt(body['id']);
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
      final body = jsonDecode(res.body);
      _mensajes = body['mensajes'] as List? ?? [];
      _predefinidos = body['mensajesPredefinidos'] as List? ?? [];
    }
  }



  Future<void> _loadDirecciones() async {
    final res = await ApiClient.get('/direcciones', auth: true);
    if (res.statusCode == 200) {
      final body = jsonDecode(res.body);
      final List dirs = body['data'] ?? [];
      _tieneDireccion = dirs.isNotEmpty;
      if (_tieneDireccion) {
        final defaultDir = dirs.firstWhere((d) => d['es_predeterminada'] == 1, orElse: () => dirs.first);
        _municipioUsuario = defaultDir['municipio']?['municipio'] ?? '';
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

  void _showPredefinedMessages() {
    if (_predefinidos.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('No hay mensajes predefinidos disponibles.'),
        backgroundColor: Colors.orange,
      ));
      return;
    }

    final isEmisor = _userId == _neg?['usuario_emisor_id'];
    final String userRole = isEmisor ? 'emisor' : 'receptor';

    // Filtrar por rol (emisor, receptor o general)
    final roleMessages = _predefinidos.where((m) {
      final String rol = m['rol']?.toString().toLowerCase() ?? 'general';
      return rol == 'general' || rol == userRole;
    }).toList();

    if (roleMessages.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('No hay mensajes predefinidos para tu rol.'),
        backgroundColor: Colors.orange,
      ));
      return;
    }

    // Obtener tipos/categorías únicos
    final List<String> types = [
      'Todos',
      ...roleMessages
          .map((m) => m['tipo']?.toString() ?? 'General')
          .toSet()
    ];

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (context) {
        String selectedType = 'Todos';

        return StatefulBuilder(
          builder: (context, setStateSheet) {
            final filtered = roleMessages.where((m) {
              if (selectedType == 'Todos') return true;
              final String t = m['tipo']?.toString() ?? 'General';
              return t == selectedType;
            }).toList();

            return Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Respuestas Rápidas',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: kTextDark),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(context),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  // Dropdown para seleccionar tipo de acción
                  Row(
                    children: [
                      const Text('Filtrar por tipo:  ', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                      Expanded(
                        child: DropdownButton<String>(
                          value: selectedType,
                          isExpanded: true,
                          items: types.map((String val) {
                            return DropdownMenuItem<String>(
                              value: val,
                              child: Text(val, style: const TextStyle(fontSize: 13)),
                            );
                          }).toList(),
                          onChanged: (val) {
                            if (val != null) {
                              setStateSheet(() {
                                selectedType = val;
                              });
                            }
                          },
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 24),
                  // Listado de mensajes
                  Expanded(
                    child: filtered.isEmpty
                        ? const Center(child: Text('No hay mensajes en esta categoría.'))
                        : ListView.separated(
                            itemCount: filtered.length,
                            separatorBuilder: (_, __) => const Divider(),
                            itemBuilder: (context, idx) {
                              final msg = filtered[idx];
                              return ListTile(
                                contentPadding: EdgeInsets.zero,
                                title: Text(
                                  msg['titulo'] ?? '',
                                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kPrimary),
                                ),
                                subtitle: Text(
                                  msg['mensaje'] ?? '',
                                  maxLines: 3,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontSize: 12, color: kTextDark),
                                ),
                                onTap: () {
                                  _msgCtrl.text = msg['mensaje'] ?? '';
                                  Navigator.pop(context);
                                },
                              );
                            },
                          ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
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
                  labelText: 'Monto adicional (RD\$)',
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

  void _mostrarPagoDialog({String modoEntrega = ''}) {
    if (!_tieneDireccion) {
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Necesitas una dirección', style: TextStyle(color: Colors.red)),
          content: const Text('Por favor, registra una dirección de envío en la sección de Direcciones / Mi Cuenta antes de continuar.'),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cerrar')),
          ],
        ),
      );
      return;
    }

    bool calculandoEnvio = true;
    double montoEnvio = 0.0;
    String errorEnvio = '';

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            
            // Iniciar calculo
            if (calculandoEnvio && montoEnvio == 0.0 && errorEnvio.isEmpty) {
               Future.microtask(() async {
                  final url = '/delivery/calcular?pueblo=${Uri.encodeComponent(_municipioUsuario)}&tipo_destinatario=persona&valor_articulo=0';
                  final res = await ApiClient.get(url, auth: true);
                  if (mounted && context.mounted) {
                     setDialogState(() {
                        if (res.statusCode == 200) {
                           final body = jsonDecode(res.body);
                           if (body['success'] == true) {
                             montoEnvio = double.tryParse(body['costo_envio_total']?.toString() ?? '0') ?? 0.0;
                           } else {
                             errorEnvio = body['message'] ?? 'Error al calcular costo de envío.';
                           }
                        } else {
                           errorEnvio = 'Error al conectar con la pasarela de envíos.';
                        }
                        calculandoEnvio = false;
                     });
                  }
               });
            }

            return AlertDialog(
              title: Text(modoEntrega == 'envio' ? '🚚 Enviar y pagar' : '💳 Pagar costo de envío'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (calculandoEnvio) ...[
                    const Center(child: CircularProgressIndicator(color: kPrimary)),
                    const SizedBox(height: 12),
                    const Text('Calculando tarifa de envío para tu dirección...', style: TextStyle(fontSize: 12)),
                  ],
                  if (!calculandoEnvio && errorEnvio.isNotEmpty) ...[
                    Text(errorEnvio, style: const TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
                  ],
                  if (!calculandoEnvio && errorEnvio.isEmpty) ...[
                    Text('Monto de envío: RD\$ $montoEnvio', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: kPrimary)),
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade50,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.blue.shade100),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.security, color: Colors.blue.shade800, size: 20),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Serás redirigido a la pasarela de pago seguro de AZUL para completar la transacción. Tu información financiera está completamente protegida.',
                              style: TextStyle(fontSize: 11, color: Colors.blue.shade900, height: 1.3),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () {
                     Navigator.pop(context);
                  },
                  child: const Text('Cancelar'),
                ),
                if (!calculandoEnvio && errorEnvio.isEmpty)
                  ElevatedButton(
                    onPressed: () async {
                      Navigator.pop(context); // Close dialog

                      setState(() => _actionLoading = true);

                      // SI elegimos enviar en este momento (Producto vs Servicio), procesamos modo_entrega primero
                      if (modoEntrega == 'envio') {
                         final modoRes = await ApiClient.post('/negociaciones/${widget.negociacionId}/modo-entrega', {'modo': 'envio'}, auth: true);
                         if (modoRes.statusCode != 200) {
                            setState(() => _actionLoading = false);
                            if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Error al procesar el método de entrega'), backgroundColor: Colors.red));
                            return;
                         }
                      }
                      
                      try {
                        final res = await ApiClient.post(
                          '/negociaciones/${widget.negociacionId}/pago',
                          {}, // No credentials / card payload sent!
                          auth: true,
                        );
                        
                        setState(() => _actionLoading = false);

                        if (res.statusCode == 200 || res.statusCode == 201) {
                          final body = jsonDecode(res.body);
                          final redirectUrl = body['redirect_url']?.toString() ?? body['redirect']?.toString();
                          if (redirectUrl != null && redirectUrl.isNotEmpty) {
                            final Uri url = Uri.parse(redirectUrl);
                            if (await canLaunchUrl(url)) {
                              await launchUrl(url, mode: LaunchMode.externalApplication);
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                                  content: Text('Redirigiendo a la pasarela de pago seguro...'),
                                  backgroundColor: Colors.blue,
                                ));
                              }
                            } else {
                              if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('No se pudo abrir la pasarela de pago.'), backgroundColor: Colors.red));
                            }
                          } else {
                            if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('No se recibió la URL de redirección.'), backgroundColor: Colors.red));
                          }
                        } else {
                          final body = jsonDecode(res.body);
                          if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(body['message'] ?? 'Error al procesar el pago.'), backgroundColor: Colors.red));
                        }
                      } catch (e) {
                        setState(() => _actionLoading = false);
                        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error de conexión: $e'), backgroundColor: Colors.red));
                      }
                    },
                    style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
                    child: const Text('Proceder al Pago Seguro', style: TextStyle(color: Colors.white)),
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

  void _mostrarRatingDialog() {
    int ratingSeleccionado = 5;
    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: const Text('Calificar experiencia'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text('¿Cómo calificarías tu intercambio con esta persona?'),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(5, (index) {
                      return IconButton(
                        icon: Icon(
                          index < ratingSeleccionado ? Icons.star : Icons.star_border,
                          color: Colors.orange,
                          size: 32,
                        ),
                        onPressed: () {
                          setDialogState(() {
                            ratingSeleccionado = index + 1;
                          });
                        },
                      );
                    }),
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
                    Navigator.pop(context);
                    _enviarRating(ratingSeleccionado);
                  },
                  child: const Text('Enviar'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Future<void> _enviarRating(int rating) async {
    final emisorId = _neg!['usuario']?['id'] ?? 0;
    final receptorId = _neg!['usuario_receptor']?['id'] ?? 0;
    final isEmisor = _userId == emisorId;
    final otroMiembroId = isEmisor ? receptorId : emisorId;

    await _ejecutarAccion(
      '/rating',
      body: {
        'id_miembro': otroMiembroId,
        'rating': rating,
      },
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
    final entregaConfirmada = _neg!['entrega_confirmada'] == 1 || _neg!['entrega_confirmada'] == true;
    final modoYaElegido = _neg!['modo_entrega'] != null && _neg!['modo_entrega'].toString().isNotEmpty;
    final modoEntrega = _neg!['modo_entrega']?.toString() ?? '';

    // Computed flags passed from backend show() method
    final esServicioServicio = _neg!['es_servicio_servicio'] == true;
    final esProductoServicio = _neg!['es_producto_servicio'] == true;
    final esProductoProducto = _neg!['es_producto_producto'] == true;

    final requierePago = esProductoProducto;
    final pagoOpcional = esProductoServicio;

    final ambosConfirmados = emisorConfirmado && receptorConfirmado;
    final miConfirmado = isEmisor ? emisorConfirmado : receptorConfirmado;
    final otroConfirmado = isEmisor ? receptorConfirmado : emisorConfirmado;
    final miPago = isEmisor ? pagoEmisor : pagoReceptor;

    final itemSolicitadoEsServicio = item['id_categoria_item'] == 29;
    final duenioProductoId = itemSolicitadoEsServicio 
        ? (emisor['id'] ?? 0) 
        : (receptor['id'] ?? 0);
    final soyDuenioProducto = _userId == duenioProductoId;


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
                    'Acciones pendientes / Estado:',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: kTextDark),
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      // RECEPTOR: Inicial o contraoferta
                      if ((estado == 'Inicial' || estado == 'contraoferta') && isReceptor) ...[
                        ElevatedButton(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/aceptar'),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                          child: const Text('✓ Aceptar propuesta', style: TextStyle(color: Colors.white)),
                        ),
                        ElevatedButton(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/rechazar'),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                          child: const Text('✕ Rechazar', style: TextStyle(color: Colors.white)),
                        ),
                        ElevatedButton(
                          onPressed: _mostrarContraofertaDialog,
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
                          child: const Text('Contraoferta', style: TextStyle(color: Colors.white)),
                        ),
                      ],

                      // EMISOR: Contraoferta
                      if (estado == 'contraoferta' && isEmisor) ...[
                        ElevatedButton(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/aceptar-como-emisor'),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                          child: const Text('✓ Aceptar contraoferta', style: TextStyle(color: Colors.white)),
                        ),
                        ElevatedButton(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/rechazar'),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                          child: const Text('✕ Rechazar', style: TextStyle(color: Colors.white)),
                        ),
                      ],

                      // EMISOR: Cancelar propuesta inicial
                      if ((estado == 'Inicial' || estado == 'contraoferta') && isEmisor) ...[
                        OutlinedButton(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/cancelar'),
                          style: OutlinedButton.styleFrom(side: const BorderSide(color: Colors.red)),
                          child: const Text('Cancelar propuesta', style: TextStyle(color: Colors.red)),
                        ),
                      ],

                      // APROBAR INTERCAMBIO (Ambos roles)
                      if (estado == 'aceptado' && !miConfirmado) ...[
                        ElevatedButton.icon(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/${isEmisor ? 'confirmar-emisor' : 'confirmar-receptor'}'),
                          icon: const Icon(Icons.check, color: Colors.white),
                          label: const Text('✅ Aprobar intercambio', style: TextStyle(color: Colors.white)),
                          style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
                        ),
                      ],
                      
                      // YA APROBÉ
                      if (estado == 'aceptado' && miConfirmado && !otroConfirmado) ...[
                        const Text(
                          '⏳ Ya aprobaste. Esperando a la otra parte...',
                          style: TextStyle(fontSize: 12, fontStyle: FontStyle.italic, color: Colors.brown),
                        ),
                      ],

                      // PAGO OBLIGATORIO (Ambos confirmados)
                      if (ambosConfirmados && requierePago && !miPago) ...[
                        if (!_tieneDireccion)
                           Container(
                              padding: const EdgeInsets.all(12),
                              margin: const EdgeInsets.only(bottom: 8),
                              decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(8)),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('🚨 Necesitas una dirección de envío', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
                                  const SizedBox(height: 4),
                                  const Text('Debes registrar tu dirección antes de realizar el pago.', style: TextStyle(fontSize: 12)),
                                  const SizedBox(height: 8),
                                  ElevatedButton(
                                    onPressed: () {
                                       // Navegaría a crear dirección, por ahora solo avisamos
                                       ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Ve a Mi Cuenta > Direcciones para agregar una.')));
                                    },
                                    style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                                    child: const Text('Agregar Dirección', style: TextStyle(color: Colors.white)),
                                  ),
                                ]
                              ),
                           )
                        else
                           ElevatedButton.icon(
                             onPressed: () => _mostrarPagoDialog(),
                             icon: const Icon(Icons.payment, color: Colors.white),
                             label: const Text('💳 Realizar pago de envío', style: TextStyle(color: Colors.white)),
                             style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
                           ),
                      ],

                      // PAGO OPCIONAL (Producto vs Servicio)
                      if (ambosConfirmados && pagoOpcional && !miPago) ...[
                        if (soyDuenioProducto && !modoYaElegido) ...[
                          ElevatedButton.icon(
                            onPressed: () => _mostrarPagoDialog(modoEntrega: 'envio'),
                            icon: const Icon(Icons.local_shipping, color: Colors.white),
                            label: const Text('🚚 Enviar y pagar', style: TextStyle(color: Colors.white)),
                            style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
                          ),
                          ElevatedButton.icon(
                            onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/modo-entrega', body: {'modo': 'retiro'}),
                            icon: const Icon(Icons.handshake, color: Colors.white),
                            label: const Text('🤝 Retiro en persona', style: TextStyle(color: Colors.white)),
                            style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                          ),
                        ] else if (soyDuenioProducto && modoYaElegido && modoEntrega == 'envio' && !miPago) ...[
                           ElevatedButton.icon(
                            onPressed: () => _mostrarPagoDialog(),
                            icon: const Icon(Icons.payment, color: Colors.white),
                            label: const Text('💳 Pagar envío', style: TextStyle(color: Colors.white)),
                            style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
                          ),
                        ] else if (!soyDuenioProducto && !modoYaElegido) ...[
                           const Text(
                            '⏳ Esperando que el dueño del producto elija el modo de entrega.',
                            style: TextStyle(fontSize: 12, fontStyle: FontStyle.italic, color: Colors.brown),
                          ),
                        ]
                      ],

                      // CONFIRMAR RECEPCIÓN / RETIRO (Para el receptor del producto o para entregas en_envio)
                      if (ambosConfirmados && modoYaElegido && !soyDuenioProducto && !entregaConfirmada) ...[
                        ElevatedButton.icon(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/confirmar-entrega'),
                          icon: const Icon(Icons.check_circle, color: Colors.white),
                          label: Text(modoEntrega == 'envio' ? '✅ Confirmar recepción del producto' : '✅ Confirmar retiro del producto', style: const TextStyle(color: Colors.white)),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                        ),
                      ],
                      if (estado == 'en_envio' && !entregaConfirmada) ...[ // Caso producto vs producto general
                        ElevatedButton.icon(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/confirmar-entrega'),
                          icon: const Icon(Icons.check_circle, color: Colors.white),
                          label: const Text('✅ Confirmar recepción del producto', style: TextStyle(color: Colors.white)),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                        ),
                      ],

                      // SERVICIO VS SERVICIO COMPLETAR
                      if (ambosConfirmados && esServicioServicio && estado != 'completado') ...[
                        ElevatedButton.icon(
                          onPressed: () => _ejecutarAccion('/negociaciones/${widget.negociacionId}/completar'),
                          icon: const Icon(Icons.check_circle, color: Colors.white),
                          label: const Text('✅ Marcar como completado', style: TextStyle(color: Colors.white)),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                        ),
                      ],

                      // ESTADO COMPLETADO Y RATING
                      if (estado == 'completado') ...[
                        const Text(
                          '🎉 Intercambio completado.',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.green),
                        ),
                        // Nota: El sistema de rating iría aquí o en un modal separado
                        ElevatedButton.icon(
                          onPressed: _mostrarRatingDialog,
                          icon: const Icon(Icons.star, color: Colors.white),
                          label: const Text('⭐ Calificar experiencia', style: TextStyle(color: Colors.white)),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
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
                            constraints: BoxConstraints(
                              maxWidth: MediaQuery.of(context).size.width * 0.75,
                            ),
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
                      hintText: 'Escribe un mensaje o selecciona uno...',
                      border: InputBorder.none,
                      contentPadding: EdgeInsets.symmetric(horizontal: 10),
                    ),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.quickreply_outlined, color: Colors.orange),
                  onPressed: _showPredefinedMessages,
                  tooltip: 'Respuestas rápidas',
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
