<?php
namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado'];

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? ''; 
        $this->password_actual = $args['password_actual'] ?? ''; 
        $this->password_nuevo = $args['password_nuevo'] ?? ''; 
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }
    //Validación del login
    public function validarLogin() {
        if(!$this->email) self::$alertas['error'][] = 'El Email del Usuario es obligatorio';
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) { //Revisa sí tiene la estructura adecuada a un email
            self::$alertas['error'][] = 'El email no es válido';
        }
        if(!$this->password) self::$alertas['error'][] = 'La contraseña es obligatoria';
        return self::$alertas;
    }

    //Validación para cuentas nuevas
    public function validarNuevaCuenta() {
        if(!$this->nombre) self::$alertas['error'][] = 'El Nombre del Usuario es obligatorio';
        if(!$this->email) self::$alertas['error'][] = 'El Email del Usuario es obligatorio';
        if(!$this->password) self::$alertas['error'][] = 'La contraseña es obligatoria';
        if(strlen($this->password) < 6) self::$alertas['error'][] = 'La contraseña debe contener al menos 6 caracteres';
        if($this->password !== $this->password2) self::$alertas['error'][] = 'Las contraseñas no coinciden';
        return self::$alertas;
    }

    //Comprobar el password
    public function comprobar_password() : bool {
        return password_verify($this->password_actual, $this->password);
    }

    //Hashea el password
    public function hashPassword() : void {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    //Generar un Token
    public function crearToken() : void {
        $this->token = uniqid();
    }

    //Validar el email y el usuario
    public function validar_perfil() {
        if(!$this->nombre)  self::$alertas['error'][] = 'El nombre es obligatorio';
        if(!$this->email)  self::$alertas['error'][] = 'El email es obligatorio';
        return self::$alertas;
    }

    //Valida un email
    public function validarEmail() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) { //Revisa sí tiene la estructura adecuada a un email
            self::$alertas['error'][] = 'El email no es válido';
        }
        return self::$alertas;
    }

    public function validarPassword() {
        if(!$this->password) self::$alertas['error'][] = 'La contraseña es obligatoria';
        if(strlen($this->password) < 6) self::$alertas['error'][] = 'La contraseña debe contener al menos 6 caracteres';
        if($this->password !== $this->password2) self::$alertas['error'][] = 'Las contraseñas no coinciden';
        return self::$alertas;
    }

    public function nuevo_password() : array {
        if(!$this->password_actual) self::$alertas['error'][] = 'La contraseña actual es obligatoria'; 
        if(!$this->password_nuevo) self::$alertas['error'][] = 'La contraseña nueva es obligatoria';
        if(strlen($this->password_nuevo) < 6) self::$alertas['error'][] = 'La contraseña nueva debe tener al menos 6 caracteres';
        return self::$alertas;
    }
   
}