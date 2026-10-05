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

$es_destacada = ($curso['id_creador'] == $id_usuario) ? 1 : 0;

try {
    // 2. Insertamos la respuesta en la base de datos
    $sql_insert = "INSERT INTO respuestas (id_pregunta, id_usuario, cuerpo, es_destacada) 
                   VALUES ($id_pregunta, $id_usuario, '$cuerpo', $es_destacada)";
    $conexion->query($sql_insert);

    // 3. ¡Magia! Sistema de Notificaciones Automático
    // Buscamos quién es el dueño de la pregunta original para avisarle
    $sql_autor_pregunta = "SELECT id_usuario FROM preguntas WHERE id_pregunta = $id_pregunta";
    $autor = $conexion->query($sql_autor_pregunta)->fetch_assoc()['id_usuario'];

    // Solo mandamos la notificación si el que responde NO es el mismo que preguntó
    if ($autor != $id_usuario) {
        // Buscamos el nombre del que acaba de responder para armar un mensaje personalizado
        $sql_nombre = "SELECT nombre, apellido FROM Usuarios WHERE id_usuario = $id_usuario";
        $usuario_responde = $conexion->query($sql_nombre)->fetch_assoc();
        $nombre_completo = $usuario_responde['nombre'] . ' ' . $usuario_responde['apellido'];
        
        $mensaje_noti = "$nombre_completo ha respondido a tu consulta en el foro.";
        
        // Insertamos la alerta con el enlace directo a la pregunta
        $enlace = "pregunta_foro.php?id=$id_pregunta";
        $sql_noti = "INSERT INTO notificaciones (id_usuario, mensaje, enlace, leida) 
                     VALUES ($autor, '$mensaje_noti', '$enlace', 0)";
        $conexion->query($sql_noti);
    }

    // Redirigimos de vuelta a la pregunta para que vea su comentario publicado
    header("Location: pregunta_foro.php?id=$id_pregunta");

} catch (mysqli_sql_exception $e) {
    // Si hay error en la base de datos, volvemos a la pregunta pero evitamos que la página colapse
    header("Location: pregunta_foro.php?id=$id_pregunta&error=1");
}
exit();
?>