import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

class SolicitarRetiroDialog extends StatefulWidget {
  final double balance;
  final List<dynamic> cuentas;

  const SolicitarRetiroDialog({Key? key, required this.balance, required this.cuentas}) : super(key: key);

  @override
  State<SolicitarRetiroDialog> createState() => _SolicitarRetiroDialogState();
}

class _SolicitarRetiroDialogState extends State<SolicitarRetiroDialog> {
  final _formKey = GlobalKey<FormState>();
  int? _cuentaId;
  final _montoController = TextEditingController();
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    if (widget.cuentas.isNotEmpty) {
      _cuentaId = widget.cuentas.first['id'];
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_cuentaId == null) return;
    
    final monto = double.tryParse(_montoController.text.trim()) ?? 0;
    if (monto < 500) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('El monto mínimo es RD\$ 500'), backgroundColor: Colors.orange));
      return;
    }
    if (monto > widget.balance) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Balance insuficiente'), backgroundColor: Colors.red));
      return;
    }

    setState(() => _saving = true);
    
    try {
      final body = {
        'id_cuenta_bancaria': _cuentaId,
        'monto': monto,
      };
      
      final res = await ApiClient.post('/billetera/retiros', body, auth: true);
      if (res.statusCode == 201 || res.statusCode == 200) {
        if (mounted) {
          Navigator.pop(context, true);
        }
      } else {
        if (mounted) {
          final err = jsonDecode(res.body);
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err['message'] ?? 'Error al procesar'), backgroundColor: Colors.red));
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
    _montoController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Solicitar Retiro'),
      content: Form(
        key: _formKey,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Balance Disponible: RD\$ ${widget.balance.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            DropdownButtonFormField<int>(
              value: _cuentaId,
              decoration: const InputDecoration(labelText: 'Cuenta Destino', border: OutlineInputBorder()),
              items: widget.cuentas.map((c) => DropdownMenuItem<int>(
                value: c['id'], 
                child: Text('${c['banco']} - *${c['numero_cuenta'].toString().length > 4 ? c['numero_cuenta'].toString().substring(c['numero_cuenta'].toString().length - 4) : c['numero_cuenta']}')
              )).toList(),
              onChanged: (v) => setState(() => _cuentaId = v),
              validator: (v) => v == null ? 'Seleccione una cuenta' : null,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _montoController,
              decoration: const InputDecoration(labelText: 'Monto a retirar', border: OutlineInputBorder(), prefixText: 'RD\$ '),
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              validator: (v) {
                if (v == null || v.isEmpty) return 'Requerido';
                final d = double.tryParse(v);
                if (d == null) return 'Monto inválido';
                return null;
              },
            ),
          ],
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
          child: _saving ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) : const Text('Solicitar'),
        )
      ],
    );
  }
}
