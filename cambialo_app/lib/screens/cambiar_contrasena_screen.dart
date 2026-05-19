import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

/// Pantalla para cambiar la contraseña del usuario
class CambiarContrasenaScreen extends StatefulWidget {
  const CambiarContrasenaScreen({super.key});
  @override
  State<CambiarContrasenaScreen> createState() => _CambiarContrasenaScreenState();
}

class _CambiarContrasenaScreenState extends State<CambiarContrasenaScreen> {
  final _formKey      = GlobalKey<FormState>();
  final _actualCtrl   = TextEditingController();
  final _nuevaCtrl    = TextEditingController();
  final _confirmarCtrl= TextEditingController();

  bool _ocultarActual   = true;
  bool _ocultarNueva    = true;
  bool _ocultarConfirmar= true;
  bool _saving          = false;
  String _error         = '';
  String _success       = '';

  @override
  void dispose() {
    _actualCtrl.dispose();
    _nuevaCtrl.dispose();
    _confirmarCtrl.dispose();
    super.dispose();
  }

  Future<void> _cambiar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() { _saving = true; _error = ''; _success = ''; });

    final res = await ApiClient.post('/auth/cambiar-contrasena', {
      'password_actual':  _actualCtrl.text,
      'password':         _nuevaCtrl.text,
      'password_confirmation': _confirmarCtrl.text,
    }, auth: true);

    setState(() => _saving = false);

    if (res.statusCode == 200) {
      setState(() => _success = 'Contraseña actualizada correctamente.');
      _actualCtrl.clear(); _nuevaCtrl.clear(); _confirmarCtrl.clear();
    } else {
      final body = jsonDecode(res.body);
      setState(() => _error = body['message'] ?? 'Error al cambiar la contraseña.');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Modificar contraseña')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

            const Icon(Icons.shield_outlined, size: 56, color: kPrimary),
            const SizedBox(height: 16),
            const Text('Seguridad de tu cuenta',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: kTextDark)),
            const SizedBox(height: 4),
            Text('Elige una contraseña segura de al menos 8 caracteres.',
                style: TextStyle(fontSize: 13, color: kTextGray)),
            const SizedBox(height: 28),

            _passField(_actualCtrl, 'Contraseña actual', _ocultarActual,
                () => setState(() => _ocultarActual = !_ocultarActual)),
            const SizedBox(height: 16),
            _passField(_nuevaCtrl, 'Nueva contraseña', _ocultarNueva,
                () => setState(() => _ocultarNueva = !_ocultarNueva),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Campo requerido';
                  if (v.length < 8) return 'Mínimo 8 caracteres';
                  return null;
                }),
            const SizedBox(height: 16),
            _passField(_confirmarCtrl, 'Confirmar nueva contraseña', _ocultarConfirmar,
                () => setState(() => _ocultarConfirmar = !_ocultarConfirmar),
                validator: (v) {
                  if (v != _nuevaCtrl.text) return 'Las contraseñas no coinciden';
                  return null;
                }),
            const SizedBox(height: 24),

            if (_error.isNotEmpty)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.red.shade50, borderRadius: BorderRadius.circular(8)),
                child: Row(children: [
                  const Icon(Icons.error_outline, color: Colors.red, size: 18),
                  const SizedBox(width: 8),
                  Expanded(child: Text(_error, style: const TextStyle(color: Colors.red, fontSize: 13))),
                ]),
              ),

            if (_success.isNotEmpty)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.green.shade50, borderRadius: BorderRadius.circular(8)),
                child: Row(children: [
                  const Icon(Icons.check_circle_outline, color: Colors.green, size: 18),
                  const SizedBox(width: 8),
                  Expanded(child: Text(_success, style: const TextStyle(color: Colors.green, fontSize: 13))),
                ]),
              ),

            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _saving ? null : _cambiar,
                style: ElevatedButton.styleFrom(
                  backgroundColor: kPrimary, padding: const EdgeInsets.symmetric(vertical: 14)),
                child: _saving
                    ? const SizedBox(width: 20, height: 20,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('Actualizar contraseña',
                        style: TextStyle(color: Colors.white, fontSize: 15)),
              ),
            ),
          ]),
        ),
      ),
    );
  }

  Widget _passField(
    TextEditingController ctrl,
    String label,
    bool ocultar,
    VoidCallback toggleOcultar, {
    String? Function(String?)? validator,
  }) =>
    TextFormField(
      controller: ctrl,
      obscureText: ocultar,
      decoration: InputDecoration(
        labelText: label,
        border: const OutlineInputBorder(),
        suffixIcon: IconButton(
          icon: Icon(ocultar ? Icons.visibility_off : Icons.visibility, color: kTextGray),
          onPressed: toggleOcultar,
        ),
      ),
      validator: validator ?? (v) => (v == null || v.isEmpty) ? 'Campo requerido' : null,
    );
}
