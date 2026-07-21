<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\ReconocimientoPermiso;
use App\Models\PdfPermiso;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            // El primer usuario del sistema es el administrador principal.
            // En una instalación nueva se crea después de ejecutar las migraciones,
            // por eso sus permisos deben establecerse aquí y no depender solo del backfill.
            $esAdministradorPrincipal = (int) $user->getKey() === 1;

            $user->permisoReconocimientos()->create([
                'ver' => true,
                'crear' => $esAdministradorPrincipal,
                'editar' => $esAdministradorPrincipal,
                'aprobar' => $esAdministradorPrincipal,
                'descargar' => true,
                'cancelar' => $esAdministradorPrincipal,
                'administrar' => $esAdministradorPrincipal,
            ]);

            if (\Illuminate\Support\Facades\Schema::hasTable('etiqueta_permisos')) {
                $user->permisoEtiquetas()->create([
                    'ver' => true,
                    'crear' => $esAdministradorPrincipal,
                    'editar' => $esAdministradorPrincipal,
                    'eliminar' => $esAdministradorPrincipal,
                    'importar' => $esAdministradorPrincipal,
                    'descargar' => true,
                    'administrar' => $esAdministradorPrincipal,
                ]);
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('pdf_permisos')) {
                $user->permisoPdf()->create([
                    'ver' => true,
                    'procesar' => $esAdministradorPrincipal,
                    'descargar' => true,
                    'eliminar' => $esAdministradorPrincipal,
                    'administrar' => $esAdministradorPrincipal,
                ]);
            }
        });
    }

    public function permisoReconocimientos()
    {
        return $this->hasOne(ReconocimientoPermiso::class);
    }

    public function puedeReconocimientos(string $accion): bool
    {
        // Protección de acceso para el administrador principal. Esto también
        // corrige instalaciones existentes donde el usuario #1 recibió por
        // error un registro de permisos limitado al crearse después de migrar.
        if ((int) $this->getKey() === 1) {
            return true;
        }

        $permiso = $this->relationLoaded('permisoReconocimientos')
            ? $this->permisoReconocimientos
            : $this->permisoReconocimientos()->first();

        return $permiso ? (bool) ($permiso->{$accion} ?? false) : false;
    }


    public function permisoEtiquetas()
    {
        return $this->hasOne(EtiquetaPermiso::class);
    }

    public function puedeEtiquetas(string $accion): bool
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('etiqueta_permisos')) {
            return false;
        }

        if ((int) $this->getKey() === 1) {
            return true;
        }

        $permiso = $this->relationLoaded('permisoEtiquetas')
            ? $this->permisoEtiquetas
            : $this->permisoEtiquetas()->first();

        return $permiso ? (bool) ($permiso->{$accion} ?? false) : false;
    }


    public function permisoPdf()
    {
        return $this->hasOne(PdfPermiso::class);
    }

    public function puedePdf(string $accion): bool
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('pdf_permisos')) {
            return false;
        }

        if ((int) $this->getKey() === 1) {
            return true;
        }

        $permiso = $this->relationLoaded('permisoPdf')
            ? $this->permisoPdf
            : $this->permisoPdf()->first();

        return $permiso ? (bool) ($permiso->{$accion} ?? false) : false;
    }

    /**
     * Get the user's initials
     */
    public function initials()
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
