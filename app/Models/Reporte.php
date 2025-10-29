<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    // 1. Nombre exacto de la tabla
    protected $table = 'reportes';

    // 2. Clave primaria
    protected $primaryKey = 'id';

    // 3. Auto‐incremental (IDENTITY en SQL Server)
    public $incrementing = true;

    // 4. Tipo de la clave primaria
    protected $keyType = 'int';

    // 5. Sin timestamps (la tabla no tiene created_at / updated_at)
    public $timestamps = false;

    // 6. Campos asignables masivamente
    protected $fillable = [
        'titulo',
        'tipo_reporte',
        'descripcion',
        'parametros',
        'fecha_generacion',
        'generado_por',
    ];

    // 7. Casts para tipos especiales y cifrado de datos sensibles
    protected $casts = [
        'parametros'       => 'encrypted', // para proteger información sensible o parámetros
        'fecha_generacion' => 'datetime',
    ];

    // 8. Relación con el usuario que generó el reporte
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'generado_por', 'id');
    }
}
