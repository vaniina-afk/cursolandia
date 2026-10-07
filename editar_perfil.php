<?php
session_start();
require_once 'includes/conexion.php';

// Validamos que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
require_once 'includes/logica_notificaciones.php';

$mensaje_perfil = "";
$mensaje_pass = "";

// Procesamiento de los formularios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // CASO 1: Actualizar Datos Personales, Antecedentes y Foto
    if (isset($_POST['actualizar_datos'])) {
        $nombre = $conexion->real_escape_string(trim($_POST['nombre']));
        $apellido = $conexion->real_escape_string(trim($_POST['apellido']));
        $email = $conexion->real_escape_string(trim($_POST['email']));
        $antecedentes = $conexion->real_escape_string(trim($_POST['antecedentes']));
        
        $query_imagen = ""; // Variable para concatenar la imagen si es que sube una nueva

        // Verificamos si subió una foto nueva y si no hubo errores
        if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['imagen_perfil']['tmp_name'];
            $file_name = $_FILES['imagen_perfil']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            // Validamos que sea una imagen
            if (in_array($file_ext, $permitidos)) {
                // Generamos un nombre único para que no se sobreescriban fotos de distintos usuarios
                $nuevo_nombre = "perfil_" . $id_usuario . "_" . time() . "." . $file_ext;
                $ruta_destino = "uploads/" . $nuevo_nombre;
                
                // Movemos el archivo de la carpeta temporal a nuestra carpeta 'uploads'
                if (move_uploaded_file($file_tmp, $ruta_destino)) {
                    $query_imagen = ", imagen_perfil = '$nuevo_nombre'";
                } else {
                    $mensaje_perfil = "<div class='alert alert-warning p-2 small mb-3 auto-ocultar'>No se pudo guardar la imagen. Verificá los permisos de la carpeta uploads.</div>";
                }
            } else {
                $mensaje_perfil = "<div class='alert alert-danger p-2 small mb-3 auto-ocultar'>Formato de imagen no permitido. Usá JPG, PNG o GIF.</div>";
            }
        }

        // Actualizamos todos los datos. Concatenamos la query de imagen por si se subió una
        $sql_update = "UPDATE Usuarios SET nombre = '$nombre', apellido = '$apellido', email = '$email', antecedentes = '$antecedentes' $query_imagen WHERE id_usuario = $id_usuario";
        
        if ($conexion->query($sql_update)) {
            $_SESSION['nombre'] = $nombre; // Actualizamos la sesión
            if (empty($mensaje_perfil)) { // Si no hubo errores con la imagen, mostramos éxito general
                $mensaje_perfil = "<div class='alert alert-success p-2 small mb-3 auto-ocultar'>Datos del perfil actualizados correctamente.</div>";
            }
        } else {
            $mensaje_perfil = "<div class='alert alert-danger p-2 small mb-3 auto-ocultar'>Hubo un error al actualizar los datos en la base de datos.</div>";
        }
    }
    
    // CASO 2: Actualizar Contraseña (se mantiene igual)
    if (isset($_POST['actualizar_password'])) {
        $pass_actual = trim($_POST['pass_actual']);
        $pass_nueva = trim($_POST['pass_nueva']);
        $pass_confirmar = trim($_POST['pass_confirmar']);

        if ($pass_nueva !== $pass_confirmar) {
            $mensaje_pass = "<div class='alert alert-danger p-2 small mb-3 auto-ocultar'>Las contraseñas nuevas no coinciden.</div>";
        } elseif (strlen($pass_nueva) < 6) {
            $mensaje_pass = "<div class='alert alert-warning p-2 small mb-3 auto-ocultar'>La nueva contraseña debe tener al menos 6 caracteres.</div>";
        } else {
            $sql_user = "SELECT contrasena FROM Usuarios WHERE id_usuario = $id_usuario";
            $row = $conexion->query($sql_user)->fetch_assoc();
            
            if (password_verify($pass_actual, $row['contrasena'])) {
                $hash_nuevo = password_hash($pass_nueva, PASSWORD_DEFAULT);
                $conexion->query("UPDATE Usuarios SET contrasena = '$hash_nuevo' WHERE id_usuario = $id_usuario");
                $mensaje_pass = "<div class='alert alert-success p-2 small mb-3 auto-ocultar'>Contraseña actualizada con éxito.</div>";
            } else {
                $mensaje_pass = "<div class='alert alert-danger p-2 small mb-3 auto-ocultar'>La contraseña actual es incorrecta.</div>";
            }
        }
    }
}

