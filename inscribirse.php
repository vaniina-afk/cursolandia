<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit();
}

// 1. Bloqueamos a los administradores
if ($_SESSION['es_admin'] == 1) {
    header("Location: index.php?error=admin");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_curso = (int)$_POST['id_curso'];

$query_cupo = $conexion->query("SELECT cupo FROM Cursos WHERE id_curso = $id_curso");
$curso_data = $query_cupo->fetch_assoc();

if ($curso_data['cupo'] !== null && $curso_data['cupo'] <= 0) {
    header("Location: index.php?error=lleno");
    exit();
}

// 2. Usamos try...catch para atrapar errores de la base de datos silenciosamente
try {
    $sql = "INSERT IGNORE INTO Inscripciones (id_curso, id_usuario, estado) VALUES ($id_curso, $id_usuario, 'Aceptado')";
    
    if ($conexion->query($sql)) {
        if ($curso_data['cupo'] !== null) {
            $conexion->query("UPDATE Cursos SET cupo = cupo - 1 WHERE id_curso = $id_curso");
        }
        header("Location: index.php?exito=1");
    } else {
        header("Location: index.php");
    }
} catch (mysqli_sql_exception $e) {
    // Si la base de datos rechaza la operación (ej: cupo en negativo), lo mandamos acá
    header("Location: index.php?error=lleno");
}
exit();
?>