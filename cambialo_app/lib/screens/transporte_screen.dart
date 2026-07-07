import 'dart:convert';
import 'dart:math';
import 'package:flutter/material.dart';
import 'package:latlong2/latlong.dart';
import 'package:http/http.dart' as http;
import '../core/api_client.dart';
import '../core/theme.dart';
import 'map_picker_screen.dart';

class TransporteScreen extends StatefulWidget {
  const TransporteScreen({super.key});

  @override
  State<TransporteScreen> createState() => _TransporteScreenState();
}

class _TransporteScreenState extends State<TransporteScreen> {
  final _formKey = GlobalKey<FormState>();

  // Controllers para Datos Personales
  final _nombreCtrl = TextEditingController();
  final _apellidoCtrl = TextEditingController();
  final _cedulaCtrl = TextEditingController();
  final _telefonoCtrl = TextEditingController();
  final _correoCtrl = TextEditingController();

  // Controllers para Direcciones
  final _recogidaCtrl = TextEditingController();
  final _recogidaAddressCtrl = TextEditingController();
  final _entregaCtrl = TextEditingController();
  final _entregaAddressCtrl = TextEditingController();

  // Búsqueda y Scroll / Paginación
  final _searchCtrl = TextEditingController();
  final _scrollController = ScrollController(); // Scroll principal del formulario
  final _listViewScrollController = ScrollController(); // Scroll interno de la lista de artículos
  String _searchQuery = '';
  int _visibleCount = 10; // Carga inicial de 10 artículos

  // Coordenadas para cálculo de distancia
  LatLng? _latLngOrigen;
  LatLng? _latLngDestino;

  // Detalles de Mudanza
  String _tipoServicio = 'transporte';
  String _fechaServicio = '';
  String _pisoOrigen = '0';
  String _pisoDestino = '0';

  // Catálogo de artículos y configuraciones
  List _articulos = [];
  bool _loadingCatalog = true;
  bool _submitting = false;

  // Tarifas de configuración cargadas desde backend
  double _precioKmTransporte = 50.0;
  double _precioKmMudanza = 100.0;
  int _limiteArticulosMudanza = 5;

  // Estado de selección de artículos
  // id_articulo -> {pequeno: bool, mediano: bool, grande: bool}
  final Map<int, Map<String, bool>> _selectedSizes = {};
  // id_articulo -> {pequeno: int, mediano: int, grande: int}
  final Map<int, Map<String, int>> _quantities = {};

  double _distancia = 0.0;
  double _totalEstimado = 0.0;

  @override
  void initState() {
    super.initState();
    _loadUserAndCatalog();
    _listViewScrollController.addListener(() {
      if (_listViewScrollController.position.pixels >= _listViewScrollController.position.maxScrollExtent - 150) {
        _loadMoreArticulos();
      }
    });
  }

  @override
  void dispose() {
    _nombreCtrl.dispose();
    _apellidoCtrl.dispose();
    _cedulaCtrl.dispose();
    _telefonoCtrl.dispose();
    _correoCtrl.dispose();
    _recogidaCtrl.dispose();
    _recogidaAddressCtrl.dispose();
    _entregaCtrl.dispose();
    _entregaAddressCtrl.dispose();
    _searchCtrl.dispose();
    _scrollController.dispose();
    _listViewScrollController.dispose();
    super.dispose();
  }

