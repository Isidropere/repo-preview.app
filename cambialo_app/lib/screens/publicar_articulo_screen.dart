import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:http/http.dart' as http;
import '../core/api_client.dart';
import '../core/theme.dart';

/// Formulario para publicar un nuevo artículo desde la app móvil
class PublicarArticuloScreen extends StatefulWidget {
  final int? itemId;
  const PublicarArticuloScreen({super.key, this.itemId});
  @override
  State<PublicarArticuloScreen> createState() => _PublicarArticuloScreenState();
}

class _PublicarArticuloScreenState extends State<PublicarArticuloScreen> {
  final _formKey       = GlobalKey<FormState>();
  final _nombreCtrl    = TextEditingController();
  final _precioCtrl    = TextEditingController();
  final _descuentoCtrl = TextEditingController(text: '0');
  final _cantidadCtrl  = TextEditingController(text: '1');
  final _descCtrl      = TextEditingController();

  // Dimensiones
  final _pesoCtrl      = TextEditingController();
  final _altoCtrl      = TextEditingController();
  final _anchoCtrl     = TextEditingController();
  final _profundoCtrl  = TextEditingController();

  List _categorias     = [];
  int? _idCategoria;
  int  _condicion      = 1;   // 1=Nuevo, 2=Como nuevo, 3=Usado - Buen estado, 4=Usado - Aceptable
  int  _tipoTrans      = 1;   // 1=Venta, 2=Intercambio, 3=Venta o Intercambio
  bool _saving         = false;
  bool _loadingItem    = false;
  String _error        = '';
  int  _step           = 1;

  // Selector de imágenes
  final ImagePicker _picker = ImagePicker();
  XFile? _mainImage;
  final List<XFile> _additionalImages = [];

  // Variables para editar item existente
  String? _existingMainImageUrl;
  List _existingAdditionalImages = [];

  // Colores
  List _allColors      = [];
  // Mapa para guardar los colores seleccionados: id_color -> { 'nombre': String, 'codigo_hex': String, 'stockCtrl': TextEditingController }
  final Map<int, Map<String, dynamic>> _selectedColors = {};

  bool _showDimensions = false;
  bool _showColors     = false;

  @override
  void initState() {
    super.initState();
    _loadCategorias();
    _loadColors();
    if (widget.itemId != null) {
      _loadItemDetails();
    }
  }

  @override
  void dispose() {
    _nombreCtrl.dispose();
    _precioCtrl.dispose();
    _descuentoCtrl.dispose();
    _cantidadCtrl.dispose();
    _descCtrl.dispose();
    _pesoCtrl.dispose();
    _altoCtrl.dispose();
    _anchoCtrl.dispose();
    _profundoCtrl.dispose();
    _selectedColors.forEach((_, value) {
      (value['stockCtrl'] as TextEditingController).dispose();
    });
    super.dispose();
  }

