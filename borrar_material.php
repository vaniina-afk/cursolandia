<?php
session_start();
require_once 'includes/conexion.php';

// Si no están los datos necesarios o no está logueado, lo pateamos
if (!isset($_SESSION['id_usuario']) || !isset($_GET['id_mat']) || !isset($_GET['id_curso'])) {
    header("Location: panel.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_material = (int)$_GET['id_mat'];
$id_curso = (int)$_GET['id_curso'];

// Control de seguridad nivel Dios: Verificar que el usuario sea el CREADOR del curso
$sql_check = "SELECT id_curso FROM Cursos WHERE id_curso = $id_curso AND id_creador = $id_usuario";
$check_curso = $conexion->query($sql_check);

if ($check_curso->num_rows > 0) {
    // Si es el creador, buscamos cómo se llama el archivo físicamente
    $sql_mat = "SELECT ruta_archivo FROM Materiales WHERE id_material = $id_material AND id_curso = $id_curso";
    $res_mat = $conexion->query($sql_mat);
    
    if ($res_mat->num_rows > 0) {
        $material = $res_mat->fetch_assoc();
        $ruta_fisica = "uploads/" . $material['ruta_archivo'];
        
        // Magia de PHP: Si el archivo existe en la carpeta, lo destruye (libera espacio)
        if (file_exists($ruta_fisica)) {
            unlink($ruta_fisica);
        }
        
        // Ahora sí, borramos el registro de la base de datos
        $conexion->query("DELETE FROM Materiales WHERE id_material = $id_material");
        
        // Redirigimos con un mensaje de éxito
        header("Location: curso_interno.php?id=$id_curso&borrado=1");
        exit();
    }
}

// Si algo falla o un alumno intentó acceder a esta URL, lo devuelve al curso
header("Location: curso_interno.php?id=$id_curso");
exit();
?>