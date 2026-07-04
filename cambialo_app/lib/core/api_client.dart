import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:shared_preferences/shared_preferences.dart';

String get kBaseUrl {
  final url = dotenv.env['API_URL']?.trim() ?? 'https://cambialord.com/api';
  if (!kIsWeb && (url.contains('127.0.0.1') || url.contains('localhost'))) {
    return url.replaceAll('127.0.0.1', '10.0.2.2').replaceAll('localhost', '10.0.2.2');
  }
  return url;
}

// ── Cache en memoria ──────────────────────────────────────────────────────
// Cachea respuestas GET públicas Y el token de auth para evitar
// leer FlutterSecureStorage en cada petición (es I/O lento en web).
final Map<String, String>  _cache       = {};
final Map<String, dynamic> _memStore    = {};  // almacén KV rápido (token, user)
const String               _tokenKey    = '__token';
const Duration             _cacheMaxAge = Duration(minutes: 3);
final Map<String, DateTime> _cacheTimes = {};

class ApiClient {
  static final ValueNotifier<int> cartCountNotifier = ValueNotifier<int>(0);
  static final _storage = const FlutterSecureStorage();
  static final _client  = http.Client();

  static int? parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is double) return value.toInt();
    final String str = value.toString();
    final int? parsedInt = int.tryParse(str);
    if (parsedInt != null) return parsedInt;
    final double? parsedDouble = double.tryParse(str);
    if (parsedDouble != null) return parsedDouble.toInt();
    return null;
  }

  static bool parseBool(dynamic value) {
    if (value == null) return false;
    if (value is bool) return value;
    if (value is int) return value == 1;
    if (value is double) return value.toInt() == 1;
    final String str = value.toString().toLowerCase().trim();
    return str == '1' || str == 'true';
  }

  static String fixImageUrl(String? url) {
    if (url == null || url.isEmpty) return 'https://via.placeholder.com/150';
    
    String fixedUrl = url;

    // Si es ruta relativa, le pegamos la URL base
    if (!url.startsWith('http')) {
      final baseUrl = kBaseUrl.replaceAll('/api', '');
      if (url.startsWith('/')) {
        fixedUrl = '$baseUrl$url';
      } else {
        fixedUrl = '$baseUrl/$url';
      }
    }

    // Si es local (emulador, web localhost, 127.0.0.1)
    final isLocal = fixedUrl.contains('localhost') || 
                    fixedUrl.contains('127.0.0.1') || 
                    fixedUrl.contains('10.0.2.2');
                    
    if (isLocal) {
      // En emulador android: mapear localhost/127.0.0.1 a 10.0.2.2
      if (!kIsWeb && kBaseUrl.contains('10.0.2.2')) {
        fixedUrl = fixedUrl.replaceAll('127.0.0.1', '10.0.2.2').replaceAll('localhost', '10.0.2.2');
      }
    }
    
    return fixedUrl;
  }

  static SharedPreferences? _webPrefs;

  static Future<SharedPreferences> get _getPrefs async {
    _webPrefs ??= await SharedPreferences.getInstance();
    return _webPrefs!;
  }

  // ── Token (memory-first para evitar SecureStorage en cada llamada) ──────
  static Future<String?> getToken() async {
    if (_memStore.containsKey(_tokenKey)) return _memStore[_tokenKey] as String?;
    String? t;
    if (kIsWeb) {
      final prefs = await _getPrefs;
      t = prefs.getString('auth_token');
    } else {
      try {
        t = await _storage.read(key: 'auth_token');
      } catch (_) {
        final prefs = await _getPrefs;
        t = prefs.getString('auth_token');
      }
    }
    if (t != null) _memStore[_tokenKey] = t;
    return t;
  }

  static Future<void> saveToken(String t) async {
    _memStore[_tokenKey] = t;
    if (kIsWeb) {
      final prefs = await _getPrefs;
      await prefs.setString('auth_token', t);
    } else {
      try {
        await _storage.write(key: 'auth_token', value: t);
      } catch (_) {
        final prefs = await _getPrefs;
        await prefs.setString('auth_token', t);
      }
    }
  }

  static Future<void> deleteToken() async {
    _memStore.remove(_tokenKey);
    _cache.clear();
    if (kIsWeb) {
      final prefs = await _getPrefs;
      await prefs.remove('auth_token');
    } else {
      try {
        await _storage.delete(key: 'auth_token');
      } catch (_) {
        final prefs = await _getPrefs;
        await prefs.remove('auth_token');
      }
    }
  }

  static const String _userKey = '__user';

  static Future<Map<String, dynamic>?> getUser() async {
    if (_memStore.containsKey(_userKey)) return _memStore[_userKey] as Map<String, dynamic>?;
    String? uStr;
    if (kIsWeb) {
      final prefs = await _getPrefs;
      uStr = prefs.getString('auth_user');
    } else {
      try {
        uStr = await _storage.read(key: 'auth_user');
      } catch (_) {
        final prefs = await _getPrefs;
        uStr = prefs.getString('auth_user');
      }
    }
    if (uStr != null) {
      try {
        final u = jsonDecode(uStr) as Map<String, dynamic>;
        _memStore[_userKey] = u;
        return u;
      } catch (_) {}
    }
    return null;
  }

  static Future<void> saveUser(Map<String, dynamic> u) async {
    _memStore[_userKey] = u;
    final uStr = jsonEncode(u);
    if (kIsWeb) {
      final prefs = await _getPrefs;
      await prefs.setString('auth_user', uStr);
    } else {
      try {
        await _storage.write(key: 'auth_user', value: uStr);
      } catch (_) {
        final prefs = await _getPrefs;
        await prefs.setString('auth_user', uStr);
      }
    }
  }

  static Future<void> deleteUser() async {
    _memStore.remove(_userKey);
    if (kIsWeb) {
      final prefs = await _getPrefs;
      await prefs.remove('auth_user');
    } else {
      try {
        await _storage.delete(key: 'auth_user');
      } catch (_) {
        final prefs = await _getPrefs;
        await prefs.remove('auth_user');
      }
    }
  }

  static Future<Map<String, String>> _headers({bool auth = false}) async {
    final h = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Cache-Control': 'no-cache, no-store, must-revalidate',
      'Pragma': 'no-cache',
      'Expires': '0',
    };
    if (auth) {
      final token = await getToken();
      if (token != null) h['Authorization'] = 'Bearer $token';
    }
    return h;
  }

  // ── GET con cache inteligente ────────────────────────────────────────────
  static Future<http.Response> get(
    String path, {
    bool auth = false,
    bool useCache = true,
    Duration? cacheDuration,
  }) async {
    final cacheKey = auth ? '__auth__$path' : path;
    final duration = cacheDuration ?? (auth ? const Duration(minutes: 1) : _cacheMaxAge);

    // Retornar de cache si es válido
    if (useCache && _cache.containsKey(cacheKey)) {
      final cacheTime = _cacheTimes[cacheKey];
      if (cacheTime != null && DateTime.now().difference(cacheTime) < duration) {
        return http.Response(_cache[cacheKey]!, 200);
      } else {
        // Cache expirada — limpiar
        _cache.remove(cacheKey);
        _cacheTimes.remove(cacheKey);
      }
    }

    final headers = await _headers(auth: auth);
    try {
      final res = await _retryRequest(() async {
        return await _client
            .get(Uri.parse('$kBaseUrl$path'), headers: headers)
            .timeout(const Duration(seconds: 25));
      });

      final cleanRes = _cleanResponse(res);
      if (cleanRes.statusCode == 200 && useCache) {
        _cache[cacheKey]      = cleanRes.body;
        _cacheTimes[cacheKey] = DateTime.now();
      }
      return cleanRes;
    } catch (e) {
      // Si hay cache aunque expirada, usarla como fallback offline
      if (_cache.containsKey(cacheKey)) {
        return http.Response(_cache[cacheKey]!, 200);
      }
      rethrow;
    }
  }

  /// Invalida manualmente una ruta del cache (tras mutaciones)
  static void clearCache([String? path]) {
    if (path != null) {
      _cache.remove(path);
      _cache.remove('__auth__$path');
      _cacheTimes.remove(path);
      _cacheTimes.remove('__auth__$path');
    } else {
      _cache.clear();
      _cacheTimes.clear();
    }
  }

  static Future<http.Response> post(String path, Map<String, dynamic> body, {bool auth = false}) async {
    final headers = await _headers(auth: auth);
    final res = await _client
        .post(Uri.parse('$kBaseUrl$path'), headers: headers, body: jsonEncode(body))
        .timeout(const Duration(seconds: 30));
    return _cleanResponse(res);
  }

  static Future<http.Response> multipartPost(
    String path,
    Map<String, String> fields, {
    required bool auth,
    http.MultipartFile? mainImage,
    List<http.MultipartFile>? additionalImages,
  }) async {
    final uri = Uri.parse('$kBaseUrl$path');
    final request = http.MultipartRequest('POST', uri);

    if (auth) {
      final token = await getToken();
      if (token != null) {
        request.headers['Authorization'] = 'Bearer $token';
      }
    }
    request.headers['Accept'] = 'application/json';

    request.fields.addAll(fields);

    if (mainImage != null) {
      request.files.add(mainImage);
    }

    if (additionalImages != null) {
      request.files.addAll(additionalImages);
    }

    final streamedResponse = await request.send().timeout(const Duration(seconds: 60));
    final response = await http.Response.fromStream(streamedResponse);
    return _cleanResponse(response);
  }

  static Future<http.Response> put(String path, Map<String, dynamic> body, {bool auth = false}) async {
    final headers = await _headers(auth: auth);
    final res = await _client
        .put(Uri.parse('$kBaseUrl$path'), headers: headers, body: jsonEncode(body))
        .timeout(const Duration(seconds: 30));
    return _cleanResponse(res);
  }

  static Future<http.Response> delete(String path, {bool auth = false}) async {
    final headers = await _headers(auth: auth);
    final res = await _client
        .delete(Uri.parse('$kBaseUrl$path'), headers: headers)
        .timeout(const Duration(seconds: 20));
    return _cleanResponse(res);
  }

  static http.Response _cleanResponse(http.Response res) {
    if (res.body.isEmpty) return res;
    final clientBase = kBaseUrl.replaceAll('/api', '');
    final cleanedBody = res.body.replaceAll('http://127.0.0.1:8000', clientBase);
    return http.Response(
      cleanedBody,
      res.statusCode,
      headers: res.headers,
      isRedirect: res.isRedirect,
      persistentConnection: res.persistentConnection,
      reasonPhrase: res.reasonPhrase,
      request: res.request,
    );
  }

  static Future<http.Response> _retryRequest(
    Future<http.Response> Function() requestFn, {
    int retries = 3,
    Duration delay = const Duration(milliseconds: 800),
  }) async {
    int attempt = 0;
    while (true) {
      attempt++;
      try {
        return await requestFn();
      } catch (e) {
        if (attempt >= retries) {
          rethrow;
        }
        await Future.delayed(delay * attempt);
      }
    }
  }
}