  Future<void> _loadItemDetails() async {
    setState(() {
      _loadingItem = true;
      _error = '';
    });

    try {
      final res = await ApiClient.get('/mis-items/${widget.itemId}', auth: true, useCache: false);
      if (res.statusCode == 200) {
        final item = jsonDecode(res.body) as Map<String, dynamic>;
        setState(() {
          _nombreCtrl.text = (item['item'] ?? '').toString();
          _precioCtrl.text = (item['valor'] ?? '').toString();
          _descuentoCtrl.text = (item['descuento'] ?? '0').toString();
          _cantidadCtrl.text = (item['inventarios']?['cantidad'] ?? '1').toString();
          _descCtrl.text = (item['presentacion'] ?? '').toString();
          _idCategoria = int.tryParse(item['id_categoria_item']?.toString() ?? '');
          _condicion = int.tryParse(item['condicion']?.toString() ?? '') ?? 1;
          _tipoTrans = int.tryParse(item['tipo_trans']?.toString() ?? '') ?? 1;

          // Dimensions
          _pesoCtrl.text = (item['peso_lbs'] ?? '').toString();
          _altoCtrl.text = (item['alto_cm'] ?? '').toString();
          _anchoCtrl.text = (item['ancho_cm'] ?? '').toString();
          _profundoCtrl.text = (item['profundo_cm'] ?? '').toString();

          // Colors
          _selectedColors.clear();
          final List colorsList = item['colors'] ?? [];
          for (final c in colorsList) {
            final int colorId = int.tryParse(c['id_color']?.toString() ?? '') ?? 0;
            final String nombre = (c['nombre'] ?? '').toString();
            final String hex = (c['codigo_hex'] ?? '').toString();
            final int stock = int.tryParse(c['pivot']?['stock']?.toString() ?? '') ?? 0;
            _selectedColors[colorId] = {
              'nombre': nombre,
              'codigo_hex': hex,
              'stockCtrl': TextEditingController(text: stock.toString()),
            };
          }

          // Images
          final List imgsList = item['imagenes'] ?? [];
          if (imgsList.isNotEmpty) {
            final mainImg = imgsList.first;
            _existingMainImageUrl = (mainImg['image_url'] ?? mainImg['url'] ?? '').toString();
            if (_existingMainImageUrl!.isEmpty) _existingMainImageUrl = null;
            _existingAdditionalImages = imgsList.skip(1).toList();
          }

          _loadingItem = false;
        });
      } else {
        setState(() {
          _error = 'Error al cargar detalles del artículo.';
          _loadingItem = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Error al conectar con el servidor: $e';
        _loadingItem = false;
      });
    }
  }

  Future<void> _loadColors() async {
    try {
      final res = await ApiClient.get('/colors');
      if (res.statusCode == 200) {
        final List decoded = jsonDecode(res.body);
        setState(() => _allColors = decoded);
      }
    } catch (e) {
      if (kDebugMode) print('Error cargando colores: $e');
    }
  }

  Future<void> _loadCategorias() async {
    final res = await ApiClient.get('/categorias');
    if (res.statusCode == 200) {
      final List decoded = jsonDecode(res.body);
      decoded.sort((a, b) {
        final String catA = (a['categoria'] ?? '').toString().toLowerCase();
        final String catB = (b['categoria'] ?? '').toString().toLowerCase();
        return catA.compareTo(catB);
      });
      setState(() => _categorias = decoded);
    }
  }

  Future<void> _pickMainImage() async {
    try {
      final XFile? picked = await _picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1200,
        maxHeight: 1200,
        imageQuality: 85,
      );
      if (picked != null) {
        setState(() {
          _mainImage = picked;
          _error = '';
        });
      }
    } catch (e) {
      setState(() => _error = 'Error al seleccionar imagen: $e');
    }
  }

