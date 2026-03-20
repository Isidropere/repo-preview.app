# CONFIGURACIÓN DE AMBIENTE — APP MÓVIL CAMBIALORD

Guía paso a paso para configurar el ambiente de desarrollo móvil.
Cuando el usuario indique "crea la app", seguir esta guía en orden.

---

## 📋 Prerrequisitos del Sistema

### Software base (Windows)
| Software | Versión mínima | Comando verificación |
|----------|---------------|---------------------|
| Node.js | 18 LTS+ | `node -v` |
| npm | 9+ | `npm -v` |
| Git | 2.40+ | `git -v` |
| Java JDK | 17 | `java -version` |
| Android Studio | Hedgehog+ | Abrir IDE |
| VS Code / Kiro | Última | - |

### Para iOS (requiere Mac)
| Software | Versión mínima |
|----------|---------------|
| macOS | Ventura 13+ |
| Xcode | 15+ |
| CocoaPods | 1.14+ |

---

## 🚀 PASO 1: Instalar React Native CLI

```bash
npm install -g react-native-cli @react-native-community/cli
```

Verificar:
```bash
npx react-native --version
```

---

## 🤖 PASO 2: Configurar Android SDK

### 2.1 Instalar Android Studio
- Descargar de https://developer.android.com/studio
- Durante instalación seleccionar:
  - Android SDK
  - Android SDK Platform
  - Android Virtual Device (AVD)

### 2.2 Instalar SDK Components
Abrir Android Studio → Settings → SDK Manager:
- SDK Platforms: Android 14 (API 34)
- SDK Tools:
  - Android SDK Build-Tools 34
  - Android SDK Command-line Tools
  - Android Emulator
  - Android SDK Platform-Tools

### 2.3 Variables de entorno (Windows)
Agregar a variables de entorno del sistema:
```
ANDROID_HOME = C:\Users\{usuario}\AppData\Local\Android\Sdk
JAVA_HOME = C:\Program Files\Java\jdk-17
```

Agregar al PATH:
```
%ANDROID_HOME%\platform-tools
%ANDROID_HOME%\emulator
%ANDROID_HOME%\tools
%ANDROID_HOME%\tools\bin
```

### 2.4 Crear emulador
```bash
# Listar dispositivos disponibles
emulator -list-avds

# Si no hay ninguno, crear desde Android Studio:
# Tools → Device Manager → Create Device
# Seleccionar: Pixel 7 → API 34 → Descargar imagen → Finish
```

---

## 📱 PASO 3: Crear el Proyecto

```bash
# Desde la raíz del workspace, crear proyecto en carpeta mobil-app
npx react-native init CambialordApp --directory mobil-app --version 0.73.6

# Entrar al proyecto
cd mobil-app
```

---

## 📦 PASO 4: Instalar Dependencias Core

```bash
# Navegación
npm install @react-navigation/native @react-navigation/native-stack @react-navigation/bottom-tabs
npm install react-native-screens react-native-safe-area-context react-native-gesture-handler react-native-reanimated

# Estado global
npm install zustand

# HTTP
npm install axios

# Almacenamiento
npm install @react-native-async-storage/async-storage react-native-keychain

# Imágenes
npm install react-native-fast-image

# Iconos
npm install react-native-vector-icons

# Formularios
npm install react-hook-form

# Splash screen
npm install react-native-splash-screen

# Notificaciones push
npm install @react-native-firebase/app @react-native-firebase/messaging

# Cámara y galería
npm install react-native-image-picker

# Dotenv
npm install react-native-dotenv
```

---

## ⚙️ PASO 5: Configuración de Archivos

### 5.1 Archivo .env (mobil-app/.env)
```env
API_BASE_URL=http://10.0.2.2:8000/api
# 10.0.2.2 es localhost del host desde el emulador Android

# Para dispositivo físico en la misma red:
# API_BASE_URL=http://192.168.x.x:8000/api

APP_NAME=Cambialord
APP_VERSION=1.0.0
```

