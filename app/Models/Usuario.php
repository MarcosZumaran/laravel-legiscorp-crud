<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class Usuario extends Model
{
    use EncryptedAttribute, HasFactory;

    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'nombres',
        'apellidos',
        'correo',
        'password',
        'rol',
        'correo_hash',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Campos que se encriptarán automáticamente
     */
    protected $encryptable = [
        'nombres',
        'apellidos',
        'correo',
    ];

    /**
     * Hash para la contraseña
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    /**
     * Generar hash del correo automáticamente
     */
    public function setCorreoAttribute($value)
    {
        // Primero asignamos el correo (se encriptará automáticamente)
        $this->attributes['correo'] = $value;
        
        // Luego generamos el hash para unicidad
        if (!empty($value)) {
            $this->attributes['correo_hash'] = hash('sha256', strtolower(trim($value)));
        }
    }

    /**
     * Búsqueda optimizada para login usando correo_hash
     */
    public static function buscarLogin($correo)
    {
        if (!empty($correo)) {
            $correoHash = hash('sha256', strtolower(trim($correo)));
            return static::where('correo_hash', $correoHash)->first();
        }
        return null;
    }

    /**
     * Búsqueda por texto en campos encriptados
     */
    public static function buscarPorTexto($texto)
    {
        if (!empty($texto)) {
            return static::whereEncrypted('nombres', 'LIKE', "%{$texto}%")
                ->orWhereEncrypted('apellidos', 'LIKE', "%{$texto}%")
                ->orWhereEncrypted('correo', 'LIKE', "%{$texto}%")
                ->orWhere('rol', 'LIKE', "%{$texto}%")
                ->get();
        }
        return collect();
    }

    /**
     * Verificar si un correo ya existe (para validaciones)
     */
    public static function correoExiste($correo)
    {
        if (empty($correo)) {
            return false;
        }
        
        $correoHash = hash('sha256', strtolower(trim($correo)));
        return static::where('correo_hash', $correoHash)->exists();
    }

    /**
     * Buscar usuario por hash de correo
     */
    public static function buscarPorHash($correoHash)
    {
        if (!empty($correoHash)) {
            return static::where('correo_hash', $correoHash)->first();
        }
        return null;
    }
}