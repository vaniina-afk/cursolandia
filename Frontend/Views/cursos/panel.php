<?php 
$titulo_pagina = "Mi Panel";
require_once __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../layouts/navbar.php'; 
/** @var mysqli_result|false $cursos_inscritos */
/** @var mysqli_result|false $cursos_creados */
?>

<div class="container mb-5">

    <?php if (!empty($mensaje_panel)) echo $mensaje_panel; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-compacta shadow-sm border-0">
                <div class="card-body p-3 d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                    <span class="fw-bold text-primario align-self-center me-3">Accesos rápidos:</span>
                    <a href="index.php?accion=crear_curso" class="btn btn-primario btn-sm fw-bold">➕ Crear Nuevo Curso</a>
                    <a href="index.php?accion=solicitar_pro" class="btn btn-outline-secondary btn-sm fw-bold">⭐ Solicitar PRO</a>
                    <?php if ($es_admin == 1): ?>
                        <a href="index.php?accion=admin_solicitudes" class="btn btn-warning btn-sm fw-bold text-dark">⚙️ Panel Admin</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda: Mis Inscripciones -->
        <div class="col-md-6">
            <h4 class="text-primario fw-bold mb-3">Mis Inscripciones</h4>
            <?php if (isset($cursos_inscritos) && $cursos_inscritos && $cursos_inscritos->num_rows > 0): ?>
                <div class="list-group shadow-sm">
                    <?php while ($curso = $cursos_inscritos->fetch_assoc()): ?>
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 border-0 border-bottom <?php echo $curso['activo'] == 0 ? 'bg-light opacity-75' : ''; ?>">
                            <div>
                                <h6 class="mb-1 fw-bold <?php echo $curso['activo'] == 0 ? 'text-muted text-decoration-line-through' : 'text-dark'; ?>">
                                    <?php echo htmlspecialchars($curso['titulo']); ?>
                                </h6>
                                <small class="text-muted">Prof: <?php echo htmlspecialchars($curso['nombre'] . ' ' . $curso['apellido']); ?></small>

                                <?php if ($curso['activo'] == 0): ?>
                                    <br><small class="text-danger fw-semibold">El creador cerró este curso.</small>
                                <?php endif; ?>
                            </div>

                            <?php if ($curso['activo'] == 1): ?>
                                <?php if (($curso['estado'] ?? 'Aceptado') === 'Pendiente'): ?>
                                    <div class="d-flex flex-column gap-1 text-end">
                                        <span class="badge bg-warning text-dark">⏳ Pendiente de aprobación</span>
                                        <a href="index.php?accion=dejar_curso&id=<?php echo $curso['id_curso']; ?>" class="btn btn-sm btn-outline-danger fw-bold" style="font-size: 0.75rem;" onclick="return confirm('¿Seguro que querés cancelar tu solicitud?');">Cancelar solicitud</a>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex flex-column gap-1 text-end">
                                        <a href="index.php?accion=curso_interno&id=<?php echo $curso['id_curso']; ?>" class="btn btn-sm btn-acento fw-bold">Entrar</a>
                                        <a href="index.php?accion=dejar_curso&id=<?php echo $curso['id_curso']; ?>" class="btn btn-sm btn-outline-danger fw-bold" style="font-size: 0.75rem;" onclick="return confirm('¿Seguro que querés abandonar este curso?');">Dejar curso</a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-1 text-end">
                                    <button class="btn btn-sm btn-secondary fw-bold" disabled>Inactivo</button>
                                    <a href="index.php?accion=dejar_curso&id=<?php echo $curso['id_curso']; ?>" class="btn btn-sm btn-outline-danger fw-bold" style="font-size: 0.75rem;" onclick="return confirm('¿Seguro que querés eliminar este curso de tu lista?');">Quitar de lista</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center shadow-sm p-3 small">
                    Todavía no te anotaste a ningún curso. <br>
                    <a href="index.php" class="fw-bold text-primario text-decoration-none">¡Explorar catálogo!</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Columna Derecha: Mis Cursos Creados -->
        <div class="col-md-6">
            <h4 class="text-primario fw-bold mb-3">Mis Cursos Creados</h4>
            <?php if (isset($cursos_creados) && $cursos_creados && $cursos_creados->num_rows > 0): ?>
                <div class="list-group shadow-sm">
                    <?php while ($creado = $cursos_creados->fetch_assoc()): ?>
                        <div class="list-group-item p-3 border-0 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-bold text-dark">
                                    <?php echo htmlspecialchars($creado['titulo']); ?>
                                    <?php if ((int)($creado['es_privado'] ?? 0) === 1): ?>
                                        <span class="badge bg-dark ms-1" style="font-size: 0.65rem;">🔒 Privado</span>
                                    <?php endif; ?>
                                </h6>
                                <div class="d-flex gap-1">
                                    <?php if ((int)($creado['solicitudes_pendientes'] ?? 0) > 0): ?>
                                        <span class="badge bg-warning text-dark">📩 <?php echo $creado['solicitudes_pendientes']; ?> pendiente/s</span>
                                    <?php endif; ?>
                                    <span class="badge bg-secondary">
                                        Cupo: <?php echo $creado['cupo'] !== null ? $creado['cupo'] : 'Ilimitado'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Inicio: <?php echo date("d/m/Y", strtotime($creado['fecha_inicio'])); ?></small>
                                <div>
                                    <a href="index.php?accion=curso_interno&id=<?php echo $creado['id_curso']; ?>" class="btn btn-sm btn-outline-primary fw-bold" style="font-size: 0.75rem;">Ver Aula</a>
                                    <a href="index.php?accion=editar_curso&id=<?php echo $creado['id_curso']; ?>" class="btn btn-sm btn-outline-secondary fw-bold" style="font-size: 0.75rem;">Editar</a>
                                    <a href="index.php?accion=borrar_curso&id=<?php echo $creado['id_curso']; ?>" class="btn btn-sm btn-outline-danger fw-bold" style="font-size: 0.75rem;" onclick="return confirm('¿Estás seguro de que querés borrar este curso?');">Borrar</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-secondary text-center shadow-sm p-3 small">
                    Aún no sos profesor en nuestra plataforma. <br>
                    <a href="index.php?accion=crear_curso" class="fw-bold text-primario text-decoration-none">Crear mi primer curso</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>