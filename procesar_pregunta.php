<?php
session_start();
require_once 'includes/conexion.php';

// Verificamos que el usuario esté logueado y que los datos vengan por POST
if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: panel.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_curso = (int)$_POST['id_curso'];

// Limpiamos los textos para evitar inyecciones SQL
$titulo = $conexion->real_escape_string(trim($_POST['titulo']));
$cuerpo = $conexion->real_escape_string(trim($_POST['cuerpo']));
$estado = 'Abierta'; // Estado inicial de la pregunta

try {
    // Insertamos usando las columnas exactas de tu tabla 'preguntas'
    $sql = "INSERT INTO preguntas (id_curso, id_usuario, titulo, cuerpo, estado) 
            VALUES ($id_curso, $id_usuario, '$titulo', '$cuerpo', '$estado')";
    
    if ($conexion->query($sql)) {
        // Redirigimos al curso con un mensaje de éxito
        header("Location: curso_interno.php?id=$id_curso&foro=pregunta_ok");
    }
} catch (mysqli_sql_exception $e) {
    // Si hay error, redirigimos con alerta
    header("Location: curso_interno.php?id=$id_curso&foro=error");
}
exit();
?>