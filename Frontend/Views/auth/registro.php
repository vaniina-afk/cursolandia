<?php 
$titulo_pagina = "Registro";
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="container mt-4 mb-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-compacta">
                <div class="card-body">
                    <h3 class="text-center text-primario mb-3 fw-bold">Crear Cuenta</h3>
                    
                    <?php if (!empty($mensaje)) echo $mensaje; ?>
                    
                    <form action="index.php?accion=registro" method="POST" enctype="multipart/form-data">
                        <div class="row mb-2">
                            <div class="col">
                                <label class="form-label text-secondary fw-semibold small mb-1">Nombre</label>
                                <input type="text" name="nombre" class="form-control form-control-sm" required>
                            </div>
                            <div class="col">
                                <label class="form-label text-secondary fw-semibold small mb-1">Apellido</label>
                                <input type="text" name="apellido" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label text-secondary fw-semibold small mb-1">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control form-control-sm" required>
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label text-secondary fw-semibold small mb-1">Contraseña</label>
                            <input type="password" name="contrasena" class="form-control form-control-sm" required>
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label text-secondary fw-semibold small mb-1">Antecedentes</label>
                            <textarea name="antecedentes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-semibold small mb-1">Imagen de Perfil</label>
                            <input type="file" name="imagen" class="form-control form-control-sm" accept="image/*">
                        </div>
                        
                        <button type="submit" class="btn btn-acento w-100 py-1 fw-bold">Registrarme</button>
                        
                        <div class="text-center mt-2">
                            <a href="index.php" class="text-decoration-none small text-primario">Volver al inicio</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>