import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';

/// Pantalla para cambiar la contraseña del usuario
class CambiarContrasenaScreen extends StatefulWidget {
  const CambiarContrasenaScreen({super.key});
  @override
  State<CambiarContrasenaScreen> createState() => _CambiarContrasenaScreenState();
}

class _CambiarContrasenaScreenState extends State<CambiarContrasenaScreen> {
  final _formKey = GlobalKey<FormState>();
  final _actualCtrl = TextEditingController();
  final _nuevaCtrl = TextEditingController();
  final _confirmarCtrl = TextEditingController();

  bool _ocultarActual = true;
  bool _ocultarNueva = true;
  bool _ocultarConfirmar = true;
  bool _saving = false;
  String _error = '';
  String _success = '';

  // Datos del usuario (como en la web)
  Map<String, dynamic>? _user;
  bool _loadingUser = false;

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  @override
  void dispose() {
    _actualCtrl.dispose();
    _nuevaCtrl.dispose();
    _confirmarCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadUser() async {
    setState(() => _loadingUser = true);
    final user = await AuthService.me();
    setState(() {
      _user = user;
      _loadingUser = false;
    });
  }

  Future<void> _cambiar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _saving = true;
      _error = '';
      _success = '';
    });

    final res = await ApiClient.post('/auth/cambiar-contrasena', {
      'password_actual': _actualCtrl.text,
      'password': _nuevaCtrl.text,
      'password_confirmation': _confirmarCtrl.text,
    }, auth: true);

    setState(() => _saving = false);

    if (res.statusCode == 200) {
      final body = jsonDecode(res.body);
      if (body['token'] != null) {
        await ApiClient.saveToken(body['token']);
      }
      setState(() => _success = 'Contraseña actualizada correctamente.');
      _actualCtrl.clear();
      _nuevaCtrl.clear();
      _confirmarCtrl.clear();
    } else {
      final body = jsonDecode(res.body);
      setState(() => _error = body['message'] ?? 'Error al cambiar la contraseña.');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(title: const Text('Modificar contraseña')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Encabezado
              Center(
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: kPrimary.withOpacity(0.1),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.lock_outline, size: 28, color: kPrimary),
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'Cambiar contraseña',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: kTextDark),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Ingresa tu contraseña actual para verificar tu identidad',
                      style: TextStyle(fontSize: 12, color: kTextGray),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Banner con Información del Usuario (Idéntico al de la Web)
              if (_loadingUser)
                const Padding(
                  padding: EdgeInsets.only(bottom: 20),
                  child: Center(
                    child: SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(color: kPrimary, strokeWidth: 2),
                    ),
                  ),
                )
              else if (_user != null)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  margin: const EdgeInsets.only(bottom: 20),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: Colors.grey.shade200),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '${_user!['nombres'] ?? ''} ${_user!['apellidos'] ?? ''}',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: kTextDark,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        _user!['email'] ?? '',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                ),

              // Inputs con placeholders y validaciones idénticas a la web
              _passField(
                _actualCtrl,
                'Contraseña actual',
                'Tu contraseña actual',
                _ocultarActual,
                () => setState(() => _ocultarActual = !_ocultarActual),
                validator: (v) {
                  if (v == null || v.isEmpty) {
                    return 'La contraseña actual es obligatoria.';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),

              _passField(
                _nuevaCtrl,
                'Nueva contraseña',
                'Mínimo 8 caracteres',
                _ocultarNueva,
                () => setState(() => _ocultarNueva = !_ocultarNueva),
                validator: (v) {
                  if (v == null || v.isEmpty) {
                    return 'La nueva contraseña es obligatoria.';
                  }
                  if (v.length < 8) {
                    return 'La nueva contraseña debe tener al menos 8 caracteres.';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),

              _passField(
                _confirmarCtrl,
                'Confirmar nueva contraseña',
                'Repetir nueva contraseña',
                _ocultarConfirmar,
                () => setState(() => _ocultarConfirmar = !_ocultarConfirmar),
                validator: (v) {
                  if (v == null || v.isEmpty) {
                    return 'La nueva contraseña es obligatoria.';
                  }
                  if (v != _nuevaCtrl.text) {
                    return 'Las contraseñas no coinciden.';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 24),

              // Alertas de Error / Éxito
              if (_error.isNotEmpty)
                Container(
                  margin: const EdgeInsets.only(bottom: 20),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.red.shade100),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.error_outline, color: Colors.red, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          _error,
                          style: const TextStyle(color: Colors.red, fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                ),

              if (_success.isNotEmpty)
                Container(
                  margin: const EdgeInsets.only(bottom: 20),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.green.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.green.shade100),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.check_circle_outline, color: Colors.green, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          _success,
                          style: const TextStyle(color: Colors.green, fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                ),

              // Botón de Enviar
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _saving ? null : _cambiar,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: kSecondary,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: _saving
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text(
                          'Cambiar contraseña',
                          style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold),
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _passField(
    TextEditingController ctrl,
    String label,
    String hint,
    bool ocultar,
    VoidCallback toggleOcultar, {
    required String? Function(String?) validator,
  }) =>
      TextFormField(
        controller: ctrl,
        obscureText: ocultar,
        decoration: InputDecoration(
          labelText: label,
          hintText: hint,
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: const BorderSide(color: kPrimary, width: 2),
          ),
          suffixIcon: IconButton(
            icon: Icon(ocultar ? Icons.visibility_off_outlined : Icons.visibility_outlined, color: kTextGray),
            onPressed: toggleOcultar,
          ),
        ),
        validator: validator,
      );
}
