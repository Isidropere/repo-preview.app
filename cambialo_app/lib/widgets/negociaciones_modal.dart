import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

class NegociacionesModal extends StatefulWidget {
  final int itemId;
  final String itemName;

  const NegociacionesModal({
    super.key,
    required this.itemId,
    required this.itemName,
  });

  @override
  State<NegociacionesModal> createState() => _NegociacionesModalState();
}

class _NegociacionesModalState extends State<NegociacionesModal> {
  bool _loading = true;
  List<dynamic> _mensajes = [];
  List<dynamic> _paquetes = [];
  List _acciones = [];
  List _mensajesPredefinidosAPI = [];

  final TextEditingController _mensajeController = TextEditingController();
  final TextEditingController _montoController = TextEditingController();
  
  String? _paqueteSeleccionado;
  String? _mensajePredefinido;
  String? _accionSeleccionada;
  bool _enviando = false;

  @override
  void initState() {
    super.initState();
    _loadNegociaciones();
  }

  Future<void> _loadNegociaciones() async {
    setState(() => _loading = true);
    try {
      final res = await ApiClient.get('/carrito/getnegociaciones/${widget.itemId}', auth: true);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        setState(() {
          _mensajes = data['mensajes'] ?? [];
          _paquetes = data['paquetes'] ?? [];
          _acciones = data['accion'] ?? [];
          _mensajesPredefinidosAPI = data['mensajesPredefinidos'] ?? [];
          _loading = false;
        });
      } else {
        setState(() => _loading = false);
      }
    } catch (e) {
      setState(() => _loading = false);
    }
  }

  Future<void> _enviar() async {
    final mensaje = _mensajeController.text.trim();
    if (mensaje.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('El mensaje es obligatorio')));
      return;
    }

    setState(() => _enviando = true);
    try {
      final res = await ApiClient.post('/carrito/savenegociaciones', {
        'item_id': widget.itemId,
        'mensaje': mensaje,
        'paquete_id': _paqueteSeleccionado,
        'monto_oferta': _montoController.text.trim(),
        'accionInput': _accionSeleccionada,
      }, auth: true);

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data['status'] == 'ok') {
          _mensajeController.clear();
          _montoController.clear();
          setState(() {
            _paqueteSeleccionado = null;
            _mensajePredefinido = null;
            _accionSeleccionada = null;
          });
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Negociación enviada')));
          _loadNegociaciones();
        } else {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message'] ?? 'Error al enviar')));
        }
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Error de conexión')));
    } finally {
      if (mounted) setState(() => _enviando = false);
    }
  }

  void _abrirCrearPaquete() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const CrearPaqueteModal(),
    ).then((value) {
      if (value == true) {
        _loadNegociaciones();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      height: MediaQuery.of(context).size.height * 0.85,
      padding: const EdgeInsets.only(top: 16),
      child: Column(
        children: [
          Text('Negociar: ${widget.itemName}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
          const Divider(),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _mensajes.isEmpty
                    ? const Center(child: Text('Sin mensajes aún.', style: TextStyle(color: Colors.grey)))
                    : ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        itemCount: _mensajes.length,
                        itemBuilder: (context, index) {
                          final msg = _mensajes[index];
                          final bool propio = msg['propio'] == true;
                          return Align(
                            alignment: propio ? Alignment.centerRight : Alignment.centerLeft,
                            child: Container(
                              margin: const EdgeInsets.only(bottom: 8),
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: propio ? kPrimary.withOpacity(0.2) : Colors.grey.shade100,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Column(
                                crossAxisAlignment: propio ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                                children: [
                                  Text(msg['texto'] ?? ''),
                                  const SizedBox(height: 4),
                                  Text(msg['fecha'] ?? '', style: const TextStyle(fontSize: 10, color: Colors.grey)),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),
          Container(
            padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom + 16, left: 16, right: 16, top: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, -2))],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              mainAxisSize: MainAxisSize.min,
              children: [
                if (_acciones.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: DropdownButtonFormField<String>(
                      decoration: InputDecoration(
                        labelText: 'Acción a realizar',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: Colors.orange.shade50,
                      ),
                      value: _accionSeleccionada,
                      items: _acciones.map((a) {
                        final tipo = a['tipo']?.toString() ?? '';
                        final label = tipo.isNotEmpty ? tipo[0].toUpperCase() + tipo.substring(1) : '';
                        return DropdownMenuItem(value: tipo, child: Text(label));
                      }).toList(),
                      onChanged: (val) => setState(() => _accionSeleccionada = val),
                    ),
                  ),
                DropdownButtonFormField<String>(
                  decoration: InputDecoration(
                    labelText: 'Mensaje Rápido',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    filled: true,
                    fillColor: Colors.orange.shade50,
                  ),
                  value: _mensajePredefinido,
                  items: _mensajesPredefinidosAPI.map((msg) {
                    return DropdownMenuItem<String>(
                      value: msg['mensaje'].toString(),
                      child: Text(msg['titulo'].toString()),
                    );
                  }).toList(),
                  onChanged: (val) {
                    setState(() {
                      _mensajePredefinido = val;
                      if (val != null) _mensajeController.text = val;
                    });
                  },
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        decoration: const InputDecoration(
                          labelText: 'Ofrecer Paquete (Opcional)',
                          border: OutlineInputBorder(),
                          isDense: true,
                        ),
                        value: _paqueteSeleccionado,
                        items: _paquetes.map((p) => DropdownMenuItem(value: p['id'].toString(), child: Text(p['nombre'] ?? ''))).toList(),
                        onChanged: (v) => setState(() => _paqueteSeleccionado = v),
                      ),
                    ),
                    const SizedBox(width: 8),
                    ElevatedButton(
                      onPressed: _abrirCrearPaquete,
                      style: ElevatedButton.styleFrom(backgroundColor: Colors.blueGrey, padding: const EdgeInsets.symmetric(horizontal: 8)),
                      child: const Text('Crear', style: TextStyle(color: Colors.white, fontSize: 12)),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      flex: 2,
                      child: TextField(
                        controller: _mensajeController,
                        decoration: const InputDecoration(
                          hintText: 'Escribe tu mensaje...',
                          border: OutlineInputBorder(),
                          isDense: true,
                        ),
                        maxLines: 2,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      flex: 1,
                      child: TextField(
                        controller: _montoController,
                        keyboardType: TextInputType.number,
                        decoration: const InputDecoration(
                          hintText: 'Monto (RD\$)',
                          border: OutlineInputBorder(),
                          isDense: true,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                ElevatedButton(
                  onPressed: _enviando ? null : _enviar,
                  style: ElevatedButton.styleFrom(backgroundColor: kPrimary, padding: const EdgeInsets.symmetric(vertical: 12)),
                  child: _enviando
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Text('Enviar Negociación', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class CrearPaqueteModal extends StatefulWidget {
  const CrearPaqueteModal({super.key});

  @override
  State<CrearPaqueteModal> createState() => _CrearPaqueteModalState();
}

class _CrearPaqueteModalState extends State<CrearPaqueteModal> {
  bool _loading = true;
  List<dynamic> _itemsUsuario = [];
  final Set<int> _seleccionados = {};
  double _totalPaquete = 0.0;
  final TextEditingController _nombreController = TextEditingController();
  bool _guardando = false;

  @override
  void initState() {
    super.initState();
    _loadItems();
  }

  Future<void> _loadItems() async {
    try {
      final res = await ApiClient.get('/carrito/items-usuario', auth: true);
      if (res.statusCode == 200) {
        setState(() {
          _itemsUsuario = jsonDecode(res.body);
          _loading = false;
        });
      } else {
        setState(() => _loading = false);
      }
    } catch (e) {
      setState(() => _loading = false);
    }
  }

  void _toggleItem(int id, double valor, bool? checked) {
    setState(() {
      if (checked == true) {
        _seleccionados.add(id);
        _totalPaquete += valor;
      } else {
        _seleccionados.remove(id);
        _totalPaquete -= valor;
      }
    });
  }

  Future<void> _guardarPaquete() async {
    if (_nombreController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Escribe un nombre para el paquete')));
      return;
    }
    if (_seleccionados.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Selecciona al menos un item')));
      return;
    }

    setState(() => _guardando = true);
    try {
      final res = await ApiClient.post('/carrito/crearPaquete', {
        'nombre': _nombreController.text.trim(),
        'items': _seleccionados.toList(),
      }, auth: true);

      if (res.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Paquete creado exitosamente')));
        Navigator.pop(context, true); // Retornar true para indicar que se creó
      } else {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Error al crear paquete')));
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Error de conexión')));
    } finally {
      if (mounted) setState(() => _guardando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      height: MediaQuery.of(context).size.height * 0.7,
      padding: const EdgeInsets.only(top: 16),
      child: Column(
        children: [
          const Text('Crear nuevo paquete', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
          const Divider(),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: TextField(
              controller: _nombreController,
              decoration: const InputDecoration(labelText: 'Nombre del paquete', border: OutlineInputBorder(), isDense: true),
            ),
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Items disponibles:', style: TextStyle(fontWeight: FontWeight.bold)),
                Text('Total: RD\$ ${_totalPaquete.toStringAsFixed(2)}', style: const TextStyle(color: kPrimary, fontWeight: FontWeight.bold)),
              ],
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _itemsUsuario.isEmpty
                    ? const Center(child: Text('No tienes items disponibles'))
                    : ListView.builder(
                        itemCount: _itemsUsuario.length,
                        itemBuilder: (context, index) {
                          final item = _itemsUsuario[index];
                          final id = item['id_item'] as int;
                          final valor = double.tryParse(item['valor']?.toString() ?? '0') ?? 0.0;
                          return CheckboxListTile(
                            title: Text(item['item'] ?? ''),
                            subtitle: Text('RD\$ ${valor.toStringAsFixed(2)}'),
                            value: _seleccionados.contains(id),
                            onChanged: (val) => _toggleItem(id, valor, val),
                          );
                        },
                      ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: ElevatedButton(
              onPressed: _guardando ? null : _guardarPaquete,
              style: ElevatedButton.styleFrom(
                backgroundColor: kPrimary,
                minimumSize: const Size(double.infinity, 50),
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
              child: _guardando
                  ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Text('Agregar paquete', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            ),
          ),
        ],
      ),
    );
  }
}
