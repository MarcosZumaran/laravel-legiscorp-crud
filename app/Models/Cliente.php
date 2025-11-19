<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use EncryptedAttribute, HasFactory;

    protected $table = 'clientes';
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

    // Campos que se encriptarán
    protected $encryptable = [
        'numero_documento',
        'telefono',
        'correo',
        'direccion',
        'representante_legal',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
    ];

    // Valores permitidos
    const TIPOS_PERSONA = ['Jurídica', 'Natural'];
    const TIPOS_DOCUMENTO = ['Pasaporte', 'RUC', 'DNI'];
    const ESTADOS = ['Activo', 'Inactivo'];

    // Atributos por defecto
    protected $attributes = [
        'estado' => 'Activo',
    ];

    // Relación con Documentos
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'cliente_id');
    }

    // Scopes útiles
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
        return $query->where('nombres', 'LIKE', "%{$termino}%")
                    ->orWhere('apellidos', 'LIKE', "%{$termino}%")
                    ->orWhere('razon_social', 'LIKE', "%{$termino}%")
                    ->orWhereEncrypted('representante_legal', 'LIKE', "%{$termino}%")
                    ->orWhereEncrypted('numero_documento', 'LIKE', "%{$termino}%")
                    ->orWhereEncrypted('telefono', 'LIKE', "%{$termino}%")
                    ->orWhereEncrypted('correo', 'LIKE', "%{$termino}%")
                    ->orWhereEncrypted('direccion', 'LIKE', "%{$termino}%");
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('creado_en', '>=', now()->subDays($dias));
    }
}