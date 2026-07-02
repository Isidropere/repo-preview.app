import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
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
import '../widgets/ticker_banner_widget.dart';

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

  bool _showBottomNav = true;
  double _lastScrollOffset = 0;

  final List<GlobalKey<NavigatorState>> _navigatorKeys = List.generate(5, (_) => GlobalKey<NavigatorState>());

  bool _onScrollNotification(ScrollNotification notification) {
    if (notification is ScrollUpdateNotification) {
      final metrics = notification.metrics;
      if (metrics.axis == Axis.vertical) {
        final double currentOffset = metrics.pixels;
        if (currentOffset <= 10) {
          if (!_showBottomNav) {
            setState(() => _showBottomNav = true);
          }
        } else {
          final double delta = currentOffset - _lastScrollOffset;
          if (delta > 15 && _showBottomNav) {
            setState(() => _showBottomNav = false);
          } else if (delta < -15 && !_showBottomNav) {
            setState(() => _showBottomNav = true);
          }
        }
        _lastScrollOffset = currentOffset;
      }
    }
    return false;
  }

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
    if (i == _index) {
      final currentNavigator = _navigatorKeys[i].currentState;
      if (currentNavigator != null && currentNavigator.canPop()) {
        currentNavigator.popUntil((route) => route.isFirst);
      }
      return;
    }

    if (i == 3 || i == 4) {
      final isLoggedIn = await AuthService.isLoggedIn();
      if (!isLoggedIn) {
        if (!mounted) return;
        final loggedIn = await Navigator.of(context, rootNavigator: true).push(
          MaterialPageRoute(builder: (_) => const LoginScreen()),
        );
        if (loggedIn == true) {
          if (mounted) {
            setState(() {
              _index = i;
              _showBottomNav = true;
              _lastScrollOffset = 0;
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
        _showBottomNav = true;
        _lastScrollOffset = 0;
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

  Widget _buildTabNavigator(int index, Widget rootPage) {
    return Navigator(
      key: _navigatorKeys[index],
      onGenerateRoute: (routeSettings) {
        return MaterialPageRoute(
          builder: (context) => rootPage,
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final screens = [
      _buildTabNavigator(0, HomeScreen(key: ValueKey('home_$_authKey'))),
      _buildTabNavigator(1, ItemsListScreen(key: ValueKey('inter_$_authKey'), tipo: 2)),
      _buildTabNavigator(2, ItemsListScreen(key: ValueKey('comp_$_authKey'), tipo: 1)),
      _buildTabNavigator(3, CarritoScreen(key: ValueKey('carrito_${_authKey}_$_cartKey'))),
      _buildTabNavigator(4, CuentaScreen(key: ValueKey('cuenta_$_authKey'))),
    ];

    return PopScope(
      canPop: false,
      onPopInvoked: (didPop) {
        if (didPop) return;
        final currentNavigator = _navigatorKeys[_index].currentState;
        if (currentNavigator != null && currentNavigator.canPop()) {
          currentNavigator.pop();
        } else if (_index != 0) {
          setState(() {
            _index = 0;
            _showBottomNav = true;
            _lastScrollOffset = 0;
          });
        } else {
          SystemNavigator.pop();
        }
      },
      child: Scaffold(
        drawer: const CategoriasDrawer(),
        body: Column(
          children: [
            const TickerBannerWidget(),
            Expanded(
              child: NotificationListener<ScrollNotification>(
                onNotification: _onScrollNotification,
                child: IndexedStack(index: _index, children: screens),
              ),
            ),
          ],
        ),
        bottomNavigationBar: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          height: _showBottomNav ? (80.0 + MediaQuery.of(context).padding.bottom) : 0.0,
          clipBehavior: Clip.antiAlias,
          decoration: const BoxDecoration(),
          child: NavigationBar(
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
      ),
    );
  }
}
