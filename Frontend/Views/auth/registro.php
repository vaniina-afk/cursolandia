<?php 
$titulo_pagina = "Registro";
require_once __DIR__ . '/../layouts/header.php'; 
/** @var mysqli_result|false $intereses_disponibles */
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

                        <div class="mb-2">
                            <label class="form-label text-secondary fw-semibold small mb-1">
                                Intereses <span class="text-muted fw-normal">(Opcional · para recomendarte cursos)</span>
                            </label>
                            <?php if (isset($intereses_disponibles) && $intereses_disponibles instanceof mysqli_result && $intereses_disponibles->num_rows > 0): ?>
                                <div class="d-flex flex-wrap gap-2 mb-2 p-2 border rounded bg-light" style="max-height: 110px; overflow-y: auto;">
                                    <?php while ($int = $intereses_disponibles->fetch_assoc()): ?>
                                        <div class="form-check m-0">
                                            <input class="form-check-input" type="checkbox" name="intereses[]" value="<?php echo $int['id_interes']; ?>" id="reg_int_<?php echo $int['id_interes']; ?>">
                                            <label class="form-check-label small" for="reg_int_<?php echo $int['id_interes']; ?>">
                                                <?php echo htmlspecialchars($int['nombre']); ?>
                                            </label>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php endif; ?>
                            <input type="text" name="nuevos_intereses" class="form-control form-control-sm" placeholder="Otros intereses separados por coma (ej: PHP, Diseño)">
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