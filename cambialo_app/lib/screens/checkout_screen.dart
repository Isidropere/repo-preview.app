import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import 'direcciones_screen.dart';

/// Pantalla de Checkout — selección de dirección + tarjetas + resumen + pago
class CheckoutScreen extends StatefulWidget {
  final Map carrito;
  const CheckoutScreen({super.key, required this.carrito});
  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  List   _direcciones  = [];
  Map?   _delivery;
  int?   _idDireccion;
  bool   _loading      = true;
  bool   _pagando      = false;
  String _errorMsg     = '';

  double _costoEnvio = 0.0;
  int? _diasHabiles;
  bool _calculandoEnvio = false;
  bool _aceptarPoliticas = false;
  bool _deliveryCostError = false;

  @override
  void initState() {
    super.initState();
    _loadDatos();
  }

  @override
  void dispose() {
    super.dispose();
  }

  Future<void> _loadDatos() async {
    setState(() {
      _loading = true;
      _errorMsg = '';
    });
    try {
      final results = await Future.wait([
        ApiClient.get('/direcciones', auth: true, useCache: false),
        ApiClient.get('/delivery/config'),
      ]);

      if (results[0].statusCode == 200) _direcciones = jsonDecode(results[0].body);
      if (results[1].statusCode == 200) _delivery = jsonDecode(results[1].body);

      // Preseleccionar la dirección predeterminada
      if (_direcciones.isNotEmpty) {
        final pred = _direcciones.firstWhere(
          (d) => ApiClient.parseInt(d['es_predeterminada']) == 1,
          orElse: () => _direcciones.first,
        );
        _idDireccion = ApiClient.parseInt(pred['id_direccion']);
      } else {
        _idDireccion = null;
      }
    } catch (e) {
      _errorMsg = 'Error al cargar los datos del checkout: $e';
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
      // Calcular costo de envío inicial
      await _calcularCostoEnvio();
    }
  }

