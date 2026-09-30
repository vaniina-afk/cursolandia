<?php
session_start();
require_once 'includes/conexion.php';

// Si no está logueado, lo mandamos al inicio
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre'];
$es_admin = $_SESSION['es_admin'];

// Buscamos los cursos a los que el usuario se inscribió
$sql_inscritos = "SELECT c.id_curso, c.titulo, u.nombre, u.apellido 
                  FROM Inscripciones i 
                  JOIN Cursos c ON i.id_curso = c.id_curso 
                  JOIN Usuarios u ON c.id_creador = u.id_usuario 
                  WHERE i.id_usuario = $id_usuario AND i.estado = 'Aceptado'";
$cursos_inscritos = $conexion->query($sql_inscritos);

// Buscamos los cursos que el usuario creó
$sql_creados = "SELECT id_curso, titulo, cupo, fecha_inicio 
                FROM Cursos 
                WHERE id_creador = $id_usuario AND activo = 1
                ORDER BY fecha_inicio DESC";
$cursos_creados = $conexion->query($sql_creados);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Panel - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background-color: var(--color-primario);">
        <div class="container">
            <!-- Acá el logo funciona como atajo para volver a la vidriera (index) -->
            <a class="navbar-brand fw-bold" href="index.php">Cursolandia</a>
            
            <div class="d-flex align-items-center">
                <span class="navbar-text text-white me-3 d-none d-md-inline fw-semibold">
                    Hola, <?php echo htmlspecialchars($nombre_usuario); ?>
                </span>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">Ver Catálogo</a>
                <a href="logout.php" class="btn btn-acento btn-sm">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        
        <!-- Sección 1: Accesos Rápidos -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-compacta shadow-sm border-0">
                    <div class="card-body p-3 d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                        <span class="fw-bold text-primario align-self-center me-3">Accesos rápidos:</span>
                        <a href="crear_curso.php" class="btn btn-primario btn-sm fw-bold">➕ Crear Nuevo Curso</a>
                        <a href="solicitar_pro.php" class="btn btn-outline-secondary btn-sm fw-bold">⭐ Solicitar PRO</a>
                        
                        <!-- Este botón SOLO lo ven los administradores -->
                        <?php if ($es_admin == 1): ?>
                            <a href="admin_solicitudes.php" class="btn btn-warning btn-sm fw-bold text-dark">⚙️ Panel Admin</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tablero Principal a dos columnas -->
        <div class="row g-4">
            
            <!-- Columna Izquierda: Mis Inscripciones -->
            <div class="col-md-6">
                <h4 class="text-primario fw-bold mb-3">Mis Inscripciones</h4>
                <?php if ($cursos_inscritos->num_rows > 0): ?>
                    <div class="list-group shadow-sm">
                        <?php while ($curso = $cursos_inscritos->fetch_assoc()): ?>
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($curso['titulo']); ?></h6>
                                    <small class="text-muted">Prof: <?php echo htmlspecialchars($curso['nombre'] . ' ' . $curso['apellido']); ?></small>
                                </div>
                                <!-- Botón preparado para el futuro cuando armemos la vista de la clase -->
                                <button class="btn btn-sm btn-acento fw-bold disabled">Entrar</button>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center shadow-sm p-3 small">
                        Todavía no te anotaste a ningún curso. <br>
                        <a href="index.php" class="fw-bold text-primario text-decoration-none">¡Explorar catálogo!</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Columna Derecha: Mis Cursos Creados -->
            <div class="col-md-6">
                <h4 class="text-primario fw-bold mb-3">Mis Cursos Creados</h4>
                <?php if ($cursos_creados->num_rows > 0): ?>
                    <div class="list-group shadow-sm">
                        <?php while ($creado = $cursos_creados->fetch_assoc()): ?>
                            <div class="list-group-item p-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($creado['titulo']); ?></h6>
                                    <span class="badge bg-secondary">
                                        Cupo: <?php echo $creado['cupo'] !== null ? $creado['cupo'] : 'Ilimitado'; ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Inicio: <?php echo date("d/m/Y", strtotime($creado['fecha_inicio'])); ?></small>
                                    <div>
                                        <!-- Botones listos para futuras funcionalidades -->
                                        <a href="editar_curso.php?id=<?php echo $creado['id_curso']; ?>" class="btn btn-sm btn-outline-secondary fw-bold" style="font-size: 0.75rem;">Editar</a>
                                        <a href="borrar_curso.php?id=<?php echo $creado['id_curso']; ?>" class="btn btn-sm btn-outline-danger fw-bold" style="font-size: 0.75rem;" onclick="return confirm('¿Estás seguro de que querés borrar este curso?');">Borrar</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary text-center shadow-sm p-3 small">
                        Aún no sos profesor en nuestra plataforma. <br>
                        <a href="crear_curso.php" class="fw-bold text-primario text-decoration-none">Crear mi primer curso</a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</body>
</html>