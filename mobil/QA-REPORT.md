# REPORTE QA — APP MÓVIL CAMBIALORD
Fecha: 18 Marzo 2026

---

## ✅ SEGURIDAD MÓVIL

| Check | Estado | Notas |
|-------|--------|-------|
| Token no visible en logs | ✅ | Sin console.log en código |
| Token en Keychain/Keystore | ✅ | react-native-keychain implementado |
| No datos sensibles en AsyncStorage | ✅ | Solo Keychain para tokens |
| No API keys hardcodeadas | ✅ | URL configurable por plataforma |
| Interceptor 401 → logout | ✅ | Limpia token automáticamente |
| Password no almacenado | ✅ | Solo token persistido |
| __DEV__ guard en logs | ✅ | Solo en desarrollo |

---

## ✅ ARQUITECTURA

| Check | Estado | Notas |
|-------|--------|-------|
| Clean Architecture | ✅ | screens → services → api |
| No lógica en screens | ✅ | Delegada a services/store |
| TypeScript tipado | ✅ | Interfaces definidas |
| Path aliases configurados | ✅ | @core, @features, @shared |
| Zustand store tipado | ✅ | AuthState interface |
| Axios interceptores | ✅ | Auth + Error handling |
| Separación por features | ✅ | 8 features independientes |

---

## ✅ COMPONENTES COMPARTIDOS

| Componente | Estado | Accesibilidad |
|------------|--------|---------------|
| Button | ✅ | accessibilityRole, accessibilityLabel, accessibilityState |
| Input | ✅ | accessibilityLabel, error states |
| Card | ✅ | ViewProps spread |
| Loading | ✅ | ActivityIndicator |
| ErrorView | ✅ | Retry button |
| EmptyView | ✅ | Mensaje configurable |

---

## ✅ PANTALLAS — CHECKLIST

### LoginScreen
| Check | Estado |
|-------|--------|
| KeyboardAvoidingView | ✅ |
| SafeAreaView | ✅ |
| Validación email | ✅ |
| Validación password min 6 | ✅ |
| Loading en botón | ✅ |
| Error del API mostrado | ✅ |
| Botón disabled si vacío | ✅ |
| keyboardShouldPersistTaps | ✅ |
| autoComplete hints | ✅ |

### RegisterScreen
| Check | Estado |
|-------|--------|
| Confirmación de contraseña | ✅ |
| Validación nombre min 2 | ✅ |
| Validación email | ✅ |
| Validación password min 8 | ✅ |
| SafeAreaView | ✅ |
| autoComplete hints | ✅ |

### HomeScreen
| Check | Estado |
|-------|--------|
| Estado loading | ✅ |
| Estado error + retry | ✅ |
| Estado vacío (EmptyView) | ✅ |
| Pull-to-refresh | ✅ |
| SafeAreaView | ✅ |
| accessibilityLabel en items | ✅ |

### CartScreen / MessagesScreen / ProfileScreen
| Check | Estado |
|-------|--------|
| SafeAreaView | ✅ |
| EmptyView placeholder | ✅ |
| accessibilityRole header | ✅ |

---

## ⚠️ ISSUES ENCONTRADOS Y CORREGIDOS

| # | Severidad | Issue | Fix |
|---|-----------|-------|-----|
| 1 | 🟠 | API URL hardcodeada sin Platform.select | Agregado Platform.select iOS/Android |
| 2 | 🟠 | Sin validación de email/password antes de enviar | Agregada validación con validators.ts |
| 3 | 🟠 | Register sin campo confirmar contraseña | Agregado confirmPassword field |
| 4 | 🟡 | Sin SafeAreaView en pantallas | Agregado SafeAreaView + SafeAreaProvider |
| 5 | 🟡 | Sin EmptyView cuando lista vacía | Agregado EmptyView en Home/Cart/Messages |
| 6 | 🟡 | Botones sin margen entre ellos | Agregado marginBottom en Button base |
| 7 | 🟡 | Sin accessibilityState en Button | Agregado disabled/busy states |
| 8 | 🟡 | Sin keyboardShouldPersistTaps | Agregado en ScrollViews de forms |
| 9 | 🔵 | Faltaban @types/react-native-vector-icons | Instalado |
| 10 | 🔵 | Import no usado (View, getCategories) | Removidos |
| 11 | 🟡 | Sin autoComplete hints en inputs | Agregado email/password/name |
| 12 | 🟡 | Logs podían filtrar datos sensibles | Solo URL en __DEV__ mode |

---

## ⚠️ PENDIENTES (requieren dispositivo/emulador)

| # | Tipo | Descripción |
|---|------|-------------|
| 1 | Rendimiento | Medir tiempo de carga inicial < 3s |
| 2 | Rendimiento | Verificar scroll a 60fps |
| 3 | Compatibilidad | Probar en Android API 24+ |
| 4 | Compatibilidad | Probar en iOS 15+ |
| 5 | E2E | Flujo login → home → carrito |
| 6 | Seguridad | Certificate pinning en release |
| 7 | Seguridad | ProGuard/R8 habilitado |
| 8 | Build | Tamaño APK < 50MB |

---

## 🔴 BLOQUEANTE

Node.js v18.20.5 instalado, React Native 0.84.1 requiere Node >= 20.19.4.
Actualizar Node.js antes de compilar/ejecutar la app.
