import 'package:flutter/material.dart';
import '../core/auth_service.dart';
import 'home_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});
  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nombresCtrl    = TextEditingController();
  final _apellidosCtrl  = TextEditingController();
  final _telefonoCtrl   = TextEditingController();
  final _emailCtrl      = TextEditingController();
  final _passCtrl       = TextEditingController();
  final _confirmCtrl    = TextEditingController();
  bool _loading = false;
  String? _error;

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() { _loading = true; _error = null; });

    final result = await AuthService.register({
      'nombres':          _nombresCtrl.text.trim(),
      'apellidos':        _apellidosCtrl.text.trim(),
      'telefono':         _telefonoCtrl.text.trim(),
      'email':            _emailCtrl.text.trim(),
      'password':         _passCtrl.text,
      'password_confirmation': _confirmCtrl.text,
      'tipos_usuario_id': 1,
    });

    setState(() => _loading = false);

    if (result['success']) {
      if (!mounted) return;
      Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const HomeScreen()));
    } else {
      setState(() => _error = result['message']);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Crear cuenta'),
        backgroundColor: const Color(0xFFF58634),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (_error != null)
                Container(
                  padding: const EdgeInsets.all(12),
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(color: Colors.red[50], borderRadius: BorderRadius.circular(8)),
                  child: Text(_error!, style: const TextStyle(color: Colors.red)),
                ),
              _field(_nombresCtrl,   'Nombres',    Icons.person_outline),
              const SizedBox(height: 12),
              _field(_apellidosCtrl, 'Apellidos',  Icons.person_outline),
              const SizedBox(height: 12),
              _field(_telefonoCtrl,  'Teléfono',   Icons.phone_outlined, type: TextInputType.phone),
              const SizedBox(height: 12),
              _field(_emailCtrl,     'Correo',     Icons.email_outlined,  type: TextInputType.emailAddress),
              const SizedBox(height: 12),
              _field(_passCtrl,      'Contraseña', Icons.lock_outline,    obscure: true),
              const SizedBox(height: 12),
              _field(_confirmCtrl,   'Confirmar contraseña', Icons.lock_outline, obscure: true,
                validator: (v) => v != _passCtrl.text ? 'Las contraseñas no coinciden' : null),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _loading ? null : _register,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFF58634),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: _loading
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('Registrarme', style: TextStyle(fontSize: 16)),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _field(TextEditingController ctrl, String label, IconData icon,
      {TextInputType type = TextInputType.text, bool obscure = false, String? Function(String?)? validator}) {
    return TextFormField(
      controller: ctrl,
      keyboardType: type,
      obscureText: obscure,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: const Color(0xFFF58634)),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFFF58634)),
        ),
      ),
      validator: validator ?? (v) => v!.isEmpty ? 'Campo requerido' : null,
    );
  }
}
