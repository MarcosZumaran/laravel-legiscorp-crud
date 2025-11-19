<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reporte extends Model
{
    use EncryptedAttribute, HasFactory;

    // Configuración básica
    protected $table = 'reportes';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    // Columnas asignables (sin ip y accion)
    protected $fillable = [
        'titulo',
        'tipo_reporte',
        'descripcion',
        'parametros',
        'fecha_generacion',
        'generado_por',
    ];

    //estos campos se encriptarán
    protected $encryptable = [
        'descripcion',
        'parametros',
    ];

    // Casts para tipos de datos
    protected $casts = [
        'fecha_generacion' => 'datetime',
        'parametros' => 'array' // JSON automático
    ];

    // Tipos de reporte permitidos
    const TIPOS_REPORTE = [
        'General',
        'Calendario',
        'Documentos',
        'Clientes',
        'Casos',
    ];

    // Scopes útiles
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

    // Relación con Usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'generado_por');
    }

    // Validación de tipo de reporte
    public function setTipoReporteAttribute($value)
    {
        if (!in_array($value, self::TIPOS_REPORTE)) {
            throw new \InvalidArgumentException("Tipo de reporte no válido: $value");
        }
        $this->attributes['tipo_reporte'] = $value;
    }
}