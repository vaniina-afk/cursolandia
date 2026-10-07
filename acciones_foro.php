<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['accion']) || !isset($_GET['id'])) {
    header("Location: panel.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$accion = $_GET['accion'];
$id_objetivo = (int)$_GET['id'];
$id_pregunta_redireccion = isset($_GET['id_pregunta']) ? (int)$_GET['id_pregunta'] : $id_objetivo;

switch ($accion) {
    case 'cerrar_pregunta':
        // Verificamos si el usuario es el creador del curso de esta pregunta
        $sql_check = "SELECT p.id_pregunta FROM preguntas p JOIN Cursos c ON p.id_curso = c.id_curso WHERE p.id_pregunta = $id_objetivo AND c.id_creador = $id_usuario";
        if ($conexion->query($sql_check)->num_rows > 0) {
            $conexion->query("UPDATE preguntas SET cerrada = 1 WHERE id_pregunta = $id_objetivo");
        }
        break;

    case 'destacar_respuesta':
        // Verificamos si el usuario es el creador del curso
        $sql_check = "SELECT r.id_respuesta FROM respuestas r JOIN preguntas p ON r.id_pregunta = p.id_pregunta JOIN Cursos c ON p.id_curso = c.id_curso WHERE r.id_respuesta = $id_objetivo AND c.id_creador = $id_usuario";
        if ($conexion->query($sql_check)->num_rows > 0) {
            // Primero quitamos el destacado a todas las respuestas de esa pregunta (opcional, si querés que haya solo 1 destacada)
            // $conexion->query("UPDATE respuestas SET es_destacada = 0 WHERE id_pregunta = $id_pregunta_redireccion");
            
            // Destacamos la seleccionada (alterna entre 0 y 1)
            $conexion->query("UPDATE respuestas SET es_destacada = NOT es_destacada WHERE id_respuesta = $id_objetivo");
        }
        break;

    case 'eliminar_pregunta':
        // Permitir si es el autor de la pregunta O si es el creador del curso
        $sql_check = "SELECT p.id_pregunta, p.id_curso 
                      FROM preguntas p 
                      JOIN Cursos c ON p.id_curso = c.id_curso 
                      WHERE p.id_pregunta = $id_objetivo 
                      AND (p.id_usuario = $id_usuario OR c.id_creador = $id_usuario)";
        $res = $conexion->query($sql_check);
        if ($res->num_rows > 0) {
            $fila = $res->fetch_assoc();
            $id_curso_volver = $fila['id_curso'];
            // Borramos respuestas asociadas primero y luego la pregunta
            $conexion->query("DELETE FROM respuestas WHERE id_pregunta = $id_objetivo");
            $conexion->query("DELETE FROM preguntas WHERE id_pregunta = $id_objetivo");
            header("Location: curso_interno.php?id=$id_curso_volver&mensaje=borrado");
            exit();
        }
        break;

    case 'eliminar_respuesta':
        // Permitir si es el autor de la respuesta O si es el creador del curso
        $sql_check = "SELECT r.id_respuesta 
                      FROM respuestas r 
                      JOIN preguntas p ON r.id_pregunta = p.id_pregunta 
                      JOIN Cursos c ON p.id_curso = c.id_curso 
                      WHERE r.id_respuesta = $id_objetivo 
                      AND (r.id_usuario = $id_usuario OR c.id_creador = $id_usuario)";
        if ($conexion->query($sql_check)->num_rows > 0) {
            $conexion->query("DELETE FROM respuestas WHERE id_respuesta = $id_objetivo");
        }
        break;
}

// Redirigir de vuelta a la pregunta
header("Location: pregunta_foro.php?id=$id_pregunta_redireccion");
exit();
?>