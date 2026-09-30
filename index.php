<?php require_once 'includes/conexion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cursolandia - Inicio</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Nuestros estilos personalizados -->
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="text-center mt-5">
    
    <div class="container">
        <h1 class="display-4 text-primario fw-bold">¡Bienvenido a Cursolandia!</h1>
        <p class="lead text-secondary mt-2">
            La plataforma de aprendizaje diseñada a tu medida.
        </p>
        
        <div class="mt-4">
            <a href="registro.php" class="btn btn-primario btn-lg m-2 px-4">Registrarse</a>
            <a href="login.php" class="btn btn-outline-secondary btn-lg m-2 px-4">Iniciar Sesión</a>
        </div>
    </div>

</body>
</html>