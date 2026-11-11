<?php 
$titulo_pagina = "Solicitar Plan PRO";
require_once __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../layouts/navbar.php'; 
?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-compacta shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h3 class="text-primario fw-bold mb-0">Solicitar Plan PRO</h3>
                        <a href="index.php?accion=panel" class="btn btn-outline-secondary btn-sm">Volver</a>
                    </div>
                    
                    <?php if (!empty($mensaje_ui)) echo $mensaje_ui; ?>

                    <?php if ($puede_solicitar): ?>
                    <form action="index.php?accion=solicitar_pro" method="POST" enctype="multipart/form-data">
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>