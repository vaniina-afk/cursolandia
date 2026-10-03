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
                            <button class="btn btn-sm btn-outline-secondary fw-bold">Nueva Pregunta</button>
                        </div>
                        <div class="alert alert-light text-center border mt-4">
                            <span class="text-muted small">No hay consultas activas. ¡Sé el primero en preguntar!</span>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
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