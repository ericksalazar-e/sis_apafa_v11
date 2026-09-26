<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'cargos';

    protected $fillable = [
        'empresa_id',
        'nombre',
    ];

    public function padres(): HasMany
    {
        return $this->hasMany(PadreFamilia::class);
    }

    /**
     * Determina si el cargo pertenece al Consejo Directivo (Órgano de Gestión y Administración)
     * Conforme a la Ley N° 28628: Presidente, Vicepresidente, Secretario, Tesorero y Vocales.
     */
    public function isConsejoDirectivo(): bool
    {
        $nom = mb_strtoupper($this->nombre);
        if (str_contains($nom, 'VIGILANCIA') || $nom === 'PADRE DE FAMILIA') {
            return false;
        }
        return str_contains($nom, 'PRESIDENTE') ||
               str_contains($nom, 'VICEPRESIDENTE') ||
               str_contains($nom, 'SECRETARIO') ||
               str_contains($nom, 'TESORERO') ||
               str_contains($nom, 'VOCAL');
    }

    /**
     * Determina si el cargo pertenece al Consejo de Vigilancia (Órgano de Control y Fiscalización)
     */
    public function isConsejoVigilancia(): bool
    {
        return str_contains(mb_strtoupper($this->nombre), 'VIGILANCIA');
    }

    /**
     * Nombre formal del órgano institucional según Ley N° 28628
     */
    public function getOrganoAttribute(): string
    {
        if ($this->isConsejoVigilancia()) {
            return 'Consejo de Vigilancia (Órgano de Control)';
        }
        if ($this->isConsejoDirectivo()) {
            return 'Consejo Directivo (Órgano de Gestión)';
        }
        return 'Asamblea General (Asociado)';
    }

    /**
     * Facultades y atribuciones legales del cargo (Ley N° 28628 y D.S. N° 004-2006-ED)
     */
    public function getFuncionesLegalesAttribute(): string
    {
        $nom = mb_strtoupper($this->nombre);
        if (str_contains($nom, 'VICEPRESIDENTE')) {
            return 'Reemplaza al Presidente en caso de vacancia o ausencia legal. Coordina y asesora a los Comités de Aula y Talleres.';
        }
        if (str_contains($nom, 'PRESIDENTE') && !str_contains($nom, 'VIGILANCIA')) {
            return 'Representante legal de la APAFA. Convoca y preside las asambleas, suscribe contratos, firma cuentas y cheques mancomunados con el Tesorero y rinde cuentas del POA.';
        }
        if (str_contains($nom, 'SECRETARIO') && !str_contains($nom, 'VIGILANCIA')) {
            return 'Custodia y redacta los libros de actas de asambleas y directiva; mantiene y actualiza el Padrón de Asociados y expide credenciales oficiales.';
        }
        if (str_contains($nom, 'TESORERO')) {
            return 'Custodia y administra los fondos de la APAFA; recauda cuotas y concesiones de quioscos; firma la cuenta mancomunada y rinde cuentas documentadas con comprobantes de pago.';
        }
        if (str_contains($nom, 'VOCAL') && !str_contains($nom, 'VIGILANCIA')) {
            return 'Preside e integra comisiones de trabajo (mantenimiento seguro, cultura y deporte); vigila la seguridad en faenas y sustituye directivos según prelación.';
        }
        if (str_contains($nom, 'PRESIDENTE') && str_contains($nom, 'VIGILANCIA')) {
            return 'Preside el órgano de fiscalización interna de la gestión económica, patrimonial y cumplimiento del POA de la APAFA.';
        }
        if (str_contains($nom, 'VIGILANCIA')) {
            return 'Audita libros contables, padrón de asociados e ingresos de quioscos; emite dictámenes previos a las asambleas generales ordinarias.';
        }
        return 'Participación democrática con voz y voto en la Asamblea General.';
    }

    /**
     * Ícono representativo FontAwesome
     */
    public function getIconoAttribute(): string
    {
        $nom = mb_strtoupper($this->nombre);
        if (str_contains($nom, 'PRESIDENTE') && !str_contains($nom, 'VIGILANCIA')) return 'fa-solid fa-user-tie';
        if (str_contains($nom, 'VICEPRESIDENTE')) return 'fa-solid fa-user-shield';
        if (str_contains($nom, 'SECRETARIO') && !str_contains($nom, 'VIGILANCIA')) return 'fa-solid fa-file-signature';
        if (str_contains($nom, 'TESORERO')) return 'fa-solid fa-coins';
        if (str_contains($nom, 'VOCAL') && !str_contains($nom, 'VIGILANCIA')) return 'fa-solid fa-users-gear';
        if (str_contains($nom, 'VIGILANCIA')) return 'fa-solid fa-shield-halved';
        return 'fa-solid fa-user';
    }

    /**
     * Clases Tailwind para badges y tarjetas
     */
    public function getColorClassesAttribute(): array
    {
        $nom = mb_strtoupper($this->nombre);
        if (str_contains($nom, 'PRESIDENTE') && !str_contains($nom, 'VIGILANCIA')) {
            return [
                'bg' => 'bg-indigo-50',
                'text' => 'text-indigo-700',
                'border' => 'border-indigo-200',
                'gradient' => 'from-indigo-600 to-blue-600',
                'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200'
            ];
        }
        if (str_contains($nom, 'VICEPRESIDENTE')) {
            return [
                'bg' => 'bg-blue-50',
                'text' => 'text-blue-700',
                'border' => 'border-blue-200',
                'gradient' => 'from-blue-600 to-cyan-600',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200'
            ];
        }
        if (str_contains($nom, 'SECRETARIO') && !str_contains($nom, 'VIGILANCIA')) {
            return [
                'bg' => 'bg-sky-50',
                'text' => 'text-sky-700',
                'border' => 'border-sky-200',
                'gradient' => 'from-sky-600 to-indigo-600',
                'badge' => 'bg-sky-100 text-sky-800 border-sky-200'
            ];
        }
        if (str_contains($nom, 'TESORERO')) {
            return [
                'bg' => 'bg-emerald-50',
                'text' => 'text-emerald-700',
                'border' => 'border-emerald-200',
                'gradient' => 'from-emerald-600 to-teal-600',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200'
            ];
        }
        if (str_contains($nom, 'VOCAL') && !str_contains($nom, 'VIGILANCIA')) {
            return [
                'bg' => 'bg-amber-50',
                'text' => 'text-amber-700',
                'border' => 'border-amber-200',
                'gradient' => 'from-amber-600 to-orange-600',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200'
            ];
        }
        if (str_contains($nom, 'VIGILANCIA')) {
            return [
                'bg' => 'bg-rose-50',
                'text' => 'text-rose-700',
                'border' => 'border-rose-200',
                'gradient' => 'from-rose-600 to-red-600',
                'badge' => 'bg-rose-100 text-rose-800 border-rose-200'
            ];
        }
        return [
            'bg' => 'bg-slate-50',
            'text' => 'text-slate-700',
            'border' => 'border-slate-200',
            'gradient' => 'from-slate-600 to-slate-700',
            'badge' => 'bg-slate-100 text-slate-800 border-slate-200'
        ];
    }
}
