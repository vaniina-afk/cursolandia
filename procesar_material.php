<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: panel.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_curso = (int)$_POST['id_curso'];
$nombre_archivo = $conexion->real_escape_string($_POST['nombre_archivo']);

// Validar que el usuario sea el creador del curso
$sql_check = "SELECT id_curso FROM Cursos WHERE id_curso = $id_curso AND id_creador = $id_usuario";
$resultado = $conexion->query($sql_check);

if ($resultado->num_rows == 0) {
    header("Location: panel.php");
    exit();
}

if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
    
    // 1. Control de tamaño máximo (5MB = 5 * 1024 * 1024 bytes)
    $tamanio_bytes = $_FILES['archivo']['size'];
    if ($tamanio_bytes > 5242880) {
        header("Location: curso_interno.php?id=$id_curso&error=tamanio");
        exit();
    }

    // Convertimos bytes a Megabytes con 2 decimales para la base de datos
    $tamanio_mb = round($tamanio_bytes / 1048576, 2);
    
    $extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
    $nombre_unico = "mat_" . $id_curso . "_" . time() . "_" . uniqid() . "." . $extension;
    $ruta_destino = "uploads/" . $nombre_unico;
    
    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
        // Insertamos usando los nombres exactos de tu tabla
        $sql_insert = "INSERT INTO Materiales (id_curso, nombre_archivo, ruta_archivo, tamanio_mb) 
                       VALUES ($id_curso, '$nombre_archivo', '$nombre_unico', $tamanio_mb)";
        
        if ($conexion->query($sql_insert)) {
            header("Location: curso_interno.php?id=$id_curso&exito=1");
            exit();
        }
    }
}

header("Location: curso_interno.php?id=$id_curso&error=1");
exit();
?>