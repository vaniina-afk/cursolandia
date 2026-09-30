<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$mensaje_ui = "";
$puede_solicitar = true;

// 1. Verificamos si ya es PRO
if ($_SESSION['plan_pro'] == 1) {
    $mensaje_ui = "<div class='alert alert-info p-3 text-center shadow-sm'>¡Ya sos un usuario PRO! Tenés acceso a todas las funciones sin límite.</div>";
    $puede_solicitar = false;
} else {
    // 2. Verificamos si ya tiene una solicitud "Pendiente"
    $check = $conexion->query("SELECT id_solicitud FROM Solicitudes_Pro WHERE id_usuario = $id_usuario AND estado = 'Pendiente'");
    if ($check->num_rows > 0) {
        $mensaje_ui = "<div class='alert alert-warning p-3 text-center shadow-sm'>Ya tenés una solicitud en revisión. Por favor, esperá a que un administrador la evalúe.</div>";
        $puede_solicitar = false;
    }
}

// 3. Procesamos el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $puede_solicitar) {
    $mensaje_texto = $conexion->real_escape_string($_POST['mensaje']);
    
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] == 0) {
        $extension = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
        $nombre_imagen = "comprobante_" . time() . "_" . uniqid() . "." . $extension;
        $ruta_destino = "uploads/" . $nombre_imagen;
        
        if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $ruta_destino)) {
            $sql = "INSERT INTO Solicitudes_Pro (id_usuario, mensaje, comprobante_url) 
                    VALUES ($id_usuario, '$mensaje_texto', '$nombre_imagen')";
            
            if ($conexion->query($sql)) {
                $mensaje_ui = "<div class='alert alert-success p-3 text-center shadow-sm'>¡Solicitud enviada con éxito! Un administrador la revisará pronto.</div>";
                $puede_solicitar = false;
            } else {
                $mensaje_ui = "<div class='alert alert-danger p-2 small mb-3'>Error: " . $conexion->error . "</div>";
            }
        }
    } else {
        $mensaje_ui = "<div class='alert alert-danger p-2 small mb-3 auto-ocultar'>Por favor, adjuntá un comprobante válido.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitar Plan PRO - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark mb-4" style="background-color: var(--color-primario);">
        <div class="container">
            <span class="navbar-brand fw-bold">Cursolandia</span>
            <a href="panel.php" class="btn btn-outline-light btn-sm">Volver al Panel</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-compacta shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="text-primario fw-bold border-bottom pb-2 mb-3">Solicitar Plan PRO</h3>
                        
                        <?php echo $mensaje_ui; ?>

                        <?php if ($puede_solicitar): ?>
                        <form action="solicitar_pro.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label text-secondary fw-semibold small mb-1">Mensaje para el administrador</label>
                                <textarea name="mensaje" class="form-control form-control-sm" rows="3" placeholder="Ej: Adjunto comprobante de transferencia por $5000" required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label text-secondary fw-semibold small mb-1">Subir Comprobante (Imagen)</label>
                                <input type="file" name="comprobante" class="form-control form-control-sm" accept="image/*" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primario w-100 fw-bold">Enviar Solicitud</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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