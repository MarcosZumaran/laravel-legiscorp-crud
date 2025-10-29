<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriaCaso extends Model
{
    use HasFactory;

    // Nombre exacto de la tabla en SQL Server
    protected $table = 'materias_casos';

    // Clave primaria personalizada (por si acaso Laravel busca "id" automáticamente)
    protected $primaryKey = 'id';

    // No se manejan timestamps porque tu tabla no los tiene
    public $timestamps = false;

    // Campos que pueden asignarse en masa (mass assignment)
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación: una materia puede tener muchos tipos de caso
    public function tiposCasos()
    {
        return $this->hasMany(TiposCasos::class, 'materia_id');
    }
}