  Future<void> _loadUserAndCatalog() async {
    // 1. Cargar datos del usuario logueado
    final user = await ApiClient.getUser();
    if (user != null) {
      _nombreCtrl.text = user['nombres'] ?? '';
      _apellidoCtrl.text = user['apellidos'] ?? '';
      _correoCtrl.text = user['correo'] ?? '';
      _telefonoCtrl.text = user['telefono'] ?? '';
    }

    // 2. Cargar catálogo de artículos
    try {
      final res = await ApiClient.get('/transporte/articulos', auth: true);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        setState(() {
          _articulos = data['articulos'] ?? [];
          final config = data['configuracion'] ?? {};
          _precioKmTransporte = double.tryParse(config['precio_km_transporte']?.toString() ?? '50.0') ?? 50.0;
          _precioKmMudanza = double.tryParse(config['precio_km_mudanza']?.toString() ?? '100.0') ?? 100.0;
          _limiteArticulosMudanza = ApiClient.parseInt(config['limite_articulos_mudanza']) ?? 5;
          _loadingCatalog = false;
        });
      }
    } catch (_) {
      setState(() => _loadingCatalog = false);
    }
  }

  double _calculateDistance(double lat1, double lon1, double lat2, double lon2) {
    const p = 0.017453292519943295; // Math.PI / 180
    final c = cos;
    final a = 0.5 - c((lat2 - lat1) * p)/2 + 
          c(lat1 * p) * c(lat2 * p) * 
          (1 - c((lon2 - lon1) * p))/2;
    return 12742 * asin(sqrt(a)); // 2 * R; R = 6371 km
  }

  Future<String> _reverseGeocode(double lat, double lng) async {
    try {
      final url = Uri.parse('https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng');
      final res = await http.get(url, headers: {'User-Agent': 'CambialoApp/1.0'});
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        return data['display_name'] ?? '$lat, $lng';
      }
    } catch (_) {}
    return '$lat, $lng';
  }

  Future<void> _selectLocation(bool isRecogida) async {
    final initialLoc = isRecogida ? _latLngOrigen : _latLngDestino;
    final result = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => MapPickerScreen(initialLocation: initialLoc),
      ),
    );
    if (result is LatLng) {
      final address = await _reverseGeocode(result.latitude, result.longitude);
      setState(() {
        if (isRecogida) {
          _latLngOrigen = result;
          _recogidaCtrl.text = "${result.latitude}, ${result.longitude}";
          _recogidaAddressCtrl.text = address;
        } else {
          _latLngDestino = result;
          _entregaCtrl.text = "${result.latitude}, ${result.longitude}";
          _entregaAddressCtrl.text = address;
        }
      });
      _recalculateDistanceAndTotal();
    }
  }

  void _recalculateDistanceAndTotal() {
    if (_latLngOrigen != null && _latLngDestino != null) {
      _distancia = _calculateDistance(
        _latLngOrigen!.latitude,
        _latLngOrigen!.longitude,
        _latLngDestino!.latitude,
        _latLngDestino!.longitude,
      );
    } else {
      _distancia = 0.0;
    }

    double totalArticulos = 0.0;
    int countArticulos = 0;

    _selectedSizes.forEach((artId, sizesMap) {
      final article = _articulos.firstWhere((a) => a['id'] == artId, orElse: () => null);
      if (article == null) return;
      sizesMap.forEach((sizeKey, isSelected) {
        if (isSelected == true) {
          final qty = _quantities[artId]?[sizeKey] ?? 1;
          final priceField = "precio_" + (sizeKey == 'pequeno' ? 'pequeno' : (sizeKey == 'mediano' ? 'mediano' : 'grande'));
          final price = double.tryParse(article[priceField]?.toString() ?? '0') ?? 0.0;
          totalArticulos += price * qty;
          countArticulos += qty;
        }
      });
    });

    double precioKm = _precioKmTransporte;
    if (countArticulos > _limiteArticulosMudanza) {
      _tipoServicio = 'mudanza';
      precioKm = _precioKmMudanza;
    } else {
      precioKm = (_tipoServicio == 'mudanza') ? _precioKmMudanza : _precioKmTransporte;
    }

    _totalEstimado = totalArticulos + (_distancia * precioKm);
    setState(() {});
  }

  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now().add(const Duration(days: 1)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 90)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: kPrimary,
              onPrimary: Colors.white,
              onSurface: Colors.black87,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() {
        _fechaServicio = picked.toString().split(' ')[0];
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_fechaServicio.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Por favor seleccione la fecha del servicio.')),
      );
      return;
    }
    if (_latLngOrigen == null || _latLngDestino == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Debe seleccionar los puntos de Origen y Destino en el mapa.')),
      );
      return;
    }

    setState(() => _submitting = true);

    final Map<String, dynamic> body = {
      'tipo_servicio': _tipoServicio,
      'nombre': _nombreCtrl.text.trim(),
      'apellido': _apellidoCtrl.text.trim(),
      'cedula': _cedulaCtrl.text.trim(),
      'telefono': _telefonoCtrl.text.trim(),
      'correo': _correoCtrl.text.trim(),
      'fecha_servicio': _fechaServicio,
      'punto_recogida': _recogidaCtrl.text.trim(),
      'punto_recogida_address': _recogidaAddressCtrl.text.trim(),
      'piso_origen': _pisoOrigen,
      'punto_entrega': _entregaCtrl.text.trim(),
      'punto_entrega_address': _entregaAddressCtrl.text.trim(),
      'piso_destino': _pisoDestino,
      'distancia_km': _distancia,
      'precio_estimado_total': _totalEstimado,
    };

    final Map<String, Map<String, dynamic>> articulosJson = {};
    final Map<String, Map<String, int>> cantidadesJson = {};

    _selectedSizes.forEach((artId, sizesMap) {
      final Map<String, dynamic> activeSizes = {};
      final Map<String, int> activeQtys = {};

      sizesMap.forEach((sizeKey, isSelected) {
        if (isSelected == true) {
          activeSizes[sizeKey] = true;
          activeQtys[sizeKey] = _quantities[artId]?[sizeKey] ?? 1;
        }
      });

      if (activeSizes.isNotEmpty) {
        articulosJson[artId.toString()] = activeSizes;
        cantidadesJson[artId.toString()] = activeQtys;
      }
    });

    body['articulos'] = articulosJson;
    body['cantidades'] = cantidadesJson;

    try {
      final res = await ApiClient.post('/transporte/solicitar', body, auth: true);
      if (!mounted) return;
      setState(() => _submitting = false);

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(data['message'] ?? 'Solicitud enviada con éxito.')),
        );
        Navigator.pop(context);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Error al enviar la solicitud. Intente de nuevo.')),
        );
      }
    } catch (_) {
      if (mounted) {
        setState(() => _submitting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Error de conexión con el servidor.')),
        );
      }
    }
  }

  List _getFilteredArticulos() {
    if (_searchQuery.isEmpty) return _articulos;
    return _articulos.where((art) {
      final name = art['nombre']?.toString().toLowerCase() ?? '';
      return name.contains(_searchQuery.toLowerCase());
    }).toList();
  }

  void _loadMoreArticulos() {
    final filtered = _getFilteredArticulos();
    if (_visibleCount < filtered.length) {
      setState(() {
        _visibleCount = min(filtered.length, _visibleCount + 10);
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: const Text('Solicitud de Transporte', style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0.5,
      ),
      body: _loadingCatalog
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : SingleChildScrollView(
              controller: _scrollController,
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildSectionHeader('1. Datos Personales'),
                    const SizedBox(height: 12),
                    _buildCard([
                      _buildTextField(_nombreCtrl, 'Nombre *'),
                      _buildTextField(_apellidoCtrl, 'Apellido *'),
                      _buildTextField(_cedulaCtrl, 'Cédula / Pasaporte *'),
                      _buildTextField(_telefonoCtrl, 'Teléfono *', keyboardType: TextInputType.phone),
                      _buildTextField(_correoCtrl, 'Correo Electrónico *', keyboardType: TextInputType.emailAddress),
                    ]),
                    const SizedBox(height: 24),
                    _buildSectionHeader('2. Detalles del Servicio'),
                    const SizedBox(height: 12),
                    _buildCard([
                      DropdownButtonFormField<String>(
                        value: _tipoServicio,
                        decoration: const InputDecoration(
                          labelText: 'Tipo de Servicio',
                          labelStyle: TextStyle(fontSize: 14, color: kTextGray),
                          border: UnderlineInputBorder(),
                        ),
                        items: const [
                          DropdownMenuItem(value: 'transporte', child: Text('Transporte de Carga')),
                          DropdownMenuItem(value: 'mudanza', child: Text('Mudanza Residencial/Comercial')),
                        ],
                        onChanged: (val) {
                          if (val != null) {
                            setState(() => _tipoServicio = val);
                            _recalculateDistanceAndTotal();
                          }
                        },
                      ),
                      const SizedBox(height: 16),
                      InkWell(
                        onTap: _selectDate,
                        child: InputDecorator(
                          decoration: const InputDecoration(
                            labelText: 'Fecha del Servicio *',
                            labelStyle: TextStyle(fontSize: 14, color: kTextGray),
                            border: UnderlineInputBorder(),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                _fechaServicio.isEmpty ? 'Seleccionar fecha' : _fechaServicio,
                                style: TextStyle(
                                  color: _fechaServicio.isEmpty ? Colors.grey : Colors.black87,
                                  fontSize: 14,
                                ),
                              ),
                              const Icon(Icons.calendar_today, size: 18, color: kPrimary),
                            ],
                          ),
                        ),
                      ),
                    ]),
                    const SizedBox(height: 24),
                    _buildSectionHeader('3. Ruta (Puntos A y B)'),
                    const SizedBox(height: 12),
                    _buildCard([
                      _buildLocationPicker(
                        label: 'Punto de Recogida (Origen) *',
                        coordCtrl: _recogidaCtrl,
                        addrCtrl: _recogidaAddressCtrl,
                        isRecogida: true,
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        value: _pisoOrigen,
                        decoration: const InputDecoration(
                          labelText: 'Piso de Origen',
                          labelStyle: TextStyle(fontSize: 14, color: kTextGray),
                          border: UnderlineInputBorder(),
                        ),
                        items: List.generate(11, (i) => DropdownMenuItem(value: i.toString(), child: Text(i == 0 ? 'Planta Baja' : 'Piso $i'))),
                        onChanged: (val) {
                          if (val != null) setState(() => _pisoOrigen = val);
                        },
                      ),
                      const Divider(height: 32),
                      _buildLocationPicker(
                        label: 'Punto de Entrega (Destino) *',
                        coordCtrl: _entregaCtrl,
                        addrCtrl: _entregaAddressCtrl,
                        isRecogida: false,
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        value: _pisoDestino,
                        decoration: const InputDecoration(
                          labelText: 'Piso de Destino',
                          labelStyle: TextStyle(fontSize: 14, color: kTextGray),
                          border: UnderlineInputBorder(),
                        ),
                        items: List.generate(11, (i) => DropdownMenuItem(value: i.toString(), child: Text(i == 0 ? 'Planta Baja' : 'Piso $i'))),
                        onChanged: (val) {
                          if (val != null) setState(() => _pisoDestino = val);
                        },
                      ),
                    ]),
                    const SizedBox(height: 24),
                    _buildSectionHeader('4. Inventario de Carga'),
                    const SizedBox(height: 4),
                    const Text('Marque los artículos y especifique cantidad por tamaño.', style: TextStyle(fontSize: 12, color: Colors.grey)),
                    const SizedBox(height: 12),
                    _buildSelectedChips(),
                    _buildSearchBar(),
                    _buildInventoryList(),
                    const SizedBox(height: 24),
                    _buildPricingPanel(),
                    const SizedBox(height: 30),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: kPrimary,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          elevation: 1,
                        ),
                        onPressed: _submitting ? null : _submit,
                        child: _submitting
                            ? const CircularProgressIndicator(color: Colors.white)
                            : const Text('Enviar Solicitud', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                      ),
                    ),
                    const SizedBox(height: 40),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Text(
      title,
      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.black87),
    );
  }

  Widget _buildCard(List<Widget> children) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 2, offset: Offset(0, 1))],
      ),
      child: Column(children: children),
    );
  }

  Widget _buildTextField(
    TextEditingController ctrl,
    String label, {
    TextInputType keyboardType = TextInputType.text,
  }) {
    return TextFormField(
      controller: ctrl,
      keyboardType: keyboardType,
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(fontSize: 14, color: kTextGray),
        border: const UnderlineInputBorder(),
      ),
      validator: (val) => val == null || val.trim().isEmpty ? 'Requerido' : null,
    );
  }

  Widget _buildLocationPicker({
    required String label,
    required TextEditingController coordCtrl,
    required TextEditingController addrCtrl,
    required bool isRecogida,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: kTextGray)),
            TextButton.icon(
              onPressed: () => _selectLocation(isRecogida),
              icon: const Icon(Icons.map, size: 16, color: kPrimary),
              label: const Text('Mapa', style: TextStyle(fontSize: 12, color: kPrimary, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
        TextFormField(
          controller: addrCtrl,
          readOnly: true,
          maxLines: 2,
          decoration: const InputDecoration(
            hintText: 'Seleccione en el mapa para cargar la dirección',
            hintStyle: TextStyle(fontSize: 12, color: Colors.grey),
            border: InputBorder.none,
          ),
          validator: (val) => val == null || val.trim().isEmpty ? 'Debe seleccionar un punto en el mapa' : null,
        ),
      ],
    );
  }

  Widget _buildSelectedChips() {
    final List<Widget> chips = [];
    _selectedSizes.forEach((artId, sizesMap) {
      final article = _articulos.firstWhere((a) => a['id'] == artId, orElse: () => null);
      if (article == null) return;
      
      final List<String> sizesDesc = [];
      sizesMap.forEach((sizeKey, isSelected) {
        if (isSelected == true) {
          final qty = _quantities[artId]?[sizeKey] ?? 1;
          sizesDesc.add("$qty ${sizeKey[0].toUpperCase()}${sizeKey.substring(1, 3)}");
        }
      });
      
      if (sizesDesc.isNotEmpty) {
        chips.add(
          Chip(
            label: Text(
              "${article['nombre']} (${sizesDesc.join(', ')})",
              style: const TextStyle(fontSize: 11),
            ),
            onDeleted: () {
              setState(() {
                _selectedSizes.remove(artId);
                _quantities.remove(artId);
                _recalculateDistanceAndTotal();
              });
            },
            deleteIcon: const Icon(Icons.close, size: 14),
            backgroundColor: Colors.blue.shade50,
          ),
        );
      }
    });

    if (chips.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.only(bottom: 12.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Artículos Seleccionados:',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: kPrimary),
          ),
          const SizedBox(height: 6),
          Wrap(
            spacing: 8,
            runSpacing: 4,
            children: chips,
          ),
          const Divider(height: 20),
        ],
      ),
    );
  }

  Widget _buildSearchBar() {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12.0),
      child: TextFormField(
        controller: _searchCtrl,
        decoration: InputDecoration(
          hintText: 'Buscar artículo...',
          prefixIcon: const Icon(Icons.search, color: kPrimary),
          suffixIcon: _searchQuery.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear),
                  onPressed: () {
                    _searchCtrl.clear();
                    setState(() {
                      _searchQuery = '';
                      _visibleCount = 10;
                    });
                  },
                )
              : null,
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(color: Colors.grey.shade300),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(color: Colors.grey.shade200),
          ),
          contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
        ),
        onChanged: (val) {
          setState(() {
            _searchQuery = val.trim();
            _visibleCount = 10; // reset pagination
          });
        },
      ),
    );
  }

  Widget _buildInventoryList() {
    final filtered = _getFilteredArticulos();
    if (filtered.isEmpty) {
      return Container(
        height: 200,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: Colors.grey.shade200),
        ),
        child: const Text('No se encontraron artículos.', style: TextStyle(color: kTextGray, fontSize: 13)),
      );
    }

    final visibleList = filtered.take(_visibleCount).toList();

    return Container(
      height: 350, // Altura fija de viewport de artículos
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: ListView.builder(
        controller: _listViewScrollController,
        padding: const EdgeInsets.all(8),
        itemCount: visibleList.length + (_visibleCount < filtered.length ? 1 : 0),
        itemBuilder: (context, index) {
          if (index == visibleList.length) {
            return const Padding(
              padding: EdgeInsets.symmetric(vertical: 12.0),
              child: Center(
                child: SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(color: kPrimary, strokeWidth: 2),
                ),
              ),
            );
          }

          final art = visibleList[index];
          final int artId = art['id'];
          final bool isExpanded = _selectedSizes.containsKey(artId) && 
              _selectedSizes[artId]!.values.contains(true);

          return Card(
            margin: const EdgeInsets.only(bottom: 8),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            elevation: 0.5,
            child: Column(
              children: [
                CheckboxListTile(
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
                  title: Text(art['nombre'] ?? '', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                  value: isExpanded,
                  onChanged: (val) {
                    setState(() {
                      if (val == true) {
                        _selectedSizes[artId] = {'pequeno': true, 'mediano': false, 'grande': false};
                        _quantities[artId] = {'pequeno': 1, 'mediano': 1, 'grande': 1};
                      } else {
                        _selectedSizes.remove(artId);
                        _quantities.remove(artId);
                      }
                    });
                    _recalculateDistanceAndTotal();
                  },
                ),
                if (isExpanded)
                  Padding(
                    padding: const EdgeInsets.only(left: 12, right: 12, bottom: 12),
                    child: Column(
                      children: [
                        _buildSizeRow(artId, 'pequeno', 'Pequeño', art['precio_pequeno']),
                        const SizedBox(height: 6),
                        _buildSizeRow(artId, 'mediano', 'Mediano', art['precio_mediano']),
                        const SizedBox(height: 6),
                        _buildSizeRow(artId, 'grande', 'Grande', art['precio_grande']),
                      ],
                    ),
                  )
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildSizeRow(int artId, String sizeKey, String label, dynamic priceRaw) {
    final double price = double.tryParse(priceRaw?.toString() ?? '0') ?? 0.0;
    final bool isChecked = _selectedSizes[artId]?[sizeKey] ?? false;
    final int qty = _quantities[artId]?[sizeKey] ?? 1;

    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Row(
          children: [
            Checkbox(
              value: isChecked,
              onChanged: (val) {
                setState(() {
                  _selectedSizes[artId]![sizeKey] = val ?? false;
                });
                _recalculateDistanceAndTotal();
              },
            ),
            Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
            if (price > 0)
              Padding(
                padding: const EdgeInsets.only(left: 8.0),
                child: Text(
                  'RD\$ ${price.toStringAsFixed(2)}',
                  style: const TextStyle(fontSize: 11, color: Colors.green, fontWeight: FontWeight.bold),
                ),
              ),
          ],
        ),
        if (isChecked)
          Row(
            children: [
              IconButton(
                icon: const Icon(Icons.remove_circle_outline, size: 20, color: Colors.redAccent),
                onPressed: qty > 1
                    ? () {
                        setState(() {
                          _quantities[artId]![sizeKey] = qty - 1;
                        });
                        _recalculateDistanceAndTotal();
                      }
                    : null,
              ),
              Text(qty.toString(), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
              IconButton(
                icon: const Icon(Icons.add_circle_outline, size: 20, color: Colors.green),
                onPressed: () {
                  setState(() {
                    _quantities[artId]![sizeKey] = qty + 1;
                  });
                  _recalculateDistanceAndTotal();
                },
              ),
            ],
          )
      ],
    );
  }

  Widget _buildPricingPanel() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.blue.shade50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.blue.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Distancia Estimada:', style: TextStyle(fontSize: 13, color: kTextGray, fontWeight: FontWeight.bold)),
              Text('${_distancia.toStringAsFixed(2)} km', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Tipo de Servicio Final:', style: TextStyle(fontSize: 13, color: kTextGray, fontWeight: FontWeight.bold)),
              Text(
                _tipoServicio == 'mudanza' ? 'MUDANZA' : 'TRANSPORTE',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                  color: _tipoServicio == 'mudanza' ? Colors.orange.shade800 : Colors.blue.shade800,
                ),
              ),
            ],
          ),
          const Divider(height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Total Estimado:', style: TextStyle(fontSize: 15, color: kTextGray, fontWeight: FontWeight.bold)),
              Text(
                'RD\$ ${_totalEstimado.toStringAsFixed(2)}',
                style: const TextStyle(fontSize: 18, color: Colors.green, fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
