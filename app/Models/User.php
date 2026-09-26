<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'name',
        'username',
        'email',
        'rol',
        'cargo_titulo',
        'password',
        'estado',
        'codigo',
    ];

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
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ROLES Y PERMISOS DE APAFA & I.E.
    |--------------------------------------------------------------------------
    | 1. ADMIN             : Presidente / Superadministrador (Acceso total)
    | 2. SECRETARIO        : Padrón de padres, alumnos, carnés, asambleas
    | 3. TESORERO          : Caja, cuotas, multas, POS y facturación UBL 2.1
    | 4. COMITE_VIGILANCIA : Auditoría y fiscalización (Solo lectura)
    | 5. VOCAL_OPERADOR    : Puerta / Lector QR y código de barras en faenas y portería
    | 6. DIRECTOR          : Dirección I.E. (Quórum, estadísticas, uso de espacios)
    */

    public function isAdmin(): bool
    {
        return $this->rol === 'ADMIN';
    }

    public function isSecretario(): bool
    {
        return $this->rol === 'SECRETARIO';
    }

    public function isTesorero(): bool
    {
        return $this->rol === 'TESORERO';
    }

    public function isComiteVigilancia(): bool
    {
        return $this->rol === 'COMITE_VIGILANCIA';
    }

    public function isVocalOperador(): bool
    {
        return $this->rol === 'VOCAL_OPERADOR';
    }

    public function isDirector(): bool
    {
        return $this->rol === 'DIRECTOR';
    }

    /**
     * Verifica si el usuario tiene uno de los roles dados.
     */
    public function hasRole(string|array $roles): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin tiene acceso a todo
        }

        $rolesArray = is_array($roles) ? $roles : func_get_args();
        return in_array($this->rol, $rolesArray);
    }

    /**
     * Indica si el usuario puede realizar modificaciones (El Comité de Vigilancia es de Solo Lectura).
     */
    public function canWrite(): bool
    {
        return $this->rol !== 'COMITE_VIGILANCIA';
    }

    /**
     * Nombre legible del rol.
     */
    public function getRolNombreAttribute(): string
    {
        return match ($this->rol) {
            'ADMIN' => 'Presidente / Administrador',
            'SECRETARIO' => 'Secretario de Directiva',
            'TESORERO' => 'Tesorero de Directiva',
            'COMITE_VIGILANCIA' => 'Comité de Vigilancia (Fiscalizador)',
            'VOCAL_OPERADOR' => 'Vocal / Operador de Puerta',
            'DIRECTOR' => 'Director de la I.E.',
            default => $this->rol ?? 'Usuario',
        };
    }

    /**
     * Badge estilizado para la interfaz.
     */
    public function getRolBadgeHtmlAttribute(): string
    {
        return match ($this->rol) {
            'ADMIN' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200"><i class="fa-solid fa-crown text-[10px]"></i> Administrador</span>',
            'SECRETARIO' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-file-signature text-[10px]"></i> Secretario</span>',
            'TESORERO' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-cash-register text-[10px]"></i> Tesorero</span>',
            'COMITE_VIGILANCIA' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200"><i class="fa-solid fa-eye text-[10px]"></i> Vigilancia (Auditoría)</span>',
            'VOCAL_OPERADOR' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-cyan-50 text-cyan-700 border border-cyan-200"><i class="fa-solid fa-qrcode text-[10px]"></i> Vocal / Operador</span>',
            'DIRECTOR' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300"><i class="fa-solid fa-school text-[10px]"></i> Director I.E.</span>',
            default => '<span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600">' . e($this->rol) . '</span>',
        };
    }
}
