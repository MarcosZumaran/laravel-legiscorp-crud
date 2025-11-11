<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

class Bitacora extends Model
{
    use EncryptedAttribute;

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

    // ✅ Campos que se encriptarán
    protected $encryptable = [
        'accion',
        'ip',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // Tipos de acción comunes (mantenido para referencia)
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

    // Relación con Usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Scopes útiles
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorAccion($query, $accion)
    {
        return $query->whereEncrypted('accion', 'LIKE', "%{$accion}%");
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

    // Método utilitario para registrar (mantenido porque es muy útil)
    public static function registrar($usuarioId, $accion, $ip = null)
    {
        return self::create([
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'ip' => $ip ?: request()->ip(),
        ]);
    }

    // Prevenir modificaciones y eliminaciones (mantenido por seguridad)
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            throw new \Exception("Los registros de bitácora no pueden ser modificados.");
        });

        static::deleting(function ($model) {
            throw new \Exception("Los registros de bitácora no pueden ser eliminados.");
        });
    }
}