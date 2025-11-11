<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calendario extends Model
{
    use HasFactory;

    protected $table = 'calendario';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'ubicacion',
        'tipo_evento',
        'estado',
        'color',
        'recurrente',
        'caso_id',
        'abogado_id',
        'cliente_id',
        'creado_por',
        'expediente',
        'creado_en',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'creado_en' => 'datetime',
        'descripcion' => 'encrypted',
    ];

    // VALORES PERMITIDOS
    const TIPOS_EVENTO = ['Audiencia', 'Reunión', 'Plazo', 'Entrega', 'Otro'];
    const ESTADOS = ['Pendiente', 'Completado', 'Cancelado'];
    const RECURRENCIAS = ['No', 'Diario', 'Semanal', 'Mensual', 'Anual'];

    // ATRIBUTOS POR DEFECTO
    protected $attributes = [
        'tipo_evento' => 'Otro',
        'estado' => 'Pendiente',
        'color' => '#3486bc',
        'recurrente' => 'No',
    ];

    // RELACIONES
    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function abogado(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'abogado_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    // SCOPES PARA BÚSQUEDAS (sin cambios)
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_evento', $tipo);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorAbogado($query, $abogadoId)
    {
        return $query->where('abogado_id', $abogadoId);
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopePorCaso($query, $casoId)
    {
        return $query->where('caso_id', $casoId);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'Pendiente');
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
    }

    public function scopeProximos($query, $dias = 7)
    {
        return $query->where('fecha_inicio', '>=', now())
                    ->where('fecha_inicio', '<=', now()->addDays($dias))
                    ->pendientes();
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_inicio', today())->pendientes();
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where('titulo', 'LIKE', "%{$termino}%")
                    ->orWhere('descripcion', 'LIKE', "%{$termino}%")
                    ->orWhere('ubicacion', 'LIKE', "%{$termino}%")
                    ->orWhere('expediente', 'LIKE', "%{$termino}%");
    }

    // ATRIBUTOS CALCULADOS (sin cambios)
    public function getEstaPendienteAttribute()
    {
        return $this->estado === 'Pendiente';
    }

    public function getEstaCompletadoAttribute()
    {
        return $this->estado === 'Completado';
    }

    public function getEstaCanceladoAttribute()
    {
        return $this->estado === 'Cancelado';
    }

    public function getEsRecurrenteAttribute()
    {
        return $this->recurrente !== 'No';
    }

    public function getDuracionMinutosAttribute()
    {
        if (!$this->fecha_inicio || !$this->fecha_fin) return null;
        return $this->fecha_inicio->diffInMinutes($this->fecha_fin);
    }

    public function getEsEventoLargoAttribute()
    {
        return $this->duracion_minutos > 240; // Más de 4 horas
    }

    public function getFechaInicioFormateadaAttribute()
    {
        return $this->fecha_inicio ? $this->fecha_inicio->format('d/m/Y H:i') : 'Sin fecha';
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

    // MÉTODOS UTILITARIOS (sin cambios)
    public function completar()
    {
        $this->update(['estado' => 'Completado']);
    }

    public function cancelar()
    {
        $this->update(['estado' => 'Cancelado']);
    }

    public function reagendar($nuevaFechaInicio, $nuevaFechaFin = null)
    {
        $this->update([
            'fecha_inicio' => $nuevaFechaInicio,
            'fecha_fin' => $nuevaFechaFin ?: $this->fecha_fin
        ]);
    }

    public function puedeEditar()
    {
        return $this->esta_pendiente;
    }

    // VALIDACIÓN AUTOMÁTICA (sin etapa_id)
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validar tipo de evento
            if (!in_array($model->tipo_evento, self::TIPOS_EVENTO)) {
                throw new \Exception("Tipo de evento no válido: {$model->tipo_evento}");
            }

            // Validar estado
            if (!in_array($model->estado, self::ESTADOS)) {
                throw new \Exception("Estado no válido: {$model->estado}");
            }

            // Validar recurrencia
            if (!in_array($model->recurrente, self::RECURRENCIAS)) {
                throw new \Exception("Recurrencia no válida: {$model->recurrente}");
            }

            // Validar fechas
            if ($model->fecha_fin && $model->fecha_inicio && $model->fecha_fin < $model->fecha_inicio) {
                throw new \Exception("La fecha de fin no puede ser anterior a la fecha de inicio.");
            }

            // Validar que el creador existe
            if ($model->creado_por && !\App\Models\Usuario::where('id', $model->creado_por)->exists()) {
                throw new \Exception("El usuario creador con ID {$model->creado_por} no existe.");
            }
        });
    }

    /**
     * Representación en string
     */
    public function __toString(): string
    {
        return "{$this->titulo} - {$this->fecha_inicio_formateada}";
    }
}