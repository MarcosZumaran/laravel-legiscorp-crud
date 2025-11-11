<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    
    // Configuración de clave primaria
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'tipo_persona',
        'tipo_documento',
        'numero_documento',
        'nombres',
        'apellidos',
        'razon_social',
        'representante_legal',
        'telefono',
        'correo',
        'direccion',
        'estado',
        'creado_en',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
        'correo' => 'encrypted',    
        'telefono' => 'encrypted',  
    ];

    // VALORES PERMITIDOS
    const TIPOS_PERSONA = ['Jurídica', 'Natural'];
    const TIPOS_DOCUMENTO = ['Pasaporte', 'RUC', 'DNI'];
    const ESTADOS = ['Activo', 'Inactivo'];

    // ATRIBUTOS POR DEFECTO
    protected $attributes = [
        'estado' => 'Activo',
        'creado_en' => null, // La BD usa GETDATE() por defecto
    ];

    // RELACIONES
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'cliente_id');
    }

    // SCOPES PARA BÚSQUEDAS
    public function scopePorTipoPersona($query, $tipo)
    {
        return $query->where('tipo_persona', $tipo);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorTipoDocumento($query, $tipoDocumento)
    {
        return $query->where('tipo_documento', $tipoDocumento);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where('numero_documento', 'LIKE', "%{$termino}%")
                    ->orWhere('nombres', 'LIKE', "%{$termino}%")
                    ->orWhere('apellidos', 'LIKE', "%{$termino}%")
                    ->orWhere('razon_social', 'LIKE', "%{$termino}%")
                    ->orWhere('representante_legal', 'LIKE', "%{$termino}%");
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('creado_en', '>=', now()->subDays($dias));
    }

    // ATRIBUTOS CALCULADOS
    public function getNombreCompletoAttribute()
    {
        if ($this->tipo_persona === 'Jurídica') {
            return $this->razon_social ?: 'Sin razón social';
        }
        
        return trim(($this->nombres ?? '') . ' ' . ($this->apellidos ?? ''));
    }

    public function getTipoDocumentoCompletoAttribute()
    {
        return "{$this->tipo_documento}: {$this->numero_documento}";
    }

    public function getEsNaturalAttribute()
    {
        return $this->tipo_persona === 'Natural';
    }

    public function getEsJuridicaAttribute()
    {
        return $this->tipo_persona === 'Jurídica';
    }

    public function getInformacionContactoAttribute()
    {
        $contacto = [];
        if ($this->telefono) $contacto[] = "Tel: {$this->telefono}";
        if ($this->correo) $contacto[] = "Email: {$this->correo}";
        if ($this->direccion) $contacto[] = "Dir: {$this->direccion}";
        
        return implode(' | ', $contacto) ?: 'Sin información de contacto';
    }

    // MÉTODOS UTILITARIOS
    public static function buscarPorDocumento($numeroDocumento)
    {
        return self::where('numero_documento', $numeroDocumento)->first();
    }

    public function puedeEliminar()
    {
        // Verificar si no tiene documentos asociados
        return !$this->documentos()->exists();
    }

    public function activar()
    {
        $this->update(['estado' => 'Activo']);
    }

    public function desactivar()
    {
        $this->update(['estado' => 'Inactivo']);
    }

    // VALIDACIÓN AUTOMÁTICA
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validar tipo_persona
            if (!in_array($model->tipo_persona, self::TIPOS_PERSONA)) {
                throw new \Exception("Tipo de persona no válido: {$model->tipo_persona}");
            }

            // Validar tipo_documento
            if (!in_array($model->tipo_documento, self::TIPOS_DOCUMENTO)) {
                throw new \Exception("Tipo de documento no válido: {$model->tipo_documento}");
            }

            // Validar estado
            if ($model->estado && !in_array($model->estado, self::ESTADOS)) {
                throw new \Exception("Estado no válido: {$model->estado}");
            }

            // Validar campos según tipo de persona
            if ($model->tipo_persona === 'Natural') {
                if (empty($model->nombres) || empty($model->apellidos)) {
                    throw new \Exception("Para persona natural, nombres y apellidos son obligatorios.");
                }
            } else {
                if (empty($model->razon_social)) {
                    throw new \Exception("Para persona jurídica, la razón social es obligatoria.");
                }
            }
        });

        static::deleting(function ($model) {
            if (!$model->puedeEliminar()) {
                throw new \Exception("No se puede eliminar el cliente porque tiene documentos asociados.");
            }
        });
    }
}