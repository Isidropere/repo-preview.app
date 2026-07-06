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
  int? _condicion      = 1;   // 1=Nuevo, 2=Como nuevo, 3=Usado - Buen estado, 4=Usado - Aceptable
  int? _tipoTrans;            // Null por defecto para obligar a seleccionar
  bool _saving         = false;
  int _estatus         = 1;   // 1=Activo, 2=Pausado (Inactivo) by default
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

  bool _showDimensions = true;
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
          final parsedCondicion = int.tryParse(item['condicion']?.toString() ?? '') ?? 1;
          _condicion = (parsedCondicion >= 1 && parsedCondicion <= 4) ? parsedCondicion : 1;
          final parsedTipoTrans = int.tryParse(item['tipo_trans']?.toString() ?? '') ?? 1;
          _tipoTrans = (parsedTipoTrans >= 1 && parsedTipoTrans <= 3) ? parsedTipoTrans : 1;
          final parsedEstatus = int.tryParse(item['estatus']?.toString() ?? '') ?? 2;
          _estatus = (parsedEstatus == 1 || parsedEstatus == 2) ? parsedEstatus : 2;

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

  bool _isVideoPath(String path) {
    final p = path.toLowerCase();
    return p.endsWith('.mp4') || p.endsWith('.mov') || p.endsWith('.m4v') || p.endsWith('.3gp') || p.endsWith('.avi');
  }

  Future<void> _pickMainImage() async {
    _mostrarOpcionesMedia(isMain: true);
  }

  Future<void> _pickAdditionalImages() async {
    _mostrarOpcionesMedia(isMain: false);
  }

  Future<void> _pickMainMediaFromSource(ImageSource source, {required bool isVideo}) async {
    try {
      XFile? picked;
      if (isVideo) {
        picked = await _picker.pickVideo(
          source: source,
          maxDuration: const Duration(seconds: 30),
        );
      } else {
        picked = await _picker.pickImage(
          source: source,
          maxWidth: 1200,
          maxHeight: 1200,
          imageQuality: 85,
        );
      }

      if (picked != null) {
        setState(() {
          _mainImage = picked;
          _error = '';
        });
      }
    } catch (e) {
      setState(() => _error = 'Error al seleccionar archivo: $e');
    }
  }

  Future<void> _pickAdditionalMediaFromSource(ImageSource source, {required bool isVideo}) async {
    final totalActual = _existingAdditionalImages.length + _additionalImages.length;
    if (totalActual >= 4) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Solo puedes agregar hasta 4 archivos opcionales.'),
        backgroundColor: Colors.orange,
      ));
      return;
    }

    try {
      if (isVideo) {
        final XFile? picked = await _picker.pickVideo(
          source: source,
          maxDuration: const Duration(seconds: 30),
        );
        if (picked != null) {
          setState(() {
            _additionalImages.add(picked);
          });
        }
      } else {
        if (source == ImageSource.gallery) {
          final List<XFile> picked = await _picker.pickMultiImage(
            maxWidth: 1200,
            maxHeight: 1200,
            imageQuality: 80,
          );
          if (picked.isNotEmpty) {
            setState(() {
              final espacioDisponible = 4 - totalActual;
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
        } else {
          final XFile? picked = await _picker.pickImage(
            source: ImageSource.camera,
            maxWidth: 1200,
            maxHeight: 1200,
            imageQuality: 85,
          );
          if (picked != null) {
            setState(() {
              _additionalImages.add(picked);
            });
          }
        }
      }
    } catch (e) {
      setState(() => _error = 'Error al seleccionar archivos: $e');
    }
  }

  void _mostrarOpcionesMedia({required bool isMain}) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(
                  isMain ? 'Seleccionar archivo principal' : 'Agregar archivo opcional',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: kTextDark,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Elige el tipo de archivo y origen',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 13,
                    color: kTextGray,
                  ),
                ),
                const SizedBox(height: 20),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: kPrimary.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.photo_library, color: kPrimary),
                  ),
                  title: const Text(
                    'Imagen de Galería',
                    style: TextStyle(fontWeight: FontWeight.w600, color: kTextDark),
                  ),
                  onTap: () {
                    Navigator.pop(context);
                    if (isMain) {
                      _pickMainMediaFromSource(ImageSource.gallery, isVideo: false);
                    } else {
                      _pickAdditionalMediaFromSource(ImageSource.gallery, isVideo: false);
                    }
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: kSecondary.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.camera_alt, color: kSecondary),
                  ),
                  title: const Text(
                    'Tomar Foto (Cámara)',
                    style: TextStyle(fontWeight: FontWeight.w600, color: kTextDark),
                  ),
                  onTap: () {
                    Navigator.pop(context);
                    if (isMain) {
                      _pickMainMediaFromSource(ImageSource.camera, isVideo: false);
                    } else {
                      _pickAdditionalMediaFromSource(ImageSource.camera, isVideo: false);
                    }
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.red.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.video_library, color: Colors.red),
                  ),
                  title: const Text(
                    'Video de Galería',
                    style: TextStyle(fontWeight: FontWeight.w600, color: kTextDark),
                  ),
                  onTap: () {
                    Navigator.pop(context);
                    if (isMain) {
                      _pickMainMediaFromSource(ImageSource.gallery, isVideo: true);
                    } else {
                      _pickAdditionalMediaFromSource(ImageSource.gallery, isVideo: true);
                    }
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.deepOrange.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.videocam, color: Colors.deepOrange),
                  ),
                  title: const Text(
                    'Grabar Video (Cámara)',
                    style: TextStyle(fontWeight: FontWeight.w600, color: kTextDark),
                  ),
                  onTap: () {
                    Navigator.pop(context);
                    if (isMain) {
                      _pickMainMediaFromSource(ImageSource.camera, isVideo: true);
                    } else {
                      _pickAdditionalMediaFromSource(ImageSource.camera, isVideo: true);
                    }
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _publicar() async {
    if (_tipoTrans == null) {
      setState(() {
        _error = 'Debe seleccionar una modalidad de negocio.';
      });
      return;
    }

    final desc = _descCtrl.text.trim();
    if (desc.isEmpty) {
      setState(() => _error = 'La descripción es obligatoria.');
      return;
    }

    if (_tipoTrans == 1 || _tipoTrans == 3) {
      final precioStr = _precioCtrl.text.trim();
      if (precioStr.isEmpty || double.tryParse(precioStr) == null || double.parse(precioStr) < 0) {
        setState(() {
          _error = 'El precio es obligatorio si la modalidad incluye venta.';
          _step = 1;
        });
        return;
      }
    }

    final pesoStr = _pesoCtrl.text.trim();
    final altoStr = _altoCtrl.text.trim();
    final anchoStr = _anchoCtrl.text.trim();
    final profundoStr = _profundoCtrl.text.trim();

    if (pesoStr.isEmpty || double.tryParse(pesoStr) == null || double.parse(pesoStr) <= 0) {
      setState(() {
        _error = 'El peso es obligatorio y debe ser mayor que 0.';
        _showDimensions = true;
      });
      return;
    }
    if (altoStr.isEmpty || double.tryParse(altoStr) == null || double.parse(altoStr) <= 0) {
      setState(() {
        _error = 'El alto es obligatorio y debe ser mayor que 0.';
        _showDimensions = true;
      });
      return;
    }
    if (anchoStr.isEmpty || double.tryParse(anchoStr) == null || double.parse(anchoStr) <= 0) {
      setState(() {
        _error = 'El ancho es obligatorio y debe ser mayor que 0.';
        _showDimensions = true;
      });
      return;
    }
    if (profundoStr.isEmpty || double.tryParse(profundoStr) == null || double.parse(profundoStr) <= 0) {
      setState(() {
        _error = 'La profundidad es obligatoria y debe ser mayor que 0.';
        _showDimensions = true;
      });
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
        'tipo_trans':        _tipoTrans?.toString() ?? '',
        'id_categoria_item': _idCategoria.toString(),
        'peso_lbs':          _pesoCtrl.text.trim(),
        'alto_cm':           _altoCtrl.text.trim(),
        'ancho_cm':          _anchoCtrl.text.trim(),
        'profundo_cm':       _profundoCtrl.text.trim(),
        'estatus':           _estatus.toString(),
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
        String errMsg = body['message'] ?? 'Error al guardar.';
        if (body['errors'] != null && body['errors'] is Map) {
          final errs = body['errors'] as Map;
          final List<String> messages = [];
          errs.forEach((key, val) {
            if (val is List) {
              messages.addAll(val.map((e) => e.toString()));
            } else {
              messages.add(val.toString());
            }
          });
          if (messages.isNotEmpty) {
            errMsg = messages.join('\n');
          }
        }
        setState(() => _error = errMsg);
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
                  // Nuevo Indicador de pasos moderno
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.grey.shade100, width: 1.5),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              _step == 1
                                  ? 'Paso 1 de 3: Información Básica'
                                  : (_step == 2 ? 'Paso 2 de 3: Multimedia' : 'Paso 3 de 3: Detalles'),
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                                color: kTextDark,
                              ),
                            ),
                            Text(
                              '${(_step / 3.0 * 100).toInt()}% completado',
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: kPrimary,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(4),
                          child: LinearProgressIndicator(
                            value: _step / 3.0,
                            backgroundColor: kPrimary.withOpacity(0.1),
                            color: kPrimary,
                            minHeight: 6,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  if (_step == 1) _buildStep1(),
                  if (_step == 2) _buildStep2(),
                  if (_step == 3) _buildStep3(),
                ],
              ),
            ),
    );
  }

  Widget _buildStep1() => Form(
        key: _formKey,
        child: Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.grey.shade100, width: 1.5),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.02),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Información básica',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: kTextDark),
              ),
              const SizedBox(height: 16),
              _campo(_nombreCtrl, 'Nombre del artículo *', required: true),
              const SizedBox(height: 16),
              
              // Precio y Descuento en fila
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: _campo(_precioCtrl, (_tipoTrans == 1 || _tipoTrans == 3) ? 'Precio (RD\$) *' : 'Precio (RD\$) (Opcional)', required: false, keyboardType: TextInputType.number),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: _campo(_descuentoCtrl, 'Descuento (%)', required: false, keyboardType: TextInputType.number),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Cantidad y Categoría en fila
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    flex: 1,
                    child: _campo(_cantidadCtrl, 'Cantidad *', required: true, keyboardType: TextInputType.number),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    flex: 2,
                    child: DropdownButtonFormField<int>(
                      value: _idCategoria,
                      style: const TextStyle(fontSize: 14, color: kTextDark, fontWeight: FontWeight.w500),
                      decoration: InputDecoration(
                        labelText: 'Categoría *',
                        labelStyle: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                        prefixIcon: const Icon(Icons.category_outlined, color: kPrimary, size: 20),
                        filled: true,
                        fillColor: Colors.grey.shade50,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide(color: Colors.grey.shade200, width: 1.5),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: kPrimary, width: 2),
                        ),
                      ),
                      items: _categorias.map<DropdownMenuItem<int>>((c) =>
                        DropdownMenuItem(value: ApiClient.parseInt(c['id_categoria_item']) ?? 0, child: Text(c['categoria'].toString(), overflow: TextOverflow.ellipsis))).toList(),
                      onChanged: (v) {
                        setState(() => _idCategoria = v);
                        if (v == 11) {
                          showDialog(
                            context: context,
                            builder: (context) => AlertDialog(
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              title: const Row(
                                children: [
                                  Icon(Icons.warning, color: Colors.orange),
                                  SizedBox(width: 8),
                                  Text('Categoría Adultos'),
                                ],
                              ),
                              content: const Text('Has seleccionado la categoría Adultos. Recuerda que el contenido debe ser exclusivo para mayores de 18 años.'),
                              actions: [
                                TextButton(
                                  onPressed: () => Navigator.pop(context),
                                  child: const Text('Entendido'),
                                ),
                              ],
                            ),
                          );
                        }
                      },
                      validator: (v) => v == null ? 'Selecciona una categoría' : null,
                    ),
                  ),
                ],
              ),
              
              if (_error.isNotEmpty) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(12)),
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
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    elevation: 0,
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
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.grey.shade100, width: 1.5),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.02),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Imágenes del producto',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: kTextDark),
            ),
            const SizedBox(height: 16),
            
            // Selector imagen principal
            GestureDetector(
              onTap: _pickMainImage,
              child: Container(
                width: double.infinity,
                height: 180,
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: (_mainImage != null || _existingMainImageUrl != null)
                        ? kPrimary
                        : Colors.grey.shade300,
                    width: (_mainImage != null || _existingMainImageUrl != null) ? 2.0 : 1.5,
                  ),
                ),
                child: _mainImage != null
                    ? Stack(
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(16),
                            child: _isVideoPath(_mainImage!.path)
                                ? Container(
                                    width: double.infinity,
                                    height: 180,
                                    color: Colors.black,
                                    child: const Center(
                                      child: Column(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Icon(Icons.play_circle_fill, color: Colors.white, size: 50),
                                          SizedBox(height: 8),
                                          Text('Video Seleccionado', style: TextStyle(color: Colors.white70, fontSize: 12)),
                                        ],
                                      ),
                                    ),
                                  )
                                : (kIsWeb
                                    ? Image.network(_mainImage!.path, width: double.infinity, height: 180, fit: BoxFit.cover)
                                    : Image.file(File(_mainImage!.path), width: double.infinity, height: 180, fit: BoxFit.cover)),
                          ),
                          Container(
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(16),
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
                                  'Cambiar Archivo Principal',
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
                                borderRadius: BorderRadius.circular(16),
                                child: _isVideoPath(_existingMainImageUrl!)
                                    ? Container(
                                        width: double.infinity,
                                        height: 180,
                                        color: Colors.black,
                                        child: const Center(
                                          child: Column(
                                            mainAxisAlignment: MainAxisAlignment.center,
                                            children: [
                                              Icon(Icons.play_circle_fill, color: Colors.white, size: 50),
                                              SizedBox(height: 8),
                                              Text('Video Principal Guardado', style: TextStyle(color: Colors.white70, fontSize: 12)),
                                            ],
                                          ),
                                        ),
                                      )
                                    : Image.network(
                                        _existingMainImageUrl!,
                                        width: double.infinity,
                                        height: 180,
                                        fit: BoxFit.cover,
                                        errorBuilder: (_, __, ___) => Container(
                                          color: Colors.grey.shade100,
                                          child: const Icon(Icons.broken_image_outlined, color: Colors.grey, size: 40),
                                        ),
                                      ),
                              ),
                              Container(
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(16),
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
                                padding: const EdgeInsets.all(16),
                                decoration: BoxDecoration(
                                  color: kPrimary.withOpacity(0.08),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.cloud_upload_outlined, size: 36, color: kPrimary),
                              ),
                              const SizedBox(height: 12),
                              const Text(
                                'Imagen Principal *',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: kTextDark),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Toca aquí para seleccionar una imagen o video de portada',
                                style: TextStyle(color: Colors.grey.shade500, fontSize: 12),
                              ),
                            ],
                          ),
              ),
            ),
            if (widget.itemId != null) ...[
              const SizedBox(height: 8),
              const Text(
                'Nota: Reemplazar la imagen principal eliminará las imágenes adicionales actuales y requerirá volver a subirlas.',
                style: TextStyle(color: Colors.orange, fontSize: 11, fontWeight: FontWeight.w500),
              ),
            ],
            const SizedBox(height: 20),

            // Selector imágenes adicionales
            Builder(
              builder: (context) {
                final totalAdicionales = _existingAdditionalImages.length + _additionalImages.length;
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Imágenes adicionales (Opcional) - $totalAdicionales/4',
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: kTextDark),
                    ),
                    const SizedBox(height: 10),
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
                                  color: Colors.grey.shade50,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: Colors.grey.shade300, width: 1.5),
                                ),
                                child: const Icon(Icons.add_a_photo_outlined, color: kPrimary, size: 24),
                              ),
                            ),
                            const SizedBox(width: 8),
                          ],
                          // Mostrar existentes
                          ...List.generate(_existingAdditionalImages.length, (index) {
                            final img = _existingAdditionalImages[index];
                            final String? imgUrl = img['image_url']?.toString();
                            return Container(
                              margin: const EdgeInsets.only(right: 8),
                              width: 80,
                              height: 80,
                              child: Stack(
                                children: [
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(12),
                                    child: imgUrl != null 
                                        ? (_isVideoPath(imgUrl)
                                            ? Container(
                                                width: 80,
                                                height: 80,
                                                color: Colors.black87,
                                                child: const Icon(Icons.play_circle_fill, color: Colors.white, size: 28),
                                              )
                                            : Image.network(
                                                imgUrl,
                                                width: 80,
                                                height: 80,
                                                fit: BoxFit.cover,
                                              ))
                                        : Container(color: Colors.grey),
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
                          // Mostrar locales
                          ...List.generate(_additionalImages.length, (index) {
                            final img = _additionalImages[index];
                            return Container(
                              margin: const EdgeInsets.only(right: 8),
                              width: 80,
                              height: 80,
                              child: Stack(
                                children: [
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(12),
                                    child: _isVideoPath(img.path)
                                        ? Container(
                                            width: 80,
                                            height: 80,
                                            color: Colors.black87,
                                            child: const Icon(Icons.play_circle_fill, color: Colors.white, size: 28),
                                          )
                                        : (kIsWeb
                                            ? Image.network(img.path, width: 80, height: 80, fit: BoxFit.cover)
                                            : Image.file(File(img.path), width: 80, height: 80, fit: BoxFit.cover)),
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
                decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(12)),
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
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      side: const BorderSide(color: Colors.grey, width: 1.5),
                    ),
                    child: const Text('Anterior', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _nextStep,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: kPrimary,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 0,
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
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.grey.shade100, width: 1.5),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.02),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Detalles del producto',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: kTextDark),
            ),
            const SizedBox(height: 20),

            // Descripción (max 250 characters, required)
            const Text(
              'Descripción *',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: kTextDark),
            ),
            const SizedBox(height: 8),
            TextFormField(
              controller: _descCtrl,
              maxLines: 4,
              maxLength: 250,
              style: const TextStyle(fontSize: 14, color: kTextDark, fontWeight: FontWeight.w500),
              decoration: InputDecoration(
                hintText: 'Describe tu producto: estado, características, etc.',
                hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                filled: true,
                fillColor: Colors.grey.shade50,
                contentPadding: const EdgeInsets.all(16),
                counterText: '',
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: Colors.grey.shade200, width: 1.5),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: kPrimary, width: 2),
                ),
              ),
              onChanged: (val) => setState(() {}),
              validator: (v) => (v == null || v.isEmpty) ? 'La descripción es obligatoria' : null,
            ),
            const SizedBox(height: 6),
            Align(
              alignment: Alignment.centerRight,
              child: Text(
                '${_descCtrl.text.length}/250 caracteres',
                style: TextStyle(color: Colors.grey.shade500, fontSize: 11, fontWeight: FontWeight.w500),
              ),
            ),
            const SizedBox(height: 16),

            // Estado y Modalidad side-by-side
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      DropdownButtonFormField<int>(
                        value: _condicion,
                        style: const TextStyle(fontSize: 14, color: kTextDark, fontWeight: FontWeight.w500),
                        decoration: InputDecoration(
                          labelText: 'Estado *',
                          labelStyle: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                          prefixIcon: const Icon(Icons.star_outline, color: kPrimary, size: 20),
                          filled: true,
                          fillColor: Colors.grey.shade50,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(color: Colors.grey.shade200, width: 1.5),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: kPrimary, width: 2),
                          ),
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
                      DropdownButtonFormField<int>(
                        value: _tipoTrans,
                        hint: const Text('Modalidad *', style: TextStyle(fontSize: 13, color: Colors.grey)),
                        style: const TextStyle(fontSize: 14, color: kTextDark, fontWeight: FontWeight.w500),
                        decoration: InputDecoration(
                          labelText: 'Modalidad de negocio *',
                          labelStyle: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                          prefixIcon: const Icon(Icons.handshake_outlined, color: kPrimary, size: 20),
                          filled: true,
                          fillColor: Colors.grey.shade50,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(color: Colors.grey.shade200, width: 1.5),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: kPrimary, width: 2),
                          ),
                        ),
                        items: const [
                          DropdownMenuItem(value: 1, child: Text('Venta')),
                          DropdownMenuItem(value: 2, child: Text('Intercambio')),
                          DropdownMenuItem(value: 3, child: Text('Venta o Intercambio')),
                        ],
                        onChanged: (v) => setState(() => _tipoTrans = v),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<int>(
              value: _estatus,
              style: const TextStyle(fontSize: 14, color: kTextDark, fontWeight: FontWeight.w500),
              decoration: InputDecoration(
                labelText: 'Estatus *',
                labelStyle: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                prefixIcon: const Icon(Icons.toggle_on_outlined, color: kPrimary, size: 20),
                filled: true,
                fillColor: Colors.grey.shade50,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: Colors.grey.shade200, width: 1.5),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: kPrimary, width: 2),
                ),
              ),
              items: const [
                DropdownMenuItem(value: 1, child: Text('Activo')),
                DropdownMenuItem(value: 2, child: Text('Pausado (Inactivo)')),
              ],
              onChanged: (v) => setState(() => _estatus = v ?? 2),
            ),
            const SizedBox(height: 20),

            // Dimensiones colapsable
            Container(
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade200, width: 1.2),
                borderRadius: BorderRadius.circular(12),
                color: Colors.grey.shade50,
              ),
              child: Column(
                children: [
                  ListTile(
                    title: const Text('Dimensiones y peso *', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
                    leading: const Icon(Icons.square_foot_outlined, color: kPrimary),
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
                border: Border.all(color: Colors.grey.shade200, width: 1.2),
                borderRadius: BorderRadius.circular(12),
                color: Colors.grey.shade50,
              ),
              child: Column(
                children: [
                  ListTile(
                    title: const Text('Colores y stock (Opcional)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark)),
                    leading: const Icon(Icons.palette_outlined, color: kPrimary),
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
                                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                                        decoration: InputDecoration(
                                          labelText: 'Stock',
                                          labelStyle: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                                          contentPadding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          enabledBorder: OutlineInputBorder(
                                            borderRadius: BorderRadius.circular(8),
                                            borderSide: BorderSide(color: Colors.grey.shade300),
                                          ),
                                          focusedBorder: OutlineInputBorder(
                                            borderRadius: BorderRadius.circular(8),
                                            borderSide: const BorderSide(color: kPrimary),
                                          ),
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
                decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(12)),
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
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      side: const BorderSide(color: Colors.grey, width: 1.5),
                    ),
                    child: const Text('Anterior', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _saving ? null : _publicar,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: kPrimary,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 0,
                    ),
                    child: _saving
                        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : Text(widget.itemId != null ? 'Guardar cambios' : 'Publicar', style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold)),
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
                                final int colorId = ApiClient.parseInt(color['id_color']) ?? 0;
                                final String nombre = color['nombre']?.toString() ?? '';
                                final String hex = color['codigo_hex']?.toString() ?? '';
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
      {bool required = false, TextInputType? keyboardType, int maxLines = 1}) {
    IconData? prefixIcon;
    if (label.contains('Nombre')) {
      prefixIcon = Icons.shopping_bag_outlined;
    } else if (label.contains('Precio')) {
      prefixIcon = Icons.monetization_on_outlined;
    } else if (label.contains('Descuento')) {
      prefixIcon = Icons.percent_outlined;
    } else if (label.contains('Cantidad')) {
      prefixIcon = Icons.inventory_2_outlined;
    } else if (label.contains('Peso') || label.contains('Alto') || label.contains('Ancho') || label.contains('Profundidad')) {
      prefixIcon = Icons.square_foot_outlined;
    }

    return TextFormField(
      controller: ctrl,
      keyboardType: keyboardType,
      maxLines: maxLines,
      style: const TextStyle(fontSize: 14, color: kTextDark, fontWeight: FontWeight.w500),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: TextStyle(color: Colors.grey.shade600, fontSize: 13),
        prefixIcon: prefixIcon != null ? Icon(prefixIcon, color: kPrimary, size: 20) : null,
        filled: true,
        fillColor: Colors.grey.shade50,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade200, width: 1.5),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: kPrimary, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.redAccent, width: 1.5),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.redAccent, width: 2),
        ),
      ),
      validator: required ? (v) => (v == null || v.isEmpty) ? 'Este campo es obligatorio' : null : null,
    );
  }
}
