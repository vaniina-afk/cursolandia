<?php 
$titulo_pagina = "Mis Notificaciones";
require_once __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../layouts/navbar.php'; 
/** @var mysqli_result|false $notificaciones */
?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <h4 class="text-primario fw-bold mb-0">🔔 Todas mis notificaciones</h4>
                        <a href="index.php?accion=panel" class="btn btn-outline-secondary btn-sm">Volver al Panel</a>
                    </div>
                    
                    <?php if ($notificaciones && $notificaciones->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($noti = $notificaciones->fetch_assoc()): ?>
                                <a href="index.php?accion=leer_notificacion&id=<?php echo $noti['id_notificacion']; ?>" 
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
                        <div class="alert alert-light text-center border mb-0">
                            No tenés notificaciones en tu historial.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>