### 5.2 babel.config.js (agregar dotenv y reanimated)
```js
module.exports = {
  presets: ['module:@react-native/babel-preset'],
  plugins: [
    ['module:react-native-dotenv', {
      envName: 'APP_ENV',
      moduleName: '@env',
      path: '.env',
    }],
    'react-native-reanimated/plugin', // SIEMPRE al final
  ],
};
```

### 5.3 tsconfig.json o jsconfig.json (paths)
```json
{
  "compilerOptions": {
    "baseUrl": ".",
    "paths": {
      "@core/*": ["src/core/*"],
      "@features/*": ["src/features/*"],
      "@shared/*": ["src/shared/*"],
      "@assets/*": ["src/assets/*"]
    }
  }
}
```

---

## 🏗️ PASO 6: Estructura de Carpetas

```
mobil-app/
├── src/
│   ├── core/
│   │   ├── config/
│   │   │   ├── api.js          # Configuración Axios
│   │   │   ├── constants.js    # Constantes globales
│   │   │   └── theme.js        # Colores, tipografía, espaciado
│   │   ├── navigation/
│   │   │   ├── AppNavigator.js # Navigator principal
│   │   │   ├── AuthNavigator.js
│   │   │   └── TabNavigator.js # Bottom tabs
│   │   ├── store/
│   │   │   └── authStore.js    # Zustand store de auth
│   │   └── utils/
│   │       ├── formatters.js   # Formato precio, fecha
│   │       └── validators.js   # Validaciones
│   ├── features/
│   │   ├── auth/
│   │   │   ├── screens/
│   │   │   │   ├── LoginScreen.js
│   │   │   │   ├── RegisterScreen.js
│   │   │   │   └── ForgotPasswordScreen.js
│   │   │   └── services/
│   │   │       └── authService.js
│   │   ├── home/
│   │   │   ├── screens/
│   │   │   │   └── HomeScreen.js
│   │   │   └── components/
│   │   │       ├── CategoryList.js
│   │   │       └── ProductGrid.js
│   │   ├── products/
│   │   │   ├── screens/
│   │   │   │   ├── ProductListScreen.js
│   │   │   │   ├── ProductDetailScreen.js
│   │   │   │   └── CreateProductScreen.js
│   │   │   └── services/
│   │   │       └── productService.js
│   │   ├── cart/
│   │   │   ├── screens/
│   │   │   │   └── CartScreen.js
│   │   │   └── services/
│   │   │       └── cartService.js
│   │   ├── checkout/
│   │   │   ├── screens/
│   │   │   │   └── CheckoutScreen.js
│   │   │   └── services/
│   │   │       └── checkoutService.js
│   │   ├── negotiations/
│   │   │   ├── screens/
│   │   │   │   └── NegotiationScreen.js
│   │   │   └── services/
│   │   │       └── negotiationService.js
│   │   ├── messages/
│   │   │   ├── screens/
│   │   │   │   ├── ConversationListScreen.js
│   │   │   │   └── ChatScreen.js
│   │   │   └── services/
│   │   │       └── messageService.js
│   │   └── profile/
│   │       ├── screens/
│   │       │   ├── ProfileScreen.js
│   │       │   ├── EditProfileScreen.js
│   │       │   └── AddressListScreen.js
│   │       └── services/
│   │           └── profileService.js
│   ├── shared/
│   │   ├── components/
│   │   │   ├── Button.js
│   │   │   ├── Input.js
│   │   │   ├── Card.js
│   │   │   ├── Loading.js
│   │   │   ├── ErrorView.js
│   │   │   ├── EmptyView.js
│   │   │   └── Header.js
│   │   └── hooks/
│   │       ├── useApi.js
│   │       └── useAuth.js
│   └── assets/
│       ├── images/
│       └── fonts/
├── android/
├── ios/
├── .env
├── .env.staging
├── .env.production
├── app.json
├── babel.config.js
├── metro.config.js
└── package.json
```

---

## 🎨 PASO 7: Archivos Base a Crear

