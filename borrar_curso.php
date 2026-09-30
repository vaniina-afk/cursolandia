<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: panel.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_curso = (int)$_GET['id'];

// Baja lógica: Ocultamos el curso sin destruir los datos históricos de los inscriptos
$sql = "UPDATE Cursos SET activo = 0 WHERE id_curso = $id_curso AND id_creador = $id_usuario";
$conexion->query($sql);

header("Location: panel.php");
exit();
?>