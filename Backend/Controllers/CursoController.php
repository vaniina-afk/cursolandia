<?php
require_once __DIR__ . '/../Models/CursoModel.php';

class CursoController {
    private $cursoModel;
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->cursoModel = new CursoModel($conexion);
    }

    // Puente temporal para la campanita de notificaciones
    private function cargarNotificaciones() {
        $conexion = $this->conexion;
        if (isset($_SESSION['id_usuario'])) {
            $id_usuario = (int)$_SESSION['id_usuario'];
            if (file_exists(__DIR__ . '/../../includes/logica_notificaciones.php')) {
                require_once __DIR__ . '/../../includes/logica_notificaciones.php';
            }
        }
        return [
            'no_leidas' => $no_leidas ?? 0,
            'result_noti' => $result_noti ?? null
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
}