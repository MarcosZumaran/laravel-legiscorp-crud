<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComentarioCaso extends Model
{
    use EncryptedAttribute, HasFactory;

    protected $table = 'comentarios_casos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'caso_id',
        'usuario_id',
        'comentario',
        'fecha',
    ];

    // Solo el comentario se encriptará
    protected $encryptable = [
        'comentario',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // Relaciones (mantenidas - esenciales)
    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Scopes útiles (mantenidos)
    public function scopePorCaso($query, $casoId)
    {
        return $query->where('caso_id', $casoId);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeRecientes($query, $dias = 7)
    {
        return $query->where('fecha', '>=', now()->subDays($dias));
    }
}