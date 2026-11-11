<?php
session_start();

// 1. Cargamos la conexión a la base de datos
// (NOTA: Si en el Paso 2 dejaste el nombre "conexion.php", cambiá "database.php" por "conexion.php")
require_once __DIR__ . '/Backend/Config/conexion.php';

// 2. Cargamos el controlador de cursos
require_once __DIR__ . '/Backend/Controllers/CursoController.php';

// 3. Leemos qué acción pide la URL (si no pide nada, por defecto carga 'inicio')
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'inicio';

switch ($accion) {
    case 'inicio':
    default:
        $cursoController = new CursoController($conexion);
        $cursoController->index();
        break;
}