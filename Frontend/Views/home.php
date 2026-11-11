<?php 
$titulo_pagina = "Inicio";
require_once __DIR__ . '/layouts/header.php'; 
require_once __DIR__ . '/layouts/navbar.php'; 
?>

<div class="container mb-5">
    <div class="p-5 text-center bg-white rounded-3 shadow-sm card-compacta border-0">
        <h1 class="display-5 fw-bold text-primario">Explorá nuestros cursos</h1>
        <p class="lead text-secondary mb-0">Anotate en las mejores clases o convertite en creador de contenido hoy mismo.</p>
    </div>
</div>

<div class="container mb-5">
    <?php if(isset($_GET['exito'])): ?>
        <div class="alert alert-success text-center shadow-sm auto-ocultar mb-4">¡Te inscribiste al curso correctamente!</div>
    <?php elseif(isset($_GET['error']) && $_GET['error'] == 'lleno'): ?>
        <div class="alert alert-danger text-center shadow-sm auto-ocultar mb-4">Lo sentimos, el cupo de este curso ya está lleno.</div>
    <?php elseif(isset($_GET['error']) && $_GET['error'] == 'admin'): ?>
        <div class="alert alert-warning text-center shadow-sm auto-ocultar mb-4">Los administradores gestionan la plataforma, no pueden inscribirse a cursos.</div>
    <?php endif; ?>

    <h4 class="text-primario fw-bold mb-4">Últimos Cursos Agregados</h4>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php /** @var mysqli_result|false $resultado_cursos */ ?>
            <?php if (isset($resultado_cursos) && $resultado_cursos && $resultado_cursos->num_rows > 0): ?>
            <?php while ($curso = $resultado_cursos->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 card-compacta shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2 d-flex justify-content-between align-items-start">
                                <span class="badge bg-success bg-opacity-75">
                                    <?php echo ($curso['costo'] > 0) ? '$' . $curso['costo'] : 'Gratis'; ?>
                                </span>
                            </div>
                            <h5 class="card-title text-primario fw-bold"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                            <p class="card-text text-secondary small flex-grow-1">
                                <?php 
                                    $desc = htmlspecialchars($curso['descripcion']);
                                    echo (strlen($desc) > 90) ? substr($desc, 0, 90) . '...' : $desc; 
                                ?>
                            </p>
                            
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
                                    <a href="login.php" class="btn btn-outline-secondary w-100 btn-sm fw-bold">Inicia sesión para inscribirte</a>
                                <?php elseif ($_SESSION['es_admin'] == 1): ?>
                                    <button class="btn btn-secondary w-100 btn-sm fw-bold" disabled>Modo Admin</button>
                                <?php elseif ($_SESSION['id_usuario'] == $curso['id_creador']): ?>
                                    <button class="btn btn-light text-muted w-100 btn-sm fw-bold" disabled>Sos el creador</button>
                                <?php elseif (in_array($curso['id_curso'], $inscripciones)): ?>
                                    <button class="btn btn-success w-100 btn-sm fw-bold" disabled>Ya estás inscripto</button>
                                <?php elseif ($curso['cupo'] !== null && $curso['cupo'] <= 0): ?>
                                    <button class="btn btn-danger w-100 btn-sm fw-bold" disabled>Cupo Lleno</button>
                                <?php else: ?>
                                    <form action="inscribirse.php" method="POST">
                                        <input type="hidden" name="id_curso" value="<?php echo $curso['id_curso']; ?>">
                                        <button type="submit" class="btn btn-primario w-100 btn-sm fw-bold">Inscribirse</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center shadow-sm">Aún no hay cursos publicados en la plataforma.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>