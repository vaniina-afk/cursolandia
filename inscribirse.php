<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit();
}

if ($_SESSION['es_admin'] == 1) {
    header("Location: index.php?error=admin");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_curso = (int)$_POST['id_curso'];

// 1. Verificar si YA está inscripto
$check_insc = $conexion->query("SELECT id_inscripcion FROM Inscripciones WHERE id_curso = $id_curso AND id_usuario = $id_usuario");
if ($check_insc->num_rows > 0) {
    header("Location: index.php"); 
    exit();
}

$query_cupo = $conexion->query("SELECT cupo FROM Cursos WHERE id_curso = $id_curso");
$curso_data = $query_cupo->fetch_assoc();

if ($curso_data['cupo'] !== null && $curso_data['cupo'] <= 0) {
    header("Location: index.php?error=lleno");
    exit();
}

// 2. Iniciamos la transacción (Propiedades ACID)
$conexion->begin_transaction();

try {
    // A. Inscribir al alumno
    $sql = "INSERT INTO Inscripciones (id_curso, id_usuario, estado) VALUES ($id_curso, $id_usuario, 'Aceptado')";
    $conexion->query($sql);
    
    // B. Descontar el cupo
    if ($curso_data['cupo'] !== null) {
        $conexion->query("UPDATE Cursos SET cupo = cupo - 1 WHERE id_curso = $id_curso");
    }
    
    // Si ambas consultas fueron exitosas, confirmamos los cambios
    $conexion->commit();
    header("Location: index.php?exito=1");
    
} catch (mysqli_sql_exception $e) {
    // Si falla la resta del cupo o cualquier otra cosa, deshacemos la inscripción
    $conexion->rollback();
    header("Location: index.php?error=lleno");
}
exit();
?>