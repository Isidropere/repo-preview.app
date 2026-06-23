import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../core/auth_service.dart';

class AdultoVerificationDialog extends StatefulWidget {
  final String userEmail;

  const AdultoVerificationDialog({super.key, required this.userEmail});

  static Future<bool> showVerification(BuildContext context) async {
    final user = await AuthService.me();
    if (user == null) return false;

    if (AuthService.adultosAceptado) return true;

    if (!context.mounted) return false;

    final result = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (_) => AdultoVerificationDialog(userEmail: user['email'] ?? ''),
    );

    return result ?? false;
  }

  @override
  State<AdultoVerificationDialog> createState() => _AdultoVerificationDialogState();
}

class _AdultoVerificationDialogState extends State<AdultoVerificationDialog> {
  bool _accepted = false;
  final _passwordCtrl = TextEditingController();
  bool _loading = false;
  String? _error;

  @override
  void dispose() {
    _passwordCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_accepted || _passwordCtrl.text.isEmpty) return;

    setState(() {
      _loading = true;
      _error = null;
    });

    final res = await AuthService.verificarAdultos(widget.userEmail, _passwordCtrl.text);

    if (mounted) {
      setState(() => _loading = false);
      if (res['success'] == true) {
        Navigator.pop(context, true);
      } else {
        setState(() => _error = res['message'] ?? 'Contraseña incorrecta');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final bool canConfirm = _accepted && _passwordCtrl.text.isNotEmpty && !_loading;

    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Row(
                children: [
                  const Icon(Icons.eighteen_up_rating, color: Colors.red, size: 28),
                  const SizedBox(width: 8),
                  const Text(
                    'Contenido para adultos',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: kTextDark,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Advertencia
              const Text(
                'Has seleccionado una categoría exclusiva para adultos. Para continuar, confirma que tienes al menos 18 años y reintroduce tu contraseña por seguridad.',
                style: TextStyle(fontSize: 13, color: kTextGray, height: 1.4),
              ),
              const SizedBox(height: 16),

              // Checkbox de aceptación
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Checkbox(
                    activeColor: Colors.red,
                    value: _accepted,
                    onChanged: (v) {
                      setState(() => _accepted = v ?? false);
                    },
                  ),
                  const Expanded(
                    child: Padding(
                      padding: EdgeInsets.only(top: 8.0),
                      child: Text(
                        'He leído y acepto los Términos y Condiciones de la sección de adultos. Confirmo que soy mayor de 18 años.',
                        style: TextStyle(fontSize: 12, color: kTextDark, height: 1.3),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Email (Prefilled, read-only)
              const Text(
                'Correo electrónico',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: kTextDark),
              ),
              const SizedBox(height: 6),
              TextFormField(
                initialValue: widget.userEmail,
                readOnly: true,
                decoration: InputDecoration(
                  filled: true,
                  fillColor: Colors.grey.shade100,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                ),
                style: const TextStyle(fontSize: 13, color: kTextGray),
              ),
              const SizedBox(height: 12),

              // Password
              const Text(
                'Introduce tu contraseña',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: kTextDark),
              ),
              const SizedBox(height: 6),
              TextField(
                controller: _passwordCtrl,
                obscureText: true,
                onChanged: (_) => setState(() {}),
                onSubmitted: (_) => _submit(),
                decoration: InputDecoration(
                  hintText: 'Tu contraseña',
                  hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: const BorderSide(color: kPrimary),
                  ),
                ),
                style: const TextStyle(fontSize: 13),
              ),

              // Error de validación
              if (_error != null) ...[
                const SizedBox(height: 10),
                Text(
                  _error!,
                  style: const TextStyle(color: Colors.red, fontSize: 12, fontWeight: FontWeight.w500),
                ),
              ],

              const SizedBox(height: 20),

              // Acciones (Cancelar y Confirmar)
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  TextButton(
                    onPressed: _loading ? null : () => Navigator.pop(context),
                    child: const Text('Cancelar', style: TextStyle(color: kTextGray)),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: canConfirm ? _submit : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: canConfirm ? Colors.red : Colors.grey.shade300,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    ),
                    child: _loading
                        ? const SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                          )
                        : const Text('Confirmar acceso', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
