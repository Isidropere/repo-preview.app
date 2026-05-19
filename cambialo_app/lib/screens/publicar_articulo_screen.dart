import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

/// Formulario para publicar un nuevo artículo desde la app móvil
class PublicarArticuloScreen extends StatefulWidget {
  const PublicarArticuloScreen({super.key});
  @override
  State<PublicarArticuloScreen> createState() => _PublicarArticuloScreenState();
}

class _PublicarArticuloScreenState extends State<PublicarArticuloScreen> {
  final _formKey       = GlobalKey<FormState>();
  final _nombreCtrl    = TextEditingController();
  final _precioCtrl    = TextEditingController();
  final _descCtrl      = TextEditingController();
  final _imgUrlCtrl    = TextEditingController();

  List _categorias     = [];
  int? _idCategoria;
  int  _condicion      = 1;   // 1=Nuevo, 2=Como nuevo, 3=Usado
  int  _tipoTrans      = 1;   // 1=Venta, 2=Intercambio, 3=Ambos
  bool _saving         = false;
  String _error        = '';

  @override
  void initState() { super.initState(); _loadCategorias(); }

  @override
  void dispose() {
    _nombreCtrl.dispose();
    _precioCtrl.dispose();
    _descCtrl.dispose();
    _imgUrlCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadCategorias() async {
    final res = await ApiClient.get('/categorias');
    if (res.statusCode == 200) setState(() => _categorias = jsonDecode(res.body));
  }

  Future<void> _publicar() async {
    if (!_formKey.currentState!.validate()) return;
    if (_idCategoria == null) { setState(() => _error = 'Selecciona una categoría.'); return; }

    setState(() { _saving = true; _error = ''; });

    final res = await ApiClient.post('/items', {
      'item':              _nombreCtrl.text.trim(),
      'presentacion':      _descCtrl.text.trim(),
      'valor':             double.tryParse(_precioCtrl.text.trim()) ?? 0,
      'condicion':         _condicion,
      'tipo_trans':        _tipoTrans,
      'id_categoria_item': _idCategoria,
      'image_url':         _imgUrlCtrl.text.trim().isEmpty ? null : _imgUrlCtrl.text.trim(),
    }, auth: true);

    setState(() => _saving = false);

    if (res.statusCode == 201) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Artículo publicado. Pendiente de aprobación.'),
        backgroundColor: Colors.green,
      ));
      Navigator.pop(context);
    } else {
      final body = jsonDecode(res.body);
      setState(() => _error = body['message'] ?? 'Error al publicar.');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Publicar artículo')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

            _titulo('Información básica'),
            const SizedBox(height: 8),
            _campo(_nombreCtrl, 'Nombre del artículo *', required: true),
            const SizedBox(height: 12),
            _campo(_precioCtrl, 'Precio (RD\$) *', required: true, keyboardType: TextInputType.number),
            const SizedBox(height: 12),
            _campo(_descCtrl, 'Descripción', maxLines: 3),
            const SizedBox(height: 16),

            _titulo('Categoría *'),
            const SizedBox(height: 8),
            DropdownButtonFormField<int>(
              value: _idCategoria,
              decoration: const InputDecoration(labelText: 'Seleccionar categoría', border: OutlineInputBorder()),
              items: _categorias.map<DropdownMenuItem<int>>((c) =>
                DropdownMenuItem(value: c['id_categoria_item'], child: Text(c['categoria']))).toList(),
              onChanged: (v) => setState(() => _idCategoria = v),
            ),
            const SizedBox(height: 16),

            _titulo('Condición del artículo *'),
            const SizedBox(height: 4),
            _segmented(
              labels: ['Nuevo', 'Como nuevo', 'Usado'],
              selected: _condicion - 1,
              onSelected: (i) => setState(() => _condicion = i + 1),
            ),
            const SizedBox(height: 16),

            _titulo('Modalidad *'),
            const SizedBox(height: 4),
            _segmented(
              labels: ['Venta', 'Intercambio', 'Ambos'],
              selected: _tipoTrans - 1,
              onSelected: (i) => setState(() => _tipoTrans = i + 1),
            ),
            const SizedBox(height: 16),

            _titulo('URL de imagen (ImgBB)'),
            const SizedBox(height: 8),
            _campo(_imgUrlCtrl, 'https://i.ibb.co/...'),
            const SizedBox(height: 4),
            Text('Sube tu imagen en imgbb.com y pega el enlace directo aquí.',
                style: TextStyle(fontSize: 11, color: kTextGray)),

            if (_error.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(_error, style: const TextStyle(color: Colors.red, fontSize: 13)),
            ],

            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _saving ? null : _publicar,
                style: ElevatedButton.styleFrom(backgroundColor: kPrimary, padding: const EdgeInsets.symmetric(vertical: 14)),
                child: _saving
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('Publicar artículo', style: TextStyle(color: Colors.white, fontSize: 15)),
              ),
            ),
          ]),
        ),
      ),
    );
  }

  Widget _titulo(String t) => Text(t,
      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark));

  Widget _campo(TextEditingController ctrl, String label,
      {bool required = false, TextInputType? keyboardType, int maxLines = 1}) =>
    TextFormField(
      controller: ctrl,
      keyboardType: keyboardType,
      maxLines: maxLines,
      decoration: InputDecoration(labelText: label, border: const OutlineInputBorder()),
      validator: required ? (v) => (v == null || v.isEmpty) ? 'Campo requerido' : null : null,
    );

  Widget _segmented({required List<String> labels, required int selected, required Function(int) onSelected}) =>
    Wrap(spacing: 8, children: List.generate(labels.length, (i) => ChoiceChip(
      label: Text(labels[i]),
      selected: selected == i,
      onSelected: (_) => onSelected(i),
      selectedColor: kPrimary,
      labelStyle: TextStyle(color: selected == i ? Colors.white : kTextDark, fontSize: 13),
    )));
}
