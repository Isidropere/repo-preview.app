# Tasks — Social Auth Providers

## Task 1: Activar Google OAuth
- [ ] Obtener credenciales de Google Cloud Console
- [ ] Actualizar `oauth_providers` en BD o `.env`
- [ ] Probar login con Google en `http://localhost:8080/auth/google`

## Task 2: Activar Facebook OAuth
- [ ] Crear app en https://developers.facebook.com
- [ ] Configurar Facebook Login con redirect URI
- [ ] Actualizar `oauth_providers` en BD
- [ ] Probar login con Facebook en `http://localhost:8080/auth/facebook`

## Task 3: Activar Instagram OAuth
- [ ] Instalar `socialiteproviders/instagram` via composer
- [ ] Registrar provider en AppServiceProvider
- [ ] Configurar Instagram Basic Display en Meta Developers
- [ ] Actualizar `oauth_providers` en BD
- [ ] Probar login con Instagram en `http://localhost:8080/auth/instagram`

## Task 4: Botones en vista login
- [ ] Agregar botones sociales en `resources/views/login.blade.php`
- [ ] Mostrar solo proveedores activos (consultar tabla `oauth_providers`)

## Task 5: Panel admin para gestionar credenciales OAuth
- [ ] Vista `/admin/oauth-providers` (solo superadmin)
- [ ] CRUD de client_id, client_secret, activo por proveedor
