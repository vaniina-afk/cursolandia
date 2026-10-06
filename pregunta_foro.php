<?php
session_start();
require_once 'includes/conexion.php';

// Validamos que el usuario esté logueado y que venga un ID de pregunta por la URL
if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: panel.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_pregunta = (int)$_GET['id'];

require_once 'includes/logica_notificaciones.php'; 

// 1. Traer los datos de la pregunta principal
$sql_pregunta = "SELECT p.*, u.nombre, u.apellido, u.imagen_perfil, c.titulo as curso_titulo 
                 FROM preguntas p 
                 JOIN Usuarios u ON p.id_usuario = u.id_usuario 
                 JOIN Cursos c ON p.id_curso = c.id_curso
                 WHERE p.id_pregunta = $id_pregunta";
$result_pregunta = $conexion->query($sql_pregunta);

if ($result_pregunta->num_rows == 0) {
    header("Location: panel.php"); // Si la pregunta no existe, lo sacamos
    exit();
}
$pregunta = $result_pregunta->fetch_assoc();
$id_curso = $pregunta['id_curso'];

// 2. Traer todas las respuestas asociadas a esta pregunta
$sql_respuestas = "SELECT r.*, u.nombre, u.apellido, u.imagen_perfil 
                   FROM respuestas r 
                   JOIN Usuarios u ON r.id_usuario = u.id_usuario 
                   WHERE r.id_pregunta = $id_pregunta 
                   ORDER BY r.fecha_creacion ASC";
$respuestas = $conexion->query($sql_respuestas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Foro de Consultas - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background-color: var(--color-primario);">
        <div class="container d-flex justify-content-between">
            <a class="navbar-brand fw-bold text-white text-decoration-none" href="index.php">Cursolandia</a>
            
            <div class="d-flex align-items-center gap-3">
                
                <!-- DROPDOWN DE NOTIFICACIONES -->
                <div class="dropdown me-2">
                    <a href="#" class="text-white text-decoration-none position-relative" id="campanita" data-bs-toggle="dropdown" aria-expanded="false">
                        <span style="font-size: 1.2rem;">🔔</span>
                        <?php if (isset($no_leidas) && $no_leidas > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                <?php echo $no_leidas; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="campanita" style="width: 320px; max-height: 400px; overflow-y: auto; z-index: 1050;">
                        <li><h6 class="dropdown-header fw-bold text-dark border-bottom pb-2">Notificaciones</h6></li>
                        
                        <?php if (isset($result_noti) && $result_noti->num_rows > 0): ?>
                            <?php while($noti = $result_noti->fetch_assoc()): ?>
                                <li>
                                    <a class="dropdown-item py-3 border-bottom text-wrap <?php echo ($noti['leida'] == 0) ? 'bg-light' : ''; ?>" href="leer_notificacion.php?id=<?php echo $noti['id_notificacion']; ?>">
                                        <div class="d-flex flex-column gap-1">
                                            <span class="small" style="line-height: 1.2;"><?php echo htmlspecialchars($noti['mensaje']); ?></span>
                                            <small class="text-primario fw-bold" style="font-size: 0.7rem;">
                                                ⏱️ <?php echo tiempo_transcurrido($noti['fecha']); ?>
                                            </small>
                                        </div>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                            <li><a class="dropdown-item text-center small text-muted py-2" href="notificaciones.php">Ver todas</a></li>
                        <?php else: ?>
                            <li><span class="dropdown-item text-muted small text-center py-3">No tenés notificaciones nuevas.</span></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- FIN NOTIFICACIONES -->

                <!-- Saludo y Botones -->
                <div class="d-flex align-items-center">
                    <span class="navbar-text text-white me-3 d-none d-md-inline fw-semibold">
                        Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    </span>
                    <a href="curso_interno.php?id=<?php echo $id_curso; ?>" class="btn btn-outline-light btn-sm me-2">Volver al Curso</a>
                    <a href="logout.php" class="btn btn-acento btn-sm">Salir</a>
                </div>

            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <!-- CAJA DE LA PREGUNTA PRINCIPAL -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h4 class="text-primario fw-bold mb-0"><?php echo htmlspecialchars($pregunta['titulo']); ?></h4>
                            <?php if ($pregunta['estado'] == 'Resuelta'): ?>
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
                    </div>
                </div>

                <!-- LISTADO DE RESPUESTAS -->
                <h5 class="fw-bold mb-3 text-secondary">Respuestas (<?php echo $respuestas->num_rows; ?>)</h5>
                
                <?php if ($respuestas->num_rows > 0): ?>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php while($respuesta = $respuestas->fetch_assoc()): ?>
                            <!-- Si la respuesta es destacada, le ponemos un borde verde a la izquierda -->
                            <div class="card shadow-sm border-0 <?php echo $respuesta['es_destacada'] ? 'border-start border-success border-4' : ''; ?>">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="fw-bold me-auto text-dark" style="font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($respuesta['nombre'] . ' ' . $respuesta['apellido']); ?>
                                            <?php if ($respuesta['es_destacada']): ?>
                                                <span class="badge bg-success ms-2" style="font-size: 0.65rem;">Respuesta del Profesor</span>
                                            <?php endif; ?>
                                        </span>
                                        <small class="text-muted" style="font-size: 0.75rem;"><?php echo date('d/m/Y H:i', strtotime($respuesta['fecha_creacion'])); ?></small>
                                    </div>
                                    <p class="mb-0 text-secondary" style="white-space: pre-wrap; font-size: 0.9rem;"><?php echo htmlspecialchars($respuesta['cuerpo']); ?></p>
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
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 text-primario">Escribir una respuesta</h6>
                        <form action="procesar_respuesta.php" method="POST">
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

            </div>
        </div>
    </div>
    <!-- Script de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>