<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_curso = (int)$_POST['id_curso'];

// 1. Verificamos el cupo actual del curso
$query_cupo = $conexion->query("SELECT cupo FROM Cursos WHERE id_curso = $id_curso");
$curso_data = $query_cupo->fetch_assoc();

// Si el cupo no es nulo (es decir, tiene límite) y es 0 o menos, rechazamos
if ($curso_data['cupo'] !== null && $curso_data['cupo'] <= 0) {
    header("Location: index.php?error=lleno");
    exit();
}

// 2. Insertamos la inscripción
$sql = "INSERT IGNORE INTO Inscripciones (id_curso, id_usuario, estado) VALUES ($id_curso, $id_usuario, 'Aceptado')";

if ($conexion->query($sql)) {
    // 3. Si la inscripción fue exitosa y el curso tiene límite, descontamos 1
    if ($curso_data['cupo'] !== null) {
        $conexion->query("UPDATE Cursos SET cupo = cupo - 1 WHERE id_curso = $id_curso");
    }
    header("Location: index.php?exito=1");
} else {
    header("Location: index.php");
}
exit();
?>