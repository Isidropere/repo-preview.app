import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/api_client.dart';
import '../core/auth_service.dart';
import '../core/theme.dart';
import 'login_screen.dart';
import 'historial_screen.dart';
import 'mis_articulos_screen.dart';
import 'publicar_articulo_screen.dart';
import 'direcciones_screen.dart';
import 'cambiar_contrasena_screen.dart';
import 'editar_perfil_screen.dart';
import 'tarjetas_screen.dart';
import 'hoja_vida_screen.dart';
import 'publicar_talento_screen.dart';
import 'mis_talentos_screen.dart';
import 'mis_intercambios_screen.dart';
import 'notificaciones_screen.dart';

/// Pantalla "Tu cuenta" — fiel al diseño web de Cambialord
class CuentaScreen extends StatefulWidget {
  const CuentaScreen({super.key});
  @override
  State<CuentaScreen> createState() => _CuentaScreenState();
}

class _CuentaScreenState extends State<CuentaScreen> {
  Map<String, dynamic>? _user;
  bool _loading = true;
  int _unreadCount = 0;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final u = await AuthService.me();
      if (!mounted) return;
      setState(() { _user = u; _loading = false; });
      if (u == null) {
        final loggedIn = await Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const LoginScreen()),
        );
        if (loggedIn == true) {
          _load();
        } else {
          if (mounted && Navigator.canPop(context)) {
            Navigator.pop(context);
          }
        }
      } else {
        _refreshBackground();
        _loadNotificationsCount();
      }
    } catch (e) {
      if (mounted) {
        setState(() { _loading = false; });
      }
    }
  }

  Future<void> _loadNotificationsCount() async {
    try {
      final res = await ApiClient.get('/notificaciones/todas', auth: true, useCache: false);
      if (res.statusCode == 200 && mounted) {
        final data = jsonDecode(res.body);
        final list = data['mensajes'] ?? [];
        final count = list.where((n) => n['leido'] == 0).length;
        setState(() {
          _unreadCount = count;
        });
      }
    } catch (_) {}
  }

  Future<void> _refreshBackground() async {
    final freshUser = await AuthService.me(forceRefresh: true);
    if (freshUser != null && mounted) {
      setState(() { _user = freshUser; });
    }
    _loadNotificationsCount();
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
      'title': 'Agregar talento',
      'sub': 'Publica tus talentos',
      'onTap': () => _checkHojaVidaYAgregarTalento(),
    },
    {
      'icon': Icons.star_outline,
      'title': 'Gestionar talentos',
      'sub': 'Administra tus talentos',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MisTalentosScreen())),
    },
    {
      'icon': Icons.description_outlined,
      'title': 'Hoja de Vida',
      'sub': 'Tu perfil profesional',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HojaVidaScreen())),
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
      'icon': Icons.credit_card_outlined,
      'title': 'Métodos de pago',
      'sub': 'Gestiona tus tarjetas guardadas',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const TarjetasScreen())),
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
      'icon': Icons.swap_horiz_outlined,
      'title': 'Mis intercambios',
      'sub': 'Gestiona tus propuestas activas',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MisIntercambiosScreen())),
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
              onPressed: () async {
                await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
                _load();
              },
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
                      backgroundImage: NetworkImage(ApiClient.fixImageUrl(_user!['profile_photo_url'])),
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
            const SizedBox(height: 16),

            // Card de Notificaciones — Cubre ancho completo abajo del nombre de usuario
            GestureDetector(
              onTap: () async {
                await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const NotificacionesScreen()),
                );
                _loadNotificationsCount();
              },
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Colors.orange.shade500, Colors.orange.shade600],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.orange.withOpacity(0.2),
                      blurRadius: 8,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Stack(
                      clipBehavior: Clip.none,
                      children: [
                        const CircleAvatar(
                          radius: 20,
                          backgroundColor: Colors.white24,
                          child: Icon(Icons.notifications_active_outlined, color: Colors.white, size: 22),
                        ),
                        if (_unreadCount > 0)
                          Positioned(
                            right: -2,
                            top: -2,
                            child: Container(
                              padding: const EdgeInsets.all(4),
                              decoration: const BoxDecoration(
                                color: Colors.red,
                                shape: BoxShape.circle,
                              ),
                              constraints: const BoxConstraints(
                                minWidth: 16,
                                minHeight: 16,
                              ),
                              child: Text(
                                '$_unreadCount',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 9,
                                  fontWeight: FontWeight.bold,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Notificaciones',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            _unreadCount > 0
                                ? 'Tienes $_unreadCount alertas sin leer'
                                : 'No tienes alertas pendientes',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.white.withOpacity(0.9),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const Icon(Icons.chevron_right, color: Colors.white, size: 24),
                  ],
                ),
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

  Future<void> _checkHojaVidaYAgregarTalento() async {
    setState(() => _loading = true);
    try {
      final res = await ApiClient.get('/hoja-vida', auth: true, useCache: false);
      setState(() => _loading = false);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        final tieneHoja = body['tiene_hoja_vida'] == true;
        if (!mounted) return;
        if (tieneHoja) {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const PublicarTalentoScreen()));
        } else {
          _mostrarDialogoRequerirHojaVida();
        }
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Error al verificar tu perfil profesional.'), backgroundColor: Colors.red)
        );
      }
    } catch (e) {
      setState(() => _loading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Error de conexión con el servidor.'), backgroundColor: Colors.red)
      );
    }
  }

  void _mostrarDialogoRequerirHojaVida() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Perfil Profesional Requerido'),
        content: const Text('Para publicar tus talentos y servicios, primero debes completar tu Hoja de Vida (perfil profesional).'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              Navigator.push(context, MaterialPageRoute(builder: (_) => const HojaVidaScreen()));
            },
            style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
            child: const Text('Completar ahora', style: TextStyle(color: Colors.white)),
          ),
        ],
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
