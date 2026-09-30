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

// 1. Traer la información general del curso
$sql_curso = "SELECT c.*, u.nombre AS prof_nombre, u.apellido AS prof_apellido 
              FROM Cursos c 
              JOIN Usuarios u ON c.id_creador = u.id_usuario 
              WHERE c.id_curso = $id_curso AND c.activo = 1";
$resultado_curso = $conexion->query($sql_curso);

if ($resultado_curso->num_rows == 0) {
    // Si no lo encuentra, es porque no existe o tiene activo = 0
    header("Location: panel.php?error=inactivo");
    exit();
}
$curso = $resultado_curso->fetch_assoc();

// 2. Control de Seguridad: ¿Tiene permiso para ver esta página?
$acceso_permitido = false;

// Si es el creador o es admin, entra directo
if ($curso['id_creador'] == $id_usuario || $es_admin == 1) {
    $acceso_permitido = true;
} else {
    // Si es alumno, verificamos que su inscripción exista y esté aceptada
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
        <!-- Cabecera del Curso -->
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

        <!-- Área de Trabajo (Materiales y Foro) -->
        <div class="row g-4">
            
            <!-- Columna Izquierda: Materiales -->
            <div class="col-md-7">
                <div class="card card-compacta shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h4 class="text-primario fw-bold mb-0">📚 Material de Estudio</h4>
                            <?php if ($curso['id_creador'] == $id_usuario): ?>
                                <button class="btn btn-sm btn-primario fw-bold">Subir Archivo</button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="alert alert-light text-center border mt-4">
                            <span class="text-muted small">Aún no hay materiales subidos para este curso.</span>
                        </div>
                        <!-- Acá insertaremos la lista de PDFs/Archivos en el próximo paso -->
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Foro de Consultas -->
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
                        <!-- Acá armaremos el sistema de preguntas y respuestas -->
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>