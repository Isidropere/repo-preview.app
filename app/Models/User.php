<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;



/**
 * ============================================================
 * Modelo: User (Usuario)
 * ============================================================
 * Representa un usuario de la plataforma CambialóRD.
 *
 * Tabla BD: users
 *
 * Tipos de usuario (id_tipo_usuario):
 *   1 = Comprador
 *   2 = Vendedor
 *   3 = Admin (no seleccionable por usuario)
 *   4 = Super Admin (no seleccionable por usuario)
 *
 * Campos de permisos (NO en $fillable por seguridad):
 *   isAdmin      → acceso al panel /admin
 *   isSuperAdmin → acceso a estadísticas y mensajes predefinidos
 *
 * Estatus: 1 = Activo, 0 = Inactivo
 *
 * Implementa MustVerifyEmail — requiere verificación de email
 * para publicar artículos y realizar compras.
 * ============================================================
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     * 
     */

    // 🔹 Agrega el nombre correcto de la tabla aquí
    protected $table = 'users'; 

    protected $fillable = [
        'nombres',
        'apellidos',
        'telefono',
        'nombre_usuario',
        'email',
        'foto_perfil',
        'foto_perfil_estado',
        'foto_perfil_motivo_rechazo',
        'google_id',
        'facebook_id',
        'instagram_id',
        'email_verified_at',
        'password',
        'estatus',
        'id_tipo_usuario',
        'remember_token',
        'created_at',
        'updated_at',
        // 'isAdmin' / 'isSuperAdmin' excluidos de fillable — nunca asignar masivamente
        'profile_photo_path',
        'password_defined',
    ];

    /** Superadmin: acceso a estadísticas y mensajes predefinidos */
    public function isSuperAdminUser(): bool
    {
        return (bool) ($this->attributes['isSuperAdmin'] ?? false);
    }

    /** Contable: acceso exclusivo al ERP */
    public function isContableUser(): bool
    {
        return (bool) ($this->attributes['isContable'] ?? false);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'isContable' => 'boolean',
            'password_defined' => 'boolean',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPassword($token, $this->email));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail());
    }

    public function tiposUsuario()
    {
        return $this->belongsTo(Tipos_usuario::class, 'id_tipo_usuario', 'id_tipo_usuario');
    }
    public function carrito()
    {
        // Suponiendo que en carrito tienes la columna id_user como FK
        return $this->hasOne(Carrito::class, 'id_user', 'id');
    }

    public function tarjetasPago()
    {
        return $this->hasMany(TarjetaPago::class, 'id_user', 'id')->where('estatus', 1);
    }

    public function direcciones()
    {
        return $this->hasMany(\App\Models\Direcciones::class, 'id_user', 'id');
    }

    public function hojaVida()
    {
        return $this->hasOne(\App\Models\HojaVida::class, 'id_user', 'id');
    }
}
