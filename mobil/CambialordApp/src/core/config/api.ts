/**
 * ============================================================
 * api.ts  Cliente HTTP centralizado (Axios)
 * ============================================================
 * Configura la instancia de Axios que usan todos los servicios
 * de la app para comunicarse con el backend Laravel (mobil/api).
 *
 * CONFIGURABLE: Cambiar API_BASE_URL según el entorno:
 *   - Android emulator  10.0.2.2 (mapea al localhost del PC)
 *   - iOS simulator     localhost
 *   - Dispositivo real  IP local del PC (ej: 192.168.1.x:8001)
 *
 * Puerto 8001  backend móvil (mobil/api)
 * Puerto 8000  backend web (proyecto principal)
 * ============================================================
 */

import axios from 'axios';
import {useAuthStore} from '../store/authStore';
import {Platform} from 'react-native';

// URL base según la plataforma donde corre la app
// CONFIGURABLE: cambiar la IP si usas dispositivo físico
const API_BASE_URL = Platform.select({
  android: 'http://10.0.2.2:8001/api', // Android emulator  localhost del PC
  ios: 'http://localhost:8001/api',     // iOS simulator
  default: 'http://10.0.2.2:8001/api',
});

// Crear instancia de Axios con configuración base
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 15000, // CONFIGURABLE: tiempo máximo de espera en ms
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

/**
 * Interceptor de REQUEST.
 *
 * Antes de cada petición, lee el token del store de Zustand
 * y lo agrega al header Authorization si existe.
 * Esto evita tener que pasar el token manualmente en cada llamada.
 */
api.interceptors.request.use(config => {
  const token = useAuthStore.getState().token;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

/**
 * Interceptor de RESPONSE.
 *
 * Maneja errores globales de la API:
 *   - 401 Unauthorized  cierra sesión automáticamente
 *   - Otros errores     los loguea en modo desarrollo
 *
 * NOTA: No se loguean tokens ni datos sensibles.
 */
api.interceptors.response.use(
  response => response, // Respuesta exitosa  pasar sin modificar
  error => {
    const status = error.response?.status;

    // Si el token expiró o es inválido, cerrar sesión
    if (status === 401) {
      useAuthStore.getState().logout();
    }

    // Solo loguear en desarrollo, nunca en producción
    if (__DEV__) {
      console.warn(`API Error ${status}: ${error.config?.url}`);
    }

    return Promise.reject(error);
  },
);

export default api;