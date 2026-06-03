import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import 'main_screen.dart';

/// Pantalla de registro — fiel al diseño web de Cambialord
class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});
  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey       = GlobalKey<FormState>();
  final _nombresCtrl   = TextEditingController();
  final _apellidosCtrl = TextEditingController();
  final _telefonoCtrl  = TextEditingController();
  final _emailCtrl     = TextEditingController();
  final _passCtrl      = TextEditingController();
  final _confirmCtrl   = TextEditingController();
  bool _loading        = false;
  bool _terms          = false;
  String? _error;

  // Nombre de usuario generado automáticamente (igual que la web)
  String get _nombreUsuario =>
      (_nombresCtrl.text + _apellidosCtrl.text).replaceAll(' ', '').toLowerCase();

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;
    if (!_terms) {
      setState(() => _error = 'Debes aceptar los Términos y Condiciones');
      return;
    }
    setState(() { _loading = true; _error = null; });

    final result = await AuthService.register({
      'nombres':               _nombresCtrl.text.trim(),
      'apellidos':             _apellidosCtrl.text.trim(),
      'telefono':              _telefonoCtrl.text.trim(),
      'email':                 _emailCtrl.text.trim(),
      'password':              _passCtrl.text,
      'password_confirmation': _confirmCtrl.text,
      'tipos_usuario_id':      1,
    });

    setState(() => _loading = false);

    if (result['success']) {
      if (!mounted) return;
      Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const MainScreen()));
    } else {
      setState(() => _error = result['message']);
    }
  }

  Future<void> _loginGoogle() async {
    if (_loading) return;
    setState(() { _loading = true; _error = null; });

    final Map<String, dynamic>? selectedAccount = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
      builder: (context) {
        final accounts = [
          {
            'google_id': 'google_isidro_perez_777',
            'email': 'isidro.perez.google@gmail.com',
            'nombres': 'Isidro',
            'apellidos': 'Pérez',
            'profile_photo_url': 'https://ui-avatars.com/api/?name=Isidro+Perez&background=4285F4&color=fff&size=128',
          },
          {
            'google_id': 'google_juan_rod_888',
            'email': 'juan.rodriguez.google@gmail.com',
            'nombres': 'Juan',
            'apellidos': 'Rodríguez',
            'profile_photo_url': 'https://ui-avatars.com/api/?name=Juan+Rodriguez&background=34A853&color=fff&size=128',
          },
          {
            'google_id': 'google_maria_gomez_999',
            'email': 'maria.gomez.google@gmail.com',
            'nombres': 'María',
            'apellidos': 'Gómez',
            'profile_photo_url': 'https://ui-avatars.com/api/?name=Maria+Gomez&background=EA4335&color=fff&size=128',
          },
        ];

        return Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Regístrate con Google',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: kTextDark),
              ),
              const SizedBox(height: 4),
              Text(
                'Selecciona una cuenta para continuar en Cambialord',
                style: TextStyle(fontSize: 12, color: kTextGray),
              ),
              const Divider(height: 24),
              ...accounts.map((acc) => ListTile(
                leading: CircleAvatar(
                  backgroundImage: NetworkImage(acc['profile_photo_url']!),
                ),
                title: Text('${acc['nombres']} ${acc['apellidos']}', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                subtitle: Text(acc['email']!, style: const TextStyle(fontSize: 12)),
                onTap: () => Navigator.pop(context, acc),
              )),
              const SizedBox(height: 8),
            ],
          ),
        );
      },
    );

    if (selectedAccount == null) {
      setState(() => _loading = false);
      return;
    }
    try {
      final result = await AuthService.loginWithGoogle(selectedAccount);
      setState(() => _loading = false);
      if (result['success']) {
        if (!mounted) return;
        Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const MainScreen()));
      } else {
        setState(() => _error = result['message']);
      }
    } catch (e) {
      setState(() {
        _loading = false;
        _error = 'No se pudo conectar al servidor. Verifica que esté corriendo.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
          child: Column(
            children: [
              // Logo
              Image.network(
                '${kBaseUrl.replaceAll('/api', '')}/imgs/logoTypes/logoFooter.png',
                height: 80,
                errorBuilder: (_, __, ___) => const Text(
                  'Cambialord',
                  style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: kPrimary),
                ),
              ),
              const SizedBox(height: 16),

              // Card
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.shade200),
                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 10)],
                ),
                padding: const EdgeInsets.all(24),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const Text('Registrarse',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: kTextDark)),
                      const SizedBox(height: 6),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text('¿Ya tienes una cuenta? ', style: TextStyle(fontSize: 13, color: kTextGray)),
                          GestureDetector(
                            onTap: () => Navigator.pop(context),
                            child: const Text('Iniciar Sesión',
                                style: TextStyle(fontSize: 13, color: kPrimary, fontWeight: FontWeight.w600)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),

                      // Botón Google
                      OutlinedButton.icon(
                        onPressed: _loading ? null : _loginGoogle,
                        icon: const Icon(Icons.g_mobiledata, color: Color(0xFF4285F4), size: 22),
                        label: const Text('Regístrate con Google',
                            style: TextStyle(color: kTextDark, fontSize: 13)),
                        style: OutlinedButton.styleFrom(
                          side: BorderSide(color: Colors.grey.shade200),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                      ),
                      const SizedBox(height: 16),

                      Row(children: [
                        Expanded(child: Divider(color: Colors.grey.shade200)),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                          child: Text('O', style: TextStyle(fontSize: 11, color: Colors.grey.shade400)),
                        ),
                        Expanded(child: Divider(color: Colors.grey.shade200)),
                      ]),
                      const SizedBox(height: 16),

                      if (_error != null) ...[
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF2F2),
                            border: Border.all(color: const Color(0xFFFECACA)),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(_error!, style: const TextStyle(color: Colors.red, fontSize: 13)),
                        ),
                        const SizedBox(height: 12),
                      ],

                      _field(_nombresCtrl,   'Nombres'),
                      _field(_apellidosCtrl, 'Apellidos'),

                      // Nombre de usuario (readonly, generado automático)
                      ValueListenableBuilder(
                        valueListenable: _nombresCtrl,
                        builder: (_, __, ___) => TextFormField(
                          readOnly: true,
                          decoration: InputDecoration(hintText: 'Nombre de usuario: $_nombreUsuario'),
                        ),
                      ),
                      const SizedBox(height: 4),

                      _field(_emailCtrl,     'Correo Electrónico', type: TextInputType.emailAddress),
                      _field(_telefonoCtrl,  'Teléfono',           type: TextInputType.phone),

                      // Tipo de usuario (simplificado en app)
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        child: Text('Opción de transacción: Comprador/Vendedor',
                            style: TextStyle(fontSize: 12, color: kTextGray)),
                      ),

                      _field(_passCtrl,     'Contraseña',         obscure: true),
                      _field(_confirmCtrl,  'Confirmar Contraseña', obscure: true,
                        validator: (v) => v != _passCtrl.text ? 'Las contraseñas no coinciden' : null),

                      const SizedBox(height: 12),

                      // Términos y condiciones
                      Row(children: [
                        Checkbox(
                          value: _terms,
                          onChanged: (v) => setState(() => _terms = v!),
                          activeColor: kPrimary,
                          materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        Expanded(
                          child: RichText(
                            text: const TextSpan(
                              style: TextStyle(fontSize: 13, color: kTextDark),
                              children: [
                                TextSpan(text: 'Acepto los '),
                                TextSpan(text: 'Términos y Condiciones',
                                    style: TextStyle(color: kPrimary, fontWeight: FontWeight.w600)),
                              ],
                            ),
                          ),
                        ),
                      ]),
                      const SizedBox(height: 16),

                      // Botón Registrarse — bg-secondary (azul)
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: _loading ? null : _register,
                          child: _loading
                              ? const SizedBox(height: 18, width: 18,
                                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                              : const Text('Registrarse'),
                        ),
                      ),
                      const SizedBox(height: 16),

                      GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Row(children: [
                          const Icon(Icons.chevron_left, color: kSecondary, size: 18),
                          const Text('Volver a página de inicio',
                              style: TextStyle(color: kSecondary, fontSize: 13)),
                        ]),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _field(TextEditingController ctrl, String hint,
      {TextInputType type = TextInputType.text, bool obscure = false,
       String? Function(String?)? validator}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: TextFormField(
        controller: ctrl,
        keyboardType: type,
        obscureText: obscure,
        decoration: InputDecoration(hintText: hint),
        validator: validator ?? (v) => v!.isEmpty ? 'Campo requerido' : null,
      ),
    );
  }
}
