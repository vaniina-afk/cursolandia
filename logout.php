<?php
session_start();
session_unset(); // Limpiamos las variables
session_destroy(); // Destruimos la sesión
header("Location: index.php"); // Volvemos al inicio
exit();
?>