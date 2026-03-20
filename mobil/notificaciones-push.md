# GUÍA DE NOTIFICACIONES PUSH

Configuración e implementación de notificaciones push para Cambialord.

---

## 🔔 Firebase Cloud Messaging (FCM)

### Configuración
1. Crear proyecto en Firebase Console
2. Agregar app iOS y Android
3. Descargar google-services.json (Android) y GoogleService-Info.plist (iOS)
4. Configurar APNs key en Firebase (para iOS)

### Flujo
1. App se registra en FCM al iniciar
2. FCM retorna un device token
3. App envía el token al backend Laravel
4. Backend almacena token asociado al usuario
5. Cuando hay evento, backend envía push via FCM API
6. FCM entrega la notificación al dispositivo

---

## 📨 Tipos de Notificaciones

| Evento | Título | Cuerpo | Acción al tap |
|--------|--------|--------|---------------|
| Nueva oferta | "Nueva oferta" | "{usuario} te hizo una oferta por {producto}" | Abrir negociación |
| Contraoferta | "Contraoferta recibida" | "{usuario} envió una contraoferta de RD${monto}" | Abrir negociación |
| Oferta aceptada | "Oferta aceptada" | "Tu oferta por {producto} fue aceptada" | Abrir negociación |
| Nuevo mensaje | "Mensaje de {usuario}" | "{preview del mensaje}" | Abrir chat |
| Compra confirmada | "Compra confirmada" | "Tu pedido #{id} fue confirmado" | Abrir detalle pedido |
| Envío actualizado | "Actualización de envío" | "Tu pedido #{id} fue enviado" | Abrir tracking |
| Nuevo rating | "Nueva calificación" | "{usuario} te calificó con {estrellas}⭐" | Abrir perfil |

---

## 🔧 Implementación Backend (Laravel)

### Almacenar token
```php
// Endpoint: POST /api/device-token
// Body: { "token": "fcm_token", "platform": "ios|android" }
```

### Enviar notificación
```php
// Usar firebase/php-jwt o kreait/firebase-php
// Enviar a FCM HTTP v1 API
```

---

## 📱 Implementación Móvil

### Permisos
- iOS: solicitar permiso explícito (UNUserNotificationCenter)
- Android 13+: solicitar permiso POST_NOTIFICATIONS

### Manejo de notificaciones
- Foreground: mostrar banner in-app (no push nativo)
- Background: push nativo del sistema
- App cerrada: push nativo, al abrir navegar a pantalla correcta

### Deep linking
- Formato: `cambialord://negociacion/{id}`, `cambialord://chat/{id}`
- Parsear data de la notificación para navegar

---

## ⚙️ Configuración del Usuario

Permitir al usuario configurar qué notificaciones recibir:
- [ ] Ofertas y negociaciones
- [ ] Mensajes
- [ ] Actualizaciones de pedidos
- [ ] Promociones y novedades

Almacenar preferencias en backend y respetar al enviar.
