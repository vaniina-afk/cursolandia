<?php
// Incluimos la conexión a la base de datos
require_once 'includes/conexion.php';

$mensaje = "";

// Verificamos si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Escapamos los datos para evitar inyecciones SQL
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $apellido = $conexion->real_escape_string($_POST['apellido']);
    $email = $conexion->real_escape_string($_POST['email']);
    $antecedentes = $conexion->real_escape_string($_POST['antecedentes']);
    
    // Encriptamos la contraseña por seguridad
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

    // Verificamos si el correo ya existe en la base de datos
    $check_email = $conexion->query("SELECT id_usuario FROM Usuarios WHERE email = '$email'");
    
    if ($check_email->num_rows > 0) {
        $mensaje = "<div class='alert alert-danger'>El correo electrónico ya está registrado.</div>";
    } else {
        // Manejo de la imagen de perfil
        $imagen_perfil = 'default.png'; // Valor por defecto si no suben nada

        // Si se subió un archivo y no hay errores en la subida
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            // Generamos un nombre único para evitar que las fotos se sobreescriban
            $nombre_imagen = time() . "_" . uniqid() . "." . $extension;
            $ruta_destino = "uploads/" . $nombre_imagen;
            
            // Movemos el archivo de la memoria temporal a nuestra carpeta uploads
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                $imagen_perfil = $nombre_imagen;
            }
        }

        // Insertamos el nuevo usuario en la base de datos
        $sql = "INSERT INTO Usuarios (nombre, apellido, email, contrasena, antecedentes, imagen_perfil) 
                VALUES ('$nombre', '$apellido', '$email', '$contrasena', '$antecedentes', '$imagen_perfil')";
        
        if ($conexion->query($sql)) {
            $mensaje = "<div class='alert alert-success'>¡Registro exitoso! Ya puedes iniciar sesión.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al registrar: " . $conexion->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4 mb-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-compacta">
                    <div class="card-body">
                        <h3 class="text-center text-primario mb-3 fw-bold">Crear Cuenta</h3>
                        
                        <?php echo $mensaje; ?>
                        
                        <form action="registro.php" method="POST" enctype="multipart/form-data">
                            <div class="row mb-2">
                                <div class="col">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Nombre</label>
                                    <input type="text" name="nombre" class="form-control form-control-sm" required>
                                </div>
                                <div class="col">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Apellido</label>
                                    <input type="text" name="apellido" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label text-secondary fw-semibold small mb-1">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control form-control-sm" required>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label text-secondary fw-semibold small mb-1">Contraseña</label>
                                <input type="password" name="contrasena" class="form-control form-control-sm" required>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label text-secondary fw-semibold small mb-1">Antecedentes</label>
                                <textarea name="antecedentes" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-secondary fw-semibold small mb-1">Imagen de Perfil</label>
                                <input type="file" name="imagen" class="form-control form-control-sm" accept="image/*">
                            </div>
                            
                            <button type="submit" class="btn btn-acento w-100 py-1 fw-bold">Registrarme</button>
                            
                            <div class="text-center mt-2">
                                <a href="index.php" class="text-decoration-none small text-primario">Volver al inicio</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>