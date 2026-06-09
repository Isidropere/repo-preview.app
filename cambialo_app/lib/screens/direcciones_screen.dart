import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:latlong2/latlong.dart';
import 'map_picker_screen.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

/// Gestión de direcciones de entrega
class DireccionesScreen extends StatefulWidget {
  const DireccionesScreen({super.key});
  @override
  State<DireccionesScreen> createState() => _DireccionesScreenState();
}

class _DireccionesScreenState extends State<DireccionesScreen> {
  List _direcciones = [];
  bool _loading     = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await ApiClient.get('/direcciones', auth: true, useCache: false);
    if (res.statusCode == 200) {
      setState(() { _direcciones = jsonDecode(res.body); _loading = false; });
    } else {
      setState(() => _loading = false);
    }
  }

  Future<void> _eliminar(int id) async {
    await ApiClient.delete('/direcciones/$id', auth: true);
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mis Direcciones')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          await Navigator.push(context, MaterialPageRoute(builder: (_) => const _FormDireccionScreen()));
          _load();
        },
        backgroundColor: kPrimary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Nueva', style: TextStyle(color: Colors.white)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : _direcciones.isEmpty
              ? Center(
                  child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                    Icon(Icons.location_off, size: 56, color: Colors.grey.shade300),
                    const SizedBox(height: 12),
                    Text('No tienes direcciones guardadas', style: TextStyle(color: kTextGray)),
                  ]),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  color: kPrimary,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(12),
                    itemCount: _direcciones.length,
                    itemBuilder: (_, i) {
                      final d = _direcciones[i];
                      final bool isPred = ApiClient.parseInt(d['es_predeterminada']) == 1;
                      return Card(
                        elevation: 1,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                          side: BorderSide(
                            color: isPred ? kPrimary : Colors.grey.shade200,
                            width: isPred ? 2 : 1,
                          ),
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(12),
                          child: Row(children: [
                            Icon(Icons.location_on, color: isPred ? kPrimary : Colors.grey),
                            const SizedBox(width: 10),
                            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              if (isPred)
                                Container(
                                  margin: const EdgeInsets.only(bottom: 4),
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(color: kPrimary.withOpacity(0.1), borderRadius: BorderRadius.circular(4)),
                                  child: Text('Predeterminada', style: TextStyle(fontSize: 10, color: kPrimary, fontWeight: FontWeight.w600)),
                                ),
                              Text('${d['calle']}${d['N_casa_edificio'] != null ? ', #${d['N_casa_edificio']}' : ''}',
                                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                              if (d['sector'] != null)
                                Text(d['sector'], style: TextStyle(fontSize: 12, color: kTextGray)),
                              Text(
                                '${d['municipio']?['municipio'] ?? ''}, ${d['provincia']?['provincia'] ?? ''}',
                                style: TextStyle(fontSize: 12, color: kTextGray),
                              ),
                            ])),
                            IconButton(
                              icon: const Icon(Icons.delete_outline, color: Colors.red, size: 20),
                              onPressed: () => _eliminar(ApiClient.parseInt(d['id_direccion']) ?? 0),
                            ),
                          ]),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}

// ── Formulario de nueva dirección ────────────────────────────────────────
class _FormDireccionScreen extends StatefulWidget {
  const _FormDireccionScreen();
  @override
  State<_FormDireccionScreen> createState() => _FormDireccionScreenState();
}

class _FormDireccionScreenState extends State<_FormDireccionScreen> {
  final _formKey    = GlobalKey<FormState>();
  final _calleCtrl  = TextEditingController();
  final _casaCtrl   = TextEditingController();
  final _sectorCtrl = TextEditingController();
  final _telCtrl    = TextEditingController();

  List _provincias  = [];
  List _municipios  = [];
  String? _idProvincia;
  String? _idMunicipio;
  String? _geolocalizacion;
  bool _esPred      = false;
  bool _saving      = false;
  String _error     = '';

  @override
  void initState() {
    super.initState();
    _loadProvincias();
  }

