<?php 
$titulo_pagina = "Inicio";
require_once __DIR__ . '/layouts/header.php'; 
require_once __DIR__ . '/layouts/navbar.php'; 
/** @var mysqli_result|false $resultado_cursos */
/** @var mysqli_result|false $etiquetas_filtro */
$busqueda_actual = $_GET['buscar'] ?? '';
$etiqueta_actual = isset($_GET['etiqueta']) ? (int)$_GET['etiqueta'] : 0;
?>

<div class="container mb-4">
    <div class="p-5 text-center bg-white rounded-3 shadow-sm card-compacta border-0">
        <h1 class="display-5 fw-bold text-primario">Explorá nuestros cursos</h1>
        <p class="lead text-secondary mb-4">Anotate en las mejores clases o convertite en creador de contenido hoy mismo.</p>

        <!-- BUSCADOR Y FILTRO POR ETIQUETAS -->
        <form action="index.php" method="GET" class="row g-2 justify-content-center mt-2">
            <div class="col-md-5">
                <input type="text" name="buscar" class="form-control" placeholder="🔍 Buscar curso por título..." value="<?php echo htmlspecialchars($busqueda_actual); ?>">
            </div>
            <div class="col-md-3">
                <select name="etiqueta" class="form-select">
                    <option value="0">Todas las etiquetas</option>
                    <?php if (isset($etiquetas_filtro) && $etiquetas_filtro): ?>
                        <?php while ($et = $etiquetas_filtro->fetch_assoc()): ?>
                            <option value="<?php echo $et['id_etiqueta']; ?>" <?php echo ($etiqueta_actual === (int)$et['id_etiqueta']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($et['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primario fw-bold px-4">Buscar</button>
                <?php if ($busqueda_actual !== '' || $etiqueta_actual > 0): ?>
                    <a href="index.php" class="btn btn-outline-secondary fw-semibold">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="container mb-5">
    <?php if (isset($_GET['exito'])): ?>
        <div class="alert alert-success text-center shadow-sm auto-ocultar mb-4">¡Te inscribiste al curso correctamente!</div>
    <?php elseif (isset($_GET['pendiente'])): ?>
        <div class="alert alert-info text-center shadow-sm auto-ocultar mb-4">📩 ¡Solicitud enviada! El creador del curso debe confirmar tu inscripción antes de que puedas ingresar.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'lleno'): ?>
        <div class="alert alert-danger text-center shadow-sm auto-ocultar mb-4">Lo sentimos, el cupo de este curso ya está lleno.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'admin'): ?>
        <div class="alert alert-warning text-center shadow-sm auto-ocultar mb-4">Los administradores gestionan la plataforma, no pueden inscribirse a cursos.</div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-primario fw-bold mb-0">
            <?php echo ($busqueda_actual !== '' || $etiqueta_actual > 0) ? 'Resultados de búsqueda' : 'Catálogo de Cursos'; ?>
        </h4>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php if (isset($resultado_cursos) && $resultado_cursos && $resultado_cursos->num_rows > 0): ?>
            <?php while ($curso = $resultado_cursos->fetch_assoc()): ?>
                <?php 
                    $es_pro = ((int)($curso['pro_vigente'] ?? 0) === 1);
                    $es_recomendado = ((int)($curso['coincidencias_interes'] ?? 0) > 0);
                    $es_privado = ((int)($curso['es_privado'] ?? 0) === 1);
                    $es_pago = ($curso['costo'] !== null && (float)$curso['costo'] > 0);
                    $requiere_aprobacion = ($es_privado || $es_pago);
                    $estado_inscripcion = $inscripciones[(int)$curso['id_curso']] ?? null;
                ?>
                <div class="col">
                    <div class="card h-100 card-compacta shadow-sm <?php echo $es_pro ? '' : 'border-0'; ?>" 
                         style="<?php echo $es_pro ? 'background-color: #eafaf1; border: 2px solid #6dce99;' : ''; ?>">
                        <div class="card-body d-flex flex-column">
                            
                            <!-- Fila de insignias: Precio, Acceso, PRO y Recomendado -->
                            <div class="mb-2 d-flex flex-wrap justify-content-between align-items-center gap-1">
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-success bg-opacity-75">
                                        <?php echo $es_pago ? '$' . $curso['costo'] : 'Gratis'; ?>
                                    </span>
                                    <?php if ($es_privado): ?>
                                        <span class="badge bg-dark">🔒 Privado</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">🌐 Público</span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex flex-wrap gap-1">
                                    <?php if ($es_pro): ?>
                                        <span class="badge text-dark fw-bold" style="background-color: #a3e4d7; border: 1px solid #50a886;">
                                            ⭐ Destacado PRO
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($es_recomendado): ?>
                                        <span class="badge bg-primary bg-opacity-75">
                                            🎯 Recomendado
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <h5 class="card-title text-primario fw-bold"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                            
                            <p class="card-text text-secondary small flex-grow-1">
                                <?php 
                                    $desc = htmlspecialchars($curso['descripcion']);
                                    echo (strlen($desc) > 90) ? substr($desc, 0, 90) . '...' : $desc; 
                                ?>
                            </p>

                            <!-- Etiquetas del curso -->
                            <?php if (!empty($curso['lista_etiquetas'])): ?>
                                <div class="mb-2 d-flex flex-wrap gap-1">
                                    <?php foreach (explode(', ', $curso['lista_etiquetas']) as $tag): ?>
                                        <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">
                                            #<?php echo htmlspecialchars($tag); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <hr class="text-muted opacity-25">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted fw-semibold">
                                    👨‍🏫 <?php echo htmlspecialchars($curso['nombre'] . ' ' . $curso['apellido']); ?>
                                </small>
                                <small class="text-muted">
                                    Cupo: <?php echo $curso['cupo'] ? $curso['cupo'] : 'Sin límite'; ?>
                                </small>
                            </div>

                            <div class="mt-auto">
                                <?php if (!isset($_SESSION['id_usuario'])): ?>
                                    <a href="index.php?accion=login" class="btn btn-outline-secondary w-100 btn-sm fw-bold">Inicia sesión para inscribirte</a>
                                <?php elseif ($_SESSION['es_admin'] == 1): ?>
                                    <button class="btn btn-secondary w-100 btn-sm fw-bold" disabled>Modo Admin</button>
                                <?php elseif ($_SESSION['id_usuario'] == $curso['id_creador']): ?>
                                    <button class="btn btn-light text-muted w-100 btn-sm fw-bold" disabled>Sos el creador</button>
                                <?php elseif ($estado_inscripcion === 'Aceptado'): ?>
                                    <button class="btn btn-success w-100 btn-sm fw-bold" disabled>Ya estás inscripto</button>
                                <?php elseif ($estado_inscripcion === 'Pendiente'): ?>
                                    <button class="btn btn-warning text-dark w-100 btn-sm fw-bold" disabled>⏳ Solicitud pendiente</button>
                                <?php elseif ($estado_inscripcion === 'Rechazado'): ?>
                                    <button class="btn btn-outline-danger w-100 btn-sm fw-bold" disabled>❌ Solicitud rechazada</button>
                                <?php elseif ($curso['cupo'] !== null && $curso['cupo'] <= 0): ?>
                                    <button class="btn btn-danger w-100 btn-sm fw-bold" disabled>Cupo Lleno</button>
                                <?php else: ?>
                                    <form action="index.php?accion=inscribirse" method="POST">
                                        <input type="hidden" name="id_curso" value="<?php echo $curso['id_curso']; ?>">
                                        <button type="submit" class="btn <?php echo $requiere_aprobacion ? 'btn-outline-success' : 'btn-primario'; ?> w-100 btn-sm fw-bold">
                                            <?php echo $requiere_aprobacion ? '📩 Solicitar inscripción' : 'Inscribirse'; ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center shadow-sm">
                    <?php echo ($busqueda_actual !== '' || $etiqueta_actual > 0) 
                        ? 'No se encontraron cursos que coincidan con tu búsqueda.' 
                        : 'Aún no hay cursos publicados en la plataforma.'; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>