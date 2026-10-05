<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: panel.php");
    exit();
}

$id_noti = (int)$_GET['id'];
$id_usuario = (int)$_SESSION['id_usuario'];

// Buscamos la notificación asegurándonos que le pertenezca a este usuario
$sql = "SELECT enlace FROM notificaciones WHERE id_notificacion = $id_noti AND id_usuario = $id_usuario";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    $noti = $resultado->fetch_assoc();
    $enlace = $noti['enlace'];

    // La marcamos como leída
    $conexion->query("UPDATE notificaciones SET leida = 1 WHERE id_notificacion = $id_noti");

    // Redirigimos al usuario a la pregunta del foro
    header("Location: " . $enlace);
} else {
    header("Location: panel.php");
}
exit();
?>