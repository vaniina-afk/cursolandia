<?php 
$titulo_pagina = "Iniciar Sesión";
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="container mt-5 mb-4">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-compacta">
                <div class="card-body">
                    <h3 class="text-center text-primario mb-3 fw-bold">Iniciar Sesión</h3>
                    
                    <?php if (!empty($mensaje)) echo $mensaje; ?>
                    
                    <form action="index.php?accion=login" method="POST">
                        <div class="mb-2">
                            <label class="form-label text-secondary fw-semibold small mb-1">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control form-control-sm" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-semibold small mb-1">Contraseña</label>
                            <input type="password" name="contrasena" class="form-control form-control-sm" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primario w-100 py-1 fw-bold">Ingresar</button>
                        
                        <div class="text-center mt-3">
                            <a href="index.php" class="text-decoration-none small text-secondary hover-primario">Volver al inicio</a>
                        </div>
                        <div class="text-center mt-1">
                            <span class="small text-muted">¿No tenés cuenta? <a href="index.php?accion=registro" class="text-primario fw-semibold text-decoration-none">Registrate acá</a></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>