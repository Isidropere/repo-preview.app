import 'package:flutter/material.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import 'main_screen.dart';
import 'register_screen.dart';

/// Pantalla de login — fiel al diseño web de Cambialord
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey    = GlobalKey<FormState>();
  final _emailCtrl  = TextEditingController();
  final _passCtrl   = TextEditingController();
  bool _loading     = false;
  bool _remember    = false;
  String? _error;

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() { _loading = true; _error = null; });

    try {
      final result = await AuthService.login(_emailCtrl.text.trim(), _passCtrl.text);
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
      backgroundColor: const Color(0xFFF9FAFB), // bg-gray-50
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 32),
          child: Column(
            children: [
              // Logo
              Image.network(
                'http://10.0.2.2:8000/imgs/logoTypes/logoFooter.png',
                height: 80,
                errorBuilder: (_, __, ___) => const Text(
                  'Cambialord',
                  style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: kPrimary),
                ),
              ),
              const SizedBox(height: 24),

              // Card blanca — igual que la web
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 12, offset: const Offset(0, 4))],
                ),
                padding: const EdgeInsets.all(24),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Título
                      const Text('Iniciar Sesión',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: kTextDark)),
                      const SizedBox(height: 6),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text('¿Aún no tienes una cuenta? ', style: TextStyle(fontSize: 13, color: kTextGray)),
                          GestureDetector(
                            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const RegisterScreen())),
                            child: const Text('Regístrate aquí',
                                style: TextStyle(fontSize: 13, color: kPrimary, fontWeight: FontWeight.w600)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),

                      // Botón Google
                      OutlinedButton.icon(
                        onPressed: () {}, // OAuth pendiente de credenciales
                        icon: _googleIcon(),
                        label: const Text('Iniciar Sesión con Google',
                            style: TextStyle(color: kTextDark, fontSize: 13)),
                        style: OutlinedButton.styleFrom(
                          side: BorderSide(color: Colors.grey.shade200),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Divisor "O"
                      Row(children: [
                        Expanded(child: Divider(color: Colors.grey.shade200)),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                          child: Text('O', style: TextStyle(fontSize: 11, color: Colors.grey.shade400)),
                        ),
                        Expanded(child: Divider(color: Colors.grey.shade200)),
                      ]),
                      const SizedBox(height: 16),

                      // Error
                      if (_error != null) ...[
                        Text(_error!, style: const TextStyle(color: Colors.red, fontSize: 13)),
                        const SizedBox(height: 12),
                      ],

                      // Email — estilo underline igual que la web
                      TextFormField(
                        controller: _emailCtrl,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(hintText: 'Correo Electrónico'),
                        validator: (v) => v!.isEmpty ? 'Ingresa tu correo' : null,
                      ),
                      const SizedBox(height: 16),

                      // Password
                      TextFormField(
                        controller: _passCtrl,
                        obscureText: true,
                        decoration: const InputDecoration(hintText: 'Contraseña'),
                        validator: (v) => v!.isEmpty ? 'Ingresa tu contraseña' : null,
                      ),
                      const SizedBox(height: 8),

                      // ¿Olvidaste tu contraseña?
                      Align(
                        alignment: Alignment.centerRight,
                        child: Text('¿Has olvidado tu contraseña?',
                            style: const TextStyle(fontSize: 12, color: kPrimary, fontWeight: FontWeight.w500)),
                      ),
                      const SizedBox(height: 12),

                      // Recordarme
                      Row(children: [
                        Checkbox(
                          value: _remember,
                          onChanged: (v) => setState(() => _remember = v!),
                          activeColor: kPrimary,
                          materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        const Text('Recordarme', style: TextStyle(fontSize: 13)),
                      ]),
                      const SizedBox(height: 16),

                      // Botón Iniciar Sesión — bg-secondary (azul)
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: _loading ? null : _login,
                          child: _loading
                              ? const SizedBox(height: 18, width: 18,
                                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                              : const Text('Iniciar Sesión'),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Volver
                      GestureDetector(
                        onTap: () {},
                        child: Row(children: [
                          const Icon(Icons.chevron_left, color: kPrimary, size: 18),
                          const Text('Volver', style: TextStyle(color: kPrimary, fontSize: 13)),
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

  Widget _googleIcon() {
    return SizedBox(
      width: 18, height: 18,
      child: CustomPaint(painter: _GoogleIconPainter()),
    );
  }
}

// Pinta el logo de Google igual que el SVG de la web
class _GoogleIconPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paths = [
      (const Color(0xFF4285F4), 'M18 9.7C18 9.1 17.9 8.6 17.8 8.1H9.2V11.3H14.1C13.9 12.4 13.3 13.3 12.4 13.9L15.1 16C16.7 14.5 18 12.3 18 9.7Z'),
      (const Color(0xFF34A853), 'M9.2 18C11.6 18 13.6 17.2 15.1 16L12.4 13.9C11.6 14.4 10.5 14.8 9.2 14.8C6.9 14.8 4.9 13.2 4.2 11H1.4L1.4 13.2C2.8 16.1 5.8 18 9.2 18Z'),
      (const Color(0xFFFBBC05), 'M4.2 11C4 10.4 3.9 9.7 3.9 9C3.9 8.3 4 7.6 4.2 7L4.2 4.8L1.4 4.8C0.7 6.2 0.3 7.6 0.3 9C0.3 10.4 0.7 11.8 1.4 13.2L4.2 11Z'),
      (const Color(0xFFEB4335), 'M9.2 3.2C10.8 3.2 12.2 3.8 13.2 4.7L15.2 2.7C13.6 1.2 11.6 0.3 9.2 0.3C5.8 0.3 2.8 2.2 1.4 5.1L4.2 7.3C4.9 5.1 6.9 3.2 9.2 3.2Z'),
    ];
    // Simplified: just draw a colored circle as placeholder
    final paint = Paint()..color = const Color(0xFF4285F4);
    canvas.drawCircle(Offset(size.width / 2, size.height / 2), size.width / 2, paint);
  }
  @override
  bool shouldRepaint(_) => false;
}