  @override
  void dispose() {
    _calleCtrl.dispose();
    _casaCtrl.dispose();
    _sectorCtrl.dispose();
    _telCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadProvincias() async {
    final res = await ApiClient.get('/ubicacion/provincias');
    if (res.statusCode == 200) {
      setState(() => _provincias = jsonDecode(res.body));
    }
  }

  Future<void> _loadMunicipios(String idProvincia) async {
    final res = await ApiClient.get('/ubicacion/municipios/$idProvincia');
    if (res.statusCode == 200) {
      setState(() { _municipios = jsonDecode(res.body); _idMunicipio = null; });
    }
  }

  Future<void> _guardar() async {
    if (!_formKey.currentState!.validate()) return;
    if (_idProvincia == null || _idMunicipio == null) {
      setState(() => _error = 'Selecciona provincia y municipio.');
      return;
    }
    setState(() { _saving = true; _error = ''; });

    final res = await ApiClient.post('/direcciones', {
      'calle':             _calleCtrl.text.trim(),
      'N_casa_edificio':   _casaCtrl.text.trim(),
      'sector':            _sectorCtrl.text.trim(),
      'telefono_contacto': _telCtrl.text.trim(),
      'id_provincia':      _idProvincia,
      'id_municipio':      _idMunicipio,
      'geolocalizacion':   _geolocalizacion,
      'es_predeterminada': _esPred,
    }, auth: true);

    setState(() => _saving = false);

    if (res.statusCode == 201) {
      if (!mounted) return;
      Navigator.pop(context);
    } else {
      final body = jsonDecode(res.body);
      setState(() => _error = body['message'] ?? 'Error al guardar.');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nueva dirección')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            _campo(_calleCtrl, 'Calle / Avenida *', required: true),
            const SizedBox(height: 12),
            _campo(_casaCtrl, 'No. Casa / Edificio'),
            const SizedBox(height: 12),
            _campo(_sectorCtrl, 'Sector'),
            const SizedBox(height: 12),
            _campo(_telCtrl, 'Teléfono de contacto', keyboardType: TextInputType.phone),
            const SizedBox(height: 16),

            // Provincia
            DropdownButtonFormField<String>(
              value: _idProvincia,
              decoration: const InputDecoration(labelText: 'Provincia *', border: OutlineInputBorder()),
              items: _provincias.map<DropdownMenuItem<String>>((p) =>
                DropdownMenuItem(value: p['id_provincia'].toString(), child: Text(p['provincia']))).toList(),
              onChanged: (v) {
                setState(() { 
                  _idProvincia = v; 
                  _idMunicipio = null; 
                  _municipios = []; 
                });
                if (v != null) _loadMunicipios(v);
              },
            ),
            const SizedBox(height: 12),

            // Municipio
            DropdownButtonFormField<String>(
              value: _idMunicipio,
              decoration: const InputDecoration(labelText: 'Municipio *', border: OutlineInputBorder()),
              items: _municipios.map<DropdownMenuItem<String>>((m) =>
                DropdownMenuItem(value: m['id_municipio'].toString(), child: Text(m['municipio']))).toList(),
              onChanged: (v) => setState(() => _idMunicipio = v),
            ),
            const SizedBox(height: 16),

            Text('Ubicación en el mapa', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.grey.shade700)),
            const SizedBox(height: 8),
            InkWell(
              onTap: () async {
                 LatLng? initial;
                 String? query;
                 if (_geolocalizacion != null && _geolocalizacion!.contains(',')) {
                    final parts = _geolocalizacion!.split(',');
                    final lat = double.tryParse(parts[0].trim());
                    final lng = double.tryParse(parts[1].trim());
                    if (lat != null && lng != null) initial = LatLng(lat, lng);
                 } else {
                    String q = '';
                    if (_idMunicipio != null) {
                       final mList = _municipios.where((m) => m['id_municipio'].toString() == _idMunicipio).toList();
                       if (mList.isNotEmpty) q += '${mList.first['municipio']}, ';
                    }
                    if (_idProvincia != null) {
                       final pList = _provincias.where((p) => p['id_provincia'].toString() == _idProvincia).toList();
                       if (pList.isNotEmpty) q += '${pList.first['provincia']}, ';
                    }
                    if (q.isNotEmpty) query = '$q Dominican Republic';
                 }
                 
                 final selected = await Navigator.push(context, MaterialPageRoute(builder: (_) => MapPickerScreen(initialLocation: initial, searchQuery: query)));
                 if (selected != null && selected is LatLng) {
                    setState(() => _geolocalizacion = '${selected.latitude}, ${selected.longitude}');
                 }
              },
              child: Container(
                 padding: const EdgeInsets.all(12),
                 decoration: BoxDecoration(border: Border.all(color: Colors.grey.shade400), borderRadius: BorderRadius.circular(4)),
                 child: Row(
                    children: [
                       const Icon(Icons.map, color: kPrimary),
                       const SizedBox(width: 10),
                       Expanded(
                          child: Text(_geolocalizacion != null ? 'Ubicación seleccionada: $_geolocalizacion' : 'Toca para ubicar en el mapa (opcional)', 
                             style: TextStyle(color: _geolocalizacion != null ? Colors.black : Colors.grey.shade600, fontSize: 13))
                       )
                    ]
                 )
              )
            ),
            const SizedBox(height: 12),

            SwitchListTile(
              value: _esPred,
              onChanged: (v) => setState(() => _esPred = v),
              activeColor: kPrimary,
              title: const Text('Establecer como predeterminada'),
              contentPadding: EdgeInsets.zero,
            ),

            if (_error.isNotEmpty) ...[
              const SizedBox(height: 8),
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
                    : const Text('Guardar dirección', style: TextStyle(color: Colors.white, fontSize: 15)),
              ),
            ),
          ]),
        ),
      ),
    );
  }

  Widget _campo(TextEditingController ctrl, String label, {bool required = false, TextInputType? keyboardType}) =>
    TextFormField(
      controller: ctrl,
      keyboardType: keyboardType,
      decoration: InputDecoration(labelText: label, border: const OutlineInputBorder()),
      validator: required ? (v) => (v == null || v.isEmpty) ? 'Campo requerido' : null : null,
    );
}
