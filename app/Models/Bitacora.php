<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    use HasFactory;

    protected $table = 'bitacora';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'accion',
        'fecha',
        'ip',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'accion' => 'encrypted', // ✅ Encriptar acciones (pueden contener info sensible)
    ];

    // 🎯 TIPOS DE ACCIÓN COMUNES
    const ACCIONES = [
        'LOGIN',
        'LOGOUT',
        'CREAR',
        'ACTUALIZAR',
        'ELIMINAR',
        'CONSULTAR',
        'DESCARGAR',
        'ERROR',
        'ACCESO_DENEGADO'
    ];

    // 🎯 ATRIBUTOS POR DEFECTO
    protected $attributes = [
        'fecha' => null, // La BD usa GETDATE() por defecto
    ];

    // 🎯 RELACIONES
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // 🎯 SCOPES PARA BÚSQUEDAS
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', 'LIKE', "%{$accion}%");
    }

    public function scopePorIp($query, $ip)
    {
        return $query->where('ip', $ip);
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    public function scopeRecientes($query, $horas = 24)
    {
        return $query->where('fecha', '>=', now()->subHours($horas));
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('fecha', today());
    }

    public function scopeOrdenarPorFecha($query, $direccion = 'desc')
    {
        return $query->orderBy('fecha', $direccion);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where('accion', 'LIKE', "%{$termino}%")
                    ->orWhere('ip', 'LIKE', "%{$termino}%")
                    ->orWhereHas('usuario', function ($q) use ($termino) {
                        $q->where('nombres', 'LIKE', "%{$termino}%")
                          ->orWhere('apellidos', 'LIKE', "%{$termino}%")
                          ->orWhere('correo', 'LIKE', "%{$termino}%");
                    });
    }

    // 🎯 ATRIBUTOS CALCULADOS
    public function getAccionCortaAttribute()
    {
        return strlen($this->accion) > 100 
            ? substr($this->accion, 0, 100) . '...' 
            : $this->accion;
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecha ? $this->fecha->format('d/m/Y H:i:s') : 'Sin fecha';
    }

    public function getEsRecienteAttribute()
    {
        return $this->fecha >= now()->subHour();
    }

    public function getTipoAccionAttribute()
    {
        foreach (self::ACCIONES as $accion) {
            if (str_contains(strtoupper($this->accion), $accion)) {
                return $accion;
            }
        }
        return 'OTRO';
    }

    // 🎯 MÉTODOS UTILITARIOS
    public static function registrar($usuarioId, $accion, $ip = null)
    {
        return self::create([
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'ip' => $ip ?: request()->ip(),
            'fecha' => now(),
        ]);
    }

    public static function actividadesRecientesUsuario($usuarioId, $horas = 24)
    {
        return self::porUsuario($usuarioId)
                  ->recientes($horas)
                  ->ordenarPorFecha()
                  ->get();
    }

    // 🎯 VALIDACIÓN AUTOMÁTICA
    protected static function boot()
    {
        parent::boot();

        // ⚠️ La bitácora es de solo lectura - prevenir modificaciones/eliminaciones
        static::updating(function ($model) {
            throw new \Exception("Los registros de bitácora no pueden ser modificados.");
        });

        static::deleting(function ($model) {
            throw new \Exception("Los registros de bitácora no pueden ser eliminados.");
        });
    }

    /**
     * Representación en string
     */
    public function __toString(): string
    {
        $usuario = $this->usuario ? $this->usuario->nombres : 'Usuario desconocido';
        return "{$usuario} - {$this->accion_corta} - {$this->fecha_formateada}";
    }
}