<?php
require_once __DIR__ . '/../Models/CursoModel.php';

class CursoController {
    private $cursoModel;
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->cursoModel = new CursoModel($conexion);
    }

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
        $id_usuario = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;
        $busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $etiqueta_filtro = isset($_GET['etiqueta']) ? (int)$_GET['etiqueta'] : 0;
        $acceso_filtro = isset($_GET['acceso']) ? trim($_GET['acceso']) : '';
        $precio_filtro = isset($_GET['precio']) ? trim($_GET['precio']) : '';

        $resultado_cursos = $this->cursoModel->obtenerCursosActivos($id_usuario, $busqueda, $etiqueta_filtro, $acceso_filtro, $precio_filtro);
        $etiquetas_filtro = $this->cursoModel->obtenerTodasLasEtiquetas();
        $inscripciones = [];
        $limite_inscripciones = false;
        $no_leidas = 0;
        $result_noti = null;

        if ($id_usuario > 0) {
            $inscripciones = $this->cursoModel->obtenerEstadosInscripcionesUsuario($id_usuario);
            $es_pro = $this->cursoModel->esUsuarioPro($id_usuario);
            $_SESSION['plan_pro'] = $es_pro;

            if ($es_pro == 0 && $this->cursoModel->contarInscripcionesActivasUsuario($id_usuario) >= 3) {
                $limite_inscripciones = true;
            }

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
        $_SESSION['plan_pro'] = $es_pro;
        $total_cursos = $this->cursoModel->contarCursosActivosCreados($id_usuario);

        $puede_crear = true;
        if ($total_cursos >= 3 && $es_pro == 0) {
            $puede_crear = false;
            $mensaje = "<div class='alert alert-warning p-3 mb-3 text-center shadow-sm'><strong>¡Límite alcanzado!</strong> Ya tenés 3 cursos activos. Para publicar más, necesitás pasarte al plan PRO.</div>";
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $puede_crear) {
            $titulo = trim($_POST['titulo']);
            $descripcion = trim($_POST['descripcion']);
            $costo = ($_POST['costo'] !== '') ? (float)$_POST['costo'] : null;
            $cupo = ($_POST['cupo'] !== '') ? (int)$_POST['cupo'] : null;
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];
            $es_privado = isset($_POST['es_privado']) ? (int)$_POST['es_privado'] : 0;

            if ($fecha_fin < $fecha_inicio) {
                $mensaje = "<div class='alert alert-danger p-2 small mb-3'>La fecha de fin no puede ser anterior a la de inicio.</div>";
            } elseif ($fecha_fin < date('Y-m-d')) {
                $mensaje = "<div class='alert alert-danger p-2 small mb-3'>La fecha de fin no puede ser anterior a hoy.</div>";
            } else {
                $id_curso_nuevo = $this->cursoModel->crearCurso($id_usuario, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $es_privado);
                
                if ($id_curso_nuevo) {
                    $ids_etiquetas = $_POST['etiquetas'] ?? [];
                    $nuevas_etiquetas = $_POST['nuevas_etiquetas'] ?? '';
                    $this->cursoModel->guardarEtiquetasCurso($id_curso_nuevo, $ids_etiquetas, $nuevas_etiquetas);

                    $total_cursos++;
                    if ($total_cursos >= 3 && $es_pro == 0) {
                        $puede_crear = false;
                        $mensaje = "<div class='alert alert-success p-2 small mb-2 auto-ocultar'>¡Curso creado con éxito!</div>
                                    <div class='alert alert-warning p-3 text-center shadow-sm'><strong>Has alcanzado el límite de 3 cursos activos.</strong> Pasate a PRO para seguir creando.</div>";
                    } else {
                        $restantes = ($es_pro == 1) ? "ilimitados (Plan PRO)" : (3 - $total_cursos) . " cupos gratuitos";
                        $mensaje = "<div class='alert alert-success p-2 small mb-3 auto-ocultar'>¡Curso creado con éxito! Te quedan $restantes.</div>";
                    }
                } else {
                    $mensaje = "<div class='alert alert-danger p-2 small mb-3'>Error al guardar el curso.</div>";
                }
            }
        }

        $etiquetas_disponibles = $this->cursoModel->obtenerTodasLasEtiquetas();
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
            $es_privado = isset($_POST['es_privado']) ? (int)$_POST['es_privado'] : 0;

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
                    if ($this->cursoModel->actualizarCurso($id_curso, $id_usuario, $titulo, $descripcion, $costo, $cupo_final, $fecha_inicio, $fecha_fin, $es_privado)) {
                        $ids_etiquetas = $_POST['etiquetas'] ?? [];
                        $nuevas_etiquetas = $_POST['nuevas_etiquetas'] ?? '';
                        $this->cursoModel->guardarEtiquetasCurso($id_curso, $ids_etiquetas, $nuevas_etiquetas);

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

        $etiquetas_disponibles = $this->cursoModel->obtenerTodasLasEtiquetas();
        $etiquetas_curso = $this->cursoModel->obtenerIdsEtiquetasPorCurso($id_curso);

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
        } elseif ($resultado === 'pendiente') {
            header("Location: index.php?pendiente=1");
        } elseif ($resultado === 'limite_pro') {
            header("Location: index.php?error=limite_pro");
        } elseif ($resultado === 'lleno') {
            header("Location: index.php?error=lleno");
        } elseif ($resultado === 'no_iniciado') {
            header("Location: index.php?error=no_iniciado");
        } elseif ($resultado === 'finalizado') {
            header("Location: index.php?error=finalizado");
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
        $resumen_materiales = $this->cursoModel->obtenerResumenMaterialesCurso($id_curso);
        $creador_es_pro = ($curso['id_creador'] == $id_usuario) ? ($this->cursoModel->esUsuarioPro($id_usuario) == 1) : false;

        $alumnos = ($curso['id_creador'] == $id_usuario) ? $this->cursoModel->obtenerAlumnosInscriptos($id_curso) : null;
        $solicitudes_pendientes = ($curso['id_creador'] == $id_usuario) ? $this->cursoModel->obtenerSolicitudesPendientesCurso($id_curso) : null;
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
        $id_curso = isset($_POST['id_curso']) ? (int)$_POST['id_curso'] : (isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0);

        if ($id_curso <= 0 || !$this->cursoModel->esCreadorCurso($id_curso, $id_usuario)) {
            header("Location: index.php?accion=panel");
            exit();
        }

        if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
            header("Location: index.php?accion=curso_interno&id=$id_curso&error=post_max");
            exit();
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $cod = $_FILES['archivo']['error'] ?? 'sin_archivo';
            if ($cod == UPLOAD_ERR_INI_SIZE || $cod == UPLOAD_ERR_FORM_SIZE) {
                header("Location: index.php?accion=curso_interno&id=$id_curso&error=post_max");
            } else {
                header("Location: index.php?accion=curso_interno&id=$id_curso&error=subida_$cod");
            }
            exit();
        }

        $es_pro = ($this->cursoModel->esUsuarioPro($id_usuario) == 1);
        $nombre_archivo = trim($_POST['nombre_archivo']);
        $tamanio_bytes = $_FILES['archivo']['size'];
        $tamanio_mb = round($tamanio_bytes / 1048576, 2);

        if (!$es_pro) {
            if ($tamanio_bytes > 5242880) {
                header("Location: index.php?accion=curso_interno&id=$id_curso&error=tamanio");
                exit();
            }

            $resumen = $this->cursoModel->obtenerResumenMaterialesCurso($id_curso);

            if ($resumen['cantidad'] >= 5) {
                header("Location: index.php?accion=curso_interno&id=$id_curso&error=limite_archivos");
                exit();
            }

            if (($resumen['total_mb'] + $tamanio_mb) > 10) {
                header("Location: index.php?accion=curso_interno&id=$id_curso&error=limite_total");
                exit();
            }
        }

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

    // 11. Aceptar o rechazar solicitud de inscripción (Solo creador)
    public function gestionarInscripcion() {
        if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_curso = (int)$_POST['id_curso'];
        $id_inscripcion = (int)$_POST['id_inscripcion'];
        $decision = $_POST['decision'] ?? '';

        $res = $this->cursoModel->responderSolicitudInscripcion($id_inscripcion, $id_curso, $id_usuario, $decision);

        if ($res === 'aceptado') {
            header("Location: index.php?accion=curso_interno&id=$id_curso&inscripcion=aceptada");
        } elseif ($res === 'rechazado') {
            header("Location: index.php?accion=curso_interno&id=$id_curso&inscripcion=rechazada");
        } elseif ($res === 'lleno') {
            header("Location: index.php?accion=curso_interno&id=$id_curso&error=cupo_lleno");
        } else {
            header("Location: index.php?accion=curso_interno&id=$id_curso&error=1");
        }
        exit();
    }
}