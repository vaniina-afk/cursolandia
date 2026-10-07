<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['es_admin'] == 0) {
    header("Location: panel.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
require_once 'includes/logica_notificaciones.php';
$mensaje_ui = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_solicitud = (int)$_POST['id_solicitud'];
    $id_user_solicitante = (int)$_POST['id_usuario_solicitante'];
    $accion = $_POST['accion']; 

    if ($accion == 'aprobar') {
        $conexion->query("UPDATE Solicitudes_Pro SET estado = 'Aprobada' WHERE id_solicitud = $id_solicitud");
        $conexion->query("UPDATE Usuarios SET plan_pro = 1 WHERE id_usuario = $id_user_solicitante");
        $mensaje_ui = "<div class='alert alert-success p-2 small mb-3 auto-ocultar'>Solicitud aprobada. El usuario ahora es PRO.</div>";
    } elseif ($accion == 'rechazar') {
        $conexion->query("UPDATE Solicitudes_Pro SET estado = 'Rechazada' WHERE id_solicitud = $id_solicitud");
        $mensaje_ui = "<div class='alert alert-secondary p-2 small mb-3 auto-ocultar'>Solicitud rechazada.</div>";
    }
}

$sql = "SELECT s.*, u.nombre, u.apellido, u.email 
        FROM Solicitudes_Pro s 
        JOIN Usuarios u ON s.id_usuario = u.id_usuario 
        WHERE s.estado = 'Pendiente' 
        ORDER BY s.fecha_solicitud ASC";
$solicitudes = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Solicitudes - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background-color: var(--color-primario);">
        <div class="container d-flex justify-content-between">
            <span class="navbar-brand fw-bold text-white">Panel de Administración</span>
            
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
                    
                    <!-- MENÚ DESPLEGABLE DEL USUARIO -->
                    <div class="dropdown me-3 d-none d-md-inline">
                        <a href="#" class="text-white text-decoration-none dropdown-toggle fw-semibold" id="perfilDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="perfilDropdown">
                            <li>
                                <a class="dropdown-item py-2 fw-semibold text-secondary" href="editar_perfil.php">
                                    ⚙️ Mi Perfil
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Botones de acción -->
                    <a href="panel.php" class="btn btn-outline-light btn-sm me-2">Volver al Panel</a>
                    <a href="logout.php" class="btn btn-acento btn-sm">Salir</a>
                </div>

            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <h3 class="text-primario fw-bold mb-4">Solicitudes PRO Pendientes</h3>
        
        <?php echo $mensaje_ui; ?>

        <?php if ($solicitudes->num_rows > 0): ?>
            <div class="card card-compacta shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Mensaje</th>
                                <th>Comprobante</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $solicitudes->fetch_assoc()): ?>
                            <tr>
                                <td class="align-middle"><?php echo htmlspecialchars($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($fila['email']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($fila['mensaje']); ?></td>
                                <td class="align-middle">
                                    <!-- Botón que activa el modal -->
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalImagen<?php echo $fila['id_solicitud']; ?>">
                                        Ver imagen
                                    </button>

                                    <!-- Estructura de la ventana emergente (Modal) -->
                                    <div class="modal fade" id="modalImagen<?php echo $fila['id_solicitud']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Comprobante de <?php echo htmlspecialchars($fila['nombre']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img src="uploads/<?php echo $fila['comprobante_url']; ?>" class="img-fluid rounded" alt="Comprobante">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <form action="admin_solicitudes.php" method="POST" class="d-inline">
                                        <input type="hidden" name="id_solicitud" value="<?php echo $fila['id_solicitud']; ?>">
                                        <input type="hidden" name="id_usuario_solicitante" value="<?php echo $fila['id_usuario']; ?>">
                                        
                                        <button type="submit" name="accion" value="aprobar" class="btn btn-sm btn-success fw-bold">Aprobar</button>
                                        <button type="submit" name="accion" value="rechazar" class="btn btn-sm btn-danger fw-bold">Rechazar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center p-3 shadow-sm">No hay solicitudes pendientes en este momento.</div>
        <?php endif; ?>
    </div>

    <!-- Script de Bootstrap necesario para que funcionen los modales -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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