import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import 'login_screen.dart';
import 'historial_screen.dart';
import 'mis_articulos_screen.dart';
import 'publicar_articulo_screen.dart';
import 'direcciones_screen.dart';
import 'cambiar_contrasena_screen.dart';
import 'editar_perfil_screen.dart';

/// Pantalla "Tu cuenta" — fiel al diseño web de Cambialord
class CuentaScreen extends StatefulWidget {
  const CuentaScreen({super.key});
  @override
  State<CuentaScreen> createState() => _CuentaScreenState();
}

class _CuentaScreenState extends State<CuentaScreen> {
  Map<String, dynamic>? _user;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final u = await AuthService.me();
    setState(() { _user = u; _loading = false; });
  }

  Future<void> _logout() async {
    await AuthService.logout();
    if (!mounted) return;
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (_) => false,
    );
  }

  // Opciones del grid — igual que la web
  List<Map<String, dynamic>> get _opciones => [
    {
      'icon': Icons.add_circle_outline,
      'title': 'Agregar un nuevo talento',
      'sub': 'Publica tus talentos',
      'onTap': () => ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Módulo de talentos próximamente'), backgroundColor: kPrimary)),
    },
    {
      'icon': Icons.star_outline,
      'title': 'Administrar tus talentos',
      'sub': 'Gestiona tus talentos',
      'onTap': () => ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Módulo de talentos próximamente'), backgroundColor: kPrimary)),
    },
    {
      'icon': Icons.add_box_outlined,
      'title': 'Agregar productos',
      'sub': 'Publica tus artículos',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PublicarArticuloScreen())),
    },
    {
      'icon': Icons.edit_outlined,
      'title': 'Gestionar productos',
      'sub': 'Elimina tus artículos',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MisArticulosScreen())),
    },
    {
      'icon': Icons.location_on_outlined,
      'title': 'Dirección',
      'sub': 'Actualiza tu dirección preferida',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const DireccionesScreen())),
    },
    {
      'icon': Icons.shield_outlined,
      'title': 'Modificar contraseña',
      'sub': 'Cambia tu contraseña de manera segura',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const CambiarContrasenaScreen())),
    },
    {
      'icon': Icons.history,
      'title': 'Historial general',
      'sub': 'Revisa tus intercambios o compras',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HistorialScreen())),
    },
    {
      'icon': Icons.workspace_premium_outlined,
      'title': 'Cambiar cuenta a premium',
      'sub': 'Descubre los beneficios premium',
      'onTap': () => ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Función premium próximamente'), backgroundColor: kPrimary)),
    },
  ];

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator(color: kPrimary)));

    if (_user == null) {
      return Scaffold(
        body: Center(
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            const Icon(Icons.person_outline, size: 64, color: Colors.grey),
            const SizedBox(height: 16),
            const Text('Inicia sesión para ver tu cuenta',
                style: TextStyle(color: kTextGray, fontSize: 15)),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen())),
              child: const Text('Iniciar sesión', style: TextStyle(color: Colors.white)),
            ),
          ]),
        ),
      );
    }

    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: const Text('Mi cuenta'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout, color: Colors.red),
            onPressed: _logout,
            tooltip: 'Cerrar sesión',
          ),
        ],
      ),
      body: RefreshIndicator(
        color: kPrimary,
        onRefresh: _load,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

            // Saludo — "Hola, Nombre Apellido"
            Text(
              'Hola, ${_capitalize(_user!['nombres'])} ${_capitalize(_user!['apellidos'])}',
              style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w600, color: kPrimary),
            ),
            const SizedBox(height: 16),

            // Card de perfil con foto — igual que la web
            GestureDetector(
              onTap: () async {
                final updated = await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => EditarPerfilScreen(user: _user!)),
                );
                if (updated == true) {
                  _load();
                }
              },
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.shade200),
                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8)],
                ),
                child: Row(children: [
                  // Foto con botón de cámara encima
                  Stack(children: [
                    CircleAvatar(
                      radius: 32,
                      backgroundColor: kPrimary,
                      backgroundImage: CachedNetworkImageProvider(_user!['profile_photo_url']),
                    ),
                    Positioned(
                      bottom: 0, right: 0,
                      child: Container(
                        width: 22, height: 22,
                        decoration: const BoxDecoration(color: kPrimary, shape: BoxShape.circle),
                        child: const Icon(Icons.camera_alt, color: Colors.white, size: 13),
                      ),
                    ),
                  ]),
                  const SizedBox(width: 16),
                  Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text('${_user!['nombres']} ${_user!['apellidos']}',
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15, color: kTextDark)),
                    const SizedBox(height: 2),
                    Text(_user!['email'],
                        style: TextStyle(fontSize: 12, color: kTextGray)),
                  ])),
                  const Icon(Icons.chevron_right, color: kTextGray),
                ]),
              ),
            ),
            const SizedBox(height: 20),

            // Grid de opciones — igual que la web (2 columnas)
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                childAspectRatio: 2.2,
                crossAxisSpacing: 10,
                mainAxisSpacing: 10,
              ),
              itemCount: _opciones.length,
              itemBuilder: (_, i) => _OpcionCard(opcion: _opciones[i]),
            ),
          ]),
        ),
      ),
    );
  }

  String _capitalize(String s) => s.isEmpty ? s : s[0].toUpperCase() + s.substring(1);
}

class _OpcionCard extends StatelessWidget {
  final Map<String, dynamic> opcion;
  const _OpcionCard({required this.opcion});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: opcion['onTap'] as VoidCallback?,
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: Colors.grey.shade200),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 6)],
        ),
        child: Row(children: [
          Icon(opcion['icon'] as IconData, color: kPrimary, size: 22),
          const SizedBox(width: 8),
          Expanded(child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(opcion['title'] as String,
                  maxLines: 1, overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: kTextDark)),
              Text(opcion['sub'] as String,
                  maxLines: 1, overflow: TextOverflow.ellipsis,
                  style: TextStyle(fontSize: 10, color: kTextGray)),
            ],
          )),
        ]),
      ),
    );
  }
}
