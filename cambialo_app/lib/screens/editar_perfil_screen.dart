import 'package:flutter/material.dart';
import '../core/auth_service.dart';
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
  late final TextEditingController _photoUrlCtrl;
  bool _saving = false;
  String _error = '';

  @override
  void initState() {
    super.initState();
    _nombresCtrl = TextEditingController(text: widget.user['nombres']);
    _apellidosCtrl = TextEditingController(text: widget.user['apellidos']);
    _telefonoCtrl = TextEditingController(text: widget.user['telefono']);
    _usernameCtrl = TextEditingController(text: widget.user['nombre_usuario']);
    _photoUrlCtrl = TextEditingController(text: widget.user['profile_photo_url']);
  }

  @override
  void dispose() {
    _nombresCtrl.dispose();
    _apellidosCtrl.dispose();
    _telefonoCtrl.dispose();
    _usernameCtrl.dispose();
    _photoUrlCtrl.dispose();
    super.dispose();
  }

  Future<void> _guardar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _saving = true;
      _error = '';
    });

    final data = {
      'nombres': _nombresCtrl.text.trim(),
      'apellidos': _apellidosCtrl.text.trim(),
      'telefono': _telefonoCtrl.text.trim(),
      'nombre_usuario': _usernameCtrl.text.trim(),
    };

    // Si cambió la foto de perfil, la enviamos también
    if (_photoUrlCtrl.text.trim() != widget.user['profile_photo_url']) {
      // Nota: Si es URL externa, la procesamos en Laravel
      data['profile_photo_url'] = _photoUrlCtrl.text.trim();
    }

    final res = await AuthService.updateProfile(data);
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
  }

  void _editarFotoUrl() {
    showDialog(
      context: context,
      builder: (context) {
        final ctrl = TextEditingController(text: _photoUrlCtrl.text);
        return AlertDialog(
          title: const Text('Foto de perfil (URL)'),
          content: TextFormField(
            controller: ctrl,
            decoration: const InputDecoration(
              labelText: 'Enlace de la imagen',
              hintText: 'https://i.ibb.co/...',
              border: OutlineInputBorder(),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancelar', style: TextStyle(color: kTextGray)),
            ),
            ElevatedButton(
              onPressed: () {
                setState(() => _photoUrlCtrl.text = ctrl.text.trim());
                Navigator.pop(context);
              },
              style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
              child: const Text('Aceptar', style: TextStyle(color: Colors.white)),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Editar Perfil')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Avatar con click para cambiar URL
              Center(
                child: Stack(
                  children: [
                    CircleAvatar(
                      radius: 50,
                      backgroundColor: kPrimary,
                      backgroundImage: _photoUrlCtrl.text.isNotEmpty
                          ? NetworkImage(_photoUrlCtrl.text)
                          : null,
                      child: _photoUrlCtrl.text.isEmpty
                          ? const Icon(Icons.person, size: 50, color: Colors.white)
                          : null,
                    ),
                    Positioned(
                      bottom: 0,
                      right: 0,
                      child: GestureDetector(
                        onTap: _editarFotoUrl,
                        child: Container(
                          width: 32,
                          height: 32,
                          decoration: const BoxDecoration(color: kPrimary, shape: BoxShape.circle),
                          child: const Icon(Icons.edit, color: Colors.white, size: 16),
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
                decoration: const InputDecoration(border: OutlineInputBorder()),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),

              const Text('Apellidos *', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
              const SizedBox(height: 6),
              TextFormField(
                controller: _apellidosCtrl,
                decoration: const InputDecoration(border: OutlineInputBorder()),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),

              const Text('Nombre de usuario *', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
              const SizedBox(height: 6),
              TextFormField(
                controller: _usernameCtrl,
                decoration: const InputDecoration(border: OutlineInputBorder()),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),

              const Text('Teléfono *', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
              const SizedBox(height: 6),
              TextFormField(
                controller: _telefonoCtrl,
                keyboardType: TextInputType.phone,
                decoration: const InputDecoration(border: OutlineInputBorder()),
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
                  ),
                  child: _saving
                      ? const CircularProgressIndicator(color: Colors.white)
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
