<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

class Caso extends Model
{
    use EncryptedAttribute;

    protected $table = 'casos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'codigo_caso',
        'numero_expediente',
        'numero_carpeta_fiscal',
        'titulo',
        'descripcion',
        'materia_id',
        'tipo_caso_id',
        'estado',
        'fecha_inicio',
        'fecha_cierre',
        'cliente_id',
        'abogado_id',
        'contraparte',
        'juzgado',
        'fiscal',
        'creado_en',
    ];

    // Campos que se encriptarán
    protected $encryptable = [
        'descripcion',
        'contraparte',
        'juzgado',
        'fiscal',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_cierre' => 'date',
        'creado_en' => 'datetime',
    ];

    // Estados permitidos
    const ESTADOS = ['Abierto', 'En Proceso', 'Cerrado'];

    // Atributos por defecto
    protected $attributes = [
        'estado' => 'Abierto',
    ];

    // Relaciones (mantenidas - esenciales)
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function abogado(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'abogado_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(MateriaCaso::class, 'materia_id');
    }

    public function tipoCaso(): BelongsTo
    {
        return $this->belongsTo(TiposCasos::class, 'tipo_caso_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(ComentarioCaso::class, 'caso_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'caso_id');
    }

    // Scopes útiles (mantenidos)
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopePorAbogado($query, $abogadoId)
    {
        return $query->where('abogado_id', $abogadoId);
    }

    public function scopePorMateria($query, $materiaId)
    {
        return $query->where('materia_id', $materiaId);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where('codigo_caso', 'LIKE', "%{$termino}%")
                    ->orWhere('numero_expediente', 'LIKE', "%{$termino}%")
                    ->orWhere('titulo', 'LIKE', "%{$termino}%")
                    ->orWhereEncrypted('contraparte', 'LIKE', "%{$termino}%")
                    ->orWhereEncrypted('juzgado', 'LIKE', "%{$termino}%")
                    ->orWhereEncrypted('fiscal', 'LIKE', "%{$termino}%");
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', '!=', 'Cerrado');
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('creado_en', '>=', now()->subDays($dias));
    }
}