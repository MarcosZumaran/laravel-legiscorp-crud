<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

class Documento extends Model
{
    use EncryptedAttribute;

    protected $table = 'documentos';
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
        'tamano_bytes',
        'es_carpeta',
        'carpeta_padre_id',
        'es_publico',
        'etiquetas',
    ];

    //Campos que se encriptarán
    protected $encryptable = [
        'nombre_archivo',
        'descripcion',
        'ruta',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
        'es_carpeta' => 'boolean',
        'es_publico' => 'boolean',
        'tamano_bytes' => 'integer',
    ];

    // Categorías permitidas
    const CATEGORIAS = [
        'General',
        'Contrato',
        'Sentencia',
        'Resolución',
        'Evidencia',
        'Otro'
    ];

    // Relaciones
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

    // Scopes útiles
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

    // Validación de categoría
    public function setCategoriaAttribute($value)
    {
        if (!in_array($value, self::CATEGORIAS)) {
            throw new \InvalidArgumentException("Categoría no válida: $value");
        }
        $this->attributes['categoria'] = $value;
    }

    // Mutator para etiquetas (mantenido porque transforma datos)
    public function setEtiquetasAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['etiquetas'] = implode(',', $value);
        } else {
            $this->attributes['etiquetas'] = $value;
        }
    }
}