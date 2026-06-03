import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:http/http.dart' as http;
import '../core/auth_service.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

/// Screen to edit profile information: names, username, phone, avatar
class EditarPerfilScreen extends StatefulWidget {
  final Map<String, dynamic> user;
  const EditarPerfilScreen({super.key, required this.user});

  @override
  State<EditarPerfilScreen> createState() => _EditarPerfilScreenState();
}

class _EditarPerfilScreenState extends State<EditarPerfilScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nombresCtrl;
  late final TextEditingController _apellidosCtrl;
  late final TextEditingController _telefonoCtrl;
  late final TextEditingController _usernameCtrl;
  bool _saving = false;
  String _error = '';

  // Selector de imagen de perfil local o URL
  final ImagePicker _picker = ImagePicker();
  XFile? _localPhoto;
  String? _urlPhoto;

  @override
  void initState() {
    super.initState();
    _nombresCtrl = TextEditingController(text: widget.user['nombres']);
    _apellidosCtrl = TextEditingController(text: widget.user['apellidos']);
    _telefonoCtrl = TextEditingController(text: widget.user['telefono']);
    _usernameCtrl = TextEditingController(text: widget.user['nombre_usuario']);
  }

  @override
  void dispose() {
    _nombresCtrl.dispose();
    _apellidosCtrl.dispose();
    _telefonoCtrl.dispose();
    _usernameCtrl.dispose();
    super.dispose();
  }

  Future<void> _cargarDeOrigen(ImageSource source) async {
    try {
      final XFile? picked = await _picker.pickImage(
        source: source,
        maxWidth: 500,
        maxHeight: 500,
        imageQuality: 85,
      );
      if (picked != null) {
        setState(() {
          _localPhoto = picked;
          _urlPhoto = null; // Priorizar foto local
          _error = '';
        });
      }
    } catch (e) {
      setState(() => _error = 'Error al seleccionar foto: $e');
    }
  }

  void _mostrarDialogoUrl() {
    final ctrl = TextEditingController(text: _urlPhoto ?? (widget.user['profile_photo_url'] ?? ''));
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Text('Foto de perfil (URL)', style: TextStyle(fontWeight: FontWeight.bold)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Pega el enlace de la imagen que deseas utilizar:',
                style: TextStyle(fontSize: 13, color: kTextGray),
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: ctrl,
                decoration: InputDecoration(
                  labelText: 'Enlace de la imagen',
                  hintText: 'https://ejemplo.com/imagen.jpg',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: const BorderSide(color: kPrimary, width: 2),
                  ),
                  labelStyle: const TextStyle(color: kPrimary),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancelar', style: TextStyle(color: kTextGray)),
            ),
            ElevatedButton(
              onPressed: () {
                final url = ctrl.text.trim();
                setState(() {
                  _urlPhoto = url.isNotEmpty ? url : null;
                  if (_urlPhoto != null) {
                    _localPhoto = null; // Priorizar URL si fue ingresada
                  }
                  _error = '';
                });
                Navigator.pop(context);
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: kPrimary,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              child: const Text('Aceptar', style: TextStyle(color: Colors.white)),
            ),
          ],
        );
      },
    );
  }

  void _mostrarOpcionesFoto() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Text(
                  'Cambiar foto de perfil',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: kTextDark,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Elige de dónde deseas cargar tu foto de perfil',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 13,
                    color: kTextGray,
                  ),
                ),
                const SizedBox(height: 20),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: kPrimary.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.photo_library, color: kPrimary),
                  ),
                  title: const Text(
                    'Seleccionar de la galería',
                    style: TextStyle(fontWeight: FontWeight.w600, color: kTextDark),
                  ),
                  subtitle: const Text('Busca una imagen en tu dispositivo'),
                  onTap: () {
                    Navigator.pop(context);
                    _cargarDeOrigen(ImageSource.gallery);
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: kSecondary.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.camera_alt, color: kSecondary),
                  ),
                  title: const Text(
                    'Tomar foto con la cámara',
                    style: TextStyle(fontWeight: FontWeight.w600, color: kTextDark),
                  ),
                  subtitle: const Text('Usa la cámara para capturar una foto'),
                  onTap: () {
                    Navigator.pop(context);
                    _cargarDeOrigen(ImageSource.camera);
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.blue.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.link, color: Colors.blue),
                  ),
                  title: const Text(
                    'Usar un enlace URL',
                    style: TextStyle(fontWeight: FontWeight.w600, color: kTextDark),
                  ),
                  subtitle: const Text('Ingresa una dirección web de la imagen'),
                  onTap: () {
                    Navigator.pop(context);
                    _mostrarDialogoUrl();
                  },
                ),
                const SizedBox(height: 8),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _guardar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _saving = true;
      _error = '';
    });

    final Map<String, String> data = {
      'nombres': _nombresCtrl.text.trim(),
      'apellidos': _apellidosCtrl.text.trim(),
      'telefono': _telefonoCtrl.text.trim(),
      'nombre_usuario': _usernameCtrl.text.trim(),
    };
    if (_urlPhoto != null && _urlPhoto!.isNotEmpty) {
      data['profile_photo_url'] = _urlPhoto!;
    }

    try {
      http.MultipartFile? profilePhotoFile;
      if (_localPhoto != null) {
        if (kIsWeb) {
          final bytes = await _localPhoto!.readAsBytes();
          profilePhotoFile = http.MultipartFile.fromBytes(
            'profile_photo',
            bytes,
            filename: _localPhoto!.name,
          );
        } else {
          profilePhotoFile = await http.MultipartFile.fromPath(
            'profile_photo',
            _localPhoto!.path,
          );
        }
      }

      final res = await AuthService.updateProfile(data, profilePhoto: profilePhotoFile);
      setState(() => _saving = false);

      if (res['success'] == true) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Perfil actualizado correctamente.'),
          backgroundColor: Colors.green,
        ));
        Navigator.pop(context, true); // Retorna true para refrescar la pantalla de cuenta
      } else {
        setState(() => _error = res['message'] ?? 'Error al actualizar el perfil.');
      }
    } catch (e) {
      setState(() {
        _saving = false;
        _error = 'Error al conectar con el servidor: $e';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: const Text('Editar Perfil', style: TextStyle(fontWeight: FontWeight.bold)),
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Avatar interactivo
              Center(
                child: Stack(
                  children: [
                    GestureDetector(
                      onTap: _mostrarOpcionesFoto,
                      child: CircleAvatar(
                        radius: 50,
                        backgroundColor: kPrimary,
                        backgroundImage: _localPhoto != null
                            ? (kIsWeb
                                ? NetworkImage(_localPhoto!.path)
                                : FileImage(File(_localPhoto!.path)) as ImageProvider)
                            : (_urlPhoto != null && _urlPhoto!.isNotEmpty
                                ? NetworkImage(_urlPhoto!)
                                : (widget.user['profile_photo_url'] != null && widget.user['profile_photo_url'].isNotEmpty
                                    ? NetworkImage(ApiClient.fixImageUrl(widget.user['profile_photo_url']))
                                    : null)),
                        child: _localPhoto == null && _urlPhoto == null && (widget.user['profile_photo_url'] == null || widget.user['profile_photo_url'].isEmpty)
                            ? const Icon(Icons.person, size: 50, color: Colors.white)
                            : null,
                      ),
                    ),
                    Positioned(
                      bottom: 0,
                      right: 0,
                      child: GestureDetector(
                        onTap: _mostrarOpcionesFoto,
                        child: Container(
                          width: 32,
                          height: 32,
                          decoration: const BoxDecoration(
                            color: kPrimary,
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 2))
                            ]
                          ),
                          child: const Icon(Icons.camera_alt, color: Colors.white, size: 16),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              const Text('Nombres *', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
              const SizedBox(height: 6),
              TextFormField(
                controller: _nombresCtrl,
                decoration: InputDecoration(
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                  filled: true,
                  fillColor: Colors.white,
                ),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),

              const Text('Apellidos *', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
              const SizedBox(height: 6),
              TextFormField(
                controller: _apellidosCtrl,
                decoration: InputDecoration(
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                  filled: true,
                  fillColor: Colors.white,
                ),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),

              const Text('Nombre de usuario *', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
              const SizedBox(height: 6),
              TextFormField(
                controller: _usernameCtrl,
                decoration: InputDecoration(
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                  filled: true,
                  fillColor: Colors.white,
                ),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),

              const Text('Teléfono *', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
              const SizedBox(height: 6),
              TextFormField(
                controller: _telefonoCtrl,
                keyboardType: TextInputType.phone,
                decoration: InputDecoration(
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                  filled: true,
                  fillColor: Colors.white,
                ),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),

              if (_error.isNotEmpty) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(8)),
                  child: Row(
                    children: [
                      const Icon(Icons.error_outline, color: Colors.red),
                      const SizedBox(width: 8),
                      Expanded(child: Text(_error, style: const TextStyle(color: Colors.red, fontSize: 13))),
                    ],
                  ),
                ),
              ],

              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _saving ? null : _guardar,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: kPrimary,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: _saving
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Text('Guardar cambios', style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
