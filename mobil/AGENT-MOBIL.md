# ESTÁNDAR GLOBAL — APP MÓVIL CAMBIALORD

Proyecto: App móvil multiplataforma para Cambialord (iOS & Android)
Tecnología: React Native 0.73+ (JavaScript/TypeScript)
Backend: API REST existente (Laravel 11.x)
Configuración de ambiente: ver `configuracion-ambiente.md`

## Arquitectura Móvil
- Clean Architecture (Presentación → Dominio → Datos)
- Screens → solo UI y navegación
- ViewModels/Controllers → lógica de presentación
- Services → comunicación con API
- Models → estructuras de datos

## Reglas obligatorias
- No lógica de negocio en las pantallas (screens)
- Validar inputs antes de enviar al API
- Manejar estados de carga (loading, error, success)
- Soporte offline básico (caché de catálogo)
- Diseño responsive para tablets y teléfonos

## Comunicación con Backend
- Base URL: configurable por ambiente (dev, staging, prod)
- Autenticación: Bearer Token (JWT o Sanctum)
- Formato de respuesta esperado del API:
```json
{
  "success": true,
  "data": null,
  "message": ""
}
```

## Seguridad Móvil
- Almacenar tokens en almacenamiento seguro (Keychain/Keystore)
- No guardar contraseñas en texto plano
- Certificate pinning en producción
- Ofuscar código en builds de release
- No exponer API keys en el código fuente

## Versionamiento
- Semantic versioning: MAJOR.MINOR.PATCH
- Build numbers incrementales
- Changelog por versión
