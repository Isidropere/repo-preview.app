# Social Auth — Google, Facebook, Instagram

## Estado
PENDIENTE — infraestructura lista, faltan credenciales y activación

---

## Qué ya está hecho

- `laravel/socialite` instalado en `composer.json`
- Controlador unificado: `app/Http/Controllers/Auth/SocialAuthController.php`
  - Método `redirect(string $provider)` → redirige al proveedor
  - Método `callback(string $provider)` → maneja respuesta, crea o vincula usuario
  - Lee credenciales primero desde la tabla BD, cae al `.env` como fallback
- Modelo: `app/Models/OauthProvider.php`
- Migración ejecutada: `database/migrations/2026_03_20_000002_add_social_auth.php`
- Columnas agregadas a `users`: `facebook_id`, `instagram_id` (ya existía `google_id`)
- `config/services.php` actualizado con bloques `google`, `facebook`, `instagram`
- `.env` actualizado con variables vacías para los 3 proveedores
- Rutas registradas en `routes/web.php`:
  - `GET /auth/{provider}` → `social.login`
  - `GET /auth/{provider}/callback` → `social.callback`
  - `{provider}` acepta: `google|facebook|instagram`

---

## Tabla en base de datos

**Nombre:** `oauth_providers`

| columna        | tipo         | descripción                              |
|----------------|--------------|------------------------------------------|
| id             | bigint PK    | autoincrement                            |
| provider       | varchar(30)  | `google` / `facebook` / `instagram`      |
| client_id      | varchar      | App ID del proveedor                     |
| client_secret  | varchar      | App Secret del proveedor                 |
| redirect_uri   | varchar      | URL de callback registrada               |
| activo         | tinyint(1)   | 0 = deshabilitado, 1 = habilitado        |
| created_at     | timestamp    |                                          |
| updated_at     | timestamp    |                                          |

### Cómo activar un proveedor (SQL directo o desde panel admin)

```sql
UPDATE oauth_providers
SET client_id = 'TU_CLIENT_ID',
    client_secret = 'TU_CLIENT_SECRET',
    activo = 1
WHERE provider = 'google';
```

---

## Tasks pendientes

### Task 1 — Activar Google OAuth
- [ ] Completar Google Cloud Console:
  1. Proyecto: `cambialo-rd` (ya creado)
  2. Ir a "APIs y servicios" → "Credenciales" → "Crear credenciales" → "ID de cliente OAuth"
  3. Tipo: Aplicación web
  4. URI de redirección autorizada: `http://localhost:8080/auth/google/callback`
  5. Copiar Client ID y Client Secret
- [ ] Actualizar tabla BD:
  ```sql
  UPDATE oauth_providers SET client_id='...', client_secret='...', activo=1 WHERE provider='google';
  ```
- [ ] O alternativamente poner en `.env`:
  ```
  GOOGLE_CLIENT_ID=...
  GOOGLE_CLIENT_SECRET=...
  ```
  y correr `php artisan config:clear`

### Task 2 — Activar Facebook OAuth
- [ ] Ir a https://developers.facebook.com → Crear app → Tipo: "Consumidor"
- [ ] Agregar producto "Facebook Login"
- [ ] En configuración de Facebook Login → URI de redirección válidos:
  `http://localhost:8080/auth/facebook/callback`
- [ ] Copiar App ID y App Secret desde "Configuración" → "Básica"
- [ ] Actualizar tabla BD:
  ```sql
  UPDATE oauth_providers SET client_id='...', client_secret='...', activo=1 WHERE provider='facebook';
  ```

### Task 3 — Activar Instagram OAuth
- [ ] Instagram usa la API de Facebook (Meta) — NO tiene OAuth independiente
- [ ] En la misma app de Facebook, agregar producto "Instagram Basic Display"
- [ ] Configurar URI de redirección: `http://localhost:8080/auth/instagram/callback`
- [ ] IMPORTANTE: el driver de Socialite para Instagram es diferente:
  - Instalar: `composer require socialiteproviders/instagram`
  - Registrar en `app/Providers/AppServiceProvider.php`:
    ```php
    \Laravel\Socialite\Facades\Socialite::extend('instagram', function ($app) {
        $config = $app['config']['services.instagram'];
        return \Laravel\Socialite\Two\InstagramProvider::class;
    });
    ```
  - O usar el provider de la comunidad: https://socialiteproviders.com/Instagram/
- [ ] Actualizar tabla BD con credenciales de Instagram Basic Display

### Task 4 — Botones en la vista de login
- [ ] Agregar botones "Continuar con Google / Facebook / Instagram" en `resources/views/login.blade.php`
- [ ] Solo mostrar el botón si el proveedor está activo en BD:
  ```blade
  @if(\App\Models\OauthProvider::getActive('google'))
      <a href="{{ route('social.login', 'google') }}">Continuar con Google</a>
  @endif
  ```

### Task 5 — Panel admin para gestionar credenciales
- [ ] Crear vista en `/admin/oauth-providers` para editar client_id, client_secret y toggle activo
- [ ] Solo accesible por superadmin

---

## Notas importantes

- En producción cambiar las `redirect_uri` de `localhost:8080` a la URL real del dominio
- Instagram Basic Display API está siendo deprecada por Meta — evaluar usar "Facebook Login" con permisos de Instagram en su lugar
- El controlador `GoogleController.php` (legacy) sigue funcionando en `/auth/google` — se puede eliminar cuando el nuevo flujo esté probado
