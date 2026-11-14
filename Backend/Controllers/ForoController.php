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
        $ultima_respuesta = $this->foroModel->obtenerUltimaRespuesta($id_pregunta);
        $es_ultimo_en_responder = ($ultima_respuesta && (int)$ultima_respuesta['id_usuario'] === $id_usuario);

        require_once __DIR__ . '/../../Frontend/views/foro/pregunta_foro.php';
    }

    // 2. Publicar nueva pregunta -> Notifica a TODOS los miembros del curso
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
                $nombre_completo = $this->foroModel->obtenerNombreCompletoUsuario($id_usuario);
                $miembros = $this->foroModel->obtenerMiembrosCurso($id_curso);
                $enlace = "index.php?accion=pregunta_foro&id=$id_pregunta_nueva";
                $mensaje_noti = "💬 $nombre_completo publicó una nueva consulta en '" . $curso['titulo'] . "': \"$titulo\".";

                foreach ($miembros as $id_miembro) {
                    if ($id_miembro !== $id_usuario) {
                        $this->foroModel->crearNotificacion($id_miembro, $mensaje_noti, $enlace);
                    }
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

    // 3. Publicar respuesta -> Valida que no responda 2 veces seguidas y notifica a participantes
    public function crearRespuesta() {
        if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_pregunta = (int)$_POST['id_pregunta'];
        $cuerpo = trim($_POST['cuerpo']);

        $pregunta = $this->foroModel->obtenerPreguntaPorId($id_pregunta);
        if (!$pregunta || (int)$pregunta['cerrada'] === 1) {
            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta&error=cerrada");
            exit();
        }

        // Regla: Un usuario no puede responder 2 veces seguidas una pregunta
        $ultima_respuesta = $this->foroModel->obtenerUltimaRespuesta($id_pregunta);
        if ($ultima_respuesta && (int)$ultima_respuesta['id_usuario'] === $id_usuario) {
            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta&error=doble_respuesta");
            exit();
        }

        try {
            $this->foroModel->crearRespuesta($id_pregunta, $id_usuario, $cuerpo, 0);

            $nombre_completo = $this->foroModel->obtenerNombreCompletoUsuario($id_usuario);
            $participantes = $this->foroModel->obtenerParticipantesPregunta($id_pregunta);
            $enlace = "index.php?accion=pregunta_foro&id=$id_pregunta";
            $mensaje_noti = "💬 $nombre_completo respondió en la consulta '" . $pregunta['titulo'] . "'.";

            foreach ($participantes as $id_part) {
                if ($id_part !== $id_usuario) {
                    $this->foroModel->crearNotificacion($id_part, $mensaje_noti, $enlace);
                }
            }

            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta&exito=respuesta_ok");
        } catch (mysqli_sql_exception $e) {
            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta&error=1");
        }
        exit();
    }

    // 4. Editar respuesta propia -> Marca como (editado)
    public function editarRespuesta() {
        if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_pregunta = (int)$_POST['id_pregunta'];
        $id_respuesta = (int)$_POST['id_respuesta'];
        $cuerpo = trim($_POST['cuerpo']);

        if ($cuerpo !== '' && $this->foroModel->editarRespuesta($id_respuesta, $id_usuario, $cuerpo)) {
            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta&exito=editada");
        } else {
            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta&error=1");
        }
        exit();
    }

    // 5. Cerrar pregunta (docente o creador de la pregunta) -> Notifica a participantes y destacada
    public function cerrarPregunta() {
        if (isset($_SESSION['id_usuario']) && isset($_GET['id'])) {
            $id_usuario = (int)$_SESSION['id_usuario'];
            $id_pregunta = (int)$_GET['id'];

            $pregunta = $this->foroModel->obtenerPreguntaPorId($id_pregunta);
            if ($pregunta && $this->foroModel->cerrarPregunta($id_pregunta, $id_usuario)) {
                $id_destacada = $this->foroModel->obtenerAutorRespuestaDestacada($id_pregunta);
                $participantes = $this->foroModel->obtenerParticipantesPregunta($id_pregunta);
                $enlace = "index.php?accion=pregunta_foro&id=$id_pregunta";

                if ($id_destacada > 0 && $id_destacada !== $id_usuario) {
                    $this->foroModel->crearNotificacion(
                        $id_destacada,
                        "⭐ Se cerró la consulta '" . $pregunta['titulo'] . "' con tu respuesta destacada.",
                        $enlace
                    );
                }

                foreach ($participantes as $id_part) {
                    if ($id_part !== $id_usuario && $id_part !== $id_destacada) {
                        $this->foroModel->crearNotificacion(
                            $id_part,
                            "🔒 La consulta '" . $pregunta['titulo'] . "' en la que participaste fue cerrada.",
                            $enlace
                        );
                    }
                }
            }

            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta&exito=cerrada");
            exit();
        }
        header("Location: index.php?accion=panel");
        exit();
    }

    // 6. Marcar respuesta como Aceptada/Destacada y cerrar tema (docente o creador de la pregunta)
    public function destacarRespuesta() {
        if (isset($_SESSION['id_usuario']) && isset($_GET['id']) && isset($_GET['id_pregunta'])) {
            $id_usuario = (int)$_SESSION['id_usuario'];
            $id_respuesta = (int)$_GET['id'];
            $id_pregunta = (int)$_GET['id_pregunta'];

            $resultado = $this->foroModel->aceptarYDestacarRespuesta($id_respuesta, $id_usuario);
            if ($resultado) {
                $id_autor_resp = $resultado['id_autor_respuesta'];
                $titulo = $resultado['titulo'];
                $participantes = $this->foroModel->obtenerParticipantesPregunta($id_pregunta);
                $enlace = "index.php?accion=pregunta_foro&id=$id_pregunta";

                if ($id_autor_resp !== $id_usuario) {
                    $this->foroModel->crearNotificacion(
                        $id_autor_resp,
                        "⭐ ¡Tu respuesta fue marcada como aceptada y se cerró el tema '$titulo'!",
                        $enlace
                    );
                }

                foreach ($participantes as $id_part) {
                    if ($id_part !== $id_usuario && $id_part !== $id_autor_resp) {
                        $this->foroModel->crearNotificacion(
                            $id_part,
                            "✅ Se aceptó una respuesta y se cerró la consulta '$titulo'.",
                            $enlace
                        );
                    }
                }
            }

            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta&exito=aceptada");
            exit();
        }
        header("Location: index.php?accion=panel");
        exit();
    }

    // 7. Eliminar pregunta (autor o creador del curso) -> Notifica al creador de la pregunta
    public function eliminarPregunta() {
        if (isset($_SESSION['id_usuario']) && isset($_GET['id'])) {
            $id_usuario = (int)$_SESSION['id_usuario'];
            $id_pregunta = (int)$_GET['id'];
            $info = $this->foroModel->eliminarPregunta($id_pregunta, $id_usuario);

            if ($info) {
                $id_curso_volver = $info['id_curso'];
                $id_autor = $info['id_autor_pregunta'];

                if ($id_autor !== $id_usuario) {
                    $this->foroModel->crearNotificacion(
                        $id_autor,
                        "🗑️ Tu consulta '" . $info['titulo'] . "' en el curso '" . $info['curso_titulo'] . "' fue eliminada por el docente.",
                        "index.php?accion=curso_interno&id=$id_curso_volver"
                    );
                }

                header("Location: index.php?accion=curso_interno&id=$id_curso_volver&mensaje=borrado");
                exit();
            }
            header("Location: index.php?accion=pregunta_foro&id=$id_pregunta");
            exit();
        }
        header("Location: index.php?accion=panel");
        exit();
    }

    // 8. Eliminar respuesta (autor de la respuesta o creador del curso)
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