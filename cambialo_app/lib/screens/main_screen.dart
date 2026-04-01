import 'package:flutter/material.dart';
import '../core/theme.dart';
import 'home_screen.dart';
import 'items_list_screen.dart';
import 'carrito_screen.dart';
import 'cuenta_screen.dart';

/// Pantalla principal con Bottom Navigation Bar
/// Igual que la web: Home | Intercambio | Comprar | Carrito | Mi cuenta
class MainScreen extends StatefulWidget {
  const MainScreen({super.key});
  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _index = 0;

  final _screens = const [
    HomeScreen(),
    ItemsListScreen(tipo: 2),   // Intercambio
    ItemsListScreen(tipo: 1),   // Comprar
    CarritoScreen(),
    CuentaScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(index: _index, children: _screens),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (i) => setState(() => _index = i),
        backgroundColor: Colors.white,
        indicatorColor: kPrimary.withOpacity(0.12),
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        destinations: const [
          NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home, color: kPrimary), label: 'Inicio'),
          NavigationDestination(icon: Icon(Icons.swap_horiz_outlined), selectedIcon: Icon(Icons.swap_horiz, color: kPrimary), label: 'Intercambiar'),
          NavigationDestination(icon: Icon(Icons.storefront_outlined), selectedIcon: Icon(Icons.storefront, color: kPrimary), label: 'Comprar'),
          NavigationDestination(icon: Icon(Icons.shopping_cart_outlined), selectedIcon: Icon(Icons.shopping_cart, color: kPrimary), label: 'Carrito'),
          NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person, color: kPrimary), label: 'Mi cuenta'),
        ],
      ),
    );
  }
}
