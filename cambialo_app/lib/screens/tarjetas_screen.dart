import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

/// Gestión de tarjetas de pago guardadas
class TarjetasScreen extends StatefulWidget {
  const TarjetasScreen({super.key});
  @override
  State<TarjetasScreen> createState() => _TarjetasScreenState();
}

class _TarjetasScreenState extends State<TarjetasScreen> {
  List _tarjetas = [];
  bool _loading  = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await ApiClient.get('/tarjetas', auth: true, useCache: false);
    if (res.statusCode == 200) {
      setState(() {
        _tarjetas = jsonDecode(res.body);
        _loading = false;
      });
    } else {
      setState(() => _loading = false);
    }
  }

  Future<void> _eliminar(String id) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('¿Eliminar tarjeta?'),
        content: const Text('Esta acción quitará esta tarjeta de tus métodos de pago guardados.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancelar')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Eliminar'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      setState(() => _loading = true);
      final res = await ApiClient.delete('/tarjetas/$id', auth: true);
      if (res.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Tarjeta eliminada correctamente.'),
          backgroundColor: Colors.green,
        ));
      }
      _load();
    }
  }

  Future<void> _seleccionarPredeterminada(String id) async {
    setState(() => _loading = true);
    final res = await ApiClient.post('/tarjetas/usar', {'id_tarjeta': id}, auth: true);
    if (res.statusCode == 200) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Tarjeta predeterminada actualizada.'),
        backgroundColor: Colors.green,
      ));
    }
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(title: const Text('Mis Tarjetas')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          await Navigator.push(context, MaterialPageRoute(builder: (_) => const _FormTarjetaScreen()));
          _load();
        },
        backgroundColor: kPrimary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Nueva', style: TextStyle(color: Colors.white)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : _tarjetas.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.credit_card_off, size: 56, color: Colors.grey.shade300),
                      const SizedBox(height: 12),
                      Text('No tienes tarjetas de pago guardadas', style: TextStyle(color: kTextGray)),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  color: kPrimary,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _tarjetas.length,
                    itemBuilder: (_, i) {
                      final t = _tarjetas[i];
                      final bool esActiva = t['usar_esta_tarjeta'] == 1 || t['usar_esta_tarjeta'] == true;
                      final String marca = (t['tipo_tarjeta'] ?? t['marca'] ?? 'Visa').toString().toLowerCase();

                      // Elegir color de tarjeta según marca o predeterminada
                      List<Color> cardColors = [const Color(0xFF1E3C72), const Color(0xFF2A5298)]; // Azul marino
                      if (esActiva) {
                        cardColors = [kPrimary, const Color(0xFFF07127)]; // Naranja Cambialo
                      } else if (marca.contains('master')) {
                        cardColors = [const Color(0xFF373B44), const Color(0xFF4286F4)]; // Gris-Azul
                      } else if (marca.contains('amex') || marca.contains('american')) {
                        cardColors = [const Color(0xFF0F2027), const Color(0xFF203A43)]; // Oscuro
                      }

                      return GestureDetector(
                        onTap: esActiva ? null : () => _seleccionarPredeterminada(t['id_tarjeta']),
                        child: Container(
                          margin: const EdgeInsets.only(bottom: 16),
                          decoration: BoxDecoration(
                            gradient: LinearGradient(colors: cardColors, begin: Alignment.topLeft, end: Alignment.bottomRight),
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(color: cardColors[0].withOpacity(0.3), blurRadius: 10, offset: const Offset(0, 4))
                            ],
                          ),
                          child: Stack(
                            children: [
                              Padding(
                                padding: const EdgeInsets.all(20),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          (t['tipo_tarjeta'] ?? t['marca'] ?? 'TARJETA').toString().toUpperCase(),
                                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16, letterSpacing: 1),
                                        ),
                                        if (esActiva)
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                            decoration: BoxDecoration(color: Colors.white.withOpacity(0.2), borderRadius: BorderRadius.circular(20)),
                                            child: const Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                Icon(Icons.check_circle, color: Colors.white, size: 12),
                                                SizedBox(width: 4),
                                                Text('ACTIVA', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                                              ],
                                            ),
                                          ),
                                      ],
                                    ),
                                    const SizedBox(height: 28),
                                    Text(
                                      '**** **** **** ${t['last4'] ?? t['ultimos_cuatro'] ?? ''}',
                                      style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold, letterSpacing: 2),
                                    ),
                                    const SizedBox(height: 24),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text('TITULAR', style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 9, fontWeight: FontWeight.bold)),
                                              const SizedBox(height: 2),
                                              Text(
                                                (t['nombre_titular'] ?? 'TITULAR').toString().toUpperCase(),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                                              ),
                                            ],
                                          ),
                                        ),
                                        Column(
                                          crossAxisAlignment: CrossAxisAlignment.end,
                                          children: [
                                            Text('VENCE', style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 9, fontWeight: FontWeight.bold)),
                                            const SizedBox(height: 2),
                                            Builder(
                                              builder: (_) {
                                                final rawYear = t['año_expiracion'] ?? t['anio_expiracion'] ?? '';
                                                final yearStr = rawYear.toString();
                                                final yearVal = yearStr.length >= 2 ? yearStr.substring(yearStr.length - 2) : yearStr;
                                                final mesVal = t['mes_expiracion'].toString().padLeft(2, '0');
                                                return Text(
                                                  '$mesVal/$yearVal',
                                                  style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                                                );
                                              },
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                              Positioned(
                                top: 8,
                                right: 8,
                                child: Visibility(
                                  visible: !esActiva, // Solo permitir borrar si no es la activa, o bien borrar directamente
                                  child: IconButton(
                                    icon: Icon(Icons.delete_outline, color: Colors.white.withOpacity(0.7), size: 20),
                                    onPressed: () => _eliminar(t['id_tarjeta']),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}

// Formulario de registro de nueva tarjeta
class _FormTarjetaScreen extends StatefulWidget {
  const _FormTarjetaScreen();
  @override
  State<_FormTarjetaScreen> createState() => _FormTarjetaScreenState();
}

class _FormTarjetaScreenState extends State<_FormTarjetaScreen> {
  final _formKey        = GlobalKey<FormState>();
  final _noTarjetaCtrl  = TextEditingController();
  final _nombreCtrl     = TextEditingController();
  final _mesExpCtrl     = TextEditingController();
  final _anioExpCtrl    = TextEditingController();
  final _bancoCtrl      = TextEditingController();
  final _tipoCtrl       = TextEditingController();

  bool _saving  = false;
  String _error = '';

  @override
  void dispose() {
    _noTarjetaCtrl.dispose();
    _nombreCtrl.dispose();
    _mesExpCtrl.dispose();
    _anioExpCtrl.dispose();
    _bancoCtrl.dispose();
    _tipoCtrl.dispose();
    super.dispose();
  }

  Future<void> _guardar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() { _saving = true; _error = ''; });

    final res = await ApiClient.post('/tarjetas', {
      'no_tarjeta':       _noTarjetaCtrl.text.trim(),
      'nombre_titular':   _nombreCtrl.text.trim(),
      'mes_expiracion':   _mesExpCtrl.text.trim(),
      'anio_expiracion':  _anioExpCtrl.text.trim(),
      'banco_tarjeta':    _bancoCtrl.text.trim().isEmpty ? 'Banco' : _bancoCtrl.text.trim(),
      'tipo_tarjeta':     _tipoCtrl.text.trim().isEmpty ? 'Visa' : _tipoCtrl.text.trim(),
      'usar_esta_tarjeta': true,
    }, auth: true);

    setState(() => _saving = false);

    if (res.statusCode == 201 || res.statusCode == 200) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Tarjeta guardada correctamente.'),
        backgroundColor: Colors.green,
      ));
      Navigator.pop(context);
    } else {
      final body = jsonDecode(res.body);
      setState(() => _error = body['message'] ?? 'Error al guardar la tarjeta.');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nueva Tarjeta')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            TextFormField(
              controller: _noTarjetaCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Número de tarjeta *', border: OutlineInputBorder(), prefixIcon: Icon(Icons.credit_card)),
              validator: (v) {
                if (v == null || v.isEmpty) return 'Campo requerido';
                if (v.replaceAll(RegExp(r'\D'), '').length < 13) return 'Número inválido';
                return null;
              },
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _nombreCtrl,
              decoration: const InputDecoration(labelText: 'Nombre del titular (como aparece en tarjeta) *', border: OutlineInputBorder(), prefixIcon: Icon(Icons.person)),
              validator: (v) => (v == null || v.isEmpty) ? 'Campo requerido' : null,
            ),
            const SizedBox(height: 12),
            Row(children: [
              Expanded(
                child: TextFormField(
                  controller: _mesExpCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'Mes Venc. (MM) *', border: OutlineInputBorder(), prefixIcon: Icon(Icons.calendar_today)),
                  validator: (v) {
                    if (v == null || v.isEmpty) return 'Requerido';
                    final m = int.tryParse(v);
                    if (m == null || m < 1 || m > 12) return 'Mes inválido';
                    return null;
                  },
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: TextFormField(
                  controller: _anioExpCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'Año Venc. (AAAA) *', border: OutlineInputBorder(), prefixIcon: Icon(Icons.date_range)),
                  validator: (v) {
                    if (v == null || v.isEmpty) return 'Requerido';
                    final y = int.tryParse(v);
                    final currentYear = DateTime.now().year;
                    if (y == null || y < currentYear) return 'Año inválido';
                    return null;
                  },
                ),
              ),
            ]),
            const SizedBox(height: 12),
            Row(children: [
              Expanded(
                child: TextFormField(
                  controller: _bancoCtrl,
                  decoration: const InputDecoration(labelText: 'Banco (Ej. Popular)', border: OutlineInputBorder()),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: TextFormField(
                  controller: _tipoCtrl,
                  decoration: const InputDecoration(labelText: 'Tipo (Ej. Visa)', border: OutlineInputBorder()),
                ),
              ),
            ]),
            if (_error.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(_error, style: const TextStyle(color: Colors.red, fontSize: 13)),
            ],
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _saving ? null : _guardar,
                style: ElevatedButton.styleFrom(backgroundColor: kPrimary, padding: const EdgeInsets.symmetric(vertical: 14)),
                child: _saving
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('Guardar tarjeta', style: TextStyle(color: Colors.white, fontSize: 15)),
              ),
            ),
          ]),
        ),
      ),
    );
  }
}
