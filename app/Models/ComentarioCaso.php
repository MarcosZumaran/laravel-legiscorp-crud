<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComentarioCaso extends Model
{
    use HasFactory;

    protected $table = 'comentarios_casos';

    protected $fillable = [
        'caso_id',
        'usuario_id',
        'comentario',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function caso()
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
