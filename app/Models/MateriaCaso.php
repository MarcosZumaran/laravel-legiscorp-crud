<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MateriaCaso extends Model
{
    use HasFactory;
    protected $table = 'materias_casos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    // Scope para búsquedas (mantenido porque es útil)
    public function scopeBuscar($query, $termino)
    {
        return $query->where('nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('descripcion', 'LIKE', "%{$termino}%");
    }

    // Relación con TiposCasos (esencial)
    public function tiposCasos(): HasMany
    {
        return $this->hasMany(TiposCasos::class, 'materia_id');
    }
}