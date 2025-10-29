<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendario extends Model
{
    use HasFactory;

    protected $table = 'calendario';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'tipo_evento',
        'estado',
        'color',
        'caso_id',
        'etapa_id',
        'abogado_id',
        'cliente_id',
        'creado_por',
        'creado_en',
    ];

    public function caso()
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function etapa()
    {
        return $this->belongsTo(EtapaProcesal::class, 'etapa_id');
    }

    public function abogado()
    {
        return $this->belongsTo(Usuario::class, 'abogado_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }
}
