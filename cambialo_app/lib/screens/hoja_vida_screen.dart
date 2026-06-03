import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

class HojaVidaScreen extends StatefulWidget {
  const HojaVidaScreen({super.key});

  @override
  State<HojaVidaScreen> createState() => _HojaVidaScreenState();
}

class _HojaVidaScreenState extends State<HojaVidaScreen> {
  final _formKey = GlobalKey<FormState>();
  final _tituloCtrl = TextEditingController();
  final _descCtrl = TextEditingController();
  final _expCtrl = TextEditingController();
  final _habCtrl = TextEditingController();
  final _anosCtrl = TextEditingController();

  bool _loading = true;
  bool _saving = false;
  String? _error;
  String? _success;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _tituloCtrl.dispose();
    _descCtrl.dispose();
    _expCtrl.dispose();
    _habCtrl.dispose();
    _anosCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiClient.get('/hoja-vida', auth: true, useCache: false);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['tiene_hoja_vida'] == true && body['hoja_vida'] != null) {
          final h = body['hoja_vida'];
          _tituloCtrl.text = h['titulo_profesional'] ?? '';
          _descCtrl.text = h['descripcion'] ?? '';
          _expCtrl.text = h['experiencia'] ?? '';
          _habCtrl.text = h['habilidades'] ?? '';
          _anosCtrl.text = (h['años_experiencia'] ?? '').toString();
        }
      }
    } catch (e) {
      setState(() => _error = 'Error de conexión con el servidor.');
    } finally {
      setState(() => _loading = false);
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _saving = true;
      _error = null;
      _success = null;
    });

    final data = {
      'titulo_profesional': _tituloCtrl.text.trim(),
      'descripcion': _descCtrl.text.trim(),
      'experiencia': _expCtrl.text.trim(),
      'habilidades': _habCtrl.text.trim(),
      'años_experiencia': int.tryParse(_anosCtrl.text.trim()) ?? 0,
    };

    try {
      final res = await ApiClient.post('/hoja-vida', data, auth: true);
      final body = jsonDecode(res.body);
      if (res.statusCode == 200) {
        setState(() => _success = '¡Hoja de Vida guardada con éxito!');
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Perfil profesional actualizado correctamente.'), backgroundColor: Colors.green)
        );
      } else {
        setState(() => _error = body['message'] ?? 'Error al guardar.');
      }
    } catch (e) {
      setState(() => _error = 'No se pudo conectar al servidor.');
    } finally {
      setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: const Text('Hoja de Vida'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.description_outlined, size: 40, color: kPrimary),
                        SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Perfil Profesional',
                                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: kTextDark),
                              ),
                              Text(
                                'Esta información es requerida para poder publicar tus talentos y servicios.',
                                style: TextStyle(fontSize: 11, color: kTextGray),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.grey.shade200),
                        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 8)],
                      ),
                      child: Column(
                        children: [
                          TextFormField(
                            controller: _tituloCtrl,
                            decoration: const InputDecoration(
                              labelText: 'Título Profesional *',
                              hintText: 'Ej: Diseñador Gráfico, Desarrollador Flutter',
                              border: OutlineInputBorder(),
                            ),
                            validator: (v) => (v == null || v.isEmpty) ? 'Este campo es obligatorio' : null,
                          ),
                          const SizedBox(height: 16),
                          TextFormField(
                            controller: _anosCtrl,
                            keyboardType: TextInputType.number,
                            decoration: const InputDecoration(
                              labelText: 'Años de Experiencia',
                              hintText: 'Ej: 5',
                              border: OutlineInputBorder(),
                            ),
                          ),
                          const SizedBox(height: 16),
                          TextFormField(
                            controller: _descCtrl,
                            maxLines: 3,
                            decoration: const InputDecoration(
                              labelText: 'Descripción Breve',
                              hintText: 'Cuéntanos un poco sobre ti...',
                              border: OutlineInputBorder(),
                            ),
                          ),
                          const SizedBox(height: 16),
                          TextFormField(
                            controller: _expCtrl,
                            maxLines: 3,
                            decoration: const InputDecoration(
                              labelText: 'Experiencia Laboral',
                              hintText: 'Detalla tu experiencia previa...',
                              border: OutlineInputBorder(),
                            ),
                          ),
                          const SizedBox(height: 16),
                          TextFormField(
                            controller: _habCtrl,
                            maxLines: 2,
                            decoration: const InputDecoration(
                              labelText: 'Habilidades',
                              hintText: 'Ej: Photoshop, Dart, Liderazgo (separados por comas)',
                              border: OutlineInputBorder(),
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (_error != null) ...[
                      const SizedBox(height: 16),
                      Text(
                        _error!,
                        style: const TextStyle(color: Colors.red, fontSize: 13),
                      ),
                    ],
                    if (_success != null) ...[
                      const SizedBox(height: 16),
                      Text(
                        _success!,
                        style: const TextStyle(color: Colors.green, fontSize: 13, fontWeight: FontWeight.bold),
                      ),
                    ],
                    const SizedBox(height: 28),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: _saving ? null : _save,
                        icon: _saving
                            ? const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                            : const Icon(Icons.save, color: Colors.white),
                        label: Text(_saving ? 'Guardando...' : 'Guardar Perfil',
                            style: const TextStyle(color: Colors.white, fontSize: 15)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: kPrimary,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
