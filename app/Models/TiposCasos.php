<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiposCasos extends Model
{
    // 1. Tabla asociada
    protected $table = 'tipos_casos';

    // 2. Clave primaria
    protected $primaryKey = 'id';

    // 3. Auto-increment
    public $incrementing = true;

    // 4. Tipo de clave primaria
    protected $keyType = 'int';

    // 5. Desactivar timestamps automáticos
    public $timestamps = false;

    // 6. Columnas asignables en masa
    protected $fillable = [
        'materia_id',
        'nombre',
        'descripcion',
    ];

    // 7. Casts para tipos de datos
    protected $casts = [
        'id' => 'integer',
        'materia_id' => 'integer',
    ];

    // 8. RELACIONES
    public function materia(): BelongsTo
    {
        return $this->belongsTo(MateriaCaso::class, 'materia_id', 'id');
    }

    // 9. SCOPES PARA BÚSQUEDAS
    public function scopeBuscar($query, $termino)
    {
        return $query->where('nombre', 'LIKE', "%{$termino}%")
            ->orWhere('descripcion', 'LIKE', "%{$termino}%");
    }

    public function scopePorMateria($query, $materiaId)
    {
        return $query->where('materia_id', $materiaId);
    }

    public function scopeOrdenarPorNombre($query, $direccion = 'asc')
    {
        return $query->orderBy('nombre', $direccion);
    }

    // 10. ATRIBUTOS CALCULADOS
    public function getNombreCompletoAttribute()
    {
        $materia = $this->materia ? $this->materia->nombre : 'Sin Materia';

        return "{$this->nombre} ({$materia})";
    }

    public function getDescripcionCortaAttribute()
    {
        if (empty($this->descripcion)) {
            return 'Sin descripción';
        }

        return strlen($this->descripcion) > 100
            ? substr($this->descripcion, 0, 100).'...'
            : $this->descripcion;
    }

    // 11. MÉTODOS UTILITARIOS
    public static function obtenerPorMateria($materiaId)
    {
        return self::porMateria($materiaId)
            ->ordenarPorNombre()
            ->get();
    }

    // 12. VALIDACIÓN BÁSICA
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validación simple de materia existente
            if (! \App\Models\MateriaCaso::where('id', $model->materia_id)->exists()) {
                throw new \Exception("La materia con ID {$model->materia_id} no existe.");
            }
        });
    }

    // 13. REGLAS DE VALIDACIÓN
    public static function reglas($id = null)
    {
        return [
            'materia_id' => 'required|integer|exists:materias_casos,id',
            'nombre' => 'required|string|max:100|unique:tipos_casos,nombre,'.$id.',id,materia_id,'.request('materia_id'),
            'descripcion' => 'nullable|string|max:2000',
        ];
    }

    // 14. VERIFICACIÓN PARA ELIMINAR
    public function puedeEliminar()
    {
        // Si tienes casos asociados, verificar aquí
        // return !\App\Models\Caso::where('tipo_caso_id', $this->id)->exists();
        return true; // Por ahora, asumiendo que no hay restricciones
    }
}
