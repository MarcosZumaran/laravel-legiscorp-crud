<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class Usuario extends Model
{
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
    ];

    protected $hidden = [
        'password',
    ];

    // CONFIGURACIÓN DE ENCRIPTACIÓN
    protected $encryptedFields = [
        'nombres',
        'apellidos',
        'correo',
    ];

    // ENCRIPTACIÓN AUTOMÁTICA
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptedFields)) {
            $this->attributes[$key] = Crypt::encryptString($value);
        } else {
            parent::setAttribute($key, $value);
        }
    }

    // DESENCRIPTACIÓN AUTOMÁTICA
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptedFields)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value; // Si no está encriptado, devolver original
            }
        }

        return $value;
    }

    // PASSWORD HASHING
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // BÚSQUEDAS EN MEMORIA (Para pequeños volúmenes de datos)
    public static function buscarPorCorreo($correo)
    {
        $usuarios = static::all();

        return $usuarios->filter(function ($usuario) use ($correo) {
            return strtolower($usuario->correo) === strtolower($correo);
        })->first();
    }

    public static function buscarPorTexto($texto)
    {
        $usuarios = static::all();
        $textoLower = strtolower(trim($texto));

        return $usuarios->filter(function ($usuario) use ($textoLower) {
            // Buscar en nombres, apellidos, correo y rol
            return str_contains(strtolower($usuario->nombres), $textoLower) ||
                   str_contains(strtolower($usuario->apellidos), $textoLower) ||
                   str_contains(strtolower($usuario->correo), $textoLower) ||
                   str_contains(strtolower($usuario->rol), $textoLower);
        })->values();
    }

    // BÚSQUEDA OPTIMIZADA PARA LOGIN
    public static function buscarLogin($correo)
    {
        // Para login, podemos hacer una búsqueda más eficiente
        $usuarios = static::where('rol', 'LIKE', '%') // Truco para obtener todos
            ->get();

        return $usuarios->filter(function ($usuario) use ($correo) {
            return strtolower($usuario->correo) === strtolower($correo);
        })->first();
    }

    // MÉTODOS PARA FRONTEND
    public function toSafeArray()
    {
        return [
            'id' => $this->id,
            'nombres' => $this->nombres, // Automáticamente desencriptado
            'apellidos' => $this->apellidos,
            'correo' => $this->correo,
            'rol' => $this->rol,
            'creado_en' => $this->creado_en,
            'iniciales' => $this->iniciales,
            'nombre_completo' => $this->nombre_completo,
        ];
    }

    public function toMaskedArray()
    {
        return [
            'id' => $this->id,
            'nombres' => $this->nombres_enmascarados,
            'apellidos' => $this->apellidos_enmascarados,
            'correo' => $this->correo_enmascarado,
            'rol' => $this->rol,
            'creado_en' => $this->creado_en,
            'iniciales' => $this->iniciales,
        ];
    }

    // ATRIBUTOS CALCULADOS
    public function getInicialesAttribute()
    {
        $inicial = substr($this->nombres, 0, 1) ?? '';
        $inicial .= substr($this->apellidos, 0, 1) ?? '';

        return strtoupper($inicial);
    }

    public function getNombreCompletoAttribute()
    {
        return $this->nombres.' '.$this->apellidos;
    }

    public function getNombresEnmascaradosAttribute()
    {
        return strlen($this->nombres) > 2 ?
               substr($this->nombres, 0, 2).'****' : '****';
    }

    public function getApellidosEnmascaradosAttribute()
    {
        return strlen($this->apellidos) > 2 ?
               substr($this->apellidos, 0, 2).'****' : '****';
    }

    public function getCorreoEnmascaradoAttribute()
    {
        $correo = $this->correo;
        $parts = explode('@', $correo);
        if (count($parts) === 2) {
            return substr($parts[0], 0, 2).'****@'.$parts[1];
        }

        return '****@****';
    }

    // VALIDACIÓN DE ROL
    public function setRolAttribute($value)
    {
        $rolesValidos = ['Asistente', 'Abogado', 'Administrador'];
        $this->attributes['rol'] = in_array($value, $rolesValidos) ? $value : 'Asistente';
    }

    // MÉTODOS DE PERSISTENCIA MEJORADOS
    public static function crearConEncriptacion($data)
    {
        $usuario = new static;

        foreach ($data as $key => $value) {
            if (in_array($key, $usuario->encryptedFields)) {
                $usuario->attributes[$key] = Crypt::encryptString($value);
            } else {
                $usuario->attributes[$key] = $value;
            }
        }

        $usuario->save();

        return $usuario;
    }
}
