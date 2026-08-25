import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';
import '../core/api_client.dart';
import '../core/analytics_service.dart';
import '../core/theme.dart';
import 'hoja_vida_screen.dart';

class PublicarTalentoScreen extends StatefulWidget {
  final int? itemId;
  const PublicarTalentoScreen({super.key, this.itemId});

  @override
  State<PublicarTalentoScreen> createState() => _PublicarTalentoScreenState();
}

class _PublicarTalentoScreenState extends State<PublicarTalentoScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nombreCtrl = TextEditingController();
  final _precioCtrl = TextEditingController();
  final _descCtrl = TextEditingController();
  final _imgUrlCtrl = TextEditingController();
  final _cantidadCtrl = TextEditingController(text: '1');

  int _tipoTrans = 3; // 1=Venta, 2=Intercambio, 3=Ambos
  int _step = 1;

  // Selector de imágenes
  final ImagePicker _picker = ImagePicker();
  XFile? _mainImage;
  final List<XFile> _additionalImages = [];

  // Datos para edición
  bool _loadingItem = false;
  String? _existingMainImageUrl;
  List<Map<String, dynamic>> _existingImages = [];

  double _montoRegistro = 150.0;
  bool _loadingConfig = true;
  bool _saving = false;
  String? _error;
  int _estatus = 2; // Default to 2 (Pausado)

  @override
  void initState() {
    super.initState();
    _loadData();
    if (widget.itemId != null) {
      _loadItemDetails();
    }
  }

  @override
  void dispose() {
    _nombreCtrl.dispose();
    _precioCtrl.dispose();
    _descCtrl.dispose();
    _imgUrlCtrl.dispose();
    _cantidadCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _loadingConfig = true);
    try {
      final results = await Future.wait([
        ApiClient.get('/talentos/config', auth: true),
        ApiClient.get('/hoja-vida', auth: true, useCache: false),
      ]);

      if (results[0].statusCode == 200) {
        final body = jsonDecode(results[0].body);
        _montoRegistro = (body['monto_registro'] as num?)?.toDouble() ?? 150.0;
      }
      if (results[1].statusCode == 200 && widget.itemId == null) {
        final body = jsonDecode(results[1].body);
        final tieneHoja = body['tiene_hoja_vida'] as bool? ?? false;
        if (!tieneHoja) {
          setState(() {
            _loadingConfig = false;
          });
          _mostrarAlertaHojaVida();
          return;
        }
      }
    } catch (e) {
      _error = 'Error de conexión con el servidor.';
    } finally {
      setState(() => _loadingConfig = false);
    }
  }

  void _mostrarAlertaHojaVida() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Completar Hoja de Vida', style: TextStyle(fontWeight: FontWeight.bold)),
        content: const Text(
          'Debes completar tu hoja de vida antes de publicar un talento.',
          style: TextStyle(fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context); // cerrar diálogo
              Navigator.pop(context); // volver atrás
            },
            child: const Text('Volver', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context); // cerrar diálogo
              Navigator.pop(context); // volver atrás
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const HojaVidaScreen()),
              );
            },
            style: ElevatedButton.styleFrom(backgroundColor: kPrimary),
            child: const Text('Completar ahora', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  Future<void> _loadItemDetails() async {
    setState(() {
      _loadingItem = true;
      _error = null;
    });
    try {
      final res = await ApiClient.get('/mis-items/${widget.itemId}', auth: true, useCache: false);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        setState(() {
          _nombreCtrl.text = (data['item'] ?? '').toString();
          _precioCtrl.text = (data['valor'] ?? '0').toString();
          _descCtrl.text = (data['presentacion'] ?? '').toString();
          _cantidadCtrl.text = (data['inventarios']?['cantidad'] ?? '1').toString();
          final parsedTipoTrans = int.tryParse(data['tipo_trans']?.toString() ?? '') ?? 3;
          _tipoTrans = (parsedTipoTrans >= 1 && parsedTipoTrans <= 3) ? parsedTipoTrans : 3;
          final parsedEstatus = int.tryParse(data['estatus']?.toString() ?? '') ?? 2;
          _estatus = (parsedEstatus == 1 || parsedEstatus == 2) ? parsedEstatus : 2;

          // Cargar imágenes existentes
          final imgs = data['imagenes'] as List? ?? [];
          if (imgs.isNotEmpty) {
            final mainImg = imgs.firstWhere(
              (i) {
                final orden = int.tryParse(i['orden_visualizacion']?.toString() ?? '');
                return orden == 1;
              },
              orElse: () => imgs.first,
            );
            _existingMainImageUrl = (mainImg['image_url'] ?? mainImg['url'] ?? '').toString();

            // Guardar el resto como imágenes adicionales existentes
            final mainImgId = int.tryParse(mainImg['id_imagen']?.toString() ?? '');
            _existingImages = imgs
                .where((i) {
                  final imgId = int.tryParse(i['id_imagen']?.toString() ?? '');
                  return imgId != mainImgId;
                })
                .map<Map<String, dynamic>>((i) => {
                      'id_imagen': int.tryParse(i['id_imagen']?.toString() ?? '') ?? 0,
                      'image_url': (i['image_url'] ?? i['url'] ?? '').toString(),
                    })
                .toList();
          }
        });
      } else {
        setState(() => _error = 'Error al cargar los detalles del talento.');
      }
    } catch (e) {
      setState(() => _error = 'Error de conexión: $e');
    } finally {
      setState(() => _loadingItem = false);
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
          _error = null;
        });
      }
    } catch (e) {
      setState(() => _error = 'Error al seleccionar archivo: $e');
    }
  }

  Future<void> _pickAdditionalMediaFromSource(ImageSource source, {required bool isVideo}) async {
    final totalActual = _existingImages.length + _additionalImages.length;
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

  Future<void> _publicarTalento() async {
    final isEdit = widget.itemId != null;

    if (_mainImage == null && _existingMainImageUrl == null) {
      setState(() => _error = 'Debes subir una imagen principal.');
      return;
    }

    setState(() {
      _saving = true;
      _error = null;
    });

    try {
      final Map<String, String> fields = {
        'item': _nombreCtrl.text.trim(),
        'presentacion': _descCtrl.text.trim(),
        'valor': (double.tryParse(_precioCtrl.text.trim()) ?? 0).toString(),
        'condicion': '1', // Nuevo/Servicio por defecto
        'tipo_trans': _tipoTrans.toString(),
        'id_categoria_item': '29', // Talentos
        'cantidad': (int.tryParse(_cantidadCtrl.text.trim()) ?? 1).toString(),
        'estatus': _estatus.toString(),
      };

      if (isEdit) {
        // Enviar lista de IDs de imágenes adicionales que conservamos
        for (int i = 0; i < _existingImages.length; i++) {
          fields['imagenes_existentes[$i]'] = _existingImages[i]['id_imagen'].toString();
        }
      }

      // Preparar MultipartFile para la imagen principal (si se seleccionó una nueva)
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

      // Preparar MultipartFiles para imágenes adicionales nuevas
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

      final String path = isEdit ? '/items/${widget.itemId}/update' : '/talentos';

      final res = await ApiClient.multipartPost(
        path,
        fields,
        auth: true,
        mainImage: mainImageFile,
        additionalImages: additionalImageFiles,
      );

      final body = jsonDecode(res.body);
      setState(() => _saving = false);

      if (res.statusCode == 200 || res.statusCode == 201) {
        if (!isEdit) {
          AnalyticsService.trackEvent('publish_talent_success');
        }
        if (!mounted) return;

        final redirectUrl = body['redirect_url']?.toString();
        if (redirectUrl != null && redirectUrl.isNotEmpty) {
          final fixedRedirectUrl = ApiClient.fixImageUrl(redirectUrl);
          final Uri url = Uri.parse(fixedRedirectUrl);
          if (await canLaunchUrl(url)) {
            await launchUrl(url, mode: LaunchMode.externalApplication);
            if (!mounted) return;
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
              content: Text('Redirigiendo a la pasarela de pago seguro...'),
              backgroundColor: Colors.blue,
            ));
            ApiClient.clearCache('/mis-items');
            Navigator.pop(context, true);
            return;
          } else {
            setState(() => _error = 'No se pudo abrir la pasarela de pago.');
            return;
          }
        } else {
          final msg = body['message'] ?? (isEdit ? '¡Talento actualizado con éxito!' : '¡Talento publicado con éxito!');
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(msg),
            backgroundColor: Colors.green,
          ));
        }

        ApiClient.clearCache('/mis-items');
        Navigator.pop(context, true); // Retorna true para indicar que hubo cambios y recargar listado
      } else {
        String errMsg = body['message'] ?? 'Error al procesar el talento.';
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

  double get _totalFinal {
    final qty = int.tryParse(_cantidadCtrl.text.trim()) ?? 1;
    return _montoRegistro * qty;
  }

  void _nextStep() {
    if (_step == 1) {
      if (!_formKey.currentState!.validate()) return;
      setState(() => _step = 2);
    } else if (_step == 2) {
      if (widget.itemId != null) {
        // En edición no hay paso 3 de pago
        _publicarTalento();
      } else {
        if (_mainImage == null) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
            content: Text('Debes subir una imagen principal.'),
            backgroundColor: Colors.orange,
          ));
          return;
        }
        setState(() => _step = 3);
      }
    }
  }

  void _prevStep() {
    if (_step > 1) {
      setState(() => _step--);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isEdit = widget.itemId != null;

    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: Text(
          isEdit ? 'Editar Talento' : 'Publicar Talento',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
      body: _loadingConfig || _loadingItem
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
                                  ? 'Paso 1 de ${isEdit ? 2 : 3}: Información Básica'
                                  : (_step == 2 ? 'Paso 2 de ${isEdit ? 2 : 3}: Multimedia' : 'Paso 3 de 3: Pago de Publicación'),
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                                color: kTextDark,
                              ),
                            ),
                            Text(
                              '${(_step / (isEdit ? 2.0 : 3.0) * 100).toInt()}%',
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
                            value: _step / (isEdit ? 2.0 : 3.0),
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
                  if (_step == 3 && !isEdit) _buildStep3(),
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
              const Text('Información del talento', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: kTextDark)),
              const SizedBox(height: 16),
              _campo(_nombreCtrl, 'Nombre del talento *', required: true),
              const SizedBox(height: 16),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: _campo(_precioCtrl, 'Precio (RD\$) *', required: true, keyboardType: TextInputType.number),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: DropdownButtonFormField<int>(
                      value: _tipoTrans,
                      style: const TextStyle(fontSize: 14, color: kTextDark, fontWeight: FontWeight.w500),
                      decoration: InputDecoration(
                        labelText: 'Modalidad *',
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
                        DropdownMenuItem(value: 3, child: Text('Ambos')),
                        DropdownMenuItem(value: 2, child: Text('Solo Canje')),
                        DropdownMenuItem(value: 1, child: Text('Solo Venta')),
                      ],
                      onChanged: (val) => setState(() => _tipoTrans = val!),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              _campo(
                _cantidadCtrl,
                'Cantidad de servicios *',
                required: true,
                keyboardType: TextInputType.number,
                enabled: widget.itemId == null,
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Este campo es obligatorio';
                  final qty = int.tryParse(v);
                  if (qty == null || qty < 1) return 'Debe ser mayor a 0';
                  return null;
                },
              ),
              if (widget.itemId != null) ...[
                const SizedBox(height: 6),
                Text(
                  'Nota: La cantidad de servicios no se puede modificar una vez publicada.',
                  style: TextStyle(color: Colors.grey.shade500, fontSize: 11, fontWeight: FontWeight.w500),
                ),
              ],
              const SizedBox(height: 16),
              
              // Descripción con contador
              const Text(
                'Descripción del talento *',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: kTextDark),
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _descCtrl,
                maxLines: 4,
                maxLength: 250,
                style: const TextStyle(fontSize: 14, color: kTextDark, fontWeight: FontWeight.w500),
                decoration: InputDecoration(
                  hintText: 'Resume tus habilidades y lo que ofreces...',
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
                onChanged: (val) => setState(() => _estatus = val ?? 2),
              ),
              const SizedBox(height: 24),
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

  Widget _buildStep2() {
    final isEdit = widget.itemId != null;

    Widget mainImagePreview;
    if (_mainImage != null) {
      mainImagePreview = Stack(
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
      );
    } else if (_existingMainImageUrl != null) {
      mainImagePreview = Stack(
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
      );
    } else {
      mainImagePreview = Column(
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
      );
    }

    return Container(
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
          const Text('Multimedia', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: kTextDark)),
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
                  color: _mainImage != null ? kPrimary : Colors.grey.shade300,
                  width: _mainImage != null ? 2.0 : 1.5,
                ),
              ),
              child: mainImagePreview,
            ),
          ),
          if (isEdit) ...[
            const Padding(
              padding: EdgeInsets.only(top: 8),
              child: Text(
                '⚠️ Si cambias la imagen principal, las imágenes opcionales previas serán eliminadas y deberás volver a agregarlas.',
                style: TextStyle(color: Colors.orange, fontSize: 11, height: 1.3, fontWeight: FontWeight.w500),
              ),
            ),
          ],
          const SizedBox(height: 20),

          // Selector imágenes adicionales
          Text(
            'Imágenes adicionales (Opcional) - ${_existingImages.length + _additionalImages.length}/4',
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: kTextDark),
          ),
          const SizedBox(height: 10),
          SizedBox(
            height: 80,
            child: ListView(
              scrollDirection: Axis.horizontal,
              physics: const BouncingScrollPhysics(),
              children: [
                if (_existingImages.length + _additionalImages.length < 4) ...[
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
                // Imágenes existentes de la base de datos
                ..._existingImages.map((img) {
                  return Container(
                    margin: const EdgeInsets.only(right: 8),
                    width: 80,
                    height: 80,
                    child: Stack(
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(12),
                          child: _isVideoPath(img['image_url'])
                              ? Container(
                                  width: 80,
                                  height: 80,
                                  color: Colors.black87,
                                  child: const Icon(Icons.play_circle_fill, color: Colors.white, size: 28),
                                )
                              : Image.network(
                                  img['image_url'],
                                  width: 80,
                                  height: 80,
                                  fit: BoxFit.cover,
                                ),
                        ),
                        Positioned(
                          right: 2,
                          top: 2,
                          child: GestureDetector(
                            onTap: () {
                              setState(() {
                                _existingImages.remove(img);
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
                // Nuevas imágenes seleccionadas localmente
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
          const SizedBox(height: 24),
          if (_error != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(12)),
              child: Row(
                children: [
                  const Icon(Icons.error_outline, color: Colors.red),
                  const SizedBox(width: 8),
                  Expanded(child: Text(_error!, style: const TextStyle(color: Colors.red, fontSize: 13))),
                ],
              ),
            ),
            const SizedBox(height: 16),
          ],
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
                  onPressed: _saving ? null : _nextStep,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: kPrimary,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    elevation: 0,
                  ),
                  child: Text(isEdit ? 'Guardar cambios' : 'Siguiente', style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

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
            const Text('Pago de publicación', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: kTextDark)),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(color: Colors.orange.shade50, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.orange.shade200)),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Monto:', style: TextStyle(fontWeight: FontWeight.w600, color: kPrimary)),
                  Text('RD\$ ${_totalFinal.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: kPrimary)),
                ],
              ),
            ),
            const SizedBox(height: 20),

            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.blue.shade200),
              ),
              child: Row(children: [
                Icon(Icons.security_outlined, color: Colors.blue.shade800, size: 22),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Serás redirigido a la pasarela de pago seguro de AZUL para completar la publicación. Tu información financiera está completamente protegida.',
                    style: TextStyle(fontSize: 12, color: Colors.blue.shade900, height: 1.3),
                  ),
                ),
              ]),
            ),

            if (_error != null) ...[
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(12)),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline, color: Colors.red),
                    const SizedBox(width: 8),
                    Expanded(child: Text(_error!, style: const TextStyle(color: Colors.red, fontSize: 13))),
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
                    onPressed: _saving ? null : _publicarTalento,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: kSecondary,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 0,
                    ),
                    child: _saving
                        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Text('Pagar con Azul', style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold)),
                  ),
                ),
              ],
            ),
          ],
        ),
      );

  Widget _campo(TextEditingController ctrl, String label,
      {bool required = false, TextInputType? keyboardType, int maxLines = 1, String? Function(String?)? validator, bool enabled = true}) {
    IconData? prefixIcon;
    if (label.contains('Nombre')) {
      prefixIcon = Icons.star_outline;
    } else if (label.contains('Precio')) {
      prefixIcon = Icons.monetization_on_outlined;
    } else if (label.contains('Cantidad') || label.contains('servicios')) {
      prefixIcon = Icons.inventory_2_outlined;
    }

    return TextFormField(
      controller: ctrl,
      keyboardType: keyboardType,
      maxLines: maxLines,
      enabled: enabled,
      style: TextStyle(
        fontSize: 14,
        color: enabled ? kTextDark : Colors.grey.shade600,
        fontWeight: FontWeight.w500,
      ),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: TextStyle(color: Colors.grey.shade600, fontSize: 13),
        prefixIcon: prefixIcon != null ? Icon(prefixIcon, color: enabled ? kPrimary : Colors.grey, size: 20) : null,
        filled: true,
        fillColor: enabled ? Colors.grey.shade50 : Colors.grey.shade100,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade200, width: 1.5),
        ),
        disabledBorder: OutlineInputBorder(
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
      validator: validator ?? (required ? (v) => (v == null || v.isEmpty) ? 'Este campo es obligatorio' : null : null),
    );
  }
}
