<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

class Calendario extends Model
{
    use EncryptedAttribute;

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
        'prioridad',
    ];

    // ✅ Campos que se encriptarán
    protected $encryptable = [
        'descripcion',
        'ubicacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'creado_en' => 'datetime',
    ];

    // Valores permitidos
    const TIPOS_EVENTO = ['Audiencia', 'Reunión', 'Plazo', 'Entrega', 'Otro'];
    const ESTADOS = ['Pendiente', 'Completado', 'Cancelado'];
    const RECURRENCIAS = ['No', 'Diario', 'Semanal', 'Mensual', 'Anual'];
    const PRIORIDADES = ['Baja', 'Media', 'Alta', 'Urgente'];

    // Atributos por defecto
    protected $attributes = [
        'tipo_evento' => 'Otro',
        'estado' => 'Pendiente',
        'color' => '#3486bc',
        'recurrente' => 'No',
        'prioridad' => 'Media',
    ];

    // Relaciones (mantenidas - esenciales)
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

    // Scopes útiles (mantenidos)
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

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
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
                    ->orWhere('expediente', 'LIKE', "%{$termino}%")
                    ->orWhereEncrypted('descripcion', 'LIKE', "%{$termino}%")
                    ->orWhereEncrypted('ubicacion', 'LIKE', "%{$termino}%");
    }
}