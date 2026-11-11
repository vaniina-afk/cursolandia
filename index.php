<?php
session_start();

require_once __DIR__ . '/Backend/Config/conexion.php';
require_once __DIR__ . '/Backend/Controllers/CursoController.php';
require_once __DIR__ . '/Backend/Controllers/AuthController.php';
require_once __DIR__ . '/Backend/Controllers/UsuarioController.php';

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

    case 'inicio':
    default:
        $cursoController = new CursoController($conexion);
        $cursoController->index();
        break;
}