  Future<void> _pickAdditionalImages() async {
    if (_additionalImages.length >= 4) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Solo puedes agregar hasta 4 imágenes opcionales.'),
        backgroundColor: Colors.orange,
      ));
      return;
    }

    try {
      final List<XFile> picked = await _picker.pickMultiImage(
        maxWidth: 1200,
        maxHeight: 1200,
        imageQuality: 80,
      );
      if (picked.isNotEmpty) {
        setState(() {
          final espacioDisponible = 4 - _additionalImages.length;
          if (picked.length > espacioDisponible) {
            _additionalImages.addAll(picked.take(espacioDisponible));
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
              content: Text('Se agregaron $espacioDisponible imágenes. Límite de 4 opcionales alcanzado.'),
              backgroundColor: Colors.orange,
            ));
          } else {
            _additionalImages.addAll(picked);
          }
        });
      }
    } catch (e) {
      setState(() => _error = 'Error al seleccionar imágenes: $e');
    }
  }

  Future<void> _publicar() async {
    final desc = _descCtrl.text.trim();
    if (desc.isEmpty) {
      setState(() => _error = 'La descripción es obligatoria.');
      return;
    }

    final int cantidadTotal = int.tryParse(_cantidadCtrl.text.trim()) ?? 1;
    int totalStockColores = 0;
    _selectedColors.forEach((colorId, data) {
      final stockVal = int.tryParse((data['stockCtrl'] as TextEditingController).text.trim()) ?? 0;
      totalStockColores += stockVal;
    });

    if (_selectedColors.isNotEmpty && totalStockColores > cantidadTotal) {
      setState(() => _error = 'La suma del stock de los colores ($totalStockColores) no puede ser mayor que la cantidad total ($cantidadTotal).');
      return;
    }

    if (_mainImage == null && _existingMainImageUrl == null) {
      setState(() => _error = 'Debes subir una imagen principal.');
      return;
    }

    setState(() {
      _saving = true;
      _error = '';
    });

    try {
      final Map<String, String> fields = {
        'item':              _nombreCtrl.text.trim(),
        'presentacion':      desc,
        'valor':             _precioCtrl.text.trim(),
        'descuento':         _descuentoCtrl.text.trim(),
        'cantidad':          _cantidadCtrl.text.trim(),
        'condicion':         _condicion.toString(),
        'tipo_trans':        _tipoTrans.toString(),
        'id_categoria_item': _idCategoria.toString(),
        'peso_lbs':          _pesoCtrl.text.trim(),
        'alto_cm':           _altoCtrl.text.trim(),
        'ancho_cm':          _anchoCtrl.text.trim(),
        'profundo_cm':       _profundoCtrl.text.trim(),
      };

      // Mapear colores y stock para el backend
      int colorIndex = 0;
      _selectedColors.forEach((colorId, data) {
        fields['colors[$colorIndex]'] = colorId.toString();
        fields['stock[$colorId]'] = (data['stockCtrl'] as TextEditingController).text.trim();
        colorIndex++;
      });

      // Mapear imágenes existentes que se conservan si es edición
      if (widget.itemId != null) {
        int extIndex = 0;
        for (final img in _existingAdditionalImages) {
          fields['imagenes_existentes[$extIndex]'] = img['id_imagen'].toString();
          extIndex++;
        }
      }

      // Preparar MultipartFile para la imagen principal (si se sube una nueva)
      http.MultipartFile? mainImageFile;
      if (_mainImage != null) {
        if (kIsWeb) {
          final bytes = await _mainImage!.readAsBytes();
          mainImageFile = http.MultipartFile.fromBytes(
            'imagen_principal',
            bytes,
            filename: _mainImage!.name,
          );
        } else {
          mainImageFile = await http.MultipartFile.fromPath(
            'imagen_principal',
            _mainImage!.path,
          );
        }
      }

      // Preparar MultipartFiles para imágenes adicionales
      final List<http.MultipartFile> additionalImageFiles = [];
      for (final img in _additionalImages) {
        if (kIsWeb) {
          final bytes = await img.readAsBytes();
          additionalImageFiles.add(http.MultipartFile.fromBytes(
            'imagenes[]',
            bytes,
            filename: img.name,
          ));
        } else {
          additionalImageFiles.add(await http.MultipartFile.fromPath(
            'imagenes[]',
            img.path,
          ));
        }
      }

      final String path = widget.itemId != null 
          ? '/items/${widget.itemId}/update'
          : '/items';

      final res = await ApiClient.multipartPost(
        path,
        fields,
        auth: true,
        mainImage: mainImageFile,
        additionalImages: additionalImageFiles,
      );

      setState(() => _saving = false);

      if (res.statusCode == 200 || res.statusCode == 201) {
        if (!mounted) return;
        final msg = widget.itemId != null 
            ? 'Artículo actualizado exitosamente.'
            : 'Artículo publicado. Pendiente de aprobación.';
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(msg),
          backgroundColor: Colors.green,
        ));
        Navigator.pop(context, true);
      } else {
        final body = jsonDecode(res.body);
        setState(() => _error = body['message'] ?? 'Error al guardar.');
      }
    } catch (e) {
      setState(() {
        _saving = false;
        _error = 'Error al conectar con el servidor: $e';
      });
    }
  }

  void _nextStep() {
    if (_step == 1) {
      if (!_formKey.currentState!.validate()) return;
      if (_idCategoria == null) {
        setState(() => _error = 'Selecciona una categoría.');
        return;
      }
      setState(() {
        _step = 2;
        _error = '';
      });
    } else if (_step == 2) {
      if (_mainImage == null && _existingMainImageUrl == null) {
        setState(() => _error = 'Debes subir una imagen principal.');
        return;
      }
      setState(() {
        _step = 3;
        _error = '';
      });
    }
  }

  void _prevStep() {
    if (_step > 1) {
      setState(() {
        _step--;
        _error = '';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: Text(
          widget.itemId != null ? 'Editar artículo' : 'Publicar artículo',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
      body: _loadingItem
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : SingleChildScrollView(
              physics: const BouncingScrollPhysics(),
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  // Indicador de pasos
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      _stepIndicator(1, 'Info', _step >= 1),
                      _stepDivider(_step >= 2),
                      _stepIndicator(2, 'Multimedia', _step >= 2),
                      _stepDivider(_step >= 3),
                      _stepIndicator(3, 'Detalles', _step >= 3),
                    ],
                  ),
                  const SizedBox(height: 24),

                  if (_step == 1) _buildStep1(),
                  if (_step == 2) _buildStep2(),
                  if (_step == 3) _buildStep3(),
                ],
              ),
            ),
    );
  }

  Widget _stepIndicator(int stepNum, String title, bool active) => Column(
        children: [
          CircleAvatar(
            radius: 18,
            backgroundColor: active ? kPrimary : Colors.grey.shade300,
            child: Text(
              stepNum.toString(),
              style: TextStyle(color: active ? Colors.white : kTextGray, fontWeight: FontWeight.bold, fontSize: 13),
            ),
          ),
          const SizedBox(height: 4),
          Text(title, style: TextStyle(fontSize: 10, color: active ? kTextDark : kTextGray, fontWeight: FontWeight.w500)),
        ],
      );

  Widget _stepDivider(bool active) => Container(
        width: 40,
        height: 2,
        margin: const EdgeInsets.only(left: 8, right: 8, bottom: 12),
        color: active ? kPrimary : Colors.grey.shade300,
      );

  Widget _buildStep1() => Form(
        key: _formKey,
        child: Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _titulo('Información básica'),
              const SizedBox(height: 12),
              _campo(_nombreCtrl, 'Nombre del artículo *', required: true),
              const SizedBox(height: 12),
              
              // Precio y Descuento en fila
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: _campo(_precioCtrl, 'Precio (RD\$) (Opcional)', required: false, keyboardType: TextInputType.number),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _campo(_descuentoCtrl, 'Descuento (%)', required: false, keyboardType: TextInputType.number),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Cantidad y Categoría en fila
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    flex: 1,
                    child: _campo(_cantidadCtrl, 'Cantidad *', required: true, keyboardType: TextInputType.number),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    flex: 2,
                    child: DropdownButtonFormField<int>(
                      value: _idCategoria,
                      decoration: InputDecoration(
                        labelText: 'Categoría *',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        filled: true,
                        fillColor: Colors.white,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                      ),
                      items: _categorias.map<DropdownMenuItem<int>>((c) =>
                        DropdownMenuItem(value: c['id_categoria_item'] as int, child: Text(c['categoria'].toString(), overflow: TextOverflow.ellipsis))).toList(),
                      onChanged: (v) => setState(() => _idCategoria = v),
                      validator: (v) => v == null ? 'Selecciona una categoría' : null,
                    ),
                  ),
                ],
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
                  onPressed: _nextStep,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: kPrimary,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: const Text('Siguiente', style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      );

  Widget _buildStep2() => Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade200),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _titulo('Imágenes del producto'),
            const SizedBox(height: 12),
            
            // Selector imagen principal
            GestureDetector(
              onTap: _pickMainImage,
              child: Container(
                width: double.infinity,
                height: 180,
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: (_mainImage != null || _existingMainImageUrl != null)
                        ? kPrimary.withOpacity(0.5)
                        : Colors.grey.shade200,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.02),
                      blurRadius: 6,
                      offset: const Offset(0, 3),
                    )
                  ]
                ),
                child: _mainImage != null
                    ? Stack(
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: kIsWeb
                                ? Image.network(_mainImage!.path, width: double.infinity, height: 180, fit: BoxFit.cover)
                                : Image.file(File(_mainImage!.path), width: double.infinity, height: 180, fit: BoxFit.cover),
                          ),
                          Container(
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(12),
                              color: Colors.black38,
                            ),
                          ),
                          const Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.edit_outlined, color: Colors.white, size: 28),
                                SizedBox(height: 4),
                                Text(
                                  'Cambiar Imagen Principal',
                                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                                ),
                              ],
                            ),
                          ),
                        ],
                      )
                    : _existingMainImageUrl != null
                        ? Stack(
                            children: [
                              ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: Image.network(_existingMainImageUrl!, width: double.infinity, height: 180, fit: BoxFit.cover),
                              ),
                              Container(
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(12),
                                  color: Colors.black38,
                                ),
                              ),
                              const Center(
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(Icons.edit_outlined, color: Colors.white, size: 28),
                                    SizedBox(height: 4),
                                    Text(
                                      'Cambiar Imagen Principal',
                                      style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          )
                        : Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: kPrimary.withOpacity(0.05),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.add_photo_alternate_outlined, size: 32, color: kPrimary),
                              ),
                              const SizedBox(height: 10),
                              const Text(
                                'Imagen Principal *',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: kTextDark),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                'Presiona para subir la imagen de portada',
                                style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
                              ),
                            ],
                          ),
              ),
            ),
            if (widget.itemId != null) ...[
              const SizedBox(height: 6),
              const Text(
                'Nota: Reemplazar la imagen principal eliminará las imágenes adicionales actuales y requerirá volver a subirlas.',
                style: TextStyle(color: Colors.orange, fontSize: 11, fontWeight: FontWeight.w500),
              ),
            ],
            const SizedBox(height: 16),

            // Selector imágenes adicionales
            Builder(
              builder: (context) {
                final totalAdicionales = _existingAdditionalImages.length + _additionalImages.length;
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Imágenes adicionales (Opcional) - $totalAdicionales/4',
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: kTextDark),
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      height: 80,
                      child: ListView(
                        scrollDirection: Axis.horizontal,
                        physics: const BouncingScrollPhysics(),
                        children: [
                          if (totalAdicionales < 4) ...[
                            GestureDetector(
                              onTap: _pickAdditionalImages,
                              child: Container(
                                width: 80,
                                height: 80,
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: Colors.grey.shade200),
                                ),
                                child: const Icon(Icons.add_a_photo_outlined, color: kTextGray, size: 22),
                              ),
                            ),
                            const SizedBox(width: 8),
                          ],
                          // Mostrar existentes
                          ...List.generate(_existingAdditionalImages.length, (index) {
                            final img = _existingAdditionalImages[index];
                            final String? imgUrl = img['image_url'] as String?;
                            return Container(
                              margin: const EdgeInsets.only(right: 8),
                              width: 80,
                              height: 80,
                              child: Stack(
                                children: [
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(8),
                                    child: imgUrl != null 
                                        ? Image.network(imgUrl, width: 80, height: 80, fit: BoxFit.cover)
                                        : Container(color: Colors.grey.shade200, child: const Icon(Icons.image)),
                                  ),
                                  Positioned(
                                    right: 2,
                                    top: 2,
                                    child: GestureDetector(
                                      onTap: () {
                                        setState(() {
                                          _existingAdditionalImages.removeAt(index);
                                        });
                                      },
                                      child: Container(
                                        padding: const EdgeInsets.all(2),
                                        decoration: const BoxDecoration(color: Colors.black54, shape: BoxShape.circle),
                                        child: const Icon(Icons.close, color: Colors.white, size: 14),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }),
                          // Mostrar nuevas
                          ...List.generate(_additionalImages.length, (index) {
                            final img = _additionalImages[index];
                            return Container(
                              margin: const EdgeInsets.only(right: 8),
                              width: 80,
                              height: 80,
                              child: Stack(
                                children: [
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(8),
                                    child: kIsWeb
                                        ? Image.network(img.path, width: 80, height: 80, fit: BoxFit.cover)
                                        : Image.file(File(img.path), width: 80, height: 80, fit: BoxFit.cover),
                                  ),
                                  Positioned(
                                    right: 2,
                                    top: 2,
                                    child: GestureDetector(
                                      onTap: () {
                                        setState(() {
                                          _additionalImages.removeAt(index);
                                        });
                                      },
                                      child: Container(
                                        padding: const EdgeInsets.all(2),
                                        decoration: const BoxDecoration(color: Colors.black54, shape: BoxShape.circle),
                                        child: const Icon(Icons.close, color: Colors.white, size: 14),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }),
                        ],
                      ),
                    ),
                  ],
                );
              }
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
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: _prevStep,
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    child: const Text('Anterior'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _nextStep,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: kPrimary,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    child: const Text('Siguiente', style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold)),
                  ),
                ),
              ],
            ),
          ],
        ),
      );

  Widget _buildStep3() => Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade200),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _titulo('Detalles del producto'),
            const SizedBox(height: 16),

            // Descripción (max 250 characters, required)
            _titulo('Descripción *'),
            const SizedBox(height: 6),
            TextFormField(
              controller: _descCtrl,
              maxLines: 4,
              maxLength: 250,
              decoration: InputDecoration(
                hintText: 'Describe tu producto: estado, características, etc.',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                filled: true,
                fillColor: Colors.white,
                counterText: '${_descCtrl.text.length}/250',
              ),
              onChanged: (val) => setState(() {}),
              validator: (v) => (v == null || v.isEmpty) ? 'La descripción es obligatoria' : null,
            ),
            const SizedBox(height: 12),

            // Estado y Modalidad side-by-side
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _titulo('Estado *'),
                      const SizedBox(height: 6),
                      DropdownButtonFormField<int>(
                        value: _condicion,
                        decoration: InputDecoration(
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                          filled: true,
                          fillColor: Colors.white,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                        ),
                        items: const [
                          DropdownMenuItem(value: 1, child: Text('Nuevo')),
                          DropdownMenuItem(value: 2, child: Text('Como nuevo')),
                          DropdownMenuItem(value: 3, child: Text('Buen estado')),
                          DropdownMenuItem(value: 4, child: Text('Aceptable')),
                        ],
                        onChanged: (v) => setState(() => _condicion = v ?? 1),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _titulo('Modalidad *'),
                      const SizedBox(height: 6),
                      DropdownButtonFormField<int>(
                        value: _tipoTrans,
                        decoration: InputDecoration(
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                          filled: true,
                          fillColor: Colors.white,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                        ),
                        items: const [
                          DropdownMenuItem(value: 1, child: Text('Venta')),
                          DropdownMenuItem(value: 2, child: Text('Intercambio')),
                          DropdownMenuItem(value: 3, child: Text('Venta o Intercambio')),
                        ],
                        onChanged: (v) => setState(() => _tipoTrans = v ?? 1),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),

            // Dimensiones colapsable
            Container(
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade200),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                children: [
                  ListTile(
                    title: const Text('Dimensiones y peso (Opcional)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
                    leading: const Icon(Icons.line_weight, color: kPrimary),
                    trailing: Icon(_showDimensions ? Icons.keyboard_arrow_up : Icons.keyboard_arrow_down),
                    onTap: () => setState(() => _showDimensions = !_showDimensions),
                  ),
                  if (_showDimensions)
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          Row(
                            children: [
                              Expanded(child: _campo(_pesoCtrl, 'Peso (lbs)', keyboardType: TextInputType.number)),
                              const SizedBox(width: 12),
                              Expanded(child: _campo(_altoCtrl, 'Alto (cm)', keyboardType: TextInputType.number)),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              Expanded(child: _campo(_anchoCtrl, 'Ancho (cm)', keyboardType: TextInputType.number)),
                              const SizedBox(width: 12),
                              Expanded(child: _campo(_profundoCtrl, 'Profundidad (cm)', keyboardType: TextInputType.number)),
                            ],
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Colores y stock colapsable
            Container(
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade200),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                children: [
                  ListTile(
                    title: const Text('Colores y stock (Opcional)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
                    leading: const Icon(Icons.color_lens, color: kPrimary),
                    trailing: Icon(_showColors ? Icons.keyboard_arrow_up : Icons.keyboard_arrow_down),
                    onTap: () => setState(() => _showColors = !_showColors),
                  ),
                  if (_showColors)
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (_selectedColors.isEmpty)
                            const Padding(
                              padding: EdgeInsets.symmetric(vertical: 8),
                              child: Text('Ningún color seleccionado.', style: TextStyle(fontSize: 13, color: kTextGray)),
                            )
                          else
                            ..._selectedColors.entries.map((entry) {
                              final int colorId = entry.key;
                              final String nombre = entry.value['nombre'] as String;
                              final String hex = entry.value['codigo_hex'] as String;
                              final TextEditingController ctrl = entry.value['stockCtrl'] as TextEditingController;

                              Color displayColor = Colors.grey;
                              try {
                                final hexString = hex.replaceAll('#', '');
                                displayColor = Color(int.parse('FF$hexString', radix: 16));
                              } catch (_) {}

                              return Padding(
                                padding: const EdgeInsets.only(bottom: 8.0),
                                child: Row(
                                  children: [
                                    Container(
                                      width: 16,
                                      height: 16,
                                      decoration: BoxDecoration(
                                        color: displayColor,
                                        shape: BoxShape.circle,
                                        border: Border.all(color: Colors.grey.shade300),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Text(nombre, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
                                    ),
                                    const SizedBox(width: 8),
                                    SizedBox(
                                      width: 80,
                                      height: 38,
                                      child: TextFormField(
                                        controller: ctrl,
                                        keyboardType: TextInputType.number,
                                        decoration: const InputDecoration(
                                          labelText: 'Stock',
                                          contentPadding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          border: OutlineInputBorder(),
                                        ),
                                      ),
                                    ),
                                    IconButton(
                                      icon: const Icon(Icons.delete_outline, color: Colors.redAccent, size: 20),
                                      onPressed: () {
                                        setState(() {
                                          ctrl.dispose();
                                          _selectedColors.remove(colorId);
                                        });
                                      },
                                    ),
                                  ],
                                ),
                              );
                            }),
                          const SizedBox(height: 8),
                          OutlinedButton.icon(
                            onPressed: _showColorPickerDialog,
                            icon: const Icon(Icons.add, size: 18),
                            label: const Text('Agregar Color', style: TextStyle(fontSize: 13)),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
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
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: _prevStep,
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    child: const Text('Anterior'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _saving ? null : _publicar,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: kPrimary,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    child: _saving
                        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : Text(widget.itemId != null ? 'Guardar cambios' : 'Publicar producto', style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold)),
                  ),
                ),
              ],
            ),
          ],
        ),
      );

  void _showColorPickerDialog() {
    String searchQuery = '';
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (BuildContext context) {
        return StatefulBuilder(
          builder: (BuildContext context, StateSetter setModalState) {
            final filteredList = _allColors.where((c) {
              final String name = (c['nombre'] ?? '').toString().toLowerCase();
              return name.contains(searchQuery.toLowerCase());
            }).toList();

            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
                left: 16,
                right: 16,
                top: 16,
              ),
              child: SizedBox(
                height: 450,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Seleccionar Color',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: kTextDark),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.pop(context),
                        )
                      ],
                    ),
                    const SizedBox(height: 8),
                    TextField(
                      decoration: InputDecoration(
                        hintText: 'Buscar color...',
                        prefixIcon: const Icon(Icons.search),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      ),
                      onChanged: (val) {
                        setModalState(() {
                          searchQuery = val;
                        });
                      },
                    ),
                    const SizedBox(height: 12),
                    Expanded(
                      child: filteredList.isEmpty
                          ? const Center(child: Text('No se encontraron colores.'))
                          : ListView.builder(
                              itemCount: filteredList.length,
                              itemBuilder: (context, idx) {
                                final color = filteredList[idx];
                                final int colorId = color['id_color'] as int;
                                final String nombre = color['nombre'] as String;
                                final String hex = color['codigo_hex'] as String;
                                final bool isSelected = _selectedColors.containsKey(colorId);

                                // Parse hex color
                                Color displayColor = Colors.grey;
                                try {
                                  final hexString = hex.replaceAll('#', '');
                                  displayColor = Color(int.parse('FF$hexString', radix: 16));
                                } catch (_) {}

                                return ListTile(
                                  leading: Container(
                                    width: 24,
                                    height: 24,
                                    decoration: BoxDecoration(
                                      color: displayColor,
                                      shape: BoxShape.circle,
                                      border: Border.all(color: Colors.grey.shade300),
                                    ),
                                  ),
                                  title: Text(nombre, style: const TextStyle(fontSize: 14)),
                                  trailing: isSelected
                                      ? const Icon(Icons.check_circle, color: kPrimary)
                                      : null,
                                  onTap: () {
                                    setState(() {
                                      if (isSelected) {
                                        // Remove
                                        final ctrl = _selectedColors[colorId]?['stockCtrl'] as TextEditingController?;
                                        ctrl?.dispose();
                                        _selectedColors.remove(colorId);
                                      } else {
                                        // Add
                                        _selectedColors[colorId] = {
                                          'nombre': nombre,
                                          'codigo_hex': hex,
                                          'stockCtrl': TextEditingController(text: '1'),
                                        };
                                      }
                                    });
                                    setModalState(() {}); // update modal state too
                                  },
                                );
                              },
                            ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _titulo(String t) => Text(
        t,
        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark),
      );

  Widget _campo(TextEditingController ctrl, String label,
      {bool required = false, TextInputType? keyboardType, int maxLines = 1}) =>
    TextFormField(
      controller: ctrl,
      keyboardType: keyboardType,
      maxLines: maxLines,
      decoration: InputDecoration(
        labelText: label,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
        filled: true,
        fillColor: Colors.white,
      ),
      validator: required ? (v) => (v == null || v.isEmpty) ? 'Campo requerido' : null : null,
    );
}
