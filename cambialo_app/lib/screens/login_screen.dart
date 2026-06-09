import 'package:flutter/material.dart';
import 'package:google_sign_in/google_sign_in.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import 'main_screen.dart';
import 'register_screen.dart';

/// Pantalla de login — rediseñada con estilo premium móvil
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
  bool _obscurePass = true;
  String? _error;
  final GoogleSignIn _googleSignIn = GoogleSignIn(
    clientId: '888336739336-ti3q4e2ejj4tuf6voeb36bbh5e2fua40.apps.googleusercontent.com',
    scopes: ['email', 'profile'],
  );

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() { _loading = true; _error = null; });

    try {
      final result = await AuthService.login(_emailCtrl.text.trim(), _passCtrl.text);
      setState(() => _loading = false);
      if (result['success']) {
        if (!mounted) return;
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (_) => const MainScreen()),
          (_) => false,
        );
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

  Future<void> _loginGoogle() async {
    if (_loading) return;
    setState(() { _loading = true; _error = null; });

    try {
      // 1. Iniciar flujo nativo de Google Sign-In
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();
      
      if (googleUser == null) {
        // El usuario canceló la autenticación
        setState(() => _loading = false);
        return;
      }

      // 2. Extraer datos del perfil
      final String googleId = googleUser.id;
      final String email = googleUser.email;
      final String nombres = googleUser.displayName ?? '';
      
      // Separar nombres y apellidos
      final nameParts = nombres.split(' ');
      final String firstName = nameParts.isNotEmpty ? nameParts[0] : nombres;
      final String lastName = nameParts.length > 1 ? nameParts.sublist(1).join(' ') : '';

      final Map<String, dynamic> selectedAccount = {
        'google_id': googleId,
        'email': email,
        'nombres': firstName,
        'apellidos': lastName,
        'profile_photo_url': googleUser.photoUrl,
      };

      // 3. Enviar datos al API de Laravel
      final result = await AuthService.loginWithGoogle(selectedAccount);
      setState(() => _loading = false);
      
      if (result['success']) {
        if (!mounted) return;
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (_) => const MainScreen()),
          (_) => false,
        );
      } else {
        setState(() => _error = result['message']);
      }
    } catch (e) {
      setState(() {
        _loading = false;
        _error = 'Error de inicio de sesión con Google: $e';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final double screenHeight = MediaQuery.of(context).size.height;

    return Scaffold(
      backgroundColor: Colors.white,
      body: Stack(
        children: [
          // Background Gradient (Aesthetics)
          Positioned.fill(
            child: Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.white,
                    Colors.orange.shade50.withOpacity(0.2),
                    Colors.blue.shade50.withOpacity(0.25),
                  ],
                ),
              ),
            ),
          ),
          
          // Back Button in Top Left Corner
          Positioned(
            top: 40,
            left: 16,
            child: SafeArea(
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.05),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: IconButton(
                  icon: const Icon(Icons.arrow_back, color: kTextDark),
                  onPressed: () {
                    if (Navigator.canPop(context)) {
                      Navigator.pop(context);
                    } else {
                      Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(builder: (_) => const MainScreen()),
                      );
                    }
                  },
                ),
              ),
            ),
          ),

          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                physics: const ClampingScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
                child: ConstrainedBox(
                  constraints: BoxConstraints(
                    minHeight: screenHeight - MediaQuery.of(context).padding.top - MediaQuery.of(context).padding.bottom - 48,
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Header Section with Logo and app info
                      Column(
                        children: [
                          Image.network(
                            '${kBaseUrl.replaceAll('/api', '')}/imgs/logoTypes/logoFooter.png',
                            height: 70,
                            errorBuilder: (_, __, ___) => const Text(
                              'Cambialord',
                              style: TextStyle(
                                fontSize: 32,
                                fontWeight: FontWeight.bold,
                                color: kPrimary,
                                letterSpacing: 0.5,
                              ),
                            ),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            '¡Cámbialo! Lo que tienes por lo que deseas',
                            style: TextStyle(
                              fontSize: 14,
                              color: kTextGray,
                              fontWeight: FontWeight.w500,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                      const SizedBox(height: 40),

                      // Form Container with premium card decoration
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.04),
                              blurRadius: 16,
                              offset: const Offset(0, 8),
                            ),
                          ],
                          border: Border.all(color: Colors.grey.shade100),
                        ),
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 28),
                        child: Form(
                          key: _formKey,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              const Text(
                                'Ingresa a tu cuenta',
                                style: TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.bold,
                                  color: kTextDark,
                                ),
                                textAlign: TextAlign.center,
                              ),
                              const SizedBox(height: 24),

                              if (_error != null) ...[
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: Colors.red.shade50,
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.error_outline, color: Colors.red, size: 20),
                                      const SizedBox(width: 10),
                                      Expanded(
                                        child: Text(
                                          _error!,
                                          style: const TextStyle(color: Colors.red, fontSize: 13, fontWeight: FontWeight.w500),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(height: 20),
                              ],

                              // Email Input
                              TextFormField(
                                controller: _emailCtrl,
                                keyboardType: TextInputType.emailAddress,
                                decoration: InputDecoration(
                                  labelText: 'Correo Electrónico',
                                  prefixIcon: const Icon(Icons.email_outlined, color: kTextGray),
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide(color: Colors.grey.shade300),
                                  ),
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide(color: Colors.grey.shade200),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: const BorderSide(color: kPrimary, width: 2),
                                  ),
                                  filled: true,
                                  fillColor: Colors.grey.shade50,
                                  contentPadding: const EdgeInsets.symmetric(vertical: 16),
                                ),
                                validator: (v) => v!.isEmpty ? 'Por favor ingresa tu correo' : null,
                              ),
                              const SizedBox(height: 20),

                              // Password Input with show/hide toggle
                              TextFormField(
                                controller: _passCtrl,
                                obscureText: _obscurePass,
                                decoration: InputDecoration(
                                  labelText: 'Contraseña',
                                  prefixIcon: const Icon(Icons.lock_outlined, color: kTextGray),
                                  suffixIcon: IconButton(
                                    icon: Icon(
                                      _obscurePass ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                      color: kTextGray,
                                    ),
                                    onPressed: () => setState(() => _obscurePass = !_obscurePass),
                                  ),
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide(color: Colors.grey.shade300),
                                  ),
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide(color: Colors.grey.shade200),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: const BorderSide(color: kPrimary, width: 2),
                                  ),
                                  filled: true,
                                  fillColor: Colors.grey.shade50,
                                  contentPadding: const EdgeInsets.symmetric(vertical: 16),
                                ),
                                validator: (v) => v!.isEmpty ? 'Por favor ingresa tu contraseña' : null,
                              ),
                              const SizedBox(height: 12),

                              // Olvidaste / Recordarme Row
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(
                                    children: [
                                      SizedBox(
                                        height: 24,
                                        width: 24,
                                        child: Checkbox(
                                          value: _remember,
                                          onChanged: (v) => setState(() => _remember = v!),
                                          activeColor: kPrimary,
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      const Text(
                                        'Recordarme',
                                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: kTextDark),
                                      ),
                                    ],
                                  ),
                                  GestureDetector(
                                    onTap: () {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(
                                          content: Text('Por favor, ponte en contacto con soporte técnico.'),
                                          backgroundColor: kPrimary,
                                        ),
                                      );
                                    },
                                    child: const Text(
                                      '¿La olvidaste?',
                                      style: TextStyle(
                                        fontSize: 13,
                                        color: kPrimary,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 28),

                              // Login Button
                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton(
                                  onPressed: _loading ? null : _login,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: kPrimary,
                                    padding: const EdgeInsets.symmetric(vertical: 16),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    elevation: 2,
                                    shadowColor: kPrimary.withOpacity(0.3),
                                  ),
                                  child: _loading
                                      ? const SizedBox(
                                          height: 20,
                                          width: 20,
                                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                        )
                                      : const Text(
                                          'Iniciar Sesión',
                                          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                                        ),
                                ),
                              ),
                              const SizedBox(height: 20),

                              // Divider "O"
                              Row(
                                children: [
                                  Expanded(child: Divider(color: Colors.grey.shade200, thickness: 1)),
                                  Padding(
                                    padding: const EdgeInsets.symmetric(horizontal: 16),
                                    child: Text(
                                      'O continúa con',
                                      style: TextStyle(fontSize: 12, color: Colors.grey.shade400, fontWeight: FontWeight.w500),
                                    ),
                                  ),
                                  Expanded(child: Divider(color: Colors.grey.shade200, thickness: 1)),
                                ],
                              ),
                              const SizedBox(height: 20),

                              // Social Google button
                              OutlinedButton.icon(
                                onPressed: _loading ? null : _loginGoogle,
                                icon: _googleIcon(),
                                label: const Text(
                                  'Google',
                                  style: TextStyle(color: kTextDark, fontSize: 14, fontWeight: FontWeight.w600),
                                ),
                                style: OutlinedButton.styleFrom(
                                  side: BorderSide(color: Colors.grey.shade200),
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  backgroundColor: Colors.white,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 32),

                      // Sign up navigation footer
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            '¿No tienes una cuenta? ',
                            style: TextStyle(fontSize: 14, color: kTextGray, fontWeight: FontWeight.w500),
                          ),
                          GestureDetector(
                            onTap: () => Navigator.push(
                              context,
                              MaterialPageRoute(builder: (_) => const RegisterScreen()),
                            ),
                            child: const Text(
                              'Regístrate',
                              style: TextStyle(
                                fontSize: 14,
                                color: kPrimary,
                                fontWeight: FontWeight.bold,
                                decoration: TextDecoration.underline,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _googleIcon() {
    return Image.network(
      'https://developers.google.com/static/identity/images/g-logo.png',
      height: 18,
      width: 18,
      errorBuilder: (_, __, ___) => SizedBox(
        width: 18, height: 18,
        child: CustomPaint(painter: _GoogleIconPainter()),
      ),
    );
  }
}

class _GoogleIconPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..color = const Color(0xFF4285F4);
    canvas.drawCircle(Offset(size.width / 2, size.height / 2), size.width / 2, paint);
  }
  @override
  bool shouldRepaint(_) => false;
}
