import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// URL base de la API.
/// En emulador Android: 10.0.2.2 apunta al localhost de tu PC.
const String kBaseUrl = 'http://10.0.2.2:8000/api';

// Cache en memoria para respuestas GET — evita llamadas repetidas
final Map<String, String> _cache = {};

class ApiClient {
  static final _storage = FlutterSecureStorage();
  static final _client  = http.Client();

  static Future<String?> getToken() => _storage.read(key: 'auth_token');
  static Future<void> saveToken(String t) => _storage.write(key: 'auth_token', value: t);
  static Future<void> deleteToken() => _storage.delete(key: 'auth_token');

  static Future<Map<String, String>> _headers({bool auth = false}) async {
    final h = {'Content-Type': 'application/json', 'Accept': 'application/json'};
    if (auth) {
      final token = await getToken();
      if (token != null) h['Authorization'] = 'Bearer $token';
    }
    return h;
  }

  static Future<http.Response> get(String path, {bool auth = false, bool useCache = true}) async {
    final cacheKey = path;
    // Devolver cache si existe y no requiere auth
    if (useCache && !auth && _cache.containsKey(cacheKey)) {
      return http.Response(_cache[cacheKey]!, 200);
    }
    final headers = await _headers(auth: auth);
    final res = await _client
        .get(Uri.parse('$kBaseUrl$path'), headers: headers)
        .timeout(const Duration(seconds: 10));
    if (res.statusCode == 200 && useCache && !auth) {
      _cache[cacheKey] = res.body;
    }
    return res;
  }

  static void clearCache([String? path]) {
    if (path != null) _cache.remove(path);
    else _cache.clear();
  }

  static Future<http.Response> post(String path, Map<String, dynamic> body, {bool auth = false}) async {
    final headers = await _headers(auth: auth);
    return _client
        .post(Uri.parse('$kBaseUrl$path'), headers: headers, body: jsonEncode(body))
        .timeout(const Duration(seconds: 10));
  }

  static Future<http.Response> delete(String path, {bool auth = false}) async {
    final headers = await _headers(auth: auth);
    return _client
        .delete(Uri.parse('$kBaseUrl$path'), headers: headers)
        .timeout(const Duration(seconds: 10));
  }
}
