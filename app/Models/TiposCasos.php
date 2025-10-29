<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiposCasos extends Model
{
    // 1. Tabla asociada
    protected $table = 'tipos_casos';

    // 2. Clave primaria
    protected $primaryKey = 'id';

    // 3. Auto‐increment (sí, pues IDENTITY en SQL Server)
    public $incrementing = true;

    // 4. Tipo de clave primaria
    protected $keyType = 'int';

    // 5. Desactivar timestamps automáticos (no vemos created_at / updated_at)
    public $timestamps = false;

    // 6. Columnas asignables en masa
    protected $fillable = [
        'materia_id',
        'nombre',
        'descripcion',
    ];

    // 7. Relaciones
    /**
     * Un tipo de caso **pertenece a** una materia de casos.
     */
    public function materia()
    {
        return $this->belongsTo(MateriaCaso::class, 'materia_id', 'id');
    }
}
