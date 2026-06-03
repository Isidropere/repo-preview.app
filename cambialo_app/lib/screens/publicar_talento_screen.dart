import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:http/http.dart' as http;
import '../core/api_client.dart';
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
  final _cvvCtrl = TextEditingController();

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
  List _tarjetas = [];
  String? _idTarjeta;
  bool _loadingConfig = true;
  bool _saving = false;
  String? _error;

  // Tarjeta nueva
  final _formTarjetaKey = GlobalKey<FormState>();
  final _noTarjetaCtrl = TextEditingController();
  final _nombreTitularCtrl = TextEditingController();
  final _mesExpCtrl = TextEditingController();
  final _anioExpCtrl = TextEditingController();
  final _bancoCtrl = TextEditingController();
  final _tipoCtrl = TextEditingController();
  bool _registrandoTarjeta = false;

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
    _cvvCtrl.dispose();
    _noTarjetaCtrl.dispose();
    _nombreTitularCtrl.dispose();
    _mesExpCtrl.dispose();
    _anioExpCtrl.dispose();
    _bancoCtrl.dispose();
    _tipoCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _loadingConfig = true);
    try {
      final results = await Future.wait([
        ApiClient.get('/talentos/config', auth: true),
        ApiClient.get('/tarjetas', auth: true, useCache: false),
        ApiClient.get('/hoja-vida', auth: true, useCache: false),
      ]);

      if (results[0].statusCode == 200) {
        final body = jsonDecode(results[0].body);
        _montoRegistro = (body['monto_registro'] as num?)?.toDouble() ?? 150.0;
      }
      if (results[1].statusCode == 200) {
        _tarjetas = jsonDecode(results[1].body) as List;
        if (_tarjetas.isNotEmpty) {
          final activa = _tarjetas.firstWhere(
            (t) => t['usar_esta_tarjeta'] == 1 || t['usar_esta_tarjeta'] == true,
            orElse: () => _tarjetas.first,
          );
          _idTarjeta = activa['id_tarjeta'];
        }
      }
      if (results[2].statusCode == 200 && widget.itemId == null) {
        final body = jsonDecode(results[2].body);
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
          _tipoTrans = int.tryParse(data['tipo_trans']?.toString() ?? '') ?? 3;

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

  Future<void> _agregarTarjeta() async {
    if (!_formTarjetaKey.currentState!.validate()) return;
    setState(() => _registrandoTarjeta = true);

    try {
      final res = await ApiClient.post('/tarjetas', {
        'no_tarjeta': _noTarjetaCtrl.text.trim(),
        'nombre_titular': _nombreTitularCtrl.text.trim(),
        'mes_expiracion': _mesExpCtrl.text.trim(),
        'anio_expiracion': _anioExpCtrl.text.trim(),
        'banco_tarjeta': _bancoCtrl.text.trim().isEmpty ? 'Banco' : _bancoCtrl.text.trim(),
        'tipo_tarjeta': _tipoCtrl.text.trim().isEmpty ? 'Visa' : _tipoCtrl.text.trim(),
        'usar_esta_tarjeta': true
      }, auth: true);

      setState(() => _registrandoTarjeta = false);

      if (!mounted) return;
      if (res.statusCode == 201 || res.statusCode == 200) {
        Navigator.pop(context); // cerrar bottom sheet
        _noTarjetaCtrl.clear();
        _nombreTitularCtrl.clear();
        _mesExpCtrl.clear();
        _anioExpCtrl.clear();
        _bancoCtrl.clear();
        _tipoCtrl.clear();
        // Recargar tarjetas
        await _loadData();
      } else {
        final body = jsonDecode(res.body);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(body['message'] ?? 'Error al guardar la tarjeta.'),
          backgroundColor: Colors.red,
        ));
      }
    } catch (e) {
      setState(() => _registrandoTarjeta = false);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Error de conexión al agregar tarjeta.'),
        backgroundColor: Colors.red,
      ));
    }
  }

  void _mostrarDialogoTarjeta() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) => Padding(
        padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom + 20,
            top: 20,
            left: 20,
            right: 20),
        child: SingleChildScrollView(
          child: Form(
            key: _formTarjetaKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Nueva Tarjeta de Pago',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: kTextDark)),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _noTarjetaCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'Número de tarjeta *', border: OutlineInputBorder()),
                  validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _nombreTitularCtrl,
                  decoration: const InputDecoration(labelText: 'Nombre del titular *', border: OutlineInputBorder()),
                  validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
                ),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(
                    child: TextFormField(
                      controller: _mesExpCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Mes Venc. (MM) *', border: OutlineInputBorder()),
                      validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextFormField(
                      controller: _anioExpCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Año Venc. (AAAA) *', border: OutlineInputBorder()),
                      validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
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
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _registrandoTarjeta ? null : _agregarTarjeta,
                    style: ElevatedButton.styleFrom(
                        backgroundColor: kPrimary, padding: const EdgeInsets.symmetric(vertical: 14)),
                    child: _registrandoTarjeta
                        ? const CircularProgressIndicator(color: Colors.white)
                        : const Text('Guardar Tarjeta', style: TextStyle(color: Colors.white, fontSize: 15)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
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
          _error = null;
        });
      }
    } catch (e) {
      setState(() => _error = 'Error al seleccionar imagen: $e');
    }
  }

  Future<void> _pickAdditionalImages() async {
    final totalActual = _existingImages.length + _additionalImages.length;
    if (totalActual >= 4) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Solo puedes tener hasta 4 imágenes opcionales.'),
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
    } catch (e) {
      setState(() => _error = 'Error al seleccionar imágenes: $e');
    }
  }

  Future<void> _publicarTalento() async {
    final isEdit = widget.itemId != null;

    if (!isEdit && _idTarjeta == null && _totalFinal > 0) {
      setState(() => _error = 'Debes seleccionar una tarjeta.');
      return;
    }
    if (!isEdit && _cvvCtrl.text.trim().isEmpty && _totalFinal > 0) {
      setState(() => _error = 'Ingresa el código CVV.');
      return;
    }
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
      };

      if (!isEdit) {
        fields['id_tarjeta'] = _idTarjeta ?? '';
        fields['cvv'] = _cvvCtrl.text.trim();
      } else {
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
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(isEdit ? '¡Talento actualizado con éxito!' : '¡Talento publicado con éxito!'),
          backgroundColor: Colors.green,
        ));
        ApiClient.clearCache('/mis-items');
        Navigator.pop(context, true); // Retorna true para indicar que hubo cambios y recargar listado
      } else {
        setState(() => _error = body['message'] ?? 'Error al procesar el talento.');
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
        title: Text(isEdit ? 'Editar Talento' : 'Publicar Talento'),
      ),
      body: _loadingConfig || _loadingItem
          ? const Center(child: CircularProgressIndicator(color: kPrimary))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  // Indicador de pasos (Omitir paso 3 en modo edición)
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      _stepIndicator(1, 'Info', _step >= 1),
                      _stepDivider(_step >= 2),
                      _stepIndicator(2, 'Multimedia', _step >= 2),
                      if (!isEdit) ...[
                        _stepDivider(_step >= 3),
                        _stepIndicator(3, 'Pago', _step >= 3),
                      ]
                    ],
                  ),
                  const SizedBox(height: 24),

                  if (_step == 1) _buildStep1(),
                  if (_step == 2) _buildStep2(),
                  if (_step == 3 && !isEdit) _buildStep3(),
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
              const Text('Información del talento', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: kTextDark)),
              const SizedBox(height: 16),
              TextFormField(
                controller: _nombreCtrl,
                decoration: const InputDecoration(labelText: 'Nombre del talento *', hintText: 'Ej: Clases de guitarra, Diseño gráfico', border: OutlineInputBorder()),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      controller: _precioCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Precio (RD\$) *', prefixText: 'RD\$ ', border: OutlineInputBorder()),
                      validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: DropdownButtonFormField<int>(
                      value: _tipoTrans,
                      items: const [
                        DropdownMenuItem(value: 3, child: Text('Venta o Canje')),
                        DropdownMenuItem(value: 2, child: Text('Solo Canje')),
                        DropdownMenuItem(value: 1, child: Text('Solo Venta')),
                      ],
                      onChanged: (val) => setState(() => _tipoTrans = val!),
                      decoration: const InputDecoration(labelText: 'Modalidad *', border: OutlineInputBorder()),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _cantidadCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'Cantidad de servicios *', border: OutlineInputBorder()),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Requerido';
                  final qty = int.tryParse(v);
                  if (qty == null || qty < 1) return 'Debe ser mayor a 0';
                  return null;
                },
                onChanged: (_) => setState(() {}),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _descCtrl,
                maxLines: 4,
                maxLength: 250,
                decoration: const InputDecoration(labelText: 'Descripción del talento *', hintText: 'Resume tus habilidades y lo que ofreces...', border: OutlineInputBorder()),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _nextStep,
                  style: ElevatedButton.styleFrom(backgroundColor: kPrimary, padding: const EdgeInsets.symmetric(vertical: 14)),
                  child: const Text('Siguiente', style: TextStyle(color: Colors.white, fontSize: 15)),
                ),
              ),
            ],
          ),
        ),
      );

  Widget _buildStep2() {
    final isEdit = widget.itemId != null;

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
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
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: _mainImage != null ? kPrimary.withOpacity(0.5) : Colors.grey.shade200,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.02),
                      blurRadius: 6,
                      offset: const Offset(0, 3),
                    )
                  ]),
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
                  : (_existingMainImageUrl != null
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
                        )),
            ),
          ),
          if (isEdit) ...[
            const Padding(
              padding: EdgeInsets.only(top: 8),
              child: Text(
                '⚠️ Si cambias la imagen principal, las imágenes opcionales previas serán eliminadas y deberás volver a agregarlas.',
                style: TextStyle(color: Colors.orange, fontSize: 11, height: 1.3),
              ),
            ),
          ],
          const SizedBox(height: 16),

          // Selector imágenes adicionales
          Text(
            'Imágenes adicionales (Opcional) - ${_existingImages.length + _additionalImages.length}/4',
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: kTextDark),
          ),
          const SizedBox(height: 8),
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
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.grey.shade200),
                      ),
                      child: const Icon(Icons.add_a_photo_outlined, color: kTextGray, size: 22),
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
                          borderRadius: BorderRadius.circular(8),
                          child: Image.network(img['image_url'], width: 80, height: 80, fit: BoxFit.cover),
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
          const SizedBox(height: 24),
          if (_error != null) ...[
            Text(_error!, style: const TextStyle(color: Colors.red, fontSize: 13)),
            const SizedBox(height: 12),
          ],
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: _prevStep,
                  style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
                  child: const Text('Anterior'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton(
                  onPressed: _saving ? null : _nextStep,
                  style: ElevatedButton.styleFrom(backgroundColor: kPrimary, padding: const EdgeInsets.symmetric(vertical: 14)),
                  child: Text(isEdit ? 'Guardar cambios' : 'Siguiente', style: const TextStyle(color: Colors.white, fontSize: 15)),
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
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade200),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Pago de publicación', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: kTextDark)),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(color: Colors.orange.shade50, borderRadius: BorderRadius.circular(8), border: Border.all(color: Colors.orange.shade200)),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Monto de publicación', style: TextStyle(fontWeight: FontWeight.w600, color: kPrimary)),
                  Text('RD\$ ${_totalFinal.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: kPrimary)),
                ],
              ),
            ),
            const SizedBox(height: 6),
            Align(
              alignment: Alignment.centerRight,
              child: Text(
                'Tarifa RD\$ ${_montoRegistro.toStringAsFixed(2)} × ${int.tryParse(_cantidadCtrl.text) ?? 1} servicio(s)',
                style: const TextStyle(fontSize: 11, color: kTextGray),
              ),
            ),
            const SizedBox(height: 20),

            // Card selector
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Selecciona una tarjeta', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: kTextDark)),
                TextButton(onPressed: _mostrarDialogoTarjeta, child: const Text('Nueva tarjeta')),
              ],
            ),
            if (_tarjetas.isEmpty)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(8)),
                child: const Text('No tienes tarjetas de pago guardadas. Agrega una para continuar.', style: TextStyle(fontSize: 12, color: kTextGray)),
              )
            else ...[
              ..._tarjetas.map((t) => RadioListTile<String>(
                    value: t['id_tarjeta'],
                    groupValue: _idTarjeta,
                    onChanged: (v) => setState(() => _idTarjeta = v),
                    activeColor: kPrimary,
                    title: Text('**** **** **** ${t['last4']}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    subtitle: Builder(
                      builder: (_) {
                        final rawYear = t['año_expiracion'] ?? t['anio_expiracion'] ?? '';
                        final mesVal = t['mes_expiracion'].toString().padLeft(2, '0');
                        return Text('${t['nombre_titular']} | Vence $mesVal/$rawYear', style: TextStyle(fontSize: 11, color: kTextGray));
                      },
                    ),
                  )),
              const SizedBox(height: 12),
              TextFormField(
                controller: _cvvCtrl,
                obscureText: true,
                keyboardType: TextInputType.number,
                maxLength: 4,
                decoration: const InputDecoration(labelText: 'Código de seguridad (CVV) *', border: OutlineInputBorder(), counterText: '', prefixIcon: Icon(Icons.lock_outline)),
              ),
            ],

            if (_error != null) ...[
              const SizedBox(height: 12),
              Text(_error!, style: const TextStyle(color: Colors.red, fontSize: 13)),
            ],
            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: _prevStep,
                    style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
                    child: const Text('Anterior'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _saving ? null : _publicarTalento,
                    style: ElevatedButton.styleFrom(backgroundColor: kSecondary, padding: const EdgeInsets.symmetric(vertical: 14)),
                    child: _saving
                        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Text('Pagar y publicar', style: TextStyle(color: Colors.white, fontSize: 15)),
                  ),
                ),
              ],
            ),
          ],
        ),
      );
}
