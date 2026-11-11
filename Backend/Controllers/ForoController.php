<?php
require_once __DIR__ . '/../Models/ForoModel.php';

class ForoController {
    private $foroModel;
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->foroModel = new ForoModel($conexion);
    }

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

    // 1. Ver el hilo de una pregunta y sus respuestas
    public function verPregunta() {
        if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_pregunta = (int)$_GET['id'];

        $pregunta = $this->foroModel->obtenerPreguntaPorId($id_pregunta);
        if (!$pregunta) {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_curso = (int)$pregunta['id_curso'];
        $es_creador = ($id_usuario == $pregunta['id_creador']);
        $es_autor_pregunta = ($id_usuario == $pregunta['id_usuario']);

        $notis = $this->cargarNotificaciones();
        $no_leidas = $notis['no_leidas'];
        $result_noti = $notis['result_noti'];

        $respuestas = $this->foroModel->obtenerRespuestasPorPregunta($id_pregunta);

        require_once __DIR__ . '/../../Frontend/views/foro/pregunta_foro.php';
    }

    // 2. Publicar nueva pregunta
    public function crearPregunta() {
        if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_curso = (int)$_POST['id_curso'];
        $titulo = trim($_POST['titulo']);
        $cuerpo = trim($_POST['cuerpo']);

        try {
            $id_pregunta_nueva = $this->foroModel->crearPregunta($id_curso, $id_usuario, $titulo, $cuerpo);

            if ($id_pregunta_nueva) {
                $curso = $this->foroModel->obtenerDatosCurso($id_curso);
                $id_creador = (int)$curso['id_creador'];

                if ($id_creador != $id_usuario) {
                    $nombre_completo = $this->foroModel->obtenerNombreCompletoUsuario($id_usuario);
                    $mensaje_noti = "$nombre_completo publicó una nueva consulta en tu curso '" . $curso['titulo'] . "'.";
                    $enlace = "index.php?accion=pregunta_foro&id=$id_pregunta_nueva";
                    $this->foroModel->crearNotificacion($id_creador, $mensaje_noti, $enlace);
                }

                header("Location: index.php?accion=curso_interno&id=$id_curso&foro=pregunta_ok");
                exit();
            }
        } catch (mysqli_sql_exception $e) {
            header("Location: index.php?accion=curso_interno&id=$id_curso&foro=error");
            exit();
        }

        header("Location: index.php?accion=curso_interno&id=$id_curso&foro=error");
        exit();
    }

    // 3. Publicar respuesta a una pregunta
    public function crearRespuesta() {
        if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_pregunta = (int)$_POST['id_pregunta'];
        $id_curso = (int)$_POST['id_curso'];
        $cuerpo = trim($_POST['cuerpo']);

        $curso = $this->foroModel->obtenerDatosCurso($id_curso);
        $id_creador = (int)$curso['id_creador'];
        $es_destacada = 0;

        try {
            $this->foroModel->crearRespuesta($id_pregunta, $id_usuario, $cuerpo, $es_destacada);

            $nombre_completo = $this->foroModel->obtenerNombreCompletoUsuario($id_usuario);
            $autor = $this->foroModel->obtenerAutorPregunta($id_pregunta);
            $enlace = "index.php?accion=pregunta_foro&id=$id_pregunta";

            if ($autor != $id_usuario) {
                $mensaje_autor = "$nombre_completo ha respondido a tu consulta en el foro.";
                $this->foroModel->crearNotificacion($autor, $mensaje_autor, $enlace);
            }

            if ($id_creador != $id_usuario && $id_creador != $autor) {
                $mensaje_profe = "$nombre_completo ha dejado una respuesta en el foro de tu curso.";
                $this->foroModel->crearNotificacion($id_creador, $mensaje_profe, $enlace);
            }

            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta");
        } catch (mysqli_sql_exception $e) {
            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta&error=1");
        }
        exit();
    }

    // 4. Cerrar pregunta (solo creador del curso)
    public function cerrarPregunta() {
        if (isset($_SESSION['id_usuario']) && isset($_GET['id'])) {
            $id_usuario = (int)$_SESSION['id_usuario'];
            $id_pregunta = (int)$_GET['id'];
            $this->foroModel->cerrarPregunta($id_pregunta, $id_usuario);
            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta");
            exit();
        }
        header("Location: index.php?accion=panel");
        exit();
    }

    // 5. Destacar / quitar destacado a respuesta (solo creador del curso)
    public function destacarRespuesta() {
        if (isset($_SESSION['id_usuario']) && isset($_GET['id']) && isset($_GET['id_pregunta'])) {
            $id_usuario = (int)$_SESSION['id_usuario'];
            $id_respuesta = (int)$_GET['id'];
            $id_pregunta = (int)$_GET['id_pregunta'];
            $this->foroModel->alternarDestacadaRespuesta($id_respuesta, $id_usuario);
            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta");
            exit();
        }
        header("Location: index.php?accion=panel");
        exit();
    }

    // 6. Eliminar pregunta (autor o creador del curso)
    public function eliminarPregunta() {
        if (isset($_SESSION['id_usuario']) && isset($_GET['id'])) {
            $id_usuario = (int)$_SESSION['id_usuario'];
            $id_pregunta = (int)$_GET['id'];
            $id_curso_volver = $this->foroModel->eliminarPregunta($id_pregunta, $id_usuario);

            if ($id_curso_volver) {
                header("Location: index.php?accion=curso_interno&id=$id_curso_volver&mensaje=borrado");
                exit();
            }
            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta");
            exit();
        }
        header("Location: index.php?accion=panel");
        exit();
    }

    // 7. Eliminar respuesta (autor de la respuesta o creador del curso)
    public function eliminarRespuesta() {
        if (isset($_SESSION['id_usuario']) && isset($_GET['id']) && isset($_GET['id_pregunta'])) {
            $id_usuario = (int)$_SESSION['id_usuario'];
            $id_respuesta = (int)$_GET['id'];
            $id_pregunta = (int)$_GET['id_pregunta'];
            $this->foroModel->eliminarRespuesta($id_respuesta, $id_usuario);
            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta");
            exit();
        }
        header("Location: index.php?accion=panel");
        exit();
    }
}