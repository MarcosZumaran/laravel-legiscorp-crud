<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caso extends Model
{
    use HasFactory;

    protected $table = 'casos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'codigo_caso',
        'numero_expediente',      // ✅ Campo real en la tabla
        'numero_carpeta_fiscal',  // ✅ Campo real en la tabla
        'titulo',
        'descripcion',
        'materia_id',
        'tipo_caso_id',
        'estado',
        'fecha_inicio',
        'fecha_cierre',
        'cliente_id',
        'abogado_id',
        'contraparte',
        'juzgado',                // ✅ Campo real en la tabla
        'fiscal',                 // ✅ Campo real en la tabla
        'creado_en',              // ✅ Campo real en la tabla
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_cierre' => 'date',
        'creado_en' => 'datetime',
        'descripcion' => 'encrypted', // ✅ Encriptar descripción (puede contener info sensible)
    ];

    // 🎯 VALORES PERMITIDOS
    const ESTADOS = ['Abierto', 'En Proceso', 'Cerrado'];

    // 🎯 ATRIBUTOS POR DEFECTO
    protected $attributes = [
        'estado' => 'Abierto',
        'creado_en' => null, // La BD usa GETDATE() por defecto
    ];

    // 🎯 RELACIONES
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function abogado(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'abogado_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(MateriaCaso::class, 'materia_id');
    }

    public function tipoCaso(): BelongsTo
    {
        return $this->belongsTo(TiposCasos::class, 'tipo_caso_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(ComentarioCaso::class, 'caso_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'caso_id');
    }

    // 🎯 SCOPES PARA BÚSQUEDAS
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopePorAbogado($query, $abogadoId)
    {
        return $query->where('abogado_id', $abogadoId);
    }

    public function scopePorMateria($query, $materiaId)
    {
        return $query->where('materia_id', $materiaId);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where('codigo_caso', 'LIKE', "%{$termino}%")
                    ->orWhere('numero_expediente', 'LIKE', "%{$termino}%")
                    ->orWhere('titulo', 'LIKE', "%{$termino}%")
                    ->orWhere('contraparte', 'LIKE', "%{$termino}%")
                    ->orWhere('juzgado', 'LIKE', "%{$termino}%")
                    ->orWhere('fiscal', 'LIKE', "%{$termino}%");
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', '!=', 'Cerrado');
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('creado_en', '>=', now()->subDays($dias));
    }

    public function scopeOrdenarPorFecha($query, $direccion = 'desc')
    {
        return $query->orderBy('creado_en', $direccion);
    }

    // 🎯 ATRIBUTOS CALCULADOS
    public function getEstaActivoAttribute()
    {
        return $this->estado !== 'Cerrado';
    }

    public function getEstaCerradoAttribute()
    {
        return $this->estado === 'Cerrado';
    }

    public function getDuracionDiasAttribute()
    {
        if (!$this->fecha_inicio) return null;
        
        $fin = $this->fecha_cierre ?: now();
        return $this->fecha_inicio->diffInDays($fin);
    }

    public function getDescripcionCortaAttribute()
    {
        if (empty($this->descripcion)) {
            return 'Sin descripción';
        }

        return strlen($this->descripcion) > 100 
            ? substr($this->descripcion, 0, 100) . '...' 
            : $this->descripcion;
    }

    public function getInformacionCompletaAttribute()
    {
        $info = [];
        if ($this->numero_expediente) $info[] = "Exp: {$this->numero_expediente}";
        if ($this->juzgado) $info[] = "Juzgado: {$this->juzgado}";
        if ($this->fiscal) $info[] = "Fiscal: {$this->fiscal}";
        
        return implode(' | ', $info) ?: 'Sin información adicional';
    }

    // 🎯 MÉTODOS UTILITARIOS
    public static function buscarPorCodigo($codigoCaso)
    {
        return self::where('codigo_caso', $codigoCaso)->first();
    }

    public function puedeEliminar()
    {
        // Verificar si no tiene comentarios ni documentos asociados
        return !$this->comentarios()->exists() && !$this->documentos()->exists();
    }

    public function cerrar()
    {
        $this->update([
            'estado' => 'Cerrado',
            'fecha_cierre' => now()
        ]);
    }

    public function abrir()
    {
        $this->update([
            'estado' => 'Abierto',
            'fecha_cierre' => null
        ]);
    }

    public function ponerEnProceso()
    {
        $this->update(['estado' => 'En Proceso']);
    }

    // 🎯 VALIDACIÓN AUTOMÁTICA
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validar que el estado sea válido
            if (!in_array($model->estado, self::ESTADOS)) {
                throw new \Exception("Estado no válido: {$model->estado}");
            }

            // Validar que el cliente existe
            if (!\App\Models\Cliente::where('id', $model->cliente_id)->exists()) {
                throw new \Exception("El cliente con ID {$model->cliente_id} no existe.");
            }

            // Validar que el abogado existe
            if (!\App\Models\Usuario::where('id', $model->abogado_id)->exists()) {
                throw new \Exception("El abogado con ID {$model->abogado_id} no existe.");
            }

            // Validar que la materia existe
            if (!\App\Models\MateriaCaso::where('id', $model->materia_id)->exists()) {
                throw new \Exception("La materia con ID {$model->materia_id} no existe.");
            }

            // Validar fechas
            if ($model->fecha_cierre && $model->fecha_inicio && $model->fecha_cierre < $model->fecha_inicio) {
                throw new \Exception("La fecha de cierre no puede ser anterior a la fecha de inicio.");
            }
        });

        static::deleting(function ($model) {
            if (!$model->puedeEliminar()) {
                throw new \Exception("No se puede eliminar el caso porque tiene comentarios o documentos asociados.");
            }
        });
    }

    /**
     * Representación en string
     */
    public function __toString(): string
    {
        return "{$this->codigo_caso} - {$this->titulo}";
    }
}