<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // VALORES PERMITIDOS
    const PERMISOS_VALIDOS = ['lectura', 'escritura'];
    const ROLES_VALIDOS = ['admin', 'abogado', 'cliente', 'asistente']; // Ajusta según tus roles

    // ATRIBUTOS POR DEFECTO
    protected $attributes = [
        'permisos' => 'lectura',
    ];

    // RELACIONES
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

    // SCOPES PARA BÚSQUEDAS
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

    public function scopeBuscar($query, $termino)
    {
        return $query->whereHas('documento', function($q) use ($termino) {
            $q->where('nombre', 'LIKE', "%{$termino}%")
              ->orWhere('descripcion', 'LIKE', "%{$termino}%");
        });
    }

    // ATRIBUTOS CALCULADOS
    public function getTienePermisoEscrituraAttribute()
    {
        return $this->permisos === 'escritura';
    }

    public function getTienePermisoLecturaAttribute()
    {
        return $this->permisos === 'lectura';
    }

    public function getEsCompartidoConUsuarioAttribute()
    {
        return !is_null($this->compartido_con_usuario_id);
    }

    public function getEsCompartidoConRolAttribute()
    {
        return !is_null($this->compartido_con_rol);
    }

    public function getFechaCompartidoFormateadaAttribute()
    {
        return $this->fecha_compartido ? $this->fecha_compartido->format('d/m/Y H:i') : 'Sin fecha';
    }

    public function getDestinatarioAttribute()
    {
        if ($this->es_compartido_con_usuario) {
            return $this->compartidoConUsuario->nombre ?? 'Usuario desconocido';
        } elseif ($this->es_compartido_con_rol) {
            return "Rol: " . ucfirst($this->compartido_con_rol);
        }
        
        return 'Destinatario no especificado';
    }

    // MÉTODOS UTILITARIOS
    public function puedeEditar()
    {
        // Lógica para determinar si se puede editar este registro
        return $this->tiene_permiso_escritura;
    }

    public function cambiarPermisos($nuevosPermisos)
    {
        if (!in_array($nuevosPermisos, self::PERMISOS_VALIDOS)) {
            throw new \Exception("Permiso no válido: {$nuevosPermisos}");
        }

        $this->update(['permisos' => $nuevosPermisos]);
    }

    public function esAccesiblePorUsuario($usuarioId, $rolUsuario)
    {
        // Verificar si el usuario tiene acceso a este documento compartido
        if ($this->es_compartido_con_usuario && $this->compartido_con_usuario_id == $usuarioId) {
            return true;
        }

        if ($this->es_compartido_con_rol && $this->compartido_con_rol == $rolUsuario) {
            return true;
        }

        return false;
    }

    // VALIDACIÓN AUTOMÁTICA
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validar permisos
            if (!in_array($model->permisos, self::PERMISOS_VALIDOS)) {
                throw new \Exception("Permiso no válido: {$model->permisos}");
            }

            // Validar que tenga al menos un destinatario
            if (is_null($model->compartido_con_usuario_id) && is_null($model->compartido_con_rol)) {
                throw new \Exception("Debe especificar un usuario o un rol para compartir.");
            }

            // Validar que no tenga ambos destinatarios
            if (!is_null($model->compartido_con_usuario_id) && !is_null($model->compartido_con_rol)) {
                throw new \Exception("Solo puede especificar un usuario o un rol, no ambos.");
            }

            // Validar que el documento existe
            if ($model->documento_id && !\App\Models\Documento::where('id', $model->documento_id)->exists()) {
                throw new \Exception("El documento con ID {$model->documento_id} no existe.");
            }

            // Validar que el usuario que comparte existe
            if ($model->compartido_por && !\App\Models\Usuario::where('id', $model->compartido_por)->exists()) {
                throw new \Exception("El usuario que comparte con ID {$model->compartido_por} no existe.");
            }

            // Validar que el usuario destinatario existe si se especifica
            if ($model->compartido_con_usuario_id && !\App\Models\Usuario::where('id', $model->compartido_con_usuario_id)->exists()) {
                throw new \Exception("El usuario destinatario con ID {$model->compartido_con_usuario_id} no existe.");
            }

            // Evitar duplicados
            $existe = self::where('documento_id', $model->documento_id)
                ->where('compartido_con_usuario_id', $model->compartido_con_usuario_id)
                ->where('compartido_con_rol', $model->compartido_con_rol)
                ->when($model->exists, function ($query) use ($model) {
                    return $query->where('id', '!=', $model->id);
                })
                ->exists();

            if ($existe) {
                throw new \Exception("Este documento ya ha sido compartido con el mismo destinatario.");
            }
        });
    }

    /**
     * Representación en string
     */
    public function __toString(): string
    {
        return "Documento {$this->documento_id} compartido con {$this->destinatario}";
    }
}