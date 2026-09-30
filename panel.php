<?php
session_start();

// Validamos que el usuario esté logueado. Si no, lo echamos al login.
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$nombre_usuario = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Panel - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--color-primario);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="panel.php">Cursolandia</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Hola, <?php echo htmlspecialchars($nombre_usuario); ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12 text-center">
                <h2 class="text-primario fw-bold">Bienvenido a tu panel de control</h2>
                <p class="text-secondary mt-3">Acá vas a poder crear tus cursos o anotarte en los de otros usuarios.</p>
            </div>
        </div>
    </div>
</body>
</html>