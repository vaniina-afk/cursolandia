<?php
session_start();
require_once 'includes/conexion.php';

// Verificamos que el usuario esté logueado y la petición sea válida
if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_curso = (int)$_POST['id_curso'];

// Insertamos la inscripción en la base de datos.
// Usamos INSERT IGNORE para que MySQL no tire error si por algún motivo intenta inscribirse dos veces
$sql = "INSERT IGNORE INTO Inscripciones (id_curso, id_usuario, estado) VALUES ($id_curso, $id_usuario, 'Aceptado')";

if ($conexion->query($sql)) {
    // Lo devolvemos al index con una variable en la URL para mostrar el mensaje de éxito
    header("Location: index.php?exito=1");
} else {
    header("Location: index.php");
}
exit();
?>