// Obtenemos los datos frescos para rellenar el formulario visual
$sql_datos = "SELECT nombre, apellido, email, antecedentes, imagen_perfil FROM Usuarios WHERE id_usuario = $id_usuario";
$datos_usuario = $conexion->query($sql_datos)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">
    
    <!-- NAVBAR ESTANDARIZADA -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background-color: var(--color-primario);">
        <div class="container d-flex justify-content-between">
            <a class="navbar-brand fw-bold text-white text-decoration-none" href="index.php">Cursolandia</a>
            
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown me-2">
                    <a href="#" class="text-white text-decoration-none position-relative" id="campanita" data-bs-toggle="dropdown" aria-expanded="false">
                        <span style="font-size: 1.2rem;">🔔</span>
                        <?php if (isset($no_leidas) && $no_leidas > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                <?php echo $no_leidas; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="campanita" style="width: 320px; max-height: 400px; overflow-y: auto; z-index: 1050;">
                        <li><h6 class="dropdown-header fw-bold text-dark border-bottom pb-2">Notificaciones</h6></li>
                        <?php if (isset($result_noti) && $result_noti->num_rows > 0): ?>
                            <?php while($noti = $result_noti->fetch_assoc()): ?>
                                <li>
                                    <a class="dropdown-item py-3 border-bottom text-wrap <?php echo ($noti['leida'] == 0) ? 'bg-light' : ''; ?>" href="leer_notificacion.php?id=<?php echo $noti['id_notificacion']; ?>">
                                        <div class="d-flex flex-column gap-1">
                                            <span class="small" style="line-height: 1.2;"><?php echo htmlspecialchars($noti['mensaje']); ?></span>
                                            <small class="text-primario fw-bold" style="font-size: 0.7rem;">⏱️ <?php echo tiempo_transcurrido($noti['fecha']); ?></small>
                                        </div>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                            <li><a class="dropdown-item text-center small text-muted py-2" href="notificaciones.php">Ver todas</a></li>
                        <?php else: ?>
                            <li><span class="dropdown-item text-muted small text-center py-3">No tenés notificaciones nuevas.</span></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Menú Desplegable del Usuario -->
                <div class="dropdown me-3 d-none d-md-inline">
                    <a href="#" class="text-white text-decoration-none dropdown-toggle fw-semibold" id="perfilDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="perfilDropdown">
                        <li><a class="dropdown-item py-2 fw-semibold text-secondary" href="editar_perfil.php">⚙️ Mi Perfil</a></li>
                    </ul>
                </div>
                <a href="panel.php" class="btn btn-outline-light btn-sm me-2">Volver al Panel</a>
                <a href="logout.php" class="btn btn-acento btn-sm">Salir</a>
            </div>
        </div>
    </nav>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container mb-5">
        <h4 class="fw-bold text-primario mb-4">Configuración de la Cuenta</h4>
        
        <div class="row g-4">
            <!-- COLUMNA 1: DATOS PERSONALES -->
            <div class="col-md-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4 text-secondary">Perfil Público y Datos</h6>
                        <?php echo $mensaje_perfil; ?>
                        
                        <!-- IMPORTANTE: El enctype="multipart/form-data" permite subir archivos -->
                        <form action="editar_perfil.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="actualizar_datos" value="1">
                            
                            <!-- Foto de perfil visualización y subida -->
                            <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                                <?php if (!empty($datos_usuario['imagen_perfil'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($datos_usuario['imagen_perfil']); ?>" class="rounded-circle me-3 shadow-sm border" style="width: 70px; height: 70px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 70px; height: 70px; background-color: var(--color-primario); font-weight: bold; font-size: 1.5rem;">
                                        <?php echo strtoupper(substr($datos_usuario['nombre'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <label class="form-label small fw-bold mb-1">Cambiar Foto de Perfil</label>
                                    <input type="file" name="imagen_perfil" class="form-control form-control-sm" accept="image/*">
                                    <div class="form-text" style="font-size: 0.75rem;">Formatos recomendados: JPG, PNG. Max: 2MB.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Nombre</label>
                                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($datos_usuario['nombre']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Apellido</label>
                                    <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($datos_usuario['apellido']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($datos_usuario['email']); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold">Antecedentes / Experiencia <span class="text-muted fw-normal">(Opcional)</span></label>
                                <textarea name="antecedentes" class="form-control bg-light" rows="4" placeholder="Contale a tus alumnos sobre tu trayectoria, estudios o experiencia laboral..."><?php echo htmlspecialchars($datos_usuario['antecedentes']); ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primario w-100 fw-bold">Guardar Cambios de Perfil</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- COLUMNA 2: SEGURIDAD (CONTRASEÑA) -->
            <div class="col-md-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4 text-secondary">Seguridad de la Cuenta</h6>
                        <?php echo $mensaje_pass; ?>
                        
                        <form action="editar_perfil.php" method="POST">
                            <input type="hidden" name="actualizar_password" value="1">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Contraseña Actual</label>
                                <input type="password" name="pass_actual" class="form-control bg-light" required placeholder="Ingresá tu contraseña actual">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nueva Contraseña</label>
                                <input type="password" name="pass_nueva" class="form-control bg-light" required placeholder="Mínimo 6 caracteres">
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Confirmar Nueva Contraseña</label>
                                <input type="password" name="pass_confirmar" class="form-control bg-light" required placeholder="Repetí la nueva contraseña">
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-100 fw-bold mt-2">Actualizar Contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Ocultar notificaciones automáticamente
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                let alertas = document.querySelectorAll('.auto-ocultar');
                alertas.forEach(function(alerta) {
                    alerta.style.transition = "opacity 0.5s ease";
                    alerta.style.opacity = "0";
                    setTimeout(() => alerta.remove(), 500);
                });
            }, 4000);
        });
    </script>
</body>
</html>