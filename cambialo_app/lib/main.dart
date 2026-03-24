import 'package:flutter/material.dart';
import 'core/theme.dart';
import 'core/auth_service.dart';
import 'screens/login_screen.dart';
import 'screens/home_screen.dart';

void main() {
  runApp(const CambialoApp());
}

class CambialoApp extends StatelessWidget {
  const CambialoApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Cambialord',
      debugShowCheckedModeBanner: false,
      theme: appTheme(),
      home: const _SplashRouter(),
    );
  }
}

class _SplashRouter extends StatefulWidget {
  const _SplashRouter();
  @override
  State<_SplashRouter> createState() => _SplashRouterState();
}

class _SplashRouterState extends State<_SplashRouter> {
  @override
  void initState() {
    super.initState();
    _check();
  }

  Future<void> _check() async {
    await Future.delayed(const Duration(milliseconds: 800));
    final loggedIn = await AuthService.isLoggedIn();
    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => loggedIn ? const HomeScreen() : const LoginScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kPrimary,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Logo igual que la web
            const Text(
              'Cambialord',
              style: TextStyle(
                color: Colors.white,
                fontSize: 38,
                fontWeight: FontWeight.bold,
                letterSpacing: 1,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Encuentra lo que deseas cambiar',
              style: TextStyle(color: Colors.white.withOpacity(0.85), fontSize: 14),
            ),
            const SizedBox(height: 40),
            const CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
          ],
        ),
      ),
    );
  }
}
