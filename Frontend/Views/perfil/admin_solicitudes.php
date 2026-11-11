<?php 
$titulo_pagina = "Administrar Solicitudes";
require_once __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../layouts/navbar.php'; 
/** @var mysqli_result|false $solicitudes */
?>

<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primario fw-bold mb-0">Solicitudes PRO Pendientes</h3>
        <a href="index.php?accion=panel" class="btn btn-outline-secondary btn-sm fw-semibold">← Volver al Panel</a>
    </div>
    
    <?php if (!empty($mensaje_ui)) echo $mensaje_ui; ?>

    <?php if (isset($solicitudes) && $solicitudes && $solicitudes->num_rows > 0): ?>
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
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalImagen<?php echo $fila['id_solicitud']; ?>">
                                    Ver imagen
                                </button>

                                <div class="modal fade" id="modalImagen<?php echo $fila['id_solicitud']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Comprobante de <?php echo htmlspecialchars($fila['nombre']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="Resources/uploads/<?php echo htmlspecialchars($fila['comprobante_url']); ?>" class="img-fluid rounded" alt="Comprobante">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                <form action="index.php?accion=admin_solicitudes" method="POST" class="d-inline">
                                    <input type="hidden" name="id_solicitud" value="<?php echo $fila['id_solicitud']; ?>">
                                    <input type="hidden" name="id_usuario_solicitante" value="<?php echo $fila['id_usuario']; ?>">
                                    
                                    <button type="submit" name="decision" value="aprobar" class="btn btn-sm btn-success fw-bold">Aprobar</button>
                                    <button type="submit" name="decision" value="rechazar" class="btn btn-sm btn-danger fw-bold">Rechazar</button>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>