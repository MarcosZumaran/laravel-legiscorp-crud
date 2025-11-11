<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComentarioCaso extends Model
{
    use HasFactory;

    protected $table = 'comentarios_casos';
    
    // Configuración de clave primaria
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

    protected $casts = [
        'fecha' => 'datetime',
        'comentario' => 'encrypted',
    ];

    // ATRIBUTOS POR DEFECTO
    protected $attributes = [
        'fecha' => null, // La BD usa GETDATE() por defecto
    ];

    // RELACIONES
    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // SCOPES PARA BÚSQUEDAS
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

    public function scopeOrdenarPorFecha($query, $direccion = 'desc')
    {
        return $query->orderBy('fecha', $direccion);
    }

    // ATRIBUTOS CALCULADOS
    public function getComentarioCortoAttribute()
    {
        if (empty($this->comentario)) {
            return 'Sin comentario';
        }

        return strlen($this->comentario) > 100 
            ? substr($this->comentario, 0, 100) . '...' 
            : $this->comentario;
    }

    public function getEsRecienteAttribute()
    {
        return $this->fecha >= now()->subDay();
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecha ? $this->fecha->format('d/m/Y H:i') : 'Sin fecha';
    }

    // MÉTODOS UTILITARIOS
    public static function obtenerPorCaso($casoId)
    {
        return self::porCaso($casoId)
                  ->with('usuario')
                  ->ordenarPorFecha()
                  ->get();
    }

    public static function totalPorCaso($casoId)
    {
        return self::porCaso($casoId)->count();
    }

    // VALIDACIÓN AUTOMÁTICA
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validar que el caso existe
            if (!\App\Models\Caso::where('id', $model->caso_id)->exists()) {
                throw new \Exception("El caso con ID {$model->caso_id} no existe.");
            }

            // Validar que el usuario existe
            if (!\App\Models\Usuario::where('id', $model->usuario_id)->exists()) {
                throw new \Exception("El usuario con ID {$model->usuario_id} no existe.");
            }
        });
    }

    /**
     * Representación en string
     */
    public function __toString(): string
    {
        return $this->comentario_corto;
    }
}