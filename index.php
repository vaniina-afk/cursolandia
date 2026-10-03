<?php
session_start();
require_once 'includes/conexion.php';

$sql_cursos = "SELECT c.*, u.nombre, u.apellido 
               FROM Cursos c 
               JOIN Usuarios u ON c.id_creador = u.id_usuario 
               WHERE c.activo = 1 
               ORDER BY c.fecha_inicio DESC";
$resultado_cursos = $conexion->query($sql_cursos);

$inscripciones = [];
if (isset($_SESSION['id_usuario'])) {
    $id_usu = $_SESSION['id_usuario'];
    $sql_insc = "SELECT id_curso FROM Inscripciones WHERE id_usuario = $id_usu";
    $res_insc = $conexion->query($sql_insc);
    while ($fila = $res_insc->fetch_assoc()) {
        $inscripciones[] = $fila['id_curso'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cursolandia - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background-color: var(--color-primario);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Cursolandia</a>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <span class="navbar-text text-white me-3 d-none d-md-inline fw-semibold">
                        Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    </span>
                    <a href="panel.php" class="btn btn-outline-light btn-sm me-2">Mi Panel</a>
                    <a href="logout.php" class="btn btn-acento btn-sm">Salir</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm me-2 fw-bold">Iniciar Sesión</a>
                    <a href="registro.php" class="btn btn-acento btn-sm fw-bold">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

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
            
            <?php if ($resultado_cursos->num_rows > 0): ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Limpiar la URL para que no se repitan los carteles al recargar
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete('exito');
                url.searchParams.delete('error');
                window.history.replaceState(null, null, url);
            }

            // Ocultar mensajes automáticamente
            setTimeout(function() {
                let alertas = document.querySelectorAll('.auto-ocultar');
                alertas.forEach(function(alerta) {
                    alerta.style.transition = "opacity 0.5s ease";
                    alerta.style.opacity = "0";
                    setTimeout(() => alerta.remove(), 500); 
                });
            }, 3000); 
        });
    </script>
</body>
</html>