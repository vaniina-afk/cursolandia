<?php
// Iniciamos la sesión ANTES de cualquier etiqueta HTML
session_start();

// Si el usuario ya tiene sesión iniciada, lo mandamos directo a su panel
if (isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

require_once 'includes/conexion.php';
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conexion->real_escape_string($_POST['email']);
    // Usamos 'contrasena' tal como definiste en la base de datos
    $contrasena_ingresada = $_POST['contrasena'];

    // Buscamos al usuario por su correo
    $sql = "SELECT id_usuario, nombre, apellido, contrasena, es_admin, plan_pro FROM Usuarios WHERE email = '$email'";
    $resultado = $conexion->query($sql);

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        
        // Verificamos si la contraseña ingresada coincide con la encriptada
        if (password_verify($contrasena_ingresada, $usuario['contrasena'])) {
            
            // Credenciales correctas: Guardamos los datos importantes en la sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['es_admin'] = $usuario['es_admin'];
            $_SESSION['plan_pro'] = $usuario['plan_pro'];
            
            // Lo redirigimos a su panel principal (que crearemos en el próximo paso)
            header("Location: index.php");
            exit();
        } else {
            $mensaje = "<div class='alert alert-danger p-2 small mb-3'>Contraseña incorrecta.</div>";
        }
    } else {
        $mensaje = "<div class='alert alert-danger p-2 small mb-3'>No existe una cuenta con este correo.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 mb-4">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card card-compacta">
                    <div class="card-body">
                        <h3 class="text-center text-primario mb-3 fw-bold">Iniciar Sesión</h3>
                        
                        <?php echo $mensaje; ?>
                        
                        <form action="login.php" method="POST">
                            <div class="mb-2">
                                <label class="form-label text-secondary fw-semibold small mb-1">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control form-control-sm" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-secondary fw-semibold small mb-1">Contraseña</label>
                                <input type="password" name="contrasena" class="form-control form-control-sm" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primario w-100 py-1 fw-bold">Ingresar</button>
                            
                            <div class="text-center mt-3">
                                <a href="index.php" class="text-decoration-none small text-secondary hover-primario">Volver al inicio</a>
                            </div>
                            <div class="text-center mt-1">
                                <span class="small text-muted">¿No tenés cuenta? <a href="registro.php" class="text-primario fw-semibold text-decoration-none">Registrate acá</a></span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>