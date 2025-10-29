<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapaProcesal extends Model
{
    use HasFactory;

    protected $table = 'etapas_procesales';

    protected $fillable = [
        'tipo_caso_id',
        'nombre',
        'descripcion',
        'orden',
    ];

    public function tipoCaso()
    {
        return $this->belongsTo(TiposCasos::class, 'tipo_caso_id');
    }
}
