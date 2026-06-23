import 'package:flutter/material.dart';
import 'dart:async';
import 'dart:convert';
import '../core/api_client.dart';
import '../core/theme.dart';
import '../core/auth_service.dart';
import 'home_screen.dart';
import 'items_list_screen.dart';
import 'carrito_screen.dart';
import 'cuenta_screen.dart';
import 'login_screen.dart';
import '../widgets/categorias_drawer.dart';

/// Pantalla principal con Bottom Navigation Bar
class MainScreen extends StatefulWidget {
  const MainScreen({super.key});
  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _index = 0;
  int _authKey = 0;
  int _cartKey = 0;
  bool _lastAuthState = false;
  
  int _cartCount = 0;
  int _intercambiosCount = 0;
  int _notifCount = 0;
  Timer? _badgeTimer;

  @override
  void initState() {
    super.initState();
    _checkAuthStateInit();
    ApiClient.cartCountNotifier.addListener(_onCartCountChanged);
    _loadBadges();
    _badgeTimer = Timer.periodic(const Duration(seconds: 15), (_) => _loadBadges());
  }

  void _onCartCountChanged() {
    if (mounted) {
      setState(() {
        _cartCount = ApiClient.cartCountNotifier.value;
        if (_index != 3) {
          _cartKey++;
        }
      });
    }
  }

  Future<void> _checkAuthStateInit() async {
    _lastAuthState = await AuthService.isLoggedIn();
  }

  @override
  void dispose() {
    ApiClient.cartCountNotifier.removeListener(_onCartCountChanged);
    _badgeTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadBadges() async {
    if (!await AuthService.isLoggedIn()) {
      if (_cartCount != 0 || _intercambiosCount != 0 || _notifCount != 0) {
        if (mounted) {
          setState(() {
            _cartCount = 0;
            ApiClient.cartCountNotifier.value = 0;
            _intercambiosCount = 0;
            _notifCount = 0;
          });
        }
      }
      return;
    }
    try {
      final res = await ApiClient.get('/auth/badges', auth: true, useCache: false);
      if (res.statusCode == 200 && mounted) {
        final data = jsonDecode(res.body);
        final count = data['cart'] ?? 0;
        ApiClient.cartCountNotifier.value = count;
        setState(() {
          _intercambiosCount = data['intercambios'] ?? 0;
          _notifCount = data['notificaciones'] ?? 0;
        });
      }
    } catch (_) {}
  }

  void _onTabTapped(int i) async {
    if (i == 3 || i == 4) {
      final isLoggedIn = await AuthService.isLoggedIn();
      if (!isLoggedIn) {
        if (!mounted) return;
        final loggedIn = await Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const LoginScreen()),
        );
        if (loggedIn == true) {
          if (mounted) {
            setState(() {
              _index = i;
              _lastAuthState = true;
              _authKey++;
              if (i == 3) {
                _cartKey++;
              }
            });
            _loadBadges();
          }
        }
        return;
      }
    }

    if (mounted) {
      setState(() {
        _index = i;
        if (i == 3) {
          _cartKey++;
        }
      });
    }
    AuthService.isLoggedIn().then((isLoggedIn) {
       if (isLoggedIn != _lastAuthState) {
          if (mounted) {
             setState(() {
                _lastAuthState = isLoggedIn;
                _authKey++;
             });
          }
       }
     }).catchError((_) {});
  }

  @override
  Widget build(BuildContext context) {
    final screens = [
      HomeScreen(key: ValueKey('home_$_authKey')),
      ItemsListScreen(key: ValueKey('inter_$_authKey'), tipo: 2),
      ItemsListScreen(key: ValueKey('comp_$_authKey'), tipo: 1),
      CarritoScreen(key: ValueKey('carrito_${_authKey}_$_cartKey')),
      CuentaScreen(key: ValueKey('cuenta_$_authKey')),
    ];

    return PopScope(
      canPop: _index == 0,
      onPopInvoked: (didPop) {
        if (didPop) return;
        if (mounted) {
          setState(() => _index = 0);
        }
      },
      child: Scaffold(
        drawer: const CategoriasDrawer(),
        body: IndexedStack(index: _index, children: screens),
        bottomNavigationBar: NavigationBar(
          selectedIndex: _index,
          onDestinationSelected: _onTabTapped,
          backgroundColor: Colors.white,
          indicatorColor: kPrimary.withOpacity(0.12),
          labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
          destinations: [
            const NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home, color: kPrimary), label: 'Inicio'),
            NavigationDestination(
              icon: Badge(isLabelVisible: _intercambiosCount > 0, label: Text('$_intercambiosCount'), child: const Icon(Icons.swap_horiz_outlined)),
              selectedIcon: Badge(isLabelVisible: _intercambiosCount > 0, label: Text('$_intercambiosCount'), child: const Icon(Icons.swap_horiz, color: kPrimary)),
              label: 'Trueque',
            ),
            const NavigationDestination(icon: Icon(Icons.storefront_outlined), selectedIcon: Icon(Icons.storefront, color: kPrimary), label: 'Compra'),
            NavigationDestination(
              icon: Badge(isLabelVisible: _cartCount > 0, label: Text('$_cartCount'), child: const Icon(Icons.shopping_cart_outlined)),
              selectedIcon: Badge(isLabelVisible: _cartCount > 0, label: Text('$_cartCount'), child: const Icon(Icons.shopping_cart, color: kPrimary)),
              label: 'Carrito',
            ),
            NavigationDestination(
              icon: Badge(isLabelVisible: _notifCount > 0, label: Text('$_notifCount'), child: const Icon(Icons.person_outline)),
              selectedIcon: Badge(isLabelVisible: _notifCount > 0, label: Text('$_notifCount'), child: const Icon(Icons.person, color: kPrimary)),
              label: 'Mi cuenta',
            ),
          ],
        ),
      ),
    );
  }
}
