<?php
// Parámetros de conexión a la base de datos local (XAMPP)
$servidor = "localhost";
$usuario = "root";
$contrasena = ""; // XAMPP por defecto no tiene contraseña
$base_datos = "cursolandia";

// Creamos la conexión usando mysqli
$conexion = new mysqli($servidor, $usuario, $contrasena, $base_datos);

// Verificamos si hubo algún error al conectar
if ($conexion->connect_error) {
    die("Error crítico de conexión: " . $conexion->connect_error);
}

// Forzamos el uso de utf8mb4 para evitar problemas con tildes y caracteres especiales
$conexion->set_charset("utf8mb4");
?>