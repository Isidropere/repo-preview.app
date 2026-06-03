import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:geolocator/geolocator.dart';
import '../core/theme.dart';

class MapPickerScreen extends StatefulWidget {
  final LatLng? initialLocation;
  final String? searchQuery;

  const MapPickerScreen({super.key, this.initialLocation, this.searchQuery});

  @override
  State<MapPickerScreen> createState() => _MapPickerScreenState();
}

class _MapPickerScreenState extends State<MapPickerScreen> {
  late LatLng _selectedLocation;
  final MapController _mapController = MapController();
  bool _loadingLocation = false;

  @override
  void initState() {
    super.initState();
    // República Dominicana como punto de inicio por defecto
    _selectedLocation = widget.initialLocation ?? const LatLng(18.4861, -69.9312);
    
    if (widget.initialLocation == null && widget.searchQuery != null) {
      _searchAndCenterLocation();
    }
  }

  Future<void> _searchAndCenterLocation() async {
    try {
      final url = Uri.parse('https://nominatim.openstreetmap.org/search?format=json&q=${Uri.encodeComponent(widget.searchQuery!)}&limit=1');
      final res = await http.get(url, headers: {'User-Agent': 'CambialoApp/1.0'});
      if (res.statusCode == 200) {
        final List data = jsonDecode(res.body);
        if (data.isNotEmpty) {
          final lat = double.tryParse(data[0]['lat'].toString());
          final lng = double.tryParse(data[0]['lon'].toString());
          if (lat != null && lng != null) {
            final loc = LatLng(lat, lng);
            if (mounted) {
              setState(() {
                _selectedLocation = loc;
              });
              _mapController.move(loc, 14.0);
            }
          }
        }
      }
    } catch (e) {
      // Si falla, se queda en el centro por defecto
    }
  }

  Future<void> _getCurrentLocation() async {
    setState(() => _loadingLocation = true);
    
    bool serviceEnabled;
    LocationPermission permission;

    serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      setState(() => _loadingLocation = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Los servicios de ubicación están desactivados.')),
        );
      }
      return;
    }

    permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        setState(() => _loadingLocation = false);
        return;
      }
    }

    if (permission == LocationPermission.deniedForever) {
      setState(() => _loadingLocation = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Permisos de ubicación denegados permanentemente.')),
        );
      }
      return;
    }

    try {
      final position = await Geolocator.getCurrentPosition();
      final userLocation = LatLng(position.latitude, position.longitude);
      
      setState(() {
        _selectedLocation = userLocation;
        _loadingLocation = false;
      });
      _mapController.move(userLocation, 16.0);
    } catch (e) {
      setState(() => _loadingLocation = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ubicación exacta'),
        actions: [
          IconButton(
            icon: _loadingLocation 
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: kPrimary, strokeWidth: 2))
                : const Icon(Icons.my_location, color: kPrimary),
            onPressed: _loadingLocation ? null : _getCurrentLocation,
            tooltip: 'Mi ubicación',
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context, _selectedLocation);
            },
            child: const Text('Confirmar', style: TextStyle(color: kPrimary, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
      body: Stack(
        children: [
          FlutterMap(
            mapController: _mapController,
            options: MapOptions(
              initialCenter: _selectedLocation,
              initialZoom: 14.0,
              onTap: (tapPosition, point) {
                setState(() {
                  _selectedLocation = point;
                });
              },
            ),
            children: [
              TileLayer(
                urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                userAgentPackageName: 'com.example.cambialo_app',
              ),
              MarkerLayer(
                markers: [
                  Marker(
                    point: _selectedLocation,
                    width: 50,
                    height: 50,
                    child: const Icon(Icons.location_on, color: Colors.red, size: 40),
                  ),
                ],
              ),
            ],
          ),
          Positioned(
            bottom: 20,
            left: 20,
            right: 20,
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
                boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4)],
              ),
              child: const Text(
                'Toca en el mapa para ajustar la ubicación exacta de tu dirección.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 13),
              ),
            ),
          )
        ],
      ),
    );
  }
}
