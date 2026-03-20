# GUÍA DE PUBLICACIÓN EN TIENDAS

Documentación para publicar la app Cambialord en App Store y Google Play.

---

## 🍎 App Store (iOS)

### Requisitos previos
- Cuenta Apple Developer ($99/año)
- Certificados de distribución configurados
- App Store Connect configurado

### Información requerida
- Nombre: Cambialord
- Subtítulo: Compra, vende e intercambia
- Categoría principal: Shopping
- Categoría secundaria: Lifestyle
- URL de privacidad: https://cambialord.com/privacidad
- URL de soporte: https://cambialord.com/soporte

### Screenshots requeridos
| Dispositivo | Tamaño | Cantidad |
|-------------|--------|----------|
| iPhone 6.7" | 1290x2796 | 3-10 |
| iPhone 6.5" | 1284x2778 | 3-10 |
| iPhone 5.5" | 1242x2208 | 3-10 |
| iPad 12.9" | 2048x2732 | 3-10 (si soporta iPad) |

### Screenshots sugeridos
1. Pantalla principal con productos
2. Detalle de producto
3. Carrito y checkout
4. Negociaciones/intercambios
5. Perfil y publicación

### Descripción (máx 4000 caracteres)
```
Cambialord es la plataforma donde puedes comprar, vender e intercambiar 
productos y servicios en República Dominicana.

Características principales:
• Publica productos y talentos fácilmente
• Compra con métodos de pago seguros
• Negocia precios directamente con vendedores
• Intercambia artículos con otros usuarios
• Chat integrado para comunicarte
• Califica y revisa vendedores

¡Únete a la comunidad Cambialord!
```

### Keywords (máx 100 caracteres)
```
comprar,vender,intercambiar,productos,servicios,marketplace,dominicana
```

### App Review Notes
```
Cuenta de prueba:
Email: [test_email]
Password: [test_password]

La app requiere conexión a internet para funcionar.
```

### Checklist pre-envío
- [ ] Build firmado con certificado de distribución
- [ ] Todos los screenshots subidos
- [ ] Descripción en español
- [ ] Política de privacidad accesible
- [ ] No usa APIs privadas
- [ ] Cumple Human Interface Guidelines
- [ ] Info.plist con permisos justificados (cámara, fotos, ubicación)

---

## 🤖 Google Play (Android)

### Requisitos previos
- Cuenta Google Play Developer ($25 una vez)
- App firmada con upload key
- Google Play Console configurado

### Información requerida
- Nombre: Cambialord
- Descripción corta (80 chars): Compra, vende e intercambia productos y servicios
- Categoría: Shopping
- Email de contacto: soporte@cambialord.com
- URL de privacidad: https://cambialord.com/privacidad

### Gráficos requeridos
| Tipo | Tamaño | Requerido |
|------|--------|-----------|
| Ícono | 512x512 PNG | Sí |
| Feature graphic | 1024x500 | Sí |
| Screenshots teléfono | min 2, máx 8 | Sí |
| Screenshots tablet 7" | min 0 | Recomendado |
| Screenshots tablet 10" | min 0 | Recomendado |

### Descripción completa (máx 4000 caracteres)
(Misma que iOS, adaptada si necesario)

### Content Rating
- Completar cuestionario IARC
- Categoría esperada: Everyone / Para todos

### Data Safety
Declarar uso de datos:
- Datos recopilados: nombre, email, teléfono, dirección, fotos
- Propósito: funcionalidad de la app, comunicación
- Datos compartidos: ninguno con terceros
- Cifrado en tránsito: sí (HTTPS)
- Eliminación de datos: disponible desde perfil

### Checklist pre-envío
- [ ] AAB firmado (no APK)
- [ ] ProGuard/R8 habilitado
- [ ] Target SDK 34
- [ ] Todos los gráficos subidos
- [ ] Data Safety completado
- [ ] Content Rating completado
- [ ] Permisos justificados en manifest
- [ ] Probado en dispositivos reales

---

## 🔄 Proceso de Actualización

### Pasos para nueva versión
1. Incrementar version name y version code
2. Actualizar changelog (What's New)
3. Build release (iOS + Android)
4. Subir a App Store Connect / Google Play Console
5. Enviar a revisión
6. Monitorear aprobación

### Tiempos de revisión estimados
- App Store: 24-48 horas (primera vez puede ser más)
- Google Play: 1-7 días (primera vez puede ser más)

### Rollout gradual (Google Play)
- Iniciar con 10% de usuarios
- Monitorear crashes y reviews
- Incrementar a 25% → 50% → 100%
- Rollback si crash rate > 1%
