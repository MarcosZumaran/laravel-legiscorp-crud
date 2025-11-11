<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model
{
    protected $table = 'reportes';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'tipo_reporte',
        'descripcion',
        'parametros',
        'fecha_generacion',
        'generado_por',
    ];

    protected $casts = [
        'parametros' => 'encrypted', //  datos sensibles, por eso se encriptan
        'fecha_generacion' => 'datetime',
    ];

    // TIPOS DE REPORTE PERMITIDOS
    const TIPOS_REPORTE = [
        'General',
        'Calendario',
        'Documentos',
        'Clientes',
        'Casos',
    ];

    // SCOPES PARA BÚSQUEDAS
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_reporte', $tipo);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('generado_por', $usuarioId);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha_generacion', '>=', now()->subDays($dias));
    }

    // RELACIÓN CON USUARIO
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'generado_por', 'id');
    }

    // VALIDACIÓN DE TIPO DE REPORTE
    public function setTipoReporteAttribute($value)
    {
        if (! in_array($value, self::TIPOS_REPORTE)) {
            throw new \InvalidArgumentException("Tipo de reporte no válido: $value");
        }
        $this->attributes['tipo_reporte'] = $value;
    }

    // MÉTODOS UTILITARIOS
    public function getParametrosDecodificadosAttribute()
    {
        try {
            return json_decode($this->parametros, true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getEsRecienteAttribute()
    {
        return $this->fecha_generacion >= now()->subDay();
    }

    // VALIDACIÓN AUTOMÁTICA
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validar que el usuario existe
            if (! \App\Models\Usuario::where('id', $model->generado_por)->exists()) {
                throw new \Exception("El usuario con ID {$model->generado_por} no existe.");
            }
        });
    }
}
