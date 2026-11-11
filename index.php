<?php
session_start();

require_once __DIR__ . '/Backend/Config/conexion.php';
require_once __DIR__ . '/Backend/Controllers/CursoController.php';
require_once __DIR__ . '/Backend/Controllers/AuthController.php';
require_once __DIR__ . '/Backend/Controllers/UsuarioController.php';
require_once __DIR__ . '/Backend/Controllers/ForoController.php';

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'inicio';

switch ($accion) {
    case 'login':
        $authController = new AuthController($conexion);
        $authController->login();
        break;

    case 'registro':
        $authController = new AuthController($conexion);
        $authController->registro();
        break;

    case 'logout':
        $authController = new AuthController($conexion);
        $authController->logout();
        break;

    case 'editar_perfil':
        $usuarioController = new UsuarioController($conexion);
        $usuarioController->editarPerfil();
        break;

    case 'solicitar_pro':
        $usuarioController = new UsuarioController($conexion);
        $usuarioController->solicitarPro();
        break;

    case 'admin_solicitudes':
        $usuarioController = new UsuarioController($conexion);
        $usuarioController->adminSolicitudes();
        break;
    
    case 'panel':
        $cursoController = new CursoController($conexion);
        $cursoController->panel();
        break;

    case 'crear_curso':
        $cursoController = new CursoController($conexion);
        $cursoController->crear();
        break;

    case 'editar_curso':
        $cursoController = new CursoController($conexion);
        $cursoController->editar();
        break;

    case 'borrar_curso':
        $cursoController = new CursoController($conexion);
        $cursoController->borrar();
        break;

    case 'inscribirse':
        $cursoController = new CursoController($conexion);
        $cursoController->inscribirse();
        break;

    case 'dejar_curso':
        $cursoController = new CursoController($conexion);
        $cursoController->dejarCurso();
        break;
    
    case 'curso_interno':
        $cursoController = new CursoController($conexion);
        $cursoController->verInterno();
        break;

    case 'subir_material':
        $cursoController = new CursoController($conexion);
        $cursoController->subirMaterial();
        break;

    case 'borrar_material':
        $cursoController = new CursoController($conexion);
        $cursoController->borrarMaterial();
        break;
    
    case 'pregunta_foro':
        $foroController = new ForoController($conexion);
        $foroController->verPregunta();
        break;

    case 'procesar_pregunta':
        $foroController = new ForoController($conexion);
        $foroController->crearPregunta();
        break;

    case 'procesar_respuesta':
        $foroController = new ForoController($conexion);
        $foroController->crearRespuesta();
        break;

    case 'cerrar_pregunta':
        $foroController = new ForoController($conexion);
        $foroController->cerrarPregunta();
        break;

    case 'destacar_respuesta':
        $foroController = new ForoController($conexion);
        $foroController->destacarRespuesta();
        break;

    case 'eliminar_pregunta':
        $foroController = new ForoController($conexion);
        $foroController->eliminarPregunta();
        break;

    case 'eliminar_respuesta':
        $foroController = new ForoController($conexion);
        $foroController->eliminarRespuesta();
        break;
    
    case 'inicio':
    default:
        $cursoController = new CursoController($conexion);
        $cursoController->index();
        break;
}