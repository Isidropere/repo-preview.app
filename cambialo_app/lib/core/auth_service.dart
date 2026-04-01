import 'dart:convert';
import 'api_client.dart';

class AuthService {
  static Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final res = await ApiClient.post('/auth/login', {'email': email, 'password': password});
      final body = jsonDecode(res.body);
      if (res.statusCode == 200) {
        await ApiClient.saveToken(body['token']);
        return {'success': true, 'user': body['user']};
      }
      final errors = body['errors'] ?? {};
      final msg = errors.isNotEmpty ? errors.values.first[0] : (body['message'] ?? 'Error al iniciar sesión');
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': 'No se pudo conectar al servidor.'};
    }
  }

  static Future<Map<String, dynamic>> register(Map<String, dynamic> data) async {
    final res = await ApiClient.post('/auth/register', data);
    final body = jsonDecode(res.body);
    if (res.statusCode == 201) {
      await ApiClient.saveToken(body['token']);
      return {'success': true, 'user': body['user']};
    }
    final errors = body['errors'] ?? {};
    final msg = errors.isNotEmpty ? errors.values.first[0] : (body['message'] ?? 'Error al registrarse');
    return {'success': false, 'message': msg};
  }

  static Future<void> logout() async {
    await ApiClient.post('/auth/logout', {}, auth: true);
    await ApiClient.deleteToken();
  }

  static Future<Map<String, dynamic>?> me() async {
    final res = await ApiClient.get('/auth/me', auth: true);
    if (res.statusCode == 200) return jsonDecode(res.body);
    return null;
  }

  static Future<bool> isLoggedIn() async {
    final token = await ApiClient.getToken();
    return token != null;
  }
}
