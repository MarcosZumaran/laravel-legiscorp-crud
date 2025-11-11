<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';
    
    // Configuración de clave primaria
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

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
        'tamano_bytes',      // ✅ Añadido
        'es_carpeta',        // ✅ Añadido
        'carpeta_padre_id',  // ✅ Añadido
        'es_publico',        // ✅ Añadido
        'etiquetas',         // ✅ Añadido
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
        'es_carpeta' => 'boolean',     // ✅ Añadido
        'es_publico' => 'boolean',     // ✅ Añadido
        'tamano_bytes' => 'integer',   // ✅ Añadido
    ];

    // 🎯 CATEGORÍAS PERMITIDAS
    const CATEGORIAS = [
        'General',
        'Contrato',
        'Sentencia',
        'Resolución',
        'Evidencia',
        'Otro'
    ];

    // 🎯 RELACIONES
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'subido_por');
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function carpetaPadre(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'carpeta_padre_id');
    }

    public function archivosHijos(): HasMany
    {
        return $this->hasMany(Documento::class, 'carpeta_padre_id');
    }

    // 🎯 SCOPES PARA BÚSQUEDAS
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorCaso($query, $casoId)
    {
        return $query->where('caso_id', $casoId);
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopeCarpetas($query)
    {
        return $query->where('es_carpeta', true);
    }

    public function scopeArchivos($query)
    {
        return $query->where('es_carpeta', false);
    }

    public function scopePublicos($query)
    {
        return $query->where('es_publico', true);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where('nombre_archivo', 'LIKE', "%{$termino}%")
                    ->orWhere('descripcion', 'LIKE', "%{$termino}%")
                    ->orWhere('expediente', 'LIKE', "%{$termino}%")
                    ->orWhere('etiquetas', 'LIKE', "%{$termino}%");
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha_subida', '>=', now()->subDays($dias));
    }

    // 🎯 ATRIBUTOS CALCULADOS
    public function getTamanoFormateadoAttribute()
    {
        if (!$this->tamano_bytes) return '0 Bytes';

        $unidades = ['Bytes', 'KB', 'MB', 'GB'];
        $tamano = $this->tamano_bytes;
        
        for ($i = 0; $tamano >= 1024 && $i < count($unidades) - 1; $i++) {
            $tamano /= 1024;
        }

        return round($tamano, 2) . ' ' . $unidades[$i];
    }

    public function getEsArchivoAttribute()
    {
        return !$this->es_carpeta;
    }

    public function getRutaCompletaAttribute()
    {
        return storage_path('app/' . $this->ruta);
    }

    public function getEtiquetasArrayAttribute()
    {
        return $this->etiquetas ? explode(',', $this->etiquetas) : [];
    }

    // 🎯 MÉTODOS UTILITARIOS
    public function esImagen()
    {
        return in_array($this->tipo_archivo, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function esDocumento()
    {
        return in_array($this->tipo_archivo, ['pdf', 'doc', 'docx', 'txt']);
    }

    public function puedeEliminar()
    {
        // No se puede eliminar si tiene archivos hijos
        return !$this->archivosHijos()->exists();
    }

    // 🎯 VALIDACIÓN DE CATEGORÍA
    public function setCategoriaAttribute($value)
    {
        if (!in_array($value, self::CATEGORIAS)) {
            throw new \InvalidArgumentException("Categoría no válida: $value");
        }
        $this->attributes['categoria'] = $value;
    }

    public function setEtiquetasAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['etiquetas'] = implode(',', $value);
        } else {
            $this->attributes['etiquetas'] = $value;
        }
    }

    // 🎯 VALIDACIÓN AUTOMÁTICA
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validar que carpeta_padre_id sea una carpeta si se especifica
            if ($model->carpeta_padre_id) {
                $carpetaPadre = Documento::find($model->carpeta_padre_id);
                if (!$carpetaPadre || !$carpetaPadre->es_carpeta) {
                    throw new \Exception("El documento padre debe ser una carpeta.");
                }
            }

            // Validar que el usuario existe
            if ($model->subido_por && !Usuario::where('id', $model->subido_por)->exists()) {
                throw new \Exception("El usuario con ID {$model->subido_por} no existe.");
            }
        });

        static::deleting(function ($model) {
            if (!$model->puedeEliminar()) {
                throw new \Exception("No se puede eliminar porque contiene archivos.");
            }
        });
    }
}