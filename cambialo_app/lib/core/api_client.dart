import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// URL base de la API.
/// En emulador Android: 10.0.2.2 apunta al localhost de tu PC.
const String kBaseUrl = 'http://10.0.2.2:8000/api';

class ApiClient {
  static final _storage = FlutterSecureStorage();

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

  static Future<http.Response> get(String path, {bool auth = false}) async {
    final headers = await _headers(auth: auth);
    return http.get(Uri.parse('$kBaseUrl$path'), headers: headers);
  }

  static Future<http.Response> post(String path, Map<String, dynamic> body, {bool auth = false}) async {
    final headers = await _headers(auth: auth);
    return http.post(Uri.parse('$kBaseUrl$path'), headers: headers, body: jsonEncode(body));
  }

  static Future<http.Response> delete(String path, {bool auth = false}) async {
    final headers = await _headers(auth: auth);
    return http.delete(Uri.parse('$kBaseUrl$path'), headers: headers);
  }
}
