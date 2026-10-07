<?php
session_start();
require_once 'includes/conexion.php';

// Validamos que el usuario esté logueado y que los datos vengan por POST
if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: panel.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_pregunta = (int)$_POST['id_pregunta'];
$id_curso = (int)$_POST['id_curso'];
$cuerpo = $conexion->real_escape_string(trim($_POST['cuerpo']));

// 1. Verificamos si el que responde es el creador del curso para destacarla
$sql_curso = "SELECT id_creador FROM Cursos WHERE id_curso = $id_curso";
$result_curso = $conexion->query($sql_curso);
$curso = $result_curso->fetch_assoc();
$id_creador = $curso['id_creador'];

$es_destacada = ($id_creador == $id_usuario) ? 1 : 0;

try {
    // 2. Insertamos la respuesta en la base de datos
    $sql_insert = "INSERT INTO respuestas (id_pregunta, id_usuario, cuerpo, es_destacada) 
                   VALUES ($id_pregunta, $id_usuario, '$cuerpo', $es_destacada)";
    $conexion->query($sql_insert);

    // 3. Sistema de Notificaciones Automático (Alumno y Profesor)
    
    // Obtenemos los datos de quién respondió
    $sql_nombre = "SELECT nombre, apellido FROM Usuarios WHERE id_usuario = $id_usuario";
    $usuario_responde = $conexion->query($sql_nombre)->fetch_assoc();
    $nombre_completo = $usuario_responde['nombre'] . ' ' . $usuario_responde['apellido'];
    
    // Obtenemos al dueño de la pregunta original
    $sql_autor_pregunta = "SELECT id_usuario FROM preguntas WHERE id_pregunta = $id_pregunta";
    $autor = $conexion->query($sql_autor_pregunta)->fetch_assoc()['id_usuario'];
    
    $enlace = "pregunta_foro.php?id=$id_pregunta";

    // A) Notificar al autor de la pregunta (solo si no es él mismo respondiéndose)
    if ($autor != $id_usuario) {
        $mensaje_autor = "$nombre_completo ha respondido a tu consulta en el foro.";
        $conexion->query("INSERT INTO notificaciones (id_usuario, mensaje, enlace, leida) VALUES ($autor, '$mensaje_autor', '$enlace', 0)");
    }

    // B) Notificar al profesor (solo si él no respondió, y si él no es el autor de la pregunta para no mandarle 2 avisos)
    if ($id_creador != $id_usuario && $id_creador != $autor) {
        $mensaje_profe = "$nombre_completo ha dejado una respuesta en el foro de tu curso.";
        $conexion->query("INSERT INTO notificaciones (id_usuario, mensaje, enlace, leida) VALUES ($id_creador, '$mensaje_profe', '$enlace', 0)");
    }

    // Redirigimos de vuelta a la pregunta para que vea su comentario publicado
    header("Location: pregunta_foro.php?id=$id_pregunta");

} catch (mysqli_sql_exception $e) {
    header("Location: pregunta_foro.php?id=$id_pregunta&error=1");
}
exit();
?>