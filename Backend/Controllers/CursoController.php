<?php
require_once __DIR__ . '/../Models/CursoModel.php';

class CursoController {
    private $cursoModel;
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->cursoModel = new CursoModel($conexion);
    }

    // Cargar notificaciones para la campanita desde el Modelo
    private function cargarNotificaciones() {
        if (isset($_SESSION['id_usuario'])) {
            require_once __DIR__ . '/../Models/NotificacionModel.php';
            $notiModel = new NotificacionModel($this->conexion);
            $id_usuario = (int)$_SESSION['id_usuario'];
            return [
                'no_leidas' => $notiModel->contarNoLeidas($id_usuario),
                'result_noti' => $notiModel->obtenerUltimasPorUsuario($id_usuario, 5)
            ];
        }
        return [
            'no_leidas' => 0,
            'result_noti' => null
        ];
    }

    // 1. Pantalla principal (Inicio)
    public function index() {
        $resultado_cursos = $this->cursoModel->obtenerCursosActivos();
        $inscripciones = [];
        $no_leidas = 0;
        $result_noti = null;

        if (isset($_SESSION['id_usuario'])) {
            $id_usuario = (int)$_SESSION['id_usuario'];
            $inscripciones = $this->cursoModel->obtenerIdsCursosInscriptos($id_usuario);
            $notis = $this->cargarNotificaciones();
            $no_leidas = $notis['no_leidas'];
            $result_noti = $notis['result_noti'];
        }

        require_once __DIR__ . '/../../Frontend/views/home.php';
    }

    // 2. Panel del usuario
    public function panel() {
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: index.php");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $es_admin = (int)$_SESSION['es_admin'];

        $notis = $this->cargarNotificaciones();
        $no_leidas = $notis['no_leidas'];
        $result_noti = $notis['result_noti'];

        $mensaje_panel = "";
        if (isset($_GET['error']) && $_GET['error'] == 'inactivo') {
            $mensaje_panel = "<div class='alert alert-warning p-3 text-center shadow-sm auto-ocultar mb-4'><strong>¡Aviso!</strong> El curso al que intentaste acceder ya no se encuentra activo porque su creador lo deshabilitó.</div>";
        } elseif (isset($_GET['baja'])) {
            $mensaje_panel = "<div class='alert alert-success p-3 text-center shadow-sm auto-ocultar mb-4'>Te has dado de baja del curso exitosamente.</div>";
        }

        $cursos_inscritos = $this->cursoModel->obtenerCursosInscritosPorUsuario($id_usuario);
        $cursos_creados = $this->cursoModel->obtenerCursosCreadosPorUsuario($id_usuario);

        require_once __DIR__ . '/../../Frontend/views/cursos/panel.php';
    }

    // 3. Crear curso
    public function crear() {
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: index.php?accion=login");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $notis = $this->cargarNotificaciones();
        $no_leidas = $notis['no_leidas'];
        $result_noti = $notis['result_noti'];

        $mensaje = "";
        $es_pro = $this->cursoModel->esUsuarioPro($id_usuario);
        $_SESSION['plan_pro'] = $es_pro; // Sincronizamos por si el admin lo acaba de aprobar
        $total_cursos = $this->cursoModel->contarCursosActivosCreados($id_usuario);

        $puede_crear = true;
        if ($total_cursos >= 3 && $es_pro == 0) {
            $puede_crear = false;
            $mensaje = "<div class='alert alert-warning p-3 mb-3 text-center shadow-sm'><strong>¡Límite alcanzado!</strong> Ya tenés 3 cursos publicados. Para publicar más, necesitás pasarte al plan PRO.</div>";
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $puede_crear) {
            $titulo = trim($_POST['titulo']);
            $descripcion = trim($_POST['descripcion']);
            $costo = ($_POST['costo'] !== '') ? (float)$_POST['costo'] : null;
            $cupo = ($_POST['cupo'] !== '') ? (int)$_POST['cupo'] : null;
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];

            if ($fecha_fin < $fecha_inicio) {
                $mensaje = "<div class='alert alert-danger p-2 small mb-3'>La fecha de fin no puede ser anterior a la de inicio.</div>";
            } else {
                if ($this->cursoModel->crearCurso($id_usuario, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin)) {
                    $total_cursos++;
                    if ($total_cursos >= 3 && $es_pro == 0) {
                        $puede_crear = false;
                        $mensaje = "<div class='alert alert-success p-2 small mb-2 auto-ocultar'>¡Curso creado con éxito!</div>
                                    <div class='alert alert-warning p-3 text-center shadow-sm'><strong>Has alcanzado el límite de 3 cursos.</strong> Pasate a PRO para seguir creando.</div>";
                    } else {
                        $restantes = ($es_pro == 1) ? "ilimitados (Plan PRO)" : (3 - $total_cursos) . " cupos gratuitos";
                        $mensaje = "<div class='alert alert-success p-2 small mb-3 auto-ocultar'>¡Curso creado con éxito! Te quedan $restantes.</div>";
                    }
                } else {
                    $mensaje = "<div class='alert alert-danger p-2 small mb-3'>Error al guardar el curso.</div>";
                }
            }
        }

        require_once __DIR__ . '/../../Frontend/views/cursos/crear_curso.php';
    }

    // 4. Editar curso
    public function editar() {
        if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_curso = (int)$_GET['id'];

        $notis = $this->cargarNotificaciones();
        $no_leidas = $notis['no_leidas'];
        $result_noti = $notis['result_noti'];

        $mensaje = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $titulo = trim($_POST['titulo']);
            $descripcion = trim($_POST['descripcion']);
            $costo = ($_POST['costo'] !== '') ? (float)$_POST['costo'] : null;
            $cupo_ingresado = ($_POST['cupo'] !== '') ? (int)$_POST['cupo'] : null;
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];

            if ($fecha_fin < $fecha_inicio) {
                $mensaje = "<div class='alert alert-danger p-2 small mb-3'>La fecha de fin no puede ser anterior a la de inicio.</div>";
            } else {
                $inscriptos = $this->cursoModel->contarInscriptosAceptados($id_curso);
                $error_cupo = false;
                $cupo_final = null;

                if ($cupo_ingresado !== null) {
                    if ($cupo_ingresado < $inscriptos) {
                        $error_cupo = true;
                        $mensaje = "<div class='alert alert-danger p-2 small mb-3'>No podés establecer un cupo total de $cupo_ingresado. Ya tenés $inscriptos alumno/s inscripto/s.</div>";
                    } else {
                        $cupo_final = $cupo_ingresado - $inscriptos;
                    }
                }

                if (!$error_cupo) {
                    if ($this->cursoModel->actualizarCurso($id_curso, $id_usuario, $titulo, $descripcion, $costo, $cupo_final, $fecha_inicio, $fecha_fin)) {
                        $mensaje = "<div class='alert alert-success p-2 small mb-3 auto-ocultar'>¡Curso actualizado con éxito!</div>";
                    } else {
                        $mensaje = "<div class='alert alert-danger p-2 small mb-3'>Error al actualizar el curso.</div>";
                    }
                }
            }
        }

        $curso = $this->cursoModel->obtenerCursoPorIdYCreador($id_curso, $id_usuario);
        if (!$curso) {
            header("Location: index.php?accion=panel");
            exit();
        }

        $inscriptos_actuales = $this->cursoModel->contarInscriptosAceptados($id_curso);
        $cupo_mostrar = ($curso['cupo'] !== null) ? ($curso['cupo'] + $inscriptos_actuales) : '';

        require_once __DIR__ . '/../../Frontend/views/cursos/editar_curso.php';
    }

    // 5. Borrar (desactivar) curso
    public function borrar() {
        if (isset($_SESSION['id_usuario']) && isset($_GET['id'])) {
            $id_usuario = (int)$_SESSION['id_usuario'];
            $id_curso = (int)$_GET['id'];
            $this->cursoModel->desactivarCurso($id_curso, $id_usuario);
        }
        header("Location: index.php?accion=panel");
        exit();
    }

    // 6. Inscribirse a un curso
    public function inscribirse() {
        if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: index.php");
            exit();
        }

        if ($_SESSION['es_admin'] == 1) {
            header("Location: index.php?error=admin");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_curso = (int)$_POST['id_curso'];

        $resultado = $this->cursoModel->inscribirUsuario($id_curso, $id_usuario);

        if ($resultado === 'ok') {
            header("Location: index.php?exito=1");
        } elseif ($resultado === 'lleno') {
            header("Location: index.php?error=lleno");
        } else {
            header("Location: index.php");
        }
        exit();
    }

    // 7. Dejar un curso
    public function dejarCurso() {
        if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_curso = (int)$_GET['id'];

        if ($this->cursoModel->darDeBajaUsuario($id_curso, $id_usuario)) {
            header("Location: index.php?accion=panel&baja=1");
        } else {
            header("Location: index.php?accion=panel");
        }
        exit();
    }

    // 8. Ver Aula Interna del Curso
    public function verInterno() {
        if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_curso = (int)$_GET['id'];
        $es_admin = (int)$_SESSION['es_admin'];

        $curso = $this->cursoModel->obtenerCursoActivoPorId($id_curso);
        if (!$curso) {
            header("Location: index.php?accion=panel&error=inactivo");
            exit();
        }

        if (!$this->cursoModel->verificarAccesoCurso($id_curso, $id_usuario, $es_admin, $curso['id_creador'])) {
            header("Location: index.php?accion=panel");
            exit();
        }

        $notis = $this->cargarNotificaciones();
        $no_leidas = $notis['no_leidas'];
        $result_noti = $notis['result_noti'];

        $materiales = $this->cursoModel->obtenerMaterialesPorCurso($id_curso);
        $alumnos = ($curso['id_creador'] == $id_usuario) ? $this->cursoModel->obtenerAlumnosInscriptos($id_curso) : null;
        $preguntas_foro = $this->cursoModel->obtenerPreguntasForoPorCurso($id_curso);

        require_once __DIR__ . '/../../Frontend/views/cursos/curso_interno.php';
    }

    // 9. Subir Material de Estudio
    public function subirMaterial() {
        if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_curso = (int)$_POST['id_curso'];
        $nombre_archivo = trim($_POST['nombre_archivo']);

        if (!$this->cursoModel->esCreadorCurso($id_curso, $id_usuario)) {
            header("Location: index.php?accion=panel");
            exit();
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $cod = $_FILES['archivo']['error'] ?? 'sin_archivo';
            header("Location: index.php?accion=curso_interno&id=$id_curso&error=subida_$cod");
            exit();
        }

        $tamanio_bytes = $_FILES['archivo']['size'];
        if ($tamanio_bytes > 5242880) {
            header("Location: index.php?accion=curso_interno&id=$id_curso&error=tamanio");
            exit();
        }

        $tamanio_mb = round($tamanio_bytes / 1048576, 2);
        $extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $nombre_unico = "mat_" . $id_curso . "_" . time() . "_" . uniqid() . "." . $extension;

        $carpeta_destino = "uploads/";
        if (!is_dir($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }
        $ruta_destino = $carpeta_destino . $nombre_unico;

        if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
            header("Location: index.php?accion=curso_interno&id=$id_curso&error=mover");
            exit();
        }

        if (!$this->cursoModel->agregarMaterial($id_curso, $nombre_archivo, $nombre_unico, $tamanio_mb)) {
            if (file_exists($ruta_destino)) {
                unlink($ruta_destino);
            }
            header("Location: index.php?accion=curso_interno&id=$id_curso&error=bd");
            exit();
        }

        header("Location: index.php?accion=curso_interno&id=$id_curso&exito=1");
        exit();
    }

    // 10. Borrar Material de Estudio
    public function borrarMaterial() {
        if (!isset($_SESSION['id_usuario']) || !isset($_GET['id_mat']) || !isset($_GET['id_curso'])) {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_material = (int)$_GET['id_mat'];
        $id_curso = (int)$_GET['id_curso'];

        if ($this->cursoModel->esCreadorCurso($id_curso, $id_usuario)) {
            $material = $this->cursoModel->obtenerMaterialPorIdYCurso($id_material, $id_curso);

            if ($material) {
                $ruta_fisica = "uploads/" . $material['ruta_archivo'];
                if (file_exists($ruta_fisica)) {
                    unlink($ruta_fisica);
                }

                $this->cursoModel->eliminarMaterial($id_material);
                header("Location: index.php?accion=curso_interno&id=$id_curso&borrado=1");
                exit();
            }
        }

        header("Location: index.php?accion=curso_interno&id=$id_curso");
        exit();
    }
}