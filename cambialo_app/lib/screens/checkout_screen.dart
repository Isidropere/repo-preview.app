import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';

/// Pantalla de Checkout — selección de dirección + tarjetas + resumen + pago
class CheckoutScreen extends StatefulWidget {
  final Map carrito;
  const CheckoutScreen({super.key, required this.carrito});
  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  List   _direcciones  = [];
  List   _tarjetas     = [];
  Map?   _delivery;
  int?   _idDireccion;
  String? _idTarjeta;
  final _cvvCtrl       = TextEditingController();
  bool   _loading      = true;
  bool   _pagando      = false;
  String _errorMsg     = '';

  // Formulario nueva tarjeta
  final _formTarjetaKey   = GlobalKey<FormState>();
  final _noTarjetaCtrl    = TextEditingController();
  final _nombreTitularCtrl= TextEditingController();
  final _mesExpCtrl       = TextEditingController();
  final _anioExpCtrl      = TextEditingController();
  final _bancoCtrl        = TextEditingController();
  final _tipoCtrl         = TextEditingController();
  bool  _registrandoTarjeta = false;

  @override
  void initState() {
    super.initState();
    _loadDatos();
  }

  @override
  void dispose() {
    _cvvCtrl.dispose();
    _noTarjetaCtrl.dispose();
    _nombreTitularCtrl.dispose();
    _mesExpCtrl.dispose();
    _anioExpCtrl.dispose();
    _bancoCtrl.dispose();
    _tipoCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadDatos() async {
    setState(() => _loading = true);
    final results = await Future.wait([
      ApiClient.get('/direcciones', auth: true, useCache: false),
      ApiClient.get('/tarjetas', auth: true, useCache: false),
      ApiClient.get('/delivery/config'),
    ]);

    if (results[0].statusCode == 200) _direcciones = jsonDecode(results[0].body);
    if (results[1].statusCode == 200) _tarjetas = jsonDecode(results[1].body);
    if (results[2].statusCode == 200) _delivery = jsonDecode(results[2].body);

    // Preseleccionar la dirección predeterminada
    if (_direcciones.isNotEmpty) {
      final pred = _direcciones.firstWhere(
        (d) => d['es_predeterminada'] == 1,
        orElse: () => _direcciones.first,
      );
      _idDireccion = pred['id_direccion'];
    }

    // Preseleccionar tarjeta activa o la primera
    if (_tarjetas.isNotEmpty) {
      final activa = _tarjetas.firstWhere(
        (t) => t['usar_esta_tarjeta'] == 1,
        orElse: () => _tarjetas.first,
      );
      _idTarjeta = activa['id_tarjeta'];
    }

    setState(() => _loading = false);
  }

  double get _subtotal {
    final total = widget.carrito['total'];
    return (total is num) ? total.toDouble() : 0.0;
  }

  double get _envio => 200.0; // En Cambialord es dinámico, fijado por defecto básico aquí.

  double get _totalFinal => _subtotal + _envio;

  Future<void> _pagar() async {
    if (_idDireccion == null) {
      setState(() => _errorMsg = 'Debes seleccionar una dirección de entrega.');
      return;
    }
    if (_idTarjeta == null) {
      setState(() => _errorMsg = 'Debes seleccionar una tarjeta de pago.');
      return;
    }
    if (_cvvCtrl.text.trim().isEmpty) {
      setState(() => _errorMsg = 'Debes ingresar el código CVV.');
      return;
    }
    setState(() { _pagando = true; _errorMsg = ''; });

    final res = await ApiClient.post('/pago/checkout', {
      'id_direccion': _idDireccion,
      'id_tarjeta':   _idTarjeta,
      'cvv':          _cvvCtrl.text.trim(),
      'total':        _totalFinal,
    }, auth: true);

    setState(() => _pagando = false);

    if (res.statusCode == 200 || res.statusCode == 201) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('¡Compra realizada con éxito!'),
        backgroundColor: Colors.green,
      ));
      // Vaciar localmente caché del carrito
      ApiClient.clearCache('/carrito');
      Navigator.pop(context);
    } else {
      final body = jsonDecode(res.body);
      setState(() => _errorMsg = body['message'] ?? 'Error al procesar el pago.');
    }
  }

  Future<void> _agregarTarjeta() async {
    if (!_formTarjetaKey.currentState!.validate()) return;
    setState(() => _registrandoTarjeta = true);

    final res = await ApiClient.post('/tarjetas', {
      'no_tarjeta':     _noTarjetaCtrl.text.trim(),
      'nombre_titular': _nombreTitularCtrl.text.trim(),
      'mes_expiracion': _mesExpCtrl.text.trim(),
      'anio_expiracion':_anioExpCtrl.text.trim(),
      'banco_tarjeta':  _bancoCtrl.text.trim().isEmpty ? 'Banco' : _bancoCtrl.text.trim(),
      'tipo_tarjeta':   _tipoCtrl.text.trim().isEmpty ? 'Visa' : _tipoCtrl.text.trim(),
      'usar_esta_tarjeta': true
    }, auth: true);

    setState(() => _registrandoTarjeta = false);

    if (res.statusCode == 201 || res.statusCode == 200) {
      Navigator.pop(context); // cerrar bottom sheet
      _noTarjetaCtrl.clear();
      _nombreTitularCtrl.clear();
      _mesExpCtrl.clear();
      _anioExpCtrl.clear();
      _bancoCtrl.clear();
      _tipoCtrl.clear();
      _loadDatos(); // Recargar datos
    } else {
      final body = jsonDecode(res.body);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(body['message'] ?? 'Error al guardar la tarjeta.'),
        backgroundColor: Colors.red,
      ));
    }
  }

  void _mostrarDialogoTarjeta() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom + 20,
          top: 20, left: 20, right: 20
        ),
        child: SingleChildScrollView(
          child: Form(
            key: _formTarjetaKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Nueva Tarjeta de Pago', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: kTextDark)),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _noTarjetaCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'Número de tarjeta *', border: OutlineInputBorder()),
                  validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _nombreTitularCtrl,
                  decoration: const InputDecoration(labelText: 'Nombre del titular *', border: OutlineInputBorder()),
                  validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
                ),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(
                    child: TextFormField(
                      controller: _mesExpCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Mes Venc. (MM) *', border: OutlineInputBorder()),
                      validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextFormField(
                      controller: _anioExpCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Año Venc. (AA) *', border: OutlineInputBorder()),
                      validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
                    ),
                  ),
                ]),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(
                    child: TextFormField(
                      controller: _bancoCtrl,
                      decoration: const InputDecoration(labelText: 'Banco (Ej. Popular)', border: OutlineInputBorder()),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextFormField(
                      controller: _tipoCtrl,
                      decoration: const InputDecoration(labelText: 'Tipo (Ej. Visa)', border: OutlineInputBorder()),
                    ),
                  ),
                ]),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _registrandoTarjeta ? null : _agregarTarjeta,
                    style: ElevatedButton.styleFrom(backgroundColor: kPrimary, padding: const EdgeInsets.symmetric(vertical: 14)),
                    child: _registrandoTarjeta
                        ? const CircularProgressIndicator(color: Colors.white)
                        : const Text('Guardar Tarjeta', style: TextStyle(color: Colors.white, fontSize: 15)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
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

                // ─ Dirección ─
                const Text('Dirección de entrega', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: kTextDark)),
                const SizedBox(height: 8),
                if (_direcciones.isEmpty)
                  _aviso(Icons.location_off, 'No tienes direcciones guardadas. Agrega una en "Mi cuenta → Dirección".')
                else
                  ..._direcciones.map((d) => RadioListTile<int>(
                    value: d['id_direccion'],
                    groupValue: _idDireccion,
                    onChanged: (v) => setState(() => _idDireccion = v),
                    activeColor: kPrimary,
                    title: Text('${d['calle']}, #${d['N_casa_edificio'] ?? ''}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    subtitle: Text('${d['municipio']?['municipio'] ?? ''}, ${d['provincia']?['provincia'] ?? ''}', style: TextStyle(fontSize: 12, color: kTextGray)),
                  )),

                const Divider(height: 24),

                // ─ Tarjetas ─
                Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                  const Text('Método de Pago', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: kTextDark)),
                  TextButton.icon(
                    onPressed: _mostrarDialogoTarjeta,
                    icon: const Icon(Icons.add, size: 16, color: kPrimary),
                    label: const Text('Nueva tarjeta', style: TextStyle(color: kPrimary, fontSize: 13)),
                  ),
                ]),
                const SizedBox(height: 8),
                if (_tarjetas.isEmpty)
                  _aviso(Icons.credit_card_off, 'No tienes tarjetas guardadas. Agrega una para continuar.')
                else ...[
                  ..._tarjetas.map((t) => RadioListTile<String>(
                    value: t['id_tarjeta'],
                    groupValue: _idTarjeta,
                    onChanged: (v) => setState(() => _idTarjeta = v),
                    activeColor: kPrimary,
                    title: Text('**** **** **** ${t['last4']}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    subtitle: Text('${t['nombre_titular']} | Vence ${t['mes_expiracion']}/${t['anio_expiracion']}', style: TextStyle(fontSize: 12, color: kTextGray)),
                  )),
                  const SizedBox(height: 12),
                  // Ingresar CVV
                  TextFormField(
                    controller: _cvvCtrl,
                    keyboardType: TextInputType.number,
                    obscureText: true,
                    maxLength: 4,
                    decoration: const InputDecoration(
                      labelText: 'Código de seguridad (CVV) *',
                      border: OutlineInputBorder(),
                      counterText: '',
                      prefixIcon: Icon(Icons.lock_outline),
                    ),
                  ),
                ],

                const Divider(height: 24),

                // ─ Resumen ─
                const Text('Resumen del pedido', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: kTextDark)),
                const SizedBox(height: 8),
                ...((widget.carrito['items'] as List? ?? []).map((i) {
                  final item = i['item'] as Map? ?? {};
                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 4),
                    child: Row(children: [
                      Expanded(child: Text(item['item'] ?? '', style: const TextStyle(fontSize: 13, color: kTextDark))),
                      Text('x${i['cantidad']}', style: TextStyle(color: kTextGray, fontSize: 12)),
                      const SizedBox(width: 8),
                      Text('RD\$ ${(item['valor'] ?? 0) * (i['cantidad'] ?? 1)}', style: const TextStyle(fontSize: 13, color: kPrimary, fontWeight: FontWeight.bold)),
                    ]),
                  );
                })),

                const Divider(height: 24),

                // ─ Totales ─
                _fila('Subtotal', 'RD\$ ${_subtotal.toStringAsFixed(2)}'),
                const SizedBox(height: 4),
                _fila('Envío estimado', 'RD\$ ${_envio.toStringAsFixed(2)}'),
                const Divider(height: 16),
                _fila('TOTAL', 'RD\$ ${_totalFinal.toStringAsFixed(2)}', bold: true),

                if (_errorMsg.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  _aviso(Icons.error_outline, _errorMsg, color: Colors.red.shade50, iconColor: Colors.red),
                ],

                const SizedBox(height: 24),

                // ─ Botón pagar ─
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _pagando ? null : _pagar,
                    icon: _pagando
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Icon(Icons.payment, color: Colors.white),
                    label: Text(_pagando ? 'Procesando...' : 'Confirmar y pagar', style: const TextStyle(color: Colors.white, fontSize: 15)),
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
