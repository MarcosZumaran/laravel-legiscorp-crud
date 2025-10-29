<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'tipo_persona',
        'tipo_documento',
        'numero_documento',
        'nombres',
        'apellidos',
        'razon_social',
        'representante_legal',
        'telefono',
        'correo',
        'direccion',
        'estado',
        'creado_en',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
    ];

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'cliente_id');
    }
}