  Future<void> _calcularCostoEnvio() async {
    if (_idDireccion == null || widget.carrito['tipo'] == 'servicio') {
      if (mounted) {
        setState(() {
          _costoEnvio = 0.0;
          _diasHabiles = null;
          _deliveryCostError = false;
        });
      }
      return;
    }

    // Buscar dirección seleccionada
    final d = _direcciones.firstWhere((element) => ApiClient.parseInt(element['id_direccion']) == _idDireccion, orElse: () => null);
    if (d == null) return;

    final municipio = d['municipio']?['municipio']?.toString();
    if (municipio == null || municipio.isEmpty) {
      if (mounted) {
        setState(() {
          _costoEnvio = 0.0;
          _diasHabiles = null;
          _deliveryCostError = false;
        });
      }
      return;
    }

    if (mounted) setState(() => _calculandoEnvio = true);

    final items = widget.carrito['items_intencion_compra'] as List? ?? widget.carrito['items'] as List? ?? [];
    final selectedItems = items.where((i) => ApiClient.parseBool(i['es_seleccionado'])).toList();

    double maxPeso = 0.0;
    double maxAlto = 0.0;
    double maxAncho = 0.0;
    double maxProfundo = 0.0;

    for (var i in selectedItems) {
      final itemData = i['item'] as Map<String, dynamic>?;
      if (itemData != null) {
        final double p = double.tryParse(itemData['peso_lbs']?.toString() ?? '') ?? 0.0;
        final double al = double.tryParse(itemData['alto_cm']?.toString() ?? '') ?? 0.0;
        final double an = double.tryParse(itemData['ancho_cm']?.toString() ?? '') ?? 0.0;
        final double pr = double.tryParse(itemData['profundo_cm']?.toString() ?? '') ?? 0.0;

        if (p > maxPeso) maxPeso = p;
        if (al > maxAlto) maxAlto = al;
        if (an > maxAncho) maxAncho = an;
        if (pr > maxProfundo) maxProfundo = pr;
      }
    }

    try {
      final res = await ApiClient.get(
        '/delivery/calcular?pueblo=${Uri.encodeComponent(municipio)}&valor_articulo=$_subtotal&peso_lbs=$maxPeso&alto_cm=$maxAlto&ancho_cm=$maxAncho&profundo_cm=$maxProfundo',
        auth: true,
      );

      if (res.statusCode == 200 || res.statusCode == 404) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          if (mounted) {
            setState(() {
              _costoEnvio = double.tryParse(body['costo_envio_total'].toString()) ?? 0.0;
              _diasHabiles = int.tryParse(body['dias_habiles'].toString());
              _calculandoEnvio = false;
              _deliveryCostError = false;
            });
          }
          return;
        } else if (body['error_code'] == 'MISSING_DELIVERY_TARIFF') {
          if (mounted) {
            setState(() {
              _costoEnvio = 0.0;
              _diasHabiles = null;
              _calculandoEnvio = false;
              _deliveryCostError = true;
            });
          }
          return;
        }
      }
    } catch (e) {
      // Fallback a 0
    }

    if (mounted) {
      setState(() {
        _costoEnvio = 0.0;
        _diasHabiles = null;
        _calculandoEnvio = false;
        _deliveryCostError = false;
      });
    }
  }

  String _obtenerFechaEntregaEstimada(int diasHabiles) {
    DateTime fecha = DateTime.now();
    int agregados = 0;
    while (agregados < diasHabiles) {
      fecha = fecha.add(const Duration(days: 1));
      if (fecha.weekday != DateTime.saturday && fecha.weekday != DateTime.sunday) {
        agregados++;
      }
    }
    
    // Formatear en español
    final List<String> meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    final List<String> dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
    
    return "${dias[fecha.weekday - 1]} ${fecha.day} de ${meses[fecha.month - 1]}";
  }

  Future<void> _gestionarDirecciones() async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const DireccionesScreen()),
    );
    // Al regresar de gestionar las direcciones, recargar los datos
    _loadDatos();
  }

  double get _subtotal {
    // Calcular dinámicamente del carrito el subtotal antes de descuento
    final items = widget.carrito['items_intencion_compra'] as List? ?? widget.carrito['items'] as List? ?? [];
    double sum = 0.0;
    for (var i in items) {
      final esSel = ApiClient.parseBool(i['es_seleccionado']);
      if (esSel) {
        final itemData = i['item'] as Map? ?? {};
        final double valor = double.tryParse((itemData['valor'] ?? 0).toString()) ?? 0.0;
        final int cantidad = int.tryParse((i['cantidad'] ?? 1).toString()) ?? 1;
        sum += valor * cantidad;
      }
    }
    return sum;
  }

  double get _totalDescuento {
    final items = widget.carrito['items_intencion_compra'] as List? ?? widget.carrito['items'] as List? ?? [];
    double sum = 0.0;
    for (var i in items) {
      final esSel = ApiClient.parseBool(i['es_seleccionado']);
      if (esSel) {
        final double descuento = double.tryParse((i['descuento'] ?? 0).toString()) ?? 0.0;
        final int cantidad = int.tryParse((i['cantidad'] ?? 1).toString()) ?? 1;
        sum += descuento * cantidad;
      }
    }
    return sum;
  }

  double get _envio {
    if (widget.carrito['tipo'] == 'servicio') return 0.0;
    return _costoEnvio;
  }

  double get _totalFinal => _subtotal - _totalDescuento + _envio;

  Future<void> _pagar() async {
    final bool esServicio = widget.carrito['tipo'] == 'servicio';
    if (!esServicio && _idDireccion == null) {
      setState(() => _errorMsg = 'Debes seleccionar una dirección de entrega.');
      return;
    }
    if (!esServicio && _deliveryCostError) {
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Costo de envío pendiente'),
          content: const Text('Esperando por el administrador para el costo de envío'),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Aceptar'),
            ),
          ],
        ),
      );
      return;
    }
    if (!_aceptarPoliticas) {
      setState(() => _errorMsg = 'Debes aceptar los Términos y Condiciones y las Políticas de Privacidad.');
      return;
    }
    setState(() { _pagando = true; _errorMsg = ''; });

    final Map<String, dynamic> payload = {};
    if (!esServicio) {
      payload['id_direccion'] = _idDireccion;
    }

    try {
      final res = await ApiClient.post('/pago/checkout', payload, auth: true);

      setState(() => _pagando = false);

      if (res.statusCode == 200 || res.statusCode == 201) {
        final body = jsonDecode(res.body);
        final redirectUrl = body['redirect_url']?.toString();
        if (redirectUrl != null && redirectUrl.isNotEmpty) {
          final Uri url = Uri.parse(redirectUrl);
          if (await canLaunchUrl(url)) {
            await launchUrl(url, mode: LaunchMode.externalApplication);
            if (!mounted) return;
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
              content: Text('Redirigiendo a la pasarela de pago seguro...'),
              backgroundColor: Colors.blue,
            ));
            ApiClient.clearCache('/carrito');
            ApiClient.cartCountNotifier.value = 0;
            Navigator.pop(context);
          } else {
            setState(() => _errorMsg = 'No se pudo abrir la pasarela de pago.');
          }
        } else {
          setState(() => _errorMsg = 'No se recibió la URL de redirección.');
        }
      } else {
        final body = jsonDecode(res.body);
        setState(() => _errorMsg = body['message'] ?? 'Error al procesar el pago.');
      }
    } catch (e) {
      setState(() {
        _pagando = false;
        _errorMsg = 'Error de conexión: $e';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Confirmar pedido')),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

                // ─ Dirección de entrega o Información del proveedor (Servicio) ─
                if (widget.carrito['tipo'] == 'servicio') ...[
                  _aviso(Icons.star, 'Servicio / Talento — no requiere envío', color: Colors.orange.shade50, iconColor: kSecondary),
                  const SizedBox(height: 12),
                  const Text('Información del proveedor', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: kTextDark)),
                  const SizedBox(height: 8),
                  Builder(
                    builder: (context) {
                      final items = widget.carrito['items_intencion_compra'] as List? ?? widget.carrito['items'] as List? ?? [];
                      final selectedItems = items.where((i) => ApiClient.parseBool(i['es_seleccionado'])).toList();
                      final Map<String, Map> proveedores = {};
                      for (var i in selectedItems) {
                        final itemData = i['item'] as Map? ?? {};
                        final prov = i['proveedor_info'] as Map?;
                        final provId = itemData['id_user']?.toString();
                        if (prov != null && provId != null) {
                          proveedores[provId] = prov;
                        }
                      }

                      if (proveedores.isEmpty) {
                        return _aviso(Icons.info_outline, 'No se pudo obtener información del proveedor.');
                      }

                      return Column(
                        children: proveedores.values.map((prov) {
                          final String nombre = prov['nombre']?.toString() ?? 'Proveedor';
                          final String municipio = prov['municipio']?.toString() ?? 'No disponible';
                          final String provincia = prov['provincia']?.toString() ?? '';
                          final String calle = prov['calle']?.toString() ?? '';
                          final String casa = prov['N_casa_edificio']?.toString() ?? '';
                          final String apto = prov['apto']?.toString() ?? '';
                          final String geolocalizacion = prov['geolocalizacion']?.toString() ?? '';

                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            color: Colors.orange.shade50.withOpacity(0.4),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                              side: BorderSide(color: Colors.orange.shade100, width: 1.5),
                            ),
                            child: Padding(
                              padding: const EdgeInsets.all(12),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      const Icon(Icons.person_pin_circle_outlined, color: kSecondary, size: 20),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          '$nombre — $municipio',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.brown),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const Divider(height: 16, color: Colors.orange),
                                  if (provincia.isNotEmpty)
                                    Text('Provincia: $provincia', style: const TextStyle(fontSize: 12)),
                                  if (calle.isNotEmpty)
                                    Text('Calle: $calle', style: const TextStyle(fontSize: 12)),
                                  if (casa.isNotEmpty)
                                    Text('Número de casa o edificio: $casa', style: const TextStyle(fontSize: 12)),
                                  if (apto.isNotEmpty)
                                    Text('Apartamento: $apto', style: const TextStyle(fontSize: 12)),
                                  if (geolocalizacion.isNotEmpty) ...[
                                    const SizedBox(height: 8),
                                    TextButton.icon(
                                      onPressed: () {
                                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                                          content: Text('Coordenadas: $geolocalizacion'),
                                          duration: const Duration(seconds: 4),
                                        ));
                                      },
                                      icon: const Icon(Icons.map_outlined, size: 14, color: Colors.brown),
                                      label: const Text(
                                        'Ver ubicación',
                                        style: TextStyle(decoration: TextDecoration.underline, color: Colors.brown, fontSize: 12),
                                      ),
                                      style: TextButton.styleFrom(
                                        padding: EdgeInsets.zero,
                                        minimumSize: Size.zero,
                                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          );
                        }).toList(),
                      );
                    }
                  ),
                ] else ...[
                  // ─ Dirección ─
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Dirección de entrega', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: kTextDark)),
                      TextButton.icon(
                        onPressed: _gestionarDirecciones,
                        icon: const Icon(Icons.add_location_alt_outlined, size: 16, color: kPrimary),
                        label: const Text('Gestionar', style: TextStyle(color: kPrimary, fontSize: 13)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (_direcciones.isEmpty)
                    GestureDetector(
                      onTap: _gestionarDirecciones,
                      child: _aviso(
                        Icons.location_off,
                        'No tienes direcciones guardadas. Agrega una en "Mi cuenta → Dirección".',
                        color: Colors.orange.shade50,
                        iconColor: Colors.orange.shade800,
                      ),
                    )
                  else
                    ..._direcciones.map((d) {
                      final int dirId = ApiClient.parseInt(d['id_direccion']) ?? 0;
                      return RadioListTile<int>(
                        value: dirId,
                        groupValue: _idDireccion,
                        onChanged: (v) {
                          setState(() {
                            _idDireccion = v;
                          });
                          _calcularCostoEnvio();
                        },
                        activeColor: kPrimary,
                        title: Text('${d['calle']}, #${d['N_casa_edificio'] ?? ''}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                        subtitle: Text('${d['municipio']?['municipio'] ?? ''}, ${d['provincia']?['provincia'] ?? ''}', style: TextStyle(fontSize: 12, color: kTextGray)),
                      );
                    }),
                  if (_deliveryCostError) ...[
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.amber.shade50,
                        border: Border.all(color: Colors.amber.shade200, width: 1.5),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.warning_amber_rounded, color: Colors.amber.shade800, size: 18),
                          const SizedBox(width: 8),
                          const Expanded(
                            child: Text(
                              'El sistema espera por una definición para el cálculo de Análisis de costos de envío.',
                              style: TextStyle(fontSize: 12, color: Colors.brown, fontWeight: FontWeight.w500),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],

                const Divider(height: 24),

                // ─ Tarjetas ─
                const Text('Método de Pago', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: kTextDark)),
                const SizedBox(height: 8),
                _aviso(
                  Icons.security,
                  'Serás redirigido a la pasarela de pago seguro de AZUL. Tu información financiera está completamente protegida.',
                  color: Colors.blue.shade50,
                  iconColor: Colors.blue.shade800,
                ),

                const Divider(height: 24),

                // ─ Resumen ─
                const Text('Resumen del pedido', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: kTextDark)),
                const SizedBox(height: 8),
                ...(((widget.carrito['items_intencion_compra'] as List? ?? widget.carrito['items'] as List? ?? [])
                    .where((i) => ApiClient.parseBool(i['es_seleccionado']))
                    .map((i) {
                  final item = i['item'] as Map? ?? {};
                  final double valor = double.tryParse((item['valor'] ?? 0).toString()) ?? 0.0;
                  final int cantidad = int.tryParse((i['cantidad'] ?? 1).toString()) ?? 1;
                  final double subtotalItem = valor * cantidad;
                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 4),
                    child: Row(children: [
                      Expanded(child: Text(item['item'] ?? '', style: const TextStyle(fontSize: 13, color: kTextDark))),
                      Text('x$cantidad', style: TextStyle(color: kTextGray, fontSize: 12)),
                      const SizedBox(width: 8),
                      Text('RD\$ ${subtotalItem.toStringAsFixed(2)}', style: const TextStyle(fontSize: 13, color: kPrimary, fontWeight: FontWeight.bold)),
                    ]),
                  );
                }))),

                const Divider(height: 24),

                // ─ Totales ─
                _fila('Subtotal', 'RD\$ ${_subtotal.toStringAsFixed(2)}'),
                if (_totalDescuento > 0) ...[
                  const SizedBox(height: 4),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Descuento', style: TextStyle(fontSize: 14, color: Colors.red)),
                      Text('- RD\$ ${_totalDescuento.toStringAsFixed(2)}', style: const TextStyle(fontSize: 14, color: Colors.red, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ],
                if (widget.carrito['tipo'] != 'servicio') ...[
                  const SizedBox(height: 4),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Envío estimado', style: TextStyle(fontSize: 14, color: kTextGray)),
                      _calculandoEnvio
                          ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2, color: kPrimary))
                          : _deliveryCostError
                              ? const Text('No se pudo calcular el envío', style: TextStyle(fontSize: 14, color: Colors.red, fontWeight: FontWeight.bold))
                              : Text('RD\$ ${_envio.toStringAsFixed(2)}', style: const TextStyle(fontSize: 14, color: kTextDark)),
                    ],
                  ),
                  if (!_calculandoEnvio && _diasHabiles != null) ...[
                    const SizedBox(height: 4),
                    Align(
                      alignment: Alignment.centerRight,
                      child: Text(
                        '🚚 Entrega estimada: ${_obtenerFechaEntregaEstimada(_diasHabiles!)} (~$_diasHabiles días hábiles)',
                        style: const TextStyle(fontSize: 12, color: kTextGray),
                      ),
                    ),
                  ],
                ],
                const Divider(height: 16),
                _fila('TOTAL', 'RD\$ ${_totalFinal.toStringAsFixed(2)}', bold: true),

                // ─ Checkbox Políticas y Legal (Requisito AZUL) ─
                const SizedBox(height: 12),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(
                      height: 24,
                      width: 24,
                      child: Checkbox(
                        value: _aceptarPoliticas,
                        onChanged: (v) => setState(() => _aceptarPoliticas = v ?? false),
                        activeColor: kPrimary,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: InkWell(
                        onTap: _mostrarDialogoPoliticas,
                        child: RichText(
                          text: const TextSpan(
                            style: TextStyle(color: kTextGray, fontSize: 11, height: 1.3),
                            children: [
                              TextSpan(text: 'He leído y acepto los '),
                              TextSpan(
                                text: 'Términos y Condiciones (Política de Entrega)',
                                style: TextStyle(color: kPrimary, fontWeight: FontWeight.bold, decoration: TextDecoration.underline),
                              ),
                              TextSpan(text: ', la '),
                              TextSpan(
                                text: 'Política de Privacidad',
                                style: TextStyle(color: kPrimary, fontWeight: FontWeight.bold, decoration: TextDecoration.underline),
                              ),
                              TextSpan(text: ' y la '),
                              TextSpan(
                                text: 'Política de Devoluciones',
                                style: TextStyle(color: kPrimary, fontWeight: FontWeight.bold, decoration: TextDecoration.underline),
                              ),
                              TextSpan(text: ' de Cámbialo RD.'),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),

                // ─ Información de Comercio y Seguridad (Requisitos AZUL) ─
                Container(
                  margin: const EdgeInsets.only(top: 16, bottom: 8),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade50,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.grey.shade200),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'INFORMACIÓN DEL COMERCIO',
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: kSecondary, letterSpacing: 0.5),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Nombre comercial: Cámbialo RD\n'
                        'Dirección permanente: Napoleón Bonaparte, Manzana T, Edificio 21, Res. Pablo Mella Morales II, Santo Domingo, República Dominicana\n'
                        'Soporte: (829) 963-4839 | cambialord.com@gmail.com',
                        style: TextStyle(fontSize: 9, color: kTextGray, height: 1.3),
                      ),
                      const Divider(height: 16),
                      const Text(
                        'SEGURIDAD DE TARJETAS Y MONEDA',
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: kSecondary, letterSpacing: 0.5),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        '• Todas las transacciones se facturan y procesan en Pesos Dominicanos (RD\$ / DOP).\n'
                        '• No almacenamos ni compartimos tus datos de tarjeta ni CVV.\n'
                        '• La información se transmite de forma cifrada mediante TLS 1.2 directamente al procesador AZUL.',
                        style: TextStyle(fontSize: 9, color: kTextGray, height: 1.3),
                      ),
                    ],
                  ),
                ),

                if (_errorMsg.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  _aviso(Icons.error_outline, _errorMsg, color: Colors.red.shade50, iconColor: Colors.red),
                ],

                const SizedBox(height: 16),

                // ─ Botón pagar ─
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _pagando ? null : _pagar,
                    icon: _pagando
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Icon(Icons.payment, color: Colors.white),
                    label: Text(_pagando ? 'Procesando...' : (widget.carrito['tipo'] == 'servicio' ? 'Confirmar y pagar servicio' : 'Confirmar y pagar'), style: const TextStyle(color: Colors.white, fontSize: 15)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: kSecondary,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                  ),
                ),
              ]),
            ),
    );
  }

  void _mostrarDialogoPoliticas() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Políticas y Legal', style: TextStyle(fontWeight: FontWeight.bold, color: kPrimary)),
        content: SizedBox(
          width: double.maxFinite,
          height: 400,
          child: DefaultTabController(
            length: 3,
            child: Column(
              children: [
                const TabBar(
                  labelColor: kPrimary,
                  unselectedLabelColor: kTextGray,
                  indicatorColor: kPrimary,
                  labelStyle: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                  tabs: [
                    Tab(text: 'Términos'),
                    Tab(text: 'Privacidad'),
                    Tab(text: 'Devolución'),
                  ],
                ),
                Expanded(
                  child: TabBarView(
                    children: [
                      // Tab 1: Términos
                      SingleChildScrollView(
                        padding: const EdgeInsets.only(top: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Términos y Condiciones (Política de Entrega)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kTextDark)),
                            const SizedBox(height: 8),
                            const Text(
                              'Cámbialo RD es un mercado en línea registrado en la República Dominicana.\n\n'
                              '1. Envíos y Entregas: Los artículos se entregan a través de socios logísticos seleccionados. Las entregas se completan entre 2 a 5 días hábiles.\n\n'
                              '2. Restricciones de Envío y Exportación: Los envíos están limitados EXCLUSIVAMENTE al territorio de la República Dominicana. No realizamos exportaciones ni entregas fuera del país.',
                              style: TextStyle(fontSize: 11, color: kTextGray, height: 1.3),
                            ),
                          ],
                        ),
                      ),
                      // Tab 2: Privacidad
                      SingleChildScrollView(
                        padding: const EdgeInsets.only(top: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Política de Privacidad y Seguridad', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kTextDark)),
                            const SizedBox(height: 8),
                            const Text(
                              'Protegemos tus datos personales.\n\n'
                              'Seguridad de Tarjetas: No guardamos, almacenamos ni compartimos los números de tus tarjetas de crédito/débito ni el código de seguridad CVV. Toda la transmisión de datos financieros se realiza de forma cifrada mediante protocolo seguro TLS 1.2 directamente a la pasarela de pagos AZUL (Banco Popular Dominicano).',
                              style: TextStyle(fontSize: 11, color: kTextGray, height: 1.3),
                            ),
                          ],
                        ),
                      ),
                      // Tab 3: Devolución
                      SingleChildScrollView(
                        padding: const EdgeInsets.only(top: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Políticas de Devoluciones y Cancelación', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kTextDark)),
                            const SizedBox(height: 8),
                            const Text(
                              '1. Devoluciones: Dispones de un plazo de 48 horas contadas a partir de la recepción física del artículo para notificar disconformidades o defectos graves.\n\n'
                              '2. Reembolsos: No se realizan reembolsos de dinero en compras de artículos físicos por cambios de opinión. En caso de devoluciones válidas, los reembolsos se acreditarán a la tarjeta de pago original a través de AZUL.\n\n'
                              '3. Cancelaciones: Los pedidos de productos físicos pueden cancelarse sin cargo antes de ser enviados.',
                              style: TextStyle(fontSize: 11, color: kTextGray, height: 1.3),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            style: TextButton.styleFrom(foregroundColor: kPrimary),
            child: const Text('Entendido'),
          ),
        ],
      ),
    );
  }

  Widget _fila(String label, String valor, {bool bold = false}) => Row(
    mainAxisAlignment: MainAxisAlignment.spaceBetween,
    children: [
      Text(label, style: TextStyle(fontSize: 14, color: kTextGray, fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
      Text(valor,  style: TextStyle(fontSize: 14, color: bold ? kPrimary : kTextDark, fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
    ],
  );

  Widget _aviso(IconData icon, String msg, {Color? color, Color? iconColor}) => Container(
    padding: const EdgeInsets.all(12),
    decoration: BoxDecoration(
      color: color ?? Colors.orange.shade50,
      borderRadius: BorderRadius.circular(8),
    ),
    child: Row(children: [
      Icon(icon, color: iconColor ?? kPrimary, size: 20),
      const SizedBox(width: 8),
      Expanded(child: Text(msg, style: TextStyle(fontSize: 12, color: iconColor ?? kTextDark))),
    ]),
  );
}
