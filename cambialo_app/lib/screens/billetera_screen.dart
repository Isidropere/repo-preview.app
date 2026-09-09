import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import '../widgets/agregar_cuenta_dialog.dart';
import '../widgets/solicitar_retiro_dialog.dart';

class BilleteraScreen extends StatefulWidget {
  const BilleteraScreen({Key? key}) : super(key: key);

  @override
  State<BilleteraScreen> createState() => _BilleteraScreenState();
}

class _BilleteraScreenState extends State<BilleteraScreen> {
  bool _loading = true;
  double _balance = 0.0;
  List<dynamic> _cuentas = [];
  List<dynamic> _historial = [];
  bool _isDeleting = false;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      var res = await ApiClient.get('/billetera/resumen', auth: true, useCache: false);
      if (res.statusCode == 404) {
        res = await ApiClient.get('/billetera', auth: true, useCache: false);
      }
      if (res.statusCode == 404) {
        res = await ApiClient.get('/mi-billetera/resumen', auth: true, useCache: false);
      }

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        final Map<String, dynamic> dataMap = (body is Map && body['data'] is Map)
            ? Map<String, dynamic>.from(body['data'])
            : (body is Map ? Map<String, dynamic>.from(body) : {});

        final rawBalance = dataMap['balance_disponible'] ?? (body is Map ? body['balance_disponible'] : null) ?? 0;
        final double balanceVal = rawBalance is num
            ? rawBalance.toDouble()
            : (double.tryParse(rawBalance.toString()) ?? 0.0);

        final rawCuentas = dataMap['cuentas'] ?? (body is Map ? body['cuentas'] : null) ?? [];
        final rawHistorial = dataMap['retiros'] ?? (body is Map ? body['retiros'] : null) ?? [];

        if (mounted) {
          setState(() {
            _balance = balanceVal;
            _cuentas = rawCuentas is List ? rawCuentas : [];
            _historial = rawHistorial is List ? rawHistorial : [];
            _loading = false;
          });
        }
      } else {
        if (mounted) {
          setState(() => _loading = false);
          String errorMsg = 'Error al cargar la billetera (${res.statusCode})';
          try {
            final errBody = jsonDecode(res.body);
            if (errBody is Map && errBody['message'] != null) {
              errorMsg = errBody['message'];
            }
          } catch (_) {}
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(errorMsg), backgroundColor: Colors.red));
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Error de conexión al cargar billetera'), backgroundColor: Colors.red));
      }
    }
  }

  Future<void> _eliminarCuenta(int id) async {
    setState(() => _isDeleting = true);
    try {
      final res = await ApiClient.delete('/billetera/cuentas-bancarias/$id', auth: true);
      if (res.statusCode == 200) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Cuenta eliminada')));
          _loadData();
        }
      } else {
        if (mounted) {
          final err = jsonDecode(res.body);
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(err['message'] ?? 'Error al eliminar cuenta'), backgroundColor: Colors.red),
          );
        }
      }
    } catch (e) {
       if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Error de conexión'), backgroundColor: Colors.red));
    } finally {
      if (mounted) setState(() => _isDeleting = false);
    }
  }

  void _showAgregarCuenta() async {
    final result = await showDialog(
      context: context,
      builder: (_) => const AgregarCuentaDialog(),
    );
    if (result == true) {
      _loadData();
    }
  }

  void _showSolicitarRetiro() async {
    if (_cuentas.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Debes agregar una cuenta bancaria primero'), backgroundColor: Colors.orange));
      return;
    }
    final result = await showDialog(
      context: context,
      builder: (_) => SolicitarRetiroDialog(balance: _balance, cuentas: _cuentas),
    );
    if (result == true) {
      _loadData();
    }
  }

  Widget _buildBalanceCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [Color(0xFF2563EB), Color(0xFF1D4ED8)]),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.blue.withOpacity(0.3), blurRadius: 10, offset: const Offset(0, 4))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Balance Disponible', style: TextStyle(color: Colors.white70, fontSize: 16)),
          const SizedBox(height: 8),
          Text('RD\$ ${_balance.toStringAsFixed(2)}', style: const TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.bold)),
          const SizedBox(height: 20),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _showSolicitarRetiro,
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.white,
                foregroundColor: const Color(0xFF1D4ED8),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                padding: const EdgeInsets.symmetric(vertical: 12),
              ),
              child: const Text('Solicitar Retiro', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          )
        ],
      ),
    );
  }

  Widget _buildCuentasBancarias() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Expanded(
              child: Text('Mis Cuentas Bancarias', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: kTextGray), overflow: TextOverflow.ellipsis),
            ),
            TextButton.icon(
              onPressed: _showAgregarCuenta,
              icon: const Icon(Icons.add, size: 18, color: kPrimary),
              label: const Text('Agregar', style: TextStyle(color: kPrimary)),
            )
          ],
        ),
        if (_cuentas.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 16),
            child: Text('No tienes cuentas bancarias registradas.', style: TextStyle(color: Colors.grey)),
          )
        else
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _cuentas.length,
            itemBuilder: (context, index) {
              final c = _cuentas[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 8),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                child: ListTile(
                  leading: const CircleAvatar(backgroundColor: Color(0xFFEFF6FF), child: Icon(Icons.account_balance, color: Color(0xFF3B82F6))),
                  title: Text('${c['banco']}', style: const TextStyle(fontWeight: FontWeight.bold)),
                  subtitle: Text('${c['tipo_cuenta']} - *${c['numero_cuenta'].toString().length > 4 ? c['numero_cuenta'].toString().substring(c['numero_cuenta'].toString().length - 4) : c['numero_cuenta']}'),
                  trailing: IconButton(
                    icon: const Icon(Icons.delete_outline, color: Colors.red),
                    onPressed: _isDeleting ? null : () => _eliminarCuenta(c['id']),
                  ),
                ),
              );
            },
          )
      ],
    );
  }

  Widget _buildHistorial() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Historial de Retiros', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: kTextGray)),
        const SizedBox(height: 12),
        if (_historial.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 16),
            child: Text('No has realizado ningún retiro aún.', style: TextStyle(color: Colors.grey)),
          )
        else
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _historial.length,
            itemBuilder: (context, index) {
              final h = _historial[index];
              Color estadoColor = Colors.orange;
              String estadoText = h['estado'] ?? 'pendiente';
              if (estadoText == 'pagado') estadoColor = Colors.green;
              if (estadoText == 'rechazado') estadoColor = Colors.red;

              return Card(
                margin: const EdgeInsets.only(bottom: 8),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                child: ListTile(
                  leading: const CircleAvatar(backgroundColor: Color(0xFFF3F4F6), child: Icon(Icons.money, color: Colors.grey)),
                  title: Text('Retiro RD\$ ${h['monto']}', style: const TextStyle(fontWeight: FontWeight.bold)),
                  subtitle: Text('Fecha: ${h['created_at'].toString().split('T')[0]}'),
                  trailing: Chip(
                    label: Text(estadoText.toUpperCase(), style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                    backgroundColor: estadoColor,
                    padding: EdgeInsets.zero,
                  ),
                ),
              );
            },
          )
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: const Text('Mi Billetera'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : RefreshIndicator(
              onRefresh: _loadData,
              color: kPrimary,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildBalanceCard(),
                    const SizedBox(height: 24),
                    _buildCuentasBancarias(),
                    const SizedBox(height: 24),
                    _buildHistorial(),
                  ],
                ),
              ),
            ),
    );
  }
}
