<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TiposCasos extends Model
{
    use HasFactory;
    // Configuración básica
    protected $table = 'tipos_casos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    // Columnas asignables
    protected $fillable = [
        'materia_id',
        'nombre',
        'descripcion',
    ];

    // Casts para tipos de datos
    protected $casts = [
        'id' => 'integer',
        'materia_id' => 'integer',
    ];

    // Relación con MateriaCaso
    public function materia(): BelongsTo
    {
        return $this->belongsTo(MateriaCaso::class, 'materia_id');
    }

    // Scopes útiles para consultas comunes
    public function scopeBuscar($query, $termino)
    {
        if(isset($termino) && isset($query)) {
            return $query->where('nombre', 'LIKE', "%{$termino}%")
                   ->orWhere('descripcion', 'LIKE', "%{$termino}%");
        }
    }

    public function scopePorMateria($query, $materiaId)
    {
        if(isset($query) && isset($materiaId)){
            return $query->where('materia_id', $materiaId);
        }
    }
}