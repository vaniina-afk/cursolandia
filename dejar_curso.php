<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: panel.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_curso = (int)$_GET['id'];

// Verificamos que realmente esté inscripto
$check = $conexion->query("SELECT id_inscripcion FROM Inscripciones WHERE id_curso = $id_curso AND id_usuario = $id_usuario");

if ($check->num_rows > 0) {
    // 1. Borramos la inscripción
    $conexion->query("DELETE FROM Inscripciones WHERE id_curso = $id_curso AND id_usuario = $id_usuario");
    
    // 2. Devolvemos el cupo al curso (solo si no es ilimitado)
    $conexion->query("UPDATE Cursos SET cupo = cupo + 1 WHERE id_curso = $id_curso AND cupo IS NOT NULL");
    
    header("Location: panel.php?baja=1");
} else {
    header("Location: panel.php");
}
exit();
?>