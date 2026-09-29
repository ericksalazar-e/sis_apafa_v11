<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionCpe extends Model
{
    use HasFactory;

    protected $table = 'configuraciones_cpe';

    protected $fillable = [
        'empresa_id',
        'ambiente',
        'ruc_emisor',
        'razon_social',
        'nombre_comercial',
        'direccion_fiscal',
        'ubigeo',
        'departamento',
        'provincia',
        'distrito',
        'usuario_sol',
        'clave_sol',
        'certificado_path',
        'certificado_password',
        'certificado_vencimiento',
        'serie_boleta',
        'serie_factura',
        'serie_nota_venta',
        'correlativo_boleta',
        'correlativo_factura',
        'correlativo_nota_venta',
        'modo_emision',
        'auto_envio_sunat',
        'formato_impresion',
        'leyenda_pie',
        'activo',
    ];

    protected $casts = [
        'auto_envio_sunat' => 'boolean',
        'activo' => 'boolean',
        'certificado_vencimiento' => 'date',
        'correlativo_boleta' => 'integer',
        'correlativo_factura' => 'integer',
        'correlativo_nota_venta' => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Obtiene la configuración CPE para una empresa, o inicializa una con datos por defecto.
     */
    public static function obtenerParaEmpresa(int $empresaId): self
    {
        $config = self::where('empresa_id', $empresaId)->first();

        if (! $config) {
            $empresa = Empresa::find($empresaId);
            $config = self::create([
                'empresa_id' => $empresaId,
                'ambiente' => 'BETA',
                'ruc_emisor' => $empresa->ruc ?? '20601234567',
                'razon_social' => $empresa->nombre ?? 'ASOCIACION DE PADRES DE FAMILIA - APAFA',
                'nombre_comercial' => $empresa->nombre ?? 'I.E. APAFA',
                'direccion_fiscal' => $empresa->ubicacion ?? 'Av. Principal S/N',
                'ubigeo' => '150101',
                'departamento' => 'LIMA',
                'provincia' => 'LIMA',
                'distrito' => 'LIMA',
                'usuario_sol' => 'MODDATOS',
                'clave_sol' => 'moddatos',
                'serie_boleta' => 'B001',
                'serie_factura' => 'F001',
                'serie_nota_venta' => 'NV01',
                'correlativo_boleta' => 1,
                'correlativo_factura' => 1,
                'correlativo_nota_venta' => 1,
                'modo_emision' => 'ASINCRONO_COLA',
                'auto_envio_sunat' => true,
                'formato_impresion' => 'TICKET_80MM',
                'leyenda_pie' => 'Representación impresa del Comprobante de Pago Electrónico UBL 2.1',
                'activo' => true,
            ]);
        }

        return $config;
    }

    public function obtenerSerie(string $tipo): string
    {
        return match (strtoupper($tipo)) {
            'FACTURA' => $this->serie_factura ?: 'F001',
            'BOLETA' => $this->serie_boleta ?: 'B001',
            default => $this->serie_nota_venta ?: 'NV01',
        };
    }

    public function esProduccion(): bool
    {
        return $this->ambiente === 'PRODUCCION';
    }

    public function esAsincrono(): bool
    {
        return $this->modo_emision === 'ASINCRONO_COLA';
    }
}
