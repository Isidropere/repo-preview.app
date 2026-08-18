import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

class AgregarCuentaDialog extends StatefulWidget {
  const AgregarCuentaDialog({Key? key}) : super(key: key);

  @override
  State<AgregarCuentaDialog> createState() => _AgregarCuentaDialogState();
}

class _AgregarCuentaDialogState extends State<AgregarCuentaDialog> {
  final _formKey = GlobalKey<FormState>();
  String _banco = 'Banreservas';
  String _tipoCuenta = 'ahorro';
  final _numeroController = TextEditingController();
  final _nombreController = TextEditingController();
  final _cedulaController = TextEditingController();
  bool _saving = false;

  final List<String> _bancos = [
    'Banreservas',
    'Scotiabank República Dominicana',
    'Citibank, N.A.',
    'Banco Popular Dominicano',
    'Banco BHD',
    'Banco Santa Cruz',
    'Banco Caribe',
    'Banco BDI',
    'Banco Vimenca',
    'Banco López de Haro',
    'Banco Promerica',
    'Banesco Banco Múltiple',
    'Banco Ademi',
    'Banco Lafise',
    'JMMB Bank',
    'Qik Banco Digital',
    'Banco Múltiple Activo Dominicana',
    'Asociación Popular de Ahorros y Préstamos (APAP)',
    'Asociación Cibao de Ahorros y Préstamos',
    'Asociación Peravia',
    'Asociación La Vega Real (ALAVER)',
    'Asociación Mocana',
    'Asociación Duarte',
    'Asociación Bonao',
    'Asociación La Nacional'
  ];

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);
    
    try {
      final body = {
        'banco': _banco,
        'tipo_cuenta': _tipoCuenta,
        'numero_cuenta': _numeroController.text.trim(),
        'titular': _nombreController.text.trim(),
        'cedula_titular': _cedulaController.text.trim(),
      };
      
      final res = await ApiClient.post('/billetera/cuentas-bancarias', body, auth: true);
      if (res.statusCode == 201 || res.statusCode == 200) {
        if (mounted) {
          Navigator.pop(context, true);
        }
      } else {
        if (mounted) {
          final err = jsonDecode(res.body);
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err['message'] ?? 'Error al guardar'), backgroundColor: Colors.red));
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Error de conexión'), backgroundColor: Colors.red));
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  void dispose() {
    _numeroController.dispose();
    _nombreController.dispose();
    _cedulaController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Agregar Cuenta Bancaria'),
      content: SingleChildScrollView(
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              DropdownButtonFormField<String>(
                value: _banco,
                decoration: const InputDecoration(labelText: 'Banco', border: OutlineInputBorder()),
                items: _bancos.map((b) => DropdownMenuItem(value: b, child: Text(b))).toList(),
                onChanged: (v) => setState(() => _banco = v!),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                value: _tipoCuenta,
                decoration: const InputDecoration(labelText: 'Tipo de Cuenta', border: OutlineInputBorder()),
                items: const [
                  DropdownMenuItem(value: 'ahorro', child: Text('Ahorro')),
                  DropdownMenuItem(value: 'corriente', child: Text('Corriente')),
                ],
                onChanged: (v) => setState(() => _tipoCuenta = v!),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _numeroController,
                decoration: const InputDecoration(labelText: 'Número de Cuenta', border: OutlineInputBorder()),
                keyboardType: TextInputType.number,
                validator: (v) => v!.isEmpty ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _nombreController,
                decoration: const InputDecoration(labelText: 'Nombre del Titular', border: OutlineInputBorder()),
                validator: (v) => v!.isEmpty ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _cedulaController,
                decoration: const InputDecoration(labelText: 'Cédula o Pasaporte', border: OutlineInputBorder()),
                validator: (v) => v!.isEmpty ? 'Requerido' : null,
              ),
            ],
          ),
        ),
      ),
      actions: [
        TextButton(
          onPressed: _saving ? null : () => Navigator.pop(context, false),
          child: const Text('Cancelar', style: TextStyle(color: Colors.grey)),
        ),
        ElevatedButton(
          onPressed: _saving ? null : _submit,
          style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
          child: _saving ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) : const Text('Guardar'),
        )
      ],
    );
  }
}
