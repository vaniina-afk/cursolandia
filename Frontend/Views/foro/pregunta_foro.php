<?php 
$titulo_pagina = "Foro de Consultas";
require_once __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../layouts/navbar.php'; 
/** @var mysqli_result|false $respuestas */
?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="mb-3">
                <a href="index.php?accion=curso_interno&id=<?php echo $id_curso; ?>" class="btn btn-outline-secondary btn-sm fw-semibold">← Volver al Curso</a>
            </div>
            
            <!-- CAJA DE LA PREGUNTA PRINCIPAL -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4 class="text-primario fw-bold mb-0"><?php echo htmlspecialchars($pregunta['titulo']); ?></h4>
                        <?php if (isset($pregunta['cerrada']) && $pregunta['cerrada'] == 1): ?>
                            <span class="badge bg-secondary">🔒 Tema Cerrado</span>
                        <?php elseif ($pregunta['estado'] == 'Resuelta'): ?>
                            <span class="badge bg-success">Resuelta</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Abierta</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <?php if (!empty($pregunta['imagen_perfil'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($pregunta['imagen_perfil']); ?>" class="rounded-circle me-2 shadow-sm" style="width: 45px; height: 45px; object-fit: cover;">
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
                    <?php if ($es_autor_pregunta || $es_creador): ?>
                        <div class="mt-4 d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="index.php?accion=eliminar_pregunta&id=<?php echo $id_pregunta; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que querés eliminar esta pregunta y todas sus respuestas?');">🗑️ Eliminar Pregunta</a>
                            
                            <?php if ($es_creador && (isset($pregunta['cerrada']) && $pregunta['cerrada'] == 0)): ?>
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
                        $es_autor_respuesta = ($id_usuario == $respuesta['id_usuario']); 
                        $es_respuesta_profesor = ($respuesta['id_usuario'] == $pregunta['id_creador']);
                        ?>
                        
                        <div class="card shadow-sm mb-1 <?php echo $respuesta['es_destacada'] ? 'border-warning border-2' : 'border-0'; ?>" <?php echo $respuesta['es_destacada'] ? 'style="background-color: #fffdf2;"' : ''; ?>>
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="fw-bold me-auto text-dark" style="font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($respuesta['nombre'] . ' ' . $respuesta['apellido']); ?>
                                        
                                        <?php if ($es_respuesta_profesor): ?>
                                            <span class="badge text-white ms-2" style="background-color: var(--color-primario); font-size: 0.65rem;">👨‍🏫 Respuesta del Profesor</span>
                                        <?php endif; ?>

                                        <?php if ($respuesta['es_destacada']): ?>
                                            <span class="badge bg-warning text-dark ms-2" style="font-size: 0.65rem;">⭐ Respuesta Destacada</span>
                                        <?php endif; ?>

                                    </span>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?php echo date('d/m/Y H:i', strtotime($respuesta['fecha_creacion'])); ?></small>
                                </div>
                                <p class="mb-3 text-secondary" style="white-space: pre-wrap; font-size: 0.9rem;"><?php echo htmlspecialchars($respuesta['cuerpo']); ?></p>
                                
                                <!-- BOTONES DE MODERACIÓN DE RESPUESTA -->
                                <?php if ($es_creador || $es_autor_respuesta): ?>
                                    <div class="d-flex justify-content-end gap-2">
                                        <?php if ($es_creador): ?>
                                            <a href="index.php?accion=destacar_respuesta&id=<?php echo $respuesta['id_respuesta']; ?>&id_pregunta=<?php echo $id_pregunta; ?>" class="btn btn-sm <?php echo $respuesta['es_destacada'] ? 'btn-warning' : 'btn-outline-warning'; ?>" title="Destacar / Quitar destacado">⭐</a>
                                        <?php endif; ?>
                                        
                                        <?php if ($es_autor_respuesta || $es_creador): ?>
                                            <a href="index.php?accion=eliminar_respuesta&id=<?php echo $respuesta['id_respuesta']; ?>&id_pregunta=<?php echo $id_pregunta; ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta respuesta?');">🗑️</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-light border text-center text-muted mb-4">
                    Nadie ha respondido aún. ¡Sé el primero en aportar una solución!
                </div>
            <?php endif; ?>

            <!-- CAJA PARA ESCRIBIR UNA NUEVA RESPUESTA (Solo si está abierta) -->
            <?php if (isset($pregunta['cerrada']) && $pregunta['cerrada'] == 1): ?>
                <div class="alert alert-secondary border text-center text-muted mb-4 shadow-sm">
                    🔒 El profesor ha cerrado este tema. Ya no se admiten nuevas respuestas.
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>