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
import 'items_list_screen.dart';
import '../widgets/footer_widget.dart';

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
      'title': 'Agregar Talento o Servicio',
      'sub': 'Publica y ofrece tus habilidades',
      'onTap': () => _checkHojaVidaYAgregarTalento(),
    },
    {
      'icon': Icons.star_outline,
      'title': 'Administrar Talentos',
      'sub': 'Edita o actualiza tus servicios ofrecidos',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MisTalentosScreen())),
    },
    {
      'icon': Icons.description_outlined,
      'title': 'Mi Hoja de Vida (CV)',
      'sub': 'Edita tu perfil profesional y laboral',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HojaVidaScreen())),
    },
    {
      'icon': Icons.add_box_outlined,
      'title': 'Agregar Producto',
      'sub': 'Publica un artículo para venta o intercambio',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PublicarArticuloScreen())),
    },
    {
      'icon': Icons.edit_outlined,
      'title': 'Administrar Productos',
      'sub': 'Edita, pausa o elimina tus artículos',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MisArticulosScreen())),
    },
    {
      'icon': Icons.location_on_outlined,
      'title': 'Mis Direcciones',
      'sub': 'Administra tus direcciones de envío',
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
      'title': 'Seguridad y Contraseña',
      'sub': 'Cambia tu contraseña de manera segura',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const CambiarContrasenaScreen())),
    },
    {
      'icon': Icons.history,
      'title': 'Historial General',
      'sub': 'Revisa tus compras, ventas e intercambios',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HistorialScreen())),
    },
    {
      'icon': Icons.swap_horiz_outlined,
      'title': 'Mis intercambios',
      'sub': 'Gestiona tus propuestas activas',
      'onTap': () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MisIntercambiosScreen())),
    },
    {
      'icon': Icons.gavel_outlined,
      'title': 'Políticas y Legal',
      'sub': 'Términos, privacidad y reembolsos',
      'onTap': () => _mostrarDialogoPoliticas(),
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
                    Builder(
                      builder: (context) {
                        final photoUrl = _user?['profile_photo_url']?.toString();
                        final isPlaceholder = photoUrl == null ||
                                              photoUrl.trim().isEmpty ||
                                              photoUrl.contains('default') ||
                                              photoUrl.contains('.svg') ||
                                              photoUrl.contains('via.placeholder.com');
                        return CircleAvatar(
                          radius: 32,
                          backgroundColor: kPrimary,
                          child: ClipOval(
                            child: isPlaceholder
                                ? const Icon(Icons.person, size: 36, color: Colors.white)
                                : Image.network(
                                    ApiClient.fixImageUrl(photoUrl),
                                    width: 64,
                                    height: 64,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) => const Icon(Icons.person, size: 36, color: Colors.white),
                                  ),
                          ),
                        );
                      }
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
            const SizedBox(height: 24),
            Container(
              color: kPrimary,
              padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
              child: Column(children: [
                const Text(
                  '¿Quieres intercambiar o vender un producto?\n¡Hazlo con nosotros!',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.white, fontSize: 16),
                ),
                const SizedBox(height: 16),
                Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                  ElevatedButton(
                    onPressed: () => Navigator.push(context,
                        MaterialPageRoute(builder: (_) => const PublicarArticuloScreen())),
                    style: ElevatedButton.styleFrom(backgroundColor: kSecondary),
                    child: const Text('Vender', style: TextStyle(color: Colors.white)),
                  ),
                  const SizedBox(width: 12),
                  ElevatedButton(
                    onPressed: () => Navigator.push(context,
                        MaterialPageRoute(builder: (_) => const ItemsListScreen(tipo: 2))),
                    style: ElevatedButton.styleFrom(backgroundColor: kSecondary),
                    child: const Text('Cambiar', style: TextStyle(color: Colors.white)),
                  ),
                ]),
              ]),
            ),
            const FooterWidget(),
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

  void _mostrarDialogoPoliticas() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Políticas y Legal', style: TextStyle(fontWeight: FontWeight.bold, color: kPrimary)),
        content: SizedBox(
          width: double.maxFinite,
          height: 400,
          child: DefaultTabController(
            length: 3,
            child: Column(
              children: [
                const TabBar(
                  labelColor: kPrimary,
                  unselectedLabelColor: kTextGray,
                  indicatorColor: kPrimary,
                  labelStyle: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                  tabs: [
                    Tab(text: 'Términos'),
                    Tab(text: 'Privacidad'),
                    Tab(text: 'Devolución'),
                  ],
                ),
                Expanded(
                  child: TabBarView(
                    children: [
                      // Tab 1: Términos
                      SingleChildScrollView(
                        padding: const EdgeInsets.only(top: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Términos y Condiciones (Política de Entrega)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kTextDark)),
                            const SizedBox(height: 8),
                            const Text(
                              'Cámbialo RD es un mercado en línea registrado en la República Dominicana.\n\n'
                              '1. Envíos y Entregas: Los artículos se entregan a través de socios logísticos seleccionados. Las entregas se completan entre 2 a 5 días hábiles.\n\n'
                              '2. Restricciones de Envío y Exportación: Los envíos están limitados EXCLUSIVAMENTE al territorio de la República Dominicana. No realizamos exportaciones ni entregas fuera del país.',
                              style: TextStyle(fontSize: 11, color: kTextGray, height: 1.3),
                            ),
                          ],
                        ),
                      ),
                      // Tab 2: Privacidad
                      SingleChildScrollView(
                        padding: const EdgeInsets.only(top: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Política de Privacidad y Seguridad', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kTextDark)),
                            const SizedBox(height: 8),
                            const Text(
                              'Protegemos tus datos personales.\n\n'
                              'Seguridad de Tarjetas: No guardamos, almacenamos ni compartimos los números de tus tarjetas de crédito/débito ni el código de seguridad CVV. Toda la transmisión de datos financieros se realiza de forma cifrada mediante protocolo seguro TLS 1.2 directamente a la pasarela de pagos AZUL (Banco Popular Dominicano).',
                              style: TextStyle(fontSize: 11, color: kTextGray, height: 1.3),
                            ),
                          ],
                        ),
                      ),
                      // Tab 3: Devolución
                      SingleChildScrollView(
                        padding: const EdgeInsets.only(top: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Políticas de Devoluciones y Cancelación', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kTextDark)),
                            const SizedBox(height: 8),
                            const Text(
                              '1. Devoluciones: Dispones de un plazo de 48 horas contadas a partir de la recepción física del artículo para notificar disconformidades o defectos graves.\n\n'
                              '2. Reembolsos: No se realizan reembolsos de dinero en compras de artículos físicos por cambios de opinión. En caso de devoluciones válidas, los reembolsos se acreditarán a la tarjeta de pago original a través de AZUL.\n\n'
                              '3. Cancelaciones: Los pedidos de productos físicos pueden cancelarse sin cargo antes de ser enviados.',
                              style: TextStyle(fontSize: 11, color: kTextGray, height: 1.3),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            style: TextButton.styleFrom(foregroundColor: kPrimary),
            child: const Text('Entendido'),
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
