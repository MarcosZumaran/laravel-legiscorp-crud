<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caso extends Model
{
    use HasFactory;

    protected $table = 'casos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'codigo_caso',
        'expediente_completo',
        'secuencia',
        'anio',
        'indicador_fuero',
        'codigo_organo',
        'tipo_organo',
        'especialidad',
        'distrito',
        'titulo',
        'descripcion',
        'materia_id',
        'tipo_caso_id',
        'estado',
        'fecha_inicio',
        'fecha_cierre',
        'cliente_id',
        'abogado_id',
        'contraparte'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function abogado()
    {
        return $this->belongsTo(Usuario::class, 'abogado_id');
    }

    public function materia()
    {
        return $this->belongsTo(MateriaCaso::class, 'materia_id');
    }

    public function tipoCaso()
    {
        return $this->belongsTo(TiposCasos::class, 'tipo_caso_id');
    }

    public function comentarios()
    {
        return $this->hasMany(ComentarioCaso::class, 'caso_id');
    }
}
