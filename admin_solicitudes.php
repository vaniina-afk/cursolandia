<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['es_admin'] == 0) {
    header("Location: panel.php");
    exit();
}

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
    <nav class="navbar navbar-dark mb-4" style="background-color: var(--color-primario);">
        <div class="container">
            <span class="navbar-brand fw-bold">Panel de Administración</span>
            <a href="panel.php" class="btn btn-outline-light btn-sm">Volver al Panel</a>
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