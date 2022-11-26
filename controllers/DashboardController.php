<?php
namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Model\Proyecto;

class DashboardController {
    public static function index(Router $router) {
        session_start();
        isAuth();

        $id = $_SESSION['id'];
        $proyectos = Proyecto::belongsTo('propietarioId', $id);

        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }

    public static function crear_proyecto(Router $router) {
        session_start();
        isAuth();

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto = new Proyecto($_POST);
            //Validación
            $alertas = $proyecto->validarProyecto();
            
            if(empty($alertas)) {
                //Generar una URL única
                $hash = md5(uniqid()); //uniqid funciona en base a la hora y md5 genera en base a un string
                $proyecto->url = $hash; //Genera 32 bits
                //Almacenar el creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];
                //Guardar el proyecto
                $proyecto->guardar();
                //Redireccionar 
                header('Location: /proyecto?id=' . $proyecto->url); //Redireccionamos al proyecto con tal URL
            }
        }

        $router->render('dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);
    }

    public static function proyecto(Router $router) {
        session_start();
        isAuth();

        $token = $_GET['id'];
        if(!$token) header('Location: /dashboard');
        //Revisar que la persona que visita el proyecto, es quien lo creó
        $proyecto = Proyecto::where('url',$token);
        if($proyecto->propietarioId !== $_SESSION['id']) {
            header('Location: /dashboard');
        }

        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router) {
        session_start();
        isAuth();
        $alertas = [];
        $id = $_SESSION['id'];

        $usuario = Usuario::find($id);
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);

            $alertas = $usuario->validar_perfil();

            if(empty($alertas)) {
                //Verificar que el email no exista
                $existeUsuario = Usuario::where('email', $usuario->email);
                if($existeUsuario && $existeUsuario->id !== $usuario->id) {
                    Usuario::setAlerta('error', 'El correo ya existe, intente otro');
                } else {
                    //Guardar el usuario
                    $usuario->guardar();

                    Usuario::setAlerta('exito', 'Guardado Corrrectamente');
                }
                
                $alertas = $usuario->getAlertas();

                //Asignar el nombre nuevo a la barra
                $_SESSION['nombre'] = $usuario->nombre;
            }
        }
        
        

        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'alertas' => $alertas,
            'usuario' => $usuario
        ]);
    }

    public static function cambiar_password(Router $router) {
        session_start();
        isAuth();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = Usuario::find($_SESSION['id']);
            //Sincronizar con los datos del usuario
            $usuario->sincronizar($_POST);

            $alertas = $usuario->nuevo_password();

            if(empty($alertas)) {
                $resultado = $usuario->comprobar_password();
                if($resultado) {
                    unset($usuario->password_actual);
                    //Asignar el nuevo password
                    $usuario->password = $usuario->password_nuevo;
                    unset($usuario->password_nuevo);
                    $usuario->hashPassword();
                    $resultado = $usuario->guardar();
                    if($resultado)  Usuario::setAlerta('exito', 'Password Guardado exitósamente');
                } else {
                    Usuario::setAlerta('error', 'Password Incorrecto');
                }
            }
            $alertas = Usuario::getAlertas();
        }

        $router->render('dashboard/cambiar-password', [
            'alertas' => $alertas,
            'titulo' => 'Cambiar Password'
        ]);
    }
}