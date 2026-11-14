<?php 
$titulo_pagina = $curso['titulo'];
require_once __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../layouts/navbar.php'; 
/** @var mysqli_result|false $materiales */
/** @var mysqli_result|false $preguntas_foro */
/** @var mysqli_result|false $alumnos */
/** @var mysqli_result|false $solicitudes_pendientes */
$cant_archivos = $resumen_materiales['cantidad'] ?? 0;
$mb_usados = $resumen_materiales['total_mb'] ?? 0;
$es_creador_curso = ($curso['id_creador'] == $id_usuario);
$creador_pro = !empty($creador_es_pro);
$cupo_material_lleno = ($es_creador_curso && !$creador_pro && ($cant_archivos >= 5 || $mb_usados >= 10));
?>

<div class="container mb-5">
    
    <?php if (isset($_GET['exito'])): ?>
        <div class="alert alert-success text-center shadow-sm auto-ocultar mb-4">¡Archivo subido correctamente!</div>
    <?php elseif (isset($_GET['borrado'])): ?>
        <div class="alert alert-success text-center shadow-sm auto-ocultar mb-4">¡Material eliminado correctamente!</div>
    <?php elseif (isset($_GET['inscripcion']) && $_GET['inscripcion'] == 'aceptada'): ?>
        <div class="alert alert-success text-center shadow-sm auto-ocultar mb-4">¡Alumno aceptado en el curso!</div>
    <?php elseif (isset($_GET['inscripcion']) && $_GET['inscripcion'] == 'rechazada'): ?>
        <div class="alert alert-secondary text-center shadow-sm auto-ocultar mb-4">Solicitud de inscripción rechazada.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'post_max'): ?>
        <div class="alert alert-danger text-center shadow-sm auto-ocultar mb-4">El archivo supera el tamaño máximo permitido por el servidor.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'tamanio'): ?>
        <div class="alert alert-danger text-center shadow-sm mb-4">El archivo supera el límite individual de 5 MB permitido para cuentas gratuitas.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'limite_archivos'): ?>
        <div class="alert alert-warning text-center shadow-sm mb-4">Alcanzaste el límite de 5 archivos para cuentas gratuitas. <a href="index.php?accion=solicitar_pro" class="fw-bold text-primario">Pasate a PRO</a> para subir sin límites.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'limite_total'): ?>
        <div class="alert alert-warning text-center shadow-sm mb-4">Este archivo supera el espacio total de 10 MB permitido por curso en el plan gratuito. <a href="index.php?accion=solicitar_pro" class="fw-bold text-primario">Pasate a PRO</a> para ampliar tu almacenamiento.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'cupo_lleno'): ?>
        <div class="alert alert-danger text-center shadow-sm mb-4">No podés aceptar más alumnos porque el cupo del curso ya está completo.</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center shadow-sm mb-4">Hubo un error al procesar tu solicitud.</div>
    <?php elseif (isset($_GET['foro']) && $_GET['foro'] == 'pregunta_ok'): ?>
        <div class="alert alert-success text-center shadow-sm auto-ocultar mb-4">¡Consulta publicada en el foro!</div>
    <?php elseif (isset($_GET['mensaje']) && $_GET['mensaje'] == 'borrado'): ?>
        <div class="alert alert-success text-center shadow-sm auto-ocultar mb-4">¡Consulta eliminada del foro!</div>
    <?php endif; ?>

    <!-- Cabecera del Curso -->
    <div class="card card-compacta shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <a href="index.php?accion=panel" class="btn btn-outline-secondary btn-sm fw-semibold">← Volver al Panel</a>
                <span class="badge bg-acento fs-6">
                    📅 <?php echo date("d/m/Y", strtotime($curso['fecha_inicio'])); ?> al <?php echo date("d/m/Y", strtotime($curso['fecha_fin'])); ?>
                </span>
            </div>
            <h2 class="text-primario fw-bold mb-1"><?php echo htmlspecialchars($curso['titulo']); ?></h2>
            <h6 class="text-muted fw-semibold mb-3">👨‍🏫 Profesor: <?php echo htmlspecialchars($curso['prof_nombre'] . ' ' . $curso['prof_apellido']); ?></h6>
            <p class="text-secondary mb-0"><?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?></p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda: Material de Estudio -->
        <div class="col-md-7">
            <div class="card card-compacta shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 border-bottom pb-2 gap-2">
                        <div>
                            <h4 class="text-primario fw-bold mb-0">📚 Material de Estudio</h4>
                            <?php if ($es_creador_curso): ?>
                                <?php if ($creador_pro): ?>
                                    <small class="text-success fw-semibold">⭐ Plan PRO: Almacenamiento y archivos ilimitados (<?php echo $cant_archivos; ?> subidos · <?php echo number_format($mb_usados, 2); ?> MB)</small>
                                <?php else: ?>
                                    <small class="text-muted">Cuota gratuita: <strong><?php echo $cant_archivos; ?>/5</strong> archivos · <strong><?php echo number_format($mb_usados, 2); ?>/10</strong> MB</small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($es_creador_curso): ?>
                            <?php if ($cupo_material_lleno): ?>
                                <a href="index.php?accion=solicitar_pro" class="btn btn-sm btn-outline-warning text-dark fw-bold">⭐ Límite alcanzado (Pasar a PRO)</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-primario fw-bold" data-bs-toggle="modal" data-bs-target="#modalSubirMaterial">
                                    Subir Archivo
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($materiales && $materiales->num_rows > 0): ?>
                        <div class="list-group list-group-flush mt-2" style="max-height: 350px; overflow-y: auto;">
                            <?php while ($mat = $materiales->fetch_assoc()): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                                    <div class="pe-2">
                                        <h6 class="mb-0 fw-bold text-dark">📄 <?php echo htmlspecialchars($mat['nombre_archivo']); ?></h6>
                                        <small class="text-muted">Peso: <?php echo htmlspecialchars($mat['tamanio_mb']); ?> MB</small>
                                    </div>
                                    <div class="text-nowrap">
                                        <a href="uploads/<?php echo htmlspecialchars($mat['ruta_archivo']); ?>" download class="btn btn-sm btn-outline-success fw-bold">Descargar</a>
                                        
                                        <?php if ($es_creador_curso): ?>
                                            <a href="index.php?accion=borrar_material&id_mat=<?php echo $mat['id_material']; ?>&id_curso=<?php echo $id_curso; ?>" 
                                               class="btn btn-sm btn-outline-danger fw-bold ms-1" 
                                               onclick="return confirm('¿Estás seguro de que querés borrar este material permanentemente?');">
                                               Borrar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light text-center border mt-4">
                            <span class="text-muted small">Aún no hay materiales subidos para este curso.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Foro de Consultas -->
        <div class="col-md-5">
            <div class="card card-compacta shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h4 class="text-primario fw-bold mb-0">💬 Foro de Consultas</h4>
                        <button class="btn btn-sm btn-outline-secondary fw-bold" data-bs-toggle="modal" data-bs-target="#modalPregunta">Nueva Pregunta</button>
                    </div>

                    <div class="foro-contenedor mt-3" style="max-height: 350px; overflow-y: auto;">
                        <?php if ($preguntas_foro && $preguntas_foro->num_rows > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while ($pregunta = $preguntas_foro->fetch_assoc()): ?>
                                    <a href="index.php?accion=pregunta_foro&id=<?php echo $pregunta['id_pregunta']; ?>" class="list-group-item list-group-item-action py-3 px-2 border-bottom">
                                        <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bold text-dark text-truncate pe-2"><?php echo htmlspecialchars($pregunta['titulo']); ?></h6>
                                            <small class="text-muted text-nowrap" style="font-size: 0.75rem;">
                                                <?php echo date('d/m H:i', strtotime($pregunta['fecha_creacion'])); ?>
                                            </small>
                                        </div>
                                        <p class="mb-2 small text-secondary text-truncate">
                                            <?php echo htmlspecialchars($pregunta['cuerpo']); ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-primario fw-semibold">
                                                👤 <?php echo htmlspecialchars($pregunta['nombre'] . ' ' . $pregunta['apellido']); ?>
                                            </small>
                                            <?php if (isset($pregunta['cerrada']) && $pregunta['cerrada'] == 1): ?>
                                                <span class="badge bg-secondary rounded-pill" style="font-size: 0.65rem;">🔒 Cerrado</span>
                                            <?php elseif ($pregunta['estado'] == 'Resuelta'): ?>
                                                <span class="badge bg-success rounded-pill" style="font-size: 0.65rem;">Resuelta</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.65rem;">Abierta</span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-center border">
                                <span class="text-muted small">No hay consultas activas. ¡Sé el primero en preguntar!</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Solicitudes Pendientes (Solo visible para el creador) -->
    <?php if ($es_creador_curso && isset($solicitudes_pendientes) && $solicitudes_pendientes && $solicitudes_pendientes->num_rows > 0): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-compacta shadow-sm border-warning border-2">
                <div class="card-body p-4">
                    <h4 class="text-primario fw-bold mb-3">📩 Solicitudes de Inscripción Pendientes</h4>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php while ($sol = $solicitudes_pendientes->fetch_assoc()): ?>
                                    <?php 
                                        $foto_sol = $sol['imagen_perfil'] ?? '';
                                        $ruta_foto_sol = "Resources/uploads/" . $foto_sol;
                                    ?>
                                    <tr>
                                        <td class="border-0 border-bottom py-3">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($foto_sol) && $foto_sol !== 'default.png' && file_exists($ruta_foto_sol)): ?>
                                                        <img src="<?php echo htmlspecialchars($ruta_foto_sol); ?>" class="rounded-circle me-3 shadow-sm" style="width: 45px; height: 45px; object-fit: cover;" alt="Foto">
                                                    <?php else: ?>
                                                        <div class="text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 45px; height: 45px; font-size: 1.2rem; font-weight: bold; background-color: var(--color-primario);">
                                                            <?php echo strtoupper(substr($sol['nombre'], 0, 1)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($sol['nombre'] . ' ' . $sol['apellido']); ?></span>
                                                        <small class="text-muted">✉️ <?php echo htmlspecialchars($sol['email']); ?> · Solicitado el <?php echo date('d/m/Y H:i', strtotime($sol['fecha_inscripcion'])); ?></small>
                                                    </div>
                                                </div>

                                                <form action="index.php?accion=gestionar_inscripcion" method="POST" class="d-flex gap-2">
                                                    <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                                                    <input type="hidden" name="id_inscripcion" value="<?php echo $sol['id_inscripcion']; ?>">
                                                    <button type="submit" name="decision" value="aceptar" class="btn btn-sm btn-success fw-bold">✅ Aceptar</button>
                                                    <button type="submit" name="decision" value="rechazar" class="btn btn-sm btn-outline-danger fw-bold" onclick="return confirm('¿Rechazar esta solicitud de inscripción?');">❌ Rechazar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sección Alumnos Inscriptos (Solo visible para el creador) -->
    <?php if ($es_creador_curso): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-compacta shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="text-primario fw-bold mb-3">👥 Alumnos Inscriptos</h4>
                    
                    <?php if ($alumnos && $alumnos->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <tbody>
                                    <?php while ($alumno = $alumnos->fetch_assoc()): ?>
                                        <?php 
                                            $foto = $alumno['imagen_perfil'] ?? '';
                                            $ruta_foto = "Resources/uploads/" . $foto;
                                        ?>
                                        <tr>
                                            <td class="border-0 border-bottom py-3">
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($foto) && $foto !== 'default.png' && file_exists($ruta_foto)): ?>
                                                        <img src="<?php echo htmlspecialchars($ruta_foto); ?>" class="rounded-circle me-3 shadow-sm" style="width: 45px; height: 45px; object-fit: cover;" alt="Foto">
                                                    <?php else: ?>
                                                        <div class="text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 45px; height: 45px; font-size: 1.2rem; font-weight: bold; background-color: var(--color-primario);">
                                                            <?php echo strtoupper(substr($alumno['nombre'], 0, 1)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <div>
                                                        <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?></span>
                                                        <a href="mailto:<?php echo htmlspecialchars($alumno['email']); ?>" class="text-decoration-none text-muted small">
                                                            ✉️ <?php echo htmlspecialchars($alumno['email']); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light text-center border mt-2 mb-0">
                            <span class="text-muted small">Aún no hay alumnos inscriptos en este curso.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Modal Subir Material (Solo creador) -->
<?php if ($es_creador_curso): ?>
<div class="modal fade" id="modalSubirMaterial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primario fw-bold">Subir Nuevo Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?accion=subir_material&id_curso=<?php echo $id_curso; ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-semibold small">Nombre del Archivo</label>
                        <input type="text" name="nombre_archivo" class="form-control form-control-sm" placeholder="Ej: Diapositivas Clase 1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-semibold small">
                            <?php echo $creador_pro ? 'Archivo (Plan PRO sin límite de tamaño)' : 'Archivo (Máx. 5 MB por archivo · Disponible: ' . number_format(max(0, 10 - $mb_usados), 2) . ' MB)'; ?>
                        </label>
                        <input type="file" name="archivo" id="inputArchivo" class="form-control form-control-sm" required>
                        <div id="errorTamanio" class="text-danger small mt-1 d-none fw-bold">
                            ⚠️ El archivo supera el límite permitido para el plan gratuito.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="btnSubir" class="btn btn-primario w-100 fw-bold">Subir Archivo</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Nueva Pregunta Foro -->
<div class="modal fade" id="modalPregunta" tabindex="-1" aria-labelledby="modalPreguntaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="index.php?accion=procesar_pregunta" method="POST">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title text-primario fw-bold" id="modalPreguntaLabel">Hacer una consulta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_curso" value="<?php echo $curso['id_curso']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-semibold small mb-1">Título de tu duda</label>
                        <input type="text" name="titulo" class="form-control form-control-sm" required placeholder="Ej: Duda sobre el Trabajo Práctico 1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-semibold small mb-1">Detalle</label>
                        <textarea name="cuerpo" class="form-control form-control-sm" rows="4" required placeholder="Explicá tu duda con más detalle para que puedan ayudarte..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primario fw-bold">Publicar Pregunta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const inputArchivo = document.getElementById('inputArchivo');
        const btnSubir = document.getElementById('btnSubir');
        const errorTamanio = document.getElementById('errorTamanio');
        const esPro = <?php echo $creador_pro ? 'true' : 'false'; ?>;
        const mbDisponibles = <?php echo max(0, 10 - $mb_usados); ?>;

        if (inputArchivo && !esPro) {
            inputArchivo.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const fileSize = this.files[0].size; 
                    const maxSize = 5 * 1024 * 1024;
                    const fileMb = fileSize / 1048576;

                    if (fileSize > maxSize) {
                        btnSubir.disabled = true;
                        errorTamanio.textContent = "⚠️ El archivo supera los 5 MB permitidos por archivo.";
                        errorTamanio.classList.remove('d-none');
                    } else if (fileMb > mbDisponibles) {
                        btnSubir.disabled = true;
                        errorTamanio.textContent = "⚠️ Este archivo supera el espacio total disponible (10 MB) en este curso.";
                        errorTamanio.classList.remove('d-none');
                    } else {
                        btnSubir.disabled = false;
                        errorTamanio.classList.add('d-none');
                    }
                }
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>