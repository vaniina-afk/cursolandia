<?php 
$titulo_pagina = "Foro de Consultas";
require_once __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../layouts/navbar.php'; 
/** @var mysqli_result|false $respuestas */
$tema_cerrado = (isset($pregunta['cerrada']) && (int)$pregunta['cerrada'] === 1);
$puede_moderar_tema = ($es_creador || $es_autor_pregunta);
?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="mb-3">
                <a href="index.php?accion=curso_interno&id=<?php echo $id_curso; ?>" class="btn btn-outline-secondary btn-sm fw-semibold">← Volver al Curso</a>
            </div>

            <?php if (isset($_GET['exito']) && $_GET['exito'] == 'respuesta_ok'): ?>
                <div class="alert alert-success text-center shadow-sm auto-ocultar mb-3">¡Respuesta publicada correctamente!</div>
            <?php elseif (isset($_GET['exito']) && $_GET['exito'] == 'editada'): ?>
                <div class="alert alert-success text-center shadow-sm auto-ocultar mb-3">¡Respuesta editada con éxito!</div>
            <?php elseif (isset($_GET['exito']) && $_GET['exito'] == 'aceptada'): ?>
                <div class="alert alert-success text-center shadow-sm auto-ocultar mb-3">¡Respuesta marcada como aceptada y tema cerrado!</div>
            <?php elseif (isset($_GET['exito']) && $_GET['exito'] == 'cerrada'): ?>
                <div class="alert alert-secondary text-center shadow-sm auto-ocultar mb-3">El tema ha sido cerrado.</div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] == 'doble_respuesta'): ?>
                <div class="alert alert-warning text-center shadow-sm auto-ocultar mb-3">No podés publicar dos respuestas seguidas. Editá tu última respuesta o esperá a que otro usuario responda.</div>
            <?php endif; ?>
            
            <!-- CAJA DE LA PREGUNTA PRINCIPAL -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4 class="text-primario fw-bold mb-0"><?php echo htmlspecialchars($pregunta['titulo']); ?></h4>
                        <?php if ($tema_cerrado): ?>
                            <span class="badge bg-secondary">🔒 Tema Cerrado</span>
                        <?php elseif ($pregunta['estado'] == 'Resuelta'): ?>
                            <span class="badge bg-success">✅ Resuelta</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Abierta</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <?php 
                            $foto_preg = $pregunta['imagen_perfil'] ?? '';
                            $ruta_foto_preg = "Resources/uploads/" . $foto_preg;
                        ?>
                        <?php if (!empty($foto_preg) && $foto_preg !== 'default.png' && file_exists($ruta_foto_preg)): ?>
                            <img src="<?php echo htmlspecialchars($ruta_foto_preg); ?>" class="rounded-circle me-2 shadow-sm" style="width: 45px; height: 45px; object-fit: cover;" alt="Foto">
                        <?php else: ?>
                            <div class="text-white rounded-circle d-flex justify-content-center align-items-center me-2 shadow-sm" style="width: 45px; height: 45px; background-color: var(--color-primario); font-weight: bold; font-size: 1.2rem;">
                                <?php echo strtoupper(substr($pregunta['nombre'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <span class="fw-bold d-block text-dark"><?php echo htmlspecialchars($pregunta['nombre'] . ' ' . $pregunta['apellido']); ?></span>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($pregunta['fecha_creacion'])); ?></small>
                        </div>
                    </div>
                    <p class="mb-0" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6;"><?php echo htmlspecialchars($pregunta['cuerpo']); ?></p>
                    
                    <!-- BOTONES DE MODERACIÓN DE PREGUNTA -->
                    <?php if ($puede_moderar_tema): ?>
                        <div class="mt-4 d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="index.php?accion=eliminar_pregunta&id=<?php echo $id_pregunta; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que querés eliminar esta pregunta y todas sus respuestas?');">🗑️ Eliminar Pregunta</a>
                            
                            <?php if (!$tema_cerrado): ?>
                                <a href="index.php?accion=cerrar_pregunta&id=<?php echo $id_pregunta; ?>" class="btn btn-sm btn-outline-secondary" onclick="return confirm('¿Querés cerrar este tema? Ya no se permitirán nuevas respuestas.');">🔒 Cerrar Tema</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- LISTADO DE RESPUESTAS -->
            <h5 class="fw-bold mb-3 text-secondary">Respuestas (<?php echo $respuestas ? $respuestas->num_rows : 0; ?>)</h5>
            
            <?php if ($respuestas && $respuestas->num_rows > 0): ?>
                <div class="d-flex flex-column gap-3 mb-4">
                    <?php while ($respuesta = $respuestas->fetch_assoc()): ?>
                        <?php 
                        $id_resp = (int)$respuesta['id_respuesta'];
                        $es_autor_respuesta = ($id_usuario == $respuesta['id_usuario']); 
                        $es_respuesta_profesor = ($respuesta['id_usuario'] == $pregunta['id_creador']);
                        $fue_editada = ((int)($respuesta['editada'] ?? 0) === 1);
                        $foto_resp = $respuesta['imagen_perfil'] ?? '';
                        $ruta_foto_resp = "Resources/uploads/" . $foto_resp;
                        ?>
                        
                        <div class="card shadow-sm mb-1 <?php echo $respuesta['es_destacada'] ? 'border-warning border-2' : 'border-0'; ?>" <?php echo $respuesta['es_destacada'] ? 'style="background-color: #fffdf2;"' : ''; ?>>
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <?php if (!empty($foto_resp) && $foto_resp !== 'default.png' && file_exists($ruta_foto_resp)): ?>
                                        <img src="<?php echo htmlspecialchars($ruta_foto_resp); ?>" class="rounded-circle me-2 shadow-sm flex-shrink-0" style="width: 36px; height: 36px; object-fit: cover;" alt="Foto">
                                    <?php else: ?>
                                        <div class="text-white rounded-circle d-flex justify-content-center align-items-center me-2 shadow-sm flex-shrink-0" style="width: 36px; height: 36px; background-color: var(--color-primario); font-weight: bold; font-size: 0.95rem;">
                                            <?php echo strtoupper(substr($respuesta['nombre'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>

                                    <span class="fw-bold me-auto text-dark" style="font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($respuesta['nombre'] . ' ' . $respuesta['apellido']); ?>
                                        
                                        <?php if ($es_respuesta_profesor): ?>
                                            <span class="badge text-white ms-2" style="background-color: var(--color-primario); font-size: 0.65rem;">👨‍🏫 Respuesta del Profesor</span>
                                        <?php endif; ?>

                                        <?php if ($respuesta['es_destacada']): ?>
                                            <span class="badge bg-warning text-dark ms-2" style="font-size: 0.65rem;">⭐ Respuesta Aceptada</span>
                                        <?php endif; ?>
                                    </span>

                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <?php echo date('d/m/Y H:i', strtotime($respuesta['fecha_creacion'])); ?>
                                        <?php if ($fue_editada): ?>
                                            <span class="fst-italic text-secondary ms-1">(editado)</span>
                                        <?php endif; ?>
                                    </small>
                                </div>

                                <p id="texto-resp-<?php echo $id_resp; ?>" class="mb-3 text-secondary" style="white-space: pre-wrap; font-size: 0.9rem;"><?php echo htmlspecialchars($respuesta['cuerpo']); ?></p>
                                
                                <!-- BOTONES DE ACCIÓN EN CADA RESPUESTA -->
                                <div class="d-flex justify-content-end gap-2">
                                    <?php if ($puede_moderar_tema && !$tema_cerrado): ?>
                                        <a href="index.php?accion=destacar_respuesta&id=<?php echo $id_resp; ?>&id_pregunta=<?php echo $id_pregunta; ?>" 
                                           class="btn btn-sm btn-outline-success fw-semibold" 
                                           title="Marcar como respuesta aceptada y cerrar el tema"
                                           onclick="return confirm('¿Marcar esta respuesta como aceptada y cerrar el tema?');">
                                            ⭐ Aceptar y cerrar
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($es_autor_respuesta && !$tema_cerrado): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary btn-editar-resp" 
                                                data-id="<?php echo $id_resp; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditarRespuesta" 
                                                title="Editar mi respuesta">
                                            ✏️ Editar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($es_autor_respuesta || $es_creador): ?>
                                        <a href="index.php?accion=eliminar_respuesta&id=<?php echo $id_resp; ?>&id_pregunta=<?php echo $id_pregunta; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Eliminar" 
                                           onclick="return confirm('¿Estás seguro de eliminar esta respuesta?');">
                                            🗑️
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-light border text-center text-muted mb-4">
                    Nadie ha respondido aún. ¡Sé el primero en aportar una solución!
                </div>
            <?php endif; ?>

            <!-- CAJA PARA ESCRIBIR UNA NUEVA RESPUESTA -->
            <?php if ($tema_cerrado): ?>
                <div class="alert alert-secondary border text-center text-muted mb-4 shadow-sm">
                    🔒 Este tema está cerrado. Ya no se admiten nuevas respuestas.
                </div>
            <?php elseif (!empty($es_ultimo_en_responder)): ?>
                <div class="alert alert-warning border text-center mb-4 shadow-sm">
                    <p class="mb-2 small fw-semibold text-dark">
                        ⚠️ No podés enviar dos respuestas seguidas en una misma consulta. Podés editar tu última respuesta o esperar a que otro usuario conteste.
                    </p>
                    <button type="button" 
                            class="btn btn-sm btn-primario fw-bold btn-editar-resp" 
                            data-id="<?php echo (int)$ultima_respuesta['id_respuesta']; ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalEditarRespuesta">
                        ✏️ Editar mi última respuesta
                    </button>
                </div>
            <?php else: ?>
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 text-primario">Escribir una respuesta</h6>
                        <form action="index.php?accion=procesar_respuesta" method="POST">
                            <input type="hidden" name="id_pregunta" value="<?php echo $id_pregunta; ?>">
                            <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                            
                            <div class="mb-3">
                                <textarea name="cuerpo" class="form-control bg-light border-0" rows="3" required placeholder="Escribí tu respuesta acá..." style="resize: none;"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primario fw-bold btn-sm px-4">Publicar Respuesta</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- MODAL EDITAR RESPUESTA -->
<div class="modal fade" id="modalEditarRespuesta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="index.php?accion=editar_respuesta" method="POST">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title text-primario fw-bold">Editar Respuesta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_pregunta" value="<?php echo $id_pregunta; ?>">
                    <input type="hidden" name="id_respuesta" id="editIdRespuesta" value="">
                    
                    <div class="mb-2">
                        <label class="form-label text-secondary fw-semibold small">Texto de la respuesta</label>
                        <textarea name="cuerpo" id="editCuerpoRespuesta" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primario fw-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const botonesEditar = document.querySelectorAll('.btn-editar-resp');
        const inputId = document.getElementById('editIdRespuesta');
        const textareaCuerpo = document.getElementById('editCuerpoRespuesta');

        botonesEditar.forEach(btn => {
            btn.addEventListener('click', function() {
                const idResp = this.getAttribute('data-id');
                const parrafo = document.getElementById('texto-resp-' + idResp);
                if (inputId && textareaCuerpo && parrafo) {
                    inputId.value = idResp;
                    textareaCuerpo.value = parrafo.textContent.trim();
                }
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>