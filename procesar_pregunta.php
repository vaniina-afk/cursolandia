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
        // Obtenemos el ID de la pregunta recién creada
        $id_pregunta_nueva = $conexion->insert_id;

        // Buscamos al creador del curso y el título para notificarle
        $sql_curso = "SELECT id_creador, titulo FROM Cursos WHERE id_curso = $id_curso";
        $curso = $conexion->query($sql_curso)->fetch_assoc();
        $id_creador = $curso['id_creador'];

        // Si el que pregunta NO es el profesor, le mandamos notificación
        if ($id_creador != $id_usuario) {
            $sql_nombre = "SELECT nombre, apellido FROM Usuarios WHERE id_usuario = $id_usuario";
            $usuario_pregunta = $conexion->query($sql_nombre)->fetch_assoc();
            $nombre_completo = $usuario_pregunta['nombre'] . ' ' . $usuario_pregunta['apellido'];

            $mensaje_noti = "$nombre_completo publicó una nueva consulta en tu curso '" . $curso['titulo'] . "'.";
            $enlace = "pregunta_foro.php?id=$id_pregunta_nueva";
            
            $conexion->query("INSERT INTO notificaciones (id_usuario, mensaje, enlace, leida) VALUES ($id_creador, '$mensaje_noti', '$enlace', 0)");
        }

        // Redirigimos al curso con un mensaje de éxito
        header("Location: curso_interno.php?id=$id_curso&foro=pregunta_ok");
    }
} catch (mysqli_sql_exception $e) {
    // Si hay error, redirigimos con alerta
    header("Location: curso_interno.php?id=$id_curso&foro=error");
}
exit();
?>