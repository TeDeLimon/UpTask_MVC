<?php 

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;

class LoginController {
    public static function login(Router $router) {
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            
            $alertas = $auth->validarLogin();

            if(empty($alertas)) {
                //Verificar que el usuario exista
                $usuario = Usuario::where('email', $auth->email);
                if(!$usuario | (!$usuario->confirmado)) { //Sino encuentra el usuario o el usuario no está confirmado
                    Usuario::setAlerta('error', 'EL usuario no existe o No está confirmado');
                } else {
                    if(!password_verify($auth->password, $usuario->password)) { //Nos devuelve true o false
                        Usuario::setAlerta('error', 'La contraseña es incorrecta');
                    } else {
                        //Iniciar la sesión del usuario
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        //Redireccionar a proyectos
                        header('Location: /dashboard');
                    }
                }
            }
        }
        $alertas = Usuario::getAlertas();
        //Render a la vista
        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesión',
            'alertas' => $alertas
        ]);
    }

    public static function logout() {
        session_start();
        $_SESSION = [];
        header('Location: /');
    }

    public static function crear(Router $router) {

        $usuario = new Usuario;
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            //Si no existen alertas 
            if(empty($alertas)) {
                //Revisar sí el usuario está registrado
                $existeUsuario = Usuario::where('email', $usuario->email);
                
                if($existeUsuario) {
                    Usuario::setAlerta('error', 'El usuario ya está registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    //Hashear el password
                    $usuario->hashPassword();
                    //Eliminar password2
                    unset($usuario->password2);
                    //Crear Token
                    $usuario->crearToken();
                    //Crear un nuevo Usuario
                    $resultado = $usuario->guardar();

                    //Enviar Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();
                    if($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
            
        }
        
        //Render a la vista
        $router->render('auth/crear', [
            'titulo' => 'Crear Cuenta',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router) {
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)) {
                //Buscar el usuario
                $usuario = Usuario::where('email', $usuario->email); //Sobreescribimos la variable, sí encontramos el usuario se sobreescribe con toda la información, de lo contrario es null
                if($usuario && $usuario->confirmado) { //De encontrar el usuario
                    //Generar un nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);
                    //Actualizar el usuario
                    $usuario->guardar();
                    //Enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();
                    //Imprimir la alerta
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                } else {
                    Usuario::setAlerta('error', 'El Usuario no existe o no está confirmado');
                }
            }
            $alertas = Usuario::getAlertas();
        }

        //Render a la vista
        $router->render('auth/olvide', [
            'titulo' => 'Olvide mi Password',
            'alertas' =>  $alertas
        ]);
    }

    public static function reestablecer(Router $router) {
        $token = $_GET['token'];
        $mostrar = true;

        if(!$token) header('Location: /');

        //Encontrar al usuario con ese token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no Válido');
            $mostrar = false;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Añadir el nuevo password
            $usuario->sincronizar($_POST);

            //Validar el password
            $alertas = $usuario->validarPassword();

            if(empty($alertas)) {
                //Hashear el nuevo password
                $usuario->hashPassword();
                unset($usuario->password2);
                //Eliminar el token
                $usuario->token = null;
                //Guardar el usuario
                $resultado = $usuario->guardar();
                //Redireccionar
                if($resultado) {
                   header('Location: /');
                }
                debuguear($usuario);
            }
        }

        $alertas = Usuario::getAlertas();
        //Render a la vista
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Password',
            'alertas' => $alertas, 
            'mostrar' =>  $mostrar
        ]);
    }

    public static function mensaje(Router $router) {
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta creada éxitosamente'
        ]);
    }

    public static function confirmar(Router $router) {

        $token = s($_GET['token']);

        if(!$token) header('Location: /');

        //Encontrar al usuario con ese token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) { //No se encontró un token con esos conejos
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            //Confirmar la cuenta
            $usuario->confirmado = 1;
            $usuario->token = null;
            unset($usuario->password2);
            //Guardar en la base de datos
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta Comprobada Correctamente');
        }
        $alertas = Usuario::getAlertas();

        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta UpTask',
            'alertas' => $alertas
        ]);
    }

    
}