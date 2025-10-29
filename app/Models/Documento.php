<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'nombre_archivo',
        'tipo_archivo',
        'ruta',
        'descripcion',
        'expediente',
        'fecha_subida',
        'subido_por',
        'caso_id',
        'cliente_id',
        'categoria',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'subido_por');
    }

    public function caso()
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