### 7.1 Theme (src/core/config/theme.js)
```js
export const colors = {
  primary: '#F58634',
  primaryHover: '#E07528',
  secondary: '#2563EB',
  background: '#F9FAFB',
  surface: '#FFFFFF',
  error: '#EF4444',
  success: '#22C55E',
  warning: '#F59E0B',
  textPrimary: '#1F2937',
  textSecondary: '#6B7280',
  border: '#E5E7EB',
};

export const spacing = {
  xs: 4, sm: 8, md: 16, lg: 24, xl: 32, xxl: 48,
};

export const borderRadius = {
  sm: 8, md: 12, lg: 16, full: 9999,
};

export const fontSize = {
  xs: 12, sm: 14, md: 16, lg: 18, xl: 20, xxl: 24, title: 32,
};
```

### 7.2 API Client (src/core/config/api.js)
```js
import axios from 'axios';
import { API_BASE_URL } from '@env';
import { useAuthStore } from '../store/authStore';

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 15000,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Interceptor: agregar token
api.interceptors.request.use((config) => {
  const token = useAuthStore.getState().token;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Interceptor: manejar errores
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      useAuthStore.getState().logout();
    }
    return Promise.reject(error);
  }
);

export default api;
```

### 7.3 Auth Store (src/core/store/authStore.js)
```js
import { create } from 'zustand';
import * as Keychain from 'react-native-keychain';

export const useAuthStore = create((set) => ({
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: true,

  setAuth: async (user, token) => {
    await Keychain.setGenericPassword('token', token);
    set({ user, token, isAuthenticated: true, isLoading: false });
  },

  logout: async () => {
    await Keychain.resetGenericPassword();
    set({ user: null, token: null, isAuthenticated: false, isLoading: false });
  },

  loadToken: async () => {
    try {
      const credentials = await Keychain.getGenericPassword();
      if (credentials) {
        set({ token: credentials.password, isLoading: false });
        return credentials.password;
      }
    } catch (e) {}
    set({ isLoading: false });
    return null;
  },
}));
```

---

## ▶️ PASO 8: Ejecutar

### Android (emulador)
```bash
# Terminal 1: Metro bundler
npx react-native start

# Terminal 2: Build y deploy
npx react-native run-android
```

### Android (dispositivo físico)
1. Habilitar "Opciones de desarrollador" en el teléfono
2. Habilitar "Depuración USB"
3. Conectar por USB
4. Verificar: `adb devices`
5. `npx react-native run-android`

### iOS (solo Mac)
```bash
cd ios && pod install && cd ..
npx react-native run-ios
```

---

## 🔗 PASO 9: Conectar con Backend Laravel

### Configurar CORS en Laravel
En `config/cors.php`:
```php
'allowed_origins' => ['*'], // En dev; restringir en producción
```

### Verificar que el API responde
```bash
# Desde el emulador Android, 10.0.2.2 apunta al localhost del host
curl http://10.0.2.2:8000/api/categorias
```

### Si usas dispositivo físico
Asegurarse de que el teléfono y la PC estén en la misma red WiFi.
Usar la IP local de la PC (ej: 192.168.1.100).

---

## 🧪 PASO 10: Verificar Instalación

### Checklist
- [ ] `node -v` → v18+
- [ ] `npm -v` → v9+
- [ ] `java -version` → 17
- [ ] `adb devices` → lista dispositivo/emulador
- [ ] `npx react-native doctor` → todo verde
- [ ] App abre en emulador/dispositivo
- [ ] API responde desde la app (test con fetch a /api/categorias)

---

## 📝 Notas Importantes

- El emulador Android usa `10.0.2.2` para acceder al localhost del host
- Si XAMPP corre en puerto 8000, la URL del API es `http://10.0.2.2:8000/api`
- Para hot reload: sacudir el dispositivo o `Ctrl+M` en emulador → "Reload"
- Si hay error de Metro: `npx react-native start --reset-cache`
- Si hay error de build Android: `cd android && ./gradlew clean && cd ..`
