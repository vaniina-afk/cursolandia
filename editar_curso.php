<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: panel.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_curso = (int)$_GET['id'];
$mensaje = "";

// 1. Procesar la actualización si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $conexion->real_escape_string($_POST['titulo']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);
    $costo = !empty($_POST['costo']) ? (float)$_POST['costo'] : 'NULL';
    $cupo = !empty($_POST['cupo']) ? (int)$_POST['cupo'] : 'NULL';
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    if ($fecha_fin < $fecha_inicio) {
         $mensaje = "<div class='alert alert-danger p-2 small mb-3'>La fecha de fin no puede ser anterior a la de inicio.</div>";
    } else {
        $sql_update = "UPDATE Cursos 
                       SET titulo = '$titulo', descripcion = '$descripcion', costo = $costo, cupo = $cupo, fecha_inicio = '$fecha_inicio', fecha_fin = '$fecha_fin' 
                       WHERE id_curso = $id_curso AND id_creador = $id_usuario";
        
        if ($conexion->query($sql_update)) {
            $mensaje = "<div class='alert alert-success p-2 small mb-3 auto-ocultar'>¡Curso actualizado con éxito!</div>";
        } else {
            $mensaje = "<div class='alert alert-danger p-2 small mb-3'>Error: " . $conexion->error . "</div>";
        }
    }
}

// 2. Traer los datos actuales para mostrar en el formulario
$sql_curso = "SELECT * FROM Cursos WHERE id_curso = $id_curso AND id_creador = $id_usuario AND activo = 1";
$resultado = $conexion->query($sql_curso);

if ($resultado->num_rows == 0) {
    header("Location: panel.php"); // Si no es el creador o no existe, lo echamos
    exit();
}

$curso = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Curso - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark mb-4" style="background-color: var(--color-primario);">
        <div class="container">
            <span class="navbar-brand fw-bold">Cursolandia</span>
            <a href="panel.php" class="btn btn-outline-light btn-sm">Volver al Panel</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-compacta shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="text-primario fw-bold border-bottom pb-2 mb-3">Editar Curso</h3>
                        
                        <?php echo $mensaje; ?>

                        <form action="editar_curso.php?id=<?php echo $id_curso; ?>" method="POST">
                            <div class="mb-3">
                                <label class="form-label text-secondary fw-semibold small mb-1">Título del Curso</label>
                                <input type="text" name="titulo" class="form-control form-control-sm" value="<?php echo htmlspecialchars($curso['titulo']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-secondary fw-semibold small mb-1">Descripción</label>
                                <textarea name="descripcion" class="form-control form-control-sm" rows="3" required><?php echo htmlspecialchars($curso['descripcion']); ?></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Costo ($)</label>
                                    <input type="number" step="0.01" min="0" name="costo" class="form-control form-control-sm" value="<?php echo $curso['costo']; ?>" placeholder="Vacío si es gratis">
                                </div>
                                <div class="col">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Cupo Máximo</label>
                                    <input type="number" min="1" name="cupo" class="form-control form-control-sm" value="<?php echo $curso['cupo']; ?>" placeholder="Vacío para sin límite">
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Fecha de Inicio</label>
                                    <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="<?php echo $curso['fecha_inicio']; ?>" required>
                                </div>
                                <div class="col">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Fecha de Fin</label>
                                    <input type="date" name="fecha_fin" class="form-control form-control-sm" value="<?php echo $curso['fecha_fin']; ?>" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primario w-100 fw-bold">Guardar Cambios</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                let alertas = document.querySelectorAll('.auto-ocultar');
                alertas.forEach(function(alerta) {
                    alerta.style.transition = "opacity 0.5s ease";
                    alerta.style.opacity = "0";
                    setTimeout(() => alerta.remove(), 500); 
                });
            }, 4000); 
        });
    </script>
</body>
</html>