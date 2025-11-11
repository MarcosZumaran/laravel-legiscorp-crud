<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    // SCOPES PARA BÚSQUEDAS
    public function scopeBuscar($query, $termino)
    {
        return $query->where('nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('descripcion', 'LIKE', "%{$termino}%");
    }

    public function scopeOrdenarPorNombre($query, $direccion = 'asc')
    {
        return $query->orderBy('nombre', $direccion);
    }

    // RELACIÓN CON TIPOS DE CASOS
    public function tiposCasos(): HasMany
    {
        return $this->hasMany(TiposCasos::class, 'materia_id');
    }

    // ATRIBUTOS CALCULADOS
    public function getDescripcionCortaAttribute()
    {
        if (empty($this->descripcion)) {
            return 'Sin descripción';
        }

        return strlen($this->descripcion) > 100 
            ? substr($this->descripcion, 0, 100) . '...' 
            : $this->descripcion;
    }

    public function getTotalTiposCasosAttribute()
    {
        return $this->tiposCasos()->count();
    }

    public function getTieneTiposCasosAttribute()
    {
        return $this->tiposCasos()->exists();
    }

    // MÉTODOS UTILITARIOS
    public static function obtenerConTiposCasos()
    {
        return self::withCount('tiposCasos')
                  ->ordenarPorNombre()
                  ->get();
    }

    public function puedeEliminar()
    {
        // Verificar si no tiene tipos de casos asociados
        return !$this->tieneTiposCasos;
    }

    // VALIDACIÓN AUTOMÁTICA
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            if ($model->tiposCasos()->exists()) {
                throw new \Exception('No se puede eliminar la materia porque tiene tipos de casos asociados.');
            }
        });
    }
}