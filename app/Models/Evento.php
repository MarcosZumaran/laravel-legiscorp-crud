<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'ubicacion',
        'color',
        'tipo_evento',
        'recurrente',
        'caso_id',
        'etapa_id',
        'expediente',
        'creado_por',
        'creado_en',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'creado_en' => 'datetime',
    ];

    public function setDescripcionAttribute($value)
    {
        $this->attributes['descripcion'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDescripcionAttribute($value)
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function caso()
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function etapa()
    {
        return $this->belongsTo(EtapaProcesal::class, 'etapa_id');
    }

    public function usuarioCreador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }
}
