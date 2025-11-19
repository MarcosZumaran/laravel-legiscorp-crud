<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentoCompartido extends Model
{
    use HasFactory;
    protected $table = 'documentos_compartidos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'documento_id',
        'compartido_con_usuario_id',
        'compartido_con_rol',
        'permisos',
        'compartido_por',
    ];

    protected $casts = [
        'fecha_compartido' => 'datetime',
    ];

    // Valores permitidos
    const PERMISOS_VALIDOS = ['lectura', 'escritura'];
    const ROLES_VALIDOS = ['Asistente', 'Abogado', 'Administrador']; // Según tu modelo Usuario

    // Atributos por defecto
    protected $attributes = [
        'permisos' => 'lectura',
    ];

    // Relaciones (mantenidas - esenciales)
    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'documento_id');
    }

    public function compartidoConUsuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'compartido_con_usuario_id');
    }

    public function compartidoPorUsuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'compartido_por');
    }

    // Scopes útiles (mantenidos)
    public function scopePorDocumento($query, $documentoId)
    {
        return $query->where('documento_id', $documentoId);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('compartido_con_usuario_id', $usuarioId);
    }

    public function scopePorRol($query, $rol)
    {
        return $query->where('compartido_con_rol', $rol);
    }

    public function scopePorPermisos($query, $permisos)
    {
        return $query->where('permisos', $permisos);
    }

    public function scopePorCompartidoPor($query, $usuarioId)
    {
        return $query->where('compartido_por', $usuarioId);
    }

    public function scopeConPermisoEscritura($query)
    {
        return $query->where('permisos', 'escritura');
    }

    public function scopeConPermisoLectura($query)
    {
        return $query->where('permisos', 'lectura');
    }

    public function scopeRecientes($query)
    {
        return $query->orderBy('fecha_compartido', 'desc');
    }
}