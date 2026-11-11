<?php 
$titulo_pagina = "Editar Perfil";
require_once __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../layouts/navbar.php'; 
?>

<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primario mb-0">Configuración de la Cuenta</h4>
        <a href="index.php?accion=panel" class="btn btn-outline-secondary btn-sm fw-semibold">← Volver al Panel</a>
    </div>
    
    <div class="row g-4">
        <!-- COLUMNA 1: DATOS PERSONALES -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4 text-secondary">Perfil Público y Datos</h6>
                    <?php if (!empty($mensaje_perfil)) echo $mensaje_perfil; ?>
                    
                    <form action="index.php?accion=editar_perfil" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="actualizar_datos" value="1">
                        
                        <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                            <?php if (!empty($datos_usuario['imagen_perfil'])): ?>
                                <img src="Resources/uploads/<?php echo htmlspecialchars($datos_usuario['imagen_perfil']); ?>" class="rounded-circle me-3 shadow-sm border" style="width: 70px; height: 70px; object-fit: cover;">
                            <?php else: ?>
                                <div class="text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 70px; height: 70px; background-color: var(--color-primario); font-weight: bold; font-size: 1.5rem;">
                                    <?php echo strtoupper(substr($datos_usuario['nombre'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <label class="form-label small fw-bold mb-1">Cambiar Foto de Perfil</label>
                                <input type="file" name="imagen_perfil" class="form-control form-control-sm" accept="image/*">
                                <div class="form-text" style="font-size: 0.75rem;">Formatos recomendados: JPG, PNG. Max: 2MB.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nombre</label>
                                <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($datos_usuario['nombre']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Apellido</label>
                                <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($datos_usuario['apellido']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($datos_usuario['email']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Antecedentes / Experiencia <span class="text-muted fw-normal">(Opcional)</span></label>
                            <textarea name="antecedentes" class="form-control bg-light" rows="4" placeholder="Contale a tus alumnos sobre tu trayectoria, estudios o experiencia laboral..."><?php echo htmlspecialchars($datos_usuario['antecedentes'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primario w-100 fw-bold">Guardar Cambios de Perfil</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- COLUMNA 2: SEGURIDAD (CONTRASEÑA) -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4 text-secondary">Seguridad de la Cuenta</h6>
                    <?php if (!empty($mensaje_pass)) echo $mensaje_pass; ?>
                    
                    <form action="index.php?accion=editar_perfil" method="POST">
                        <input type="hidden" name="actualizar_password" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Contraseña Actual</label>
                            <input type="password" name="pass_actual" class="form-control bg-light" required placeholder="Ingresá tu contraseña actual">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nueva Contraseña</label>
                            <input type="password" name="pass_nueva" class="form-control bg-light" required placeholder="Mínimo 6 caracteres">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Confirmar Nueva Contraseña</label>
                            <input type="password" name="pass_confirmar" class="form-control bg-light" required placeholder="Repetí la nueva contraseña">
                        </div>
                        <button type="submit" class="btn btn-outline-danger w-100 fw-bold mt-2">Actualizar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>