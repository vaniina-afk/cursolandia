<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: panel.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_curso = (int)$_GET['id'];
$es_admin = $_SESSION['es_admin'];

$sql_curso = "SELECT c.*, u.nombre AS prof_nombre, u.apellido AS prof_apellido 
              FROM Cursos c 
              JOIN Usuarios u ON c.id_creador = u.id_usuario 
              WHERE c.id_curso = $id_curso AND c.activo = 1";
$resultado_curso = $conexion->query($sql_curso);

if ($resultado_curso->num_rows == 0) {
    header("Location: panel.php?error=inactivo");
    exit();
}
$curso = $resultado_curso->fetch_assoc();

$acceso_permitido = false;
if ($curso['id_creador'] == $id_usuario || $es_admin == 1) {
    $acceso_permitido = true;
} else {
    $sql_acceso = "SELECT id_inscripcion FROM Inscripciones 
                   WHERE id_curso = $id_curso AND id_usuario = $id_usuario AND estado = 'Aceptado'";
    $check_acceso = $conexion->query($sql_acceso);
    if ($check_acceso->num_rows > 0) {
        $acceso_permitido = true;
    }
}

if (!$acceso_permitido) {
    header("Location: panel.php");
    exit();
}

// Traemos los materiales
$sql_materiales = "SELECT * FROM Materiales WHERE id_curso = $id_curso";
$materiales = $conexion->query($sql_materiales);

// Traemos los alumnos (Solo si es el creador)
$alumnos = null;
if ($curso['id_creador'] == $id_usuario) {
    $sql_alumnos = "SELECT u.nombre, u.apellido, u.email, u.imagen_perfil 
                    FROM Inscripciones i
                    JOIN Usuarios u ON i.id_usuario = u.id_usuario
                    WHERE i.id_curso = $id_curso AND i.estado = 'Aceptado'";
    $alumnos = $conexion->query($sql_alumnos);
}

// Traemos las preguntas del foro
$sql_foro = "SELECT p.*, u.nombre, u.apellido 
             FROM preguntas p 
             JOIN Usuarios u ON p.id_usuario = u.id_usuario 
             WHERE p.id_curso = $id_curso 
             ORDER BY p.fecha_creacion DESC";
$preguntas_foro = $conexion->query($sql_foro);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($curso['titulo']); ?> - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark shadow-sm mb-4" style="background-color: var(--color-primario);">
        <div class="container">
            <span class="navbar-brand fw-bold">Aula Virtual</span>
            <a href="panel.php" class="btn btn-outline-light btn-sm">Volver al Panel</a>
        </div>
    </nav>

    <div class="container mb-5">
        
        <?php if(isset($_GET['exito'])): ?>
            <div class="alert alert-success text-center shadow-sm auto-ocultar mb-4">¡Archivo subido correctamente!</div>
        <?php elseif(isset($_GET['borrado'])): ?>
            <div class="alert alert-success text-center shadow-sm auto-ocultar mb-4">¡Material eliminado correctamente!</div>
        <?php elseif(isset($_GET['error']) && $_GET['error'] == 'tamanio'): ?>
            <div class="alert alert-danger text-center shadow-sm mb-4">Error desde el servidor: El archivo supera el límite máximo de 5MB.</div>
        <?php elseif(isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center shadow-sm mb-4">Hubo un error al procesar tu solicitud.</div>
        <?php endif; ?>

        <div class="card card-compacta shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h2 class="text-primario fw-bold mb-0"><?php echo htmlspecialchars($curso['titulo']); ?></h2>
                    <span class="badge bg-acento fs-6">
                        Inicio: <?php echo date("d/m/Y", strtotime($curso['fecha_inicio'])); ?>
                    </span>
                </div>
                <h6 class="text-muted fw-semibold mb-3">👨‍🏫 Profesor: <?php echo htmlspecialchars($curso['prof_nombre'] . ' ' . $curso['prof_apellido']); ?></h6>
                <p class="text-secondary mb-0"><?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?></p>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-md-7">
                <div class="card card-compacta shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h4 class="text-primario fw-bold mb-0">📚 Material de Estudio</h4>
                            <?php if ($curso['id_creador'] == $id_usuario): ?>
                                <button class="btn btn-sm btn-primario fw-bold" data-bs-toggle="modal" data-bs-target="#modalSubirMaterial">
                                    Subir Archivo
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($materiales->num_rows > 0): ?>
                            <div class="list-group">
                                <?php while ($mat = $materiales->fetch_assoc()): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">📄 <?php echo htmlspecialchars($mat['nombre_archivo']); ?></h6>
                                            <small class="text-muted">Peso: <?php echo htmlspecialchars($mat['tamanio_mb']); ?> MB</small>
                                        </div>
                                        <div>
                                            <a href="uploads/<?php echo htmlspecialchars($mat['ruta_archivo']); ?>" download class="btn btn-sm btn-outline-success fw-bold">Descargar</a>
                                            
                                            <!-- Botón Borrar: SOLO se muestra si es el creador -->
                                            <?php if ($curso['id_creador'] == $id_usuario): ?>
                                                <a href="borrar_material.php?id_mat=<?php echo $mat['id_material']; ?>&id_curso=<?php echo $id_curso; ?>" 
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

            <div class="col-md-5">
                <div class="card card-compacta shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h4 class="text-primario fw-bold mb-0">💬 Foro de Consultas</h4>
                            <button class="btn btn-sm btn-outline-secondary fw-bold" data-bs-toggle="modal" data-bs-target="#modalPregunta">Nueva Pregunta</button>
                        </div>
                        <!-- Listado dinámico de preguntas -->
                        <div class="foro-contenedor mt-3" style="max-height: 350px; overflow-y: auto;">
                            <?php if ($preguntas_foro && $preguntas_foro->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($pregunta = $preguntas_foro->fetch_assoc()): ?>
                                        <!-- Más adelante crearemos 'pregunta_foro.php' para ver las respuestas -->
                                        <a href="pregunta_foro.php?id=<?php echo $pregunta['id_pregunta']; ?>" class="list-group-item list-group-item-action py-3 px-2 border-bottom">
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
                                                <?php if ($pregunta['estado'] == 'Resuelta'): ?>
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

        <?php if ($curso['id_creador'] == $id_usuario): ?>
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
                                            <tr>
                                                <td class="border-0 border-bottom py-3">
                                                    <div class="d-flex align-items-center">
                                                        
                                                        <!-- Lógica de la Foto / Inicial -->
                                                        <?php if (!empty($alumno['foto_perfil'])): ?>
                                                            <img src="uploads/<?php echo htmlspecialchars($alumno['foto_perfil']); ?>" class="rounded-circle me-3 shadow-sm" style="width: 45px; height: 45px; object-fit: cover;" alt="Foto">
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

    <!-- Modal (Oculto para alumnos) -->
    <?php if ($curso['id_creador'] == $id_usuario): ?>
    <div class="modal fade" id="modalSubirMaterial" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-primario fw-bold">Subir Nuevo Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="procesar_material.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-semibold small">Nombre del Archivo</label>
                            <input type="text" name="nombre_archivo" class="form-control form-control-sm" placeholder="Ej: Diapositivas Clase 1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-semibold small">Archivo (Máx. 5MB)</label>
                            <input type="file" name="archivo" id="inputArchivo" class="form-control form-control-sm" required>
                            <div id="errorTamanio" class="text-danger small mt-1 d-none fw-bold">
                                ⚠️ El archivo supera los 5MB permitidos. Elegí uno más liviano.
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
                <form action="procesar_pregunta.php" method="POST">
                    <div class="modal-header bg-light border-bottom-0">
                        <h5 class="modal-title text-primario fw-bold" id="modalPreguntaLabel">Hacer una consulta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Pasamos el ID del curso de forma oculta -->
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // 1. LIMPIAR LA URL (Evita que los mensajes se repitan al recargar)
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete('exito');
                url.searchParams.delete('borrado');
                url.searchParams.delete('error');
                // Reemplaza la URL en el navegador sin recargar la página
                window.history.replaceState(null, null, url);
            }

            // 2. OCULTAR CARTELES AUTOMÁTICAMENTE
            setTimeout(function() {
                let alertas = document.querySelectorAll('.auto-ocultar');
                alertas.forEach(function(alerta) {
                    alerta.style.transition = "opacity 0.5s ease";
                    alerta.style.opacity = "0";
                    setTimeout(() => alerta.remove(), 500); 
                });
            }, 4000); 

            // 3. VALIDACIÓN DE TAMAÑO DE ARCHIVO (Máx 5MB)
            const inputArchivo = document.getElementById('inputArchivo');
            const btnSubir = document.getElementById('btnSubir');
            const errorTamanio = document.getElementById('errorTamanio');

            if (inputArchivo) {
                inputArchivo.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        const fileSize = this.files[0].size; 
                        const maxSize = 5 * 1024 * 1024; 

                        if (fileSize > maxSize) {
                            btnSubir.disabled = true;
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
</body>
</html>