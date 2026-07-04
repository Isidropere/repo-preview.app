import 'dart:convert';
import 'package:http/http.dart' as http;
import 'api_client.dart';

/// Cache en memoria del perfil del usuario autenticado
/// para evitar llamadas HTTP a /auth/me en cada pantalla
Map<String, dynamic>? _cachedUser;
DateTime?             _userCachedAt;
const Duration        _userCacheTtl = Duration(minutes: 5);

class AuthService {
  static bool adultosAceptado = false;

  /// Retorna de inmediato el usuario en caché si está disponible en memoria (0ms)
  static Map<String, dynamic>? get currentUser => _cachedUser;

  static Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final res = await ApiClient.post('/auth/login', {'email': email, 'password': password});
      final body = jsonDecode(res.body);
      if (res.statusCode == 200) {
        await ApiClient.saveToken(body['token']);
        await ApiClient.saveUser(body['user']);
        _cachedUser    = body['user'];
        _userCachedAt  = DateTime.now();
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
      await ApiClient.saveUser(body['user']);
      _cachedUser   = body['user'];
      _userCachedAt = DateTime.now();
      return {'success': true, 'user': body['user']};
    }
    final errors = body['errors'] ?? {};
    final msg = errors.isNotEmpty ? errors.values.first[0] : (body['message'] ?? 'Error al registrarse');
    return {'success': false, 'message': msg};
  }

  static Future<void> logout() async {
    _cachedUser   = null;
    _userCachedAt = null;
    adultosAceptado = false;
    await ApiClient.post('/auth/logout', {}, auth: true);
    await ApiClient.deleteToken();
    await ApiClient.deleteUser();
  }

  /// Retorna datos del usuario — usa cache en memoria (5 min TTL) para evitar
  /// llamadas repetitivas a /auth/me en cada pantalla que carga el perfil.
  /// Implementa Cache-First para cargar instantáneamente de disco local (SecureStorage).
  static Future<Map<String, dynamic>?> me({bool forceRefresh = false}) async {
    // Verificar si hay token rápidamente (en memoria)
    final token = await ApiClient.getToken();
    if (token == null) return null;

    // Retornar del cache si es válido y fresco
    if (!forceRefresh && _cachedUser != null && _userCachedAt != null) {
      if (DateTime.now().difference(_userCachedAt!) < _userCacheTtl) {
        return _cachedUser;
      }
    }

    // Cache-First: Si no está en memoria, intentar leer de SecureStorage de inmediato
    if (!forceRefresh && _cachedUser == null) {
      final savedUser = await ApiClient.getUser();
      if (savedUser != null) {
        _cachedUser = savedUser;
        _userCachedAt = DateTime.now(); // marcamos temporalmente como fresco
        // Lanzamos la actualización de red en segundo plano de forma asíncrona
        _refreshUserBackground();
        return _cachedUser; // Respuesta instantánea en 0ms!
      }
    }

    // De lo contrario, hacer la consulta síncrona real
    return await _fetchUserFromApi();
  }

  static Future<Map<String, dynamic>?> _fetchUserFromApi() async {
    final res = await ApiClient.get('/auth/me', auth: true, useCache: false);
    if (res.statusCode == 200) {
      try {
        final user = jsonDecode(res.body) as Map<String, dynamic>;
        _cachedUser   = user;
        _userCachedAt = DateTime.now();
        await ApiClient.saveUser(user);
        return _cachedUser;
      } catch (e) {
        return null;
      }
    }
    return null;
  }

  static Future<void> _refreshUserBackground() async {
    try {
      await _fetchUserFromApi();
    } catch (_) {}
  }

  /// Fuerza recarga del perfil (usar después de actualizar perfil)
  static void invalidateUserCache() {
    _cachedUser   = null;
    _userCachedAt = null;
  }

  static Future<bool> isLoggedIn() async {
    final token = await ApiClient.getToken();
    return token != null;
  }

  static Future<Map<String, dynamic>> updateProfile(
    Map<String, String> data, {
    http.MultipartFile? profilePhoto,
  }) async {
    final res = await ApiClient.multipartPost(
      '/auth/profile',
      data,
      auth: true,
      mainImage: profilePhoto,
    );
    final body = jsonDecode(res.body);
    if (res.statusCode == 200) {
      // Actualizar cache
      _cachedUser   = body['user'];
      _userCachedAt = DateTime.now();
      await ApiClient.saveUser(body['user']);
      return {'success': true, 'user': body['user']};
    }
    return {'success': false, 'message': body['message'] ?? 'Error al actualizar el perfil.'};
  }

  static Future<Map<String, dynamic>> loginWithGoogle(Map<String, dynamic> googleData) async {
    try {
      final res = await ApiClient.post('/auth/google', googleData);
      final body = jsonDecode(res.body);
      if (res.statusCode == 200 || res.statusCode == 201) {
        await ApiClient.saveToken(body['token']);
        await ApiClient.saveUser(body['user']);
        _cachedUser   = body['user'];
        _userCachedAt = DateTime.now();
        return {'success': true, 'user': body['user']};
      }
      return {'success': false, 'message': body['message'] ?? 'Error al iniciar sesión con Google'};
    } catch (e) {
      return {'success': false, 'message': 'No se pudo conectar al servidor.'};
    }
  }

  static Future<Map<String, dynamic>> verificarAdultos(String email, String password) async {
    try {
      final res = await ApiClient.post('/auth/adultos/verificar', {
        'email': email,
        'password': password,
      }, auth: true);
      final body = jsonDecode(res.body);
      if (res.statusCode == 200) {
        adultosAceptado = true;
        return {'success': true};
      }
      return {
        'success': false,
        'message': body['message'] ?? 'Error al verificar credenciales.'
      };
    } catch (e) {
      return {'success': false, 'message': 'No se pudo conectar al servidor.'};
    }
  }

  static Future<Map<String, dynamic>> sendPasswordResetEmail(String email) async {
    try {
      final res = await ApiClient.post('/auth/password/email', {'email': email});
      final body = jsonDecode(res.body);
      if (body['success'] == true) {
        return {
          'success': true,
          'message': body['message'] ?? 'Se ha enviado un link a su correo para cambiar su contraseña.'
        };
      }
      return {
        'success': false,
        'message': body['message'] ?? 'Error al enviar el correo de recuperación.'
      };
    } catch (e) {
      return {'success': false, 'message': 'No se pudo conectar al servidor.'};
    }
  }
}
