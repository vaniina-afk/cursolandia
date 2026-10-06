<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];

// Llamamos a la lógica central de la campanita
require_once 'includes/logica_notificaciones.php';

// Traemos todas las notificaciones del usuario
$sql = "SELECT * FROM notificaciones WHERE id_usuario = $id_usuario ORDER BY fecha DESC";
$notificaciones = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Notificaciones - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark mb-4" style="background-color: var(--color-primario);">
        <div class="container d-flex justify-content-between">
            <span class="navbar-brand fw-bold">Cursolandia</span>
            
            <div class="d-flex align-items-center gap-3">
                <!-- DROPDOWN DE NOTIFICACIONES -->
                <div class="dropdown me-2">
                    <a href="#" class="text-white text-decoration-none position-relative" id="campanita" data-bs-toggle="dropdown" aria-expanded="false">
                        <span style="font-size: 1.2rem;">🔔</span>
                        <?php if ($no_leidas > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                <?php echo $no_leidas; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="campanita" style="width: 320px; max-height: 400px; overflow-y: auto; z-index: 1050;">
                        <li><h6 class="dropdown-header fw-bold text-dark border-bottom pb-2">Notificaciones</h6></li>
                        
                        <?php if ($result_noti && $result_noti->num_rows > 0): ?>
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

                <a href="panel.php" class="btn btn-outline-light btn-sm">Volver al Panel</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="text-primario fw-bold border-bottom pb-3 mb-4">🔔 Todas mis notificaciones</h4>
                        
                        <?php if ($notificaciones->num_rows > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while($noti = $notificaciones->fetch_assoc()): ?>
                                    <a href="leer_notificacion.php?id=<?php echo $noti['id_notificacion']; ?>" 
                                       class="list-group-item list-group-item-action py-3 px-3 border-bottom <?php echo ($noti['leida'] == 0) ? 'bg-light border-start border-primario border-4' : ''; ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="<?php echo ($noti['leida'] == 0) ? 'fw-bold text-dark' : 'text-secondary'; ?>">
                                                <?php echo htmlspecialchars($noti['mensaje']); ?>
                                            </span>
                                            <small class="text-muted text-nowrap ms-3" style="font-size: 0.8rem;">
                                                <?php echo tiempo_transcurrido($noti['fecha']); ?>
                                            </small>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-center border">
                                No tenés notificaciones en tu historial.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>