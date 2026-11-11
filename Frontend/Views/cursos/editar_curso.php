<?php 
$titulo_pagina = "Editar Curso";
require_once __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../layouts/navbar.php'; 
?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-compacta shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h3 class="text-primario fw-bold mb-0">Editar Curso</h3>
                        <a href="index.php?accion=panel" class="btn btn-outline-secondary btn-sm">Volver al Panel</a>
                    </div>
                    
                    <?php if (!empty($mensaje)) echo $mensaje; ?>

                    <form action="index.php?accion=editar_curso&id=<?php echo $id_curso; ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-semibold small mb-1">Título del Curso</label>
                            <input type="text" name="titulo" class="form-control form-control-sm" value="<?php echo htmlspecialchars($curso['titulo']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-semibold small mb-1">Descripción</label>
                            <textarea name="descripcion" class="form-control form-control-sm" rows="3" required><?php echo htmlspecialchars($curso['descripcion']); ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label text-secondary fw-semibold small mb-1">Costo ($)</label>
                                <input type="number" step="0.01" min="0" name="costo" class="form-control form-control-sm" value="<?php echo $curso['costo']; ?>" placeholder="Vacío si es gratis">
                            </div>
                            <div class="col">
                                <label class="form-label text-secondary fw-semibold small mb-1">Cupo Máximo</label>
                                <input type="number" min="1" name="cupo" class="form-control form-control-sm" value="<?php echo $cupo_mostrar; ?>" placeholder="Vacío para sin límite">
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col">
                                <label class="form-label text-secondary fw-semibold small mb-1">Fecha de Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="<?php echo $curso['fecha_inicio']; ?>" required>
                            </div>
                            <div class="col">
                                <label class="form-label text-secondary fw-semibold small mb-1">Fecha de Fin</label>
                                <input type="date" name="fecha_fin" class="form-control form-control-sm" value="<?php echo $curso['fecha_fin']; ?>" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primario w-100 fw-bold">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>