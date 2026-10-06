<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
require_once 'includes/logica_notificaciones.php';
$mensaje = "";

$query_usuario = $conexion->query("SELECT plan_pro FROM Usuarios WHERE id_usuario = $id_usuario");
$es_pro = $query_usuario->fetch_assoc()['plan_pro'];

$query_cursos = $conexion->query("SELECT COUNT(*) as total FROM Cursos WHERE id_creador = $id_usuario AND activo = 1");
$total_cursos = $query_cursos->fetch_assoc()['total'];

$puede_crear = true;

if ($total_cursos >= 3 && $es_pro == 0) {
    $puede_crear = false;
    // Le quitamos el auto-ocultar para que el mensaje quede fijo
    $mensaje = "<div class='alert alert-warning p-3 mb-3 text-center shadow-sm'><strong>¡Límite alcanzado!</strong> Ya tenés 3 cursos publicados. Para publicar más, necesitás pasarte al plan PRO.</div>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $puede_crear) {
    $titulo = $conexion->real_escape_string($_POST['titulo']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);
    
    $costo = !empty($_POST['costo']) ? $_POST['costo'] : 'NULL';
    $cupo = !empty($_POST['cupo']) ? $_POST['cupo'] : 'NULL';
    
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    if ($fecha_fin < $fecha_inicio) {
         // Los errores tampoco desaparecen
         $mensaje = "<div class='alert alert-danger p-2 small mb-3'>La fecha de fin no puede ser anterior a la de inicio.</div>";
    } else {
        $sql = "INSERT INTO Cursos (id_creador, titulo, descripcion, costo, cupo, fecha_inicio, fecha_fin) 
                VALUES ($id_usuario, '$titulo', '$descripcion', $costo, $cupo, '$fecha_inicio', '$fecha_fin')";
        
        if ($conexion->query($sql)) {
            $total_cursos++;
            
            if ($total_cursos >= 3 && $es_pro == 0) {
                $puede_crear = false;
                // El de éxito desaparece, el de advertencia se queda
                $mensaje = "<div class='alert alert-success p-2 small mb-2 auto-ocultar'>¡Curso creado con éxito!</div>
                            <div class='alert alert-warning p-3 text-center shadow-sm'><strong>Has alcanzado el límite de 3 cursos.</strong> Pasate a PRO para seguir creando.</div>";
            } else {
                // Solo auto-ocultar a este mensaje de éxito
                $mensaje = "<div class='alert alert-success p-2 small mb-3 auto-ocultar'>¡Curso creado con éxito! Te quedan " . (3 - $total_cursos) . " cupos gratuitos.</div>";
            }
        } else {
            // El error de BD se queda fijo
            $mensaje = "<div class='alert alert-danger p-2 small mb-3'>Error: " . $conexion->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Curso - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background-color: var(--color-primario);">
        <div class="container d-flex justify-content-between">
            <a class="navbar-brand fw-bold text-white text-decoration-none" href="index.php">Cursolandia</a>
            
            <div class="d-flex align-items-center gap-3">
                
                <!-- DROPDOWN DE NOTIFICACIONES -->
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
                                            <small class="text-primario fw-bold" style="font-size: 0.7rem;">
                                                ⏱️ <?php echo tiempo_transcurrido($noti['fecha']); ?>
                                            </small>
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
                <!-- FIN NOTIFICACIONES -->

                <!-- Saludo y Botones -->
                <div class="d-flex align-items-center">
                    <span class="navbar-text text-white me-3 d-none d-md-inline fw-semibold">
                        Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    </span>
                    <a href="panel.php" class="btn btn-outline-light btn-sm me-2">Volver al Panel</a>
                    <a href="logout.php" class="btn btn-acento btn-sm">Salir</a>
                </div>

            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-compacta shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="text-primario fw-bold border-bottom pb-2 mb-3">Crear Nuevo Curso</h3>
                        
                        <?php echo $mensaje; ?>

                        <?php if ($puede_crear): ?>
                        <form action="crear_curso.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label text-secondary fw-semibold small mb-1">Título del Curso</label>
                                <input type="text" name="titulo" class="form-control form-control-sm" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-secondary fw-semibold small mb-1">Descripción</label>
                                <textarea name="descripcion" class="form-control form-control-sm" rows="3" required></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Costo ($)</label>
                                    <input type="number" step="0.01" min="0" name="costo" class="form-control form-control-sm" placeholder="Vacío si es gratis">
                                </div>
                                <div class="col">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Cupo Máximo</label>
                                    <input type="number" min="1" name="cupo" class="form-control form-control-sm" placeholder="Vacío para sin límite">
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Fecha de Inicio</label>
                                    <input type="date" name="fecha_inicio" class="form-control form-control-sm" required>
                                </div>
                                <div class="col">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Fecha de Fin</label>
                                    <input type="date" name="fecha_fin" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primario w-100 fw-bold">Publicar Curso</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para ocultar SOLO los mensajes con la clase 'auto-ocultar' -->
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