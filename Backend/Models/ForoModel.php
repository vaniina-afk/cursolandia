<?php
class ForoModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerPreguntaPorId($id_pregunta) {
        $stmt = $this->conexion->prepare("SELECT p.*, u.nombre, u.apellido, u.imagen_perfil, c.titulo AS curso_titulo, c.id_creador 
                                          FROM preguntas p 
                                          JOIN Usuarios u ON p.id_usuario = u.id_usuario 
                                          JOIN Cursos c ON p.id_curso = c.id_curso
                                          WHERE p.id_pregunta = ?");
        $stmt->bind_param("i", $id_pregunta);
        $stmt->execute();
        $pregunta = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $pregunta;
    }

    public function obtenerRespuestasPorPregunta($id_pregunta) {
        $stmt = $this->conexion->prepare("SELECT r.*, u.nombre, u.apellido, u.imagen_perfil 
                                          FROM respuestas r 
                                          JOIN Usuarios u ON r.id_usuario = u.id_usuario 
                                          WHERE r.id_pregunta = ? 
                                          ORDER BY r.es_destacada DESC, r.fecha_creacion ASC, r.id_respuesta ASC");
        $stmt->bind_param("i", $id_pregunta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    // Devuelve la última respuesta cronológica para validar la regla de no responder 2 veces seguidas
    public function obtenerUltimaRespuesta($id_pregunta) {
        $stmt = $this->conexion->prepare("SELECT id_respuesta, id_usuario, cuerpo 
                                          FROM respuestas 
                                          WHERE id_pregunta = ? 
                                          ORDER BY fecha_creacion DESC, id_respuesta DESC 
                                          LIMIT 1");
        $stmt->bind_param("i", $id_pregunta);
        $stmt->execute();
        $ultima = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $ultima;
    }

    public function crearPregunta($id_curso, $id_usuario, $titulo, $cuerpo) {
        $estado = 'Abierta';
        $stmt = $this->conexion->prepare("INSERT INTO preguntas (id_curso, id_usuario, titulo, cuerpo, estado) 
                                          VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $id_curso, $id_usuario, $titulo, $cuerpo, $estado);
        $exito = $stmt->execute();
        $id_nueva = $exito ? $this->conexion->insert_id : false;
        $stmt->close();

        return $id_nueva;
    }

    public function crearRespuesta($id_pregunta, $id_usuario, $cuerpo, $es_destacada = 0) {
        $stmt = $this->conexion->prepare("INSERT INTO respuestas (id_pregunta, id_usuario, cuerpo, es_destacada) 
                                          VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $id_pregunta, $id_usuario, $cuerpo, $es_destacada);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    // Edita el texto de una respuesta propia mientras el tema siga abierto
    public function editarRespuesta($id_respuesta, $id_usuario, $cuerpo) {
        $stmt = $this->conexion->prepare("UPDATE respuestas r
                                          JOIN preguntas p ON r.id_pregunta = p.id_pregunta
                                          SET r.cuerpo = ?, r.editada = 1
                                          WHERE r.id_respuesta = ? AND r.id_usuario = ? AND p.cerrada = 0");
        $stmt->bind_param("sii", $cuerpo, $id_respuesta, $id_usuario);
        $exito = $stmt->execute();
        $afectadas = $stmt->affected_rows;
        $stmt->close();

        return ($exito && $afectadas >= 0);
    }

    public function obtenerDatosCurso($id_curso) {
        $stmt = $this->conexion->prepare("SELECT id_creador, titulo FROM Cursos WHERE id_curso = ?");
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $curso = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $curso;
    }

    public function obtenerAutorPregunta($id_pregunta) {
        $stmt = $this->conexion->prepare("SELECT id_usuario FROM preguntas WHERE id_pregunta = ?");
        $stmt->bind_param("i", $id_pregunta);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila ? (int)$fila['id_usuario'] : 0;
    }

    public function obtenerNombreCompletoUsuario($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT nombre, apellido FROM Usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila ? ($fila['nombre'] . ' ' . $fila['apellido']) : 'Un usuario';
    }

    // Devuelve todos los IDs de usuarios del curso (creador + alumnos aceptados)
    public function obtenerMiembrosCurso($id_curso) {
        $ids = [];

        $stmt1 = $this->conexion->prepare("SELECT id_creador FROM Cursos WHERE id_curso = ?");
        $stmt1->bind_param("i", $id_curso);
        $stmt1->execute();
        $curso = $stmt1->get_result()->fetch_assoc();
        $stmt1->close();
        if ($curso) {
            $ids[] = (int)$curso['id_creador'];
        }

        $stmt2 = $this->conexion->prepare("SELECT id_usuario FROM Inscripciones WHERE id_curso = ? AND estado = 'Aceptado'");
        $stmt2->bind_param("i", $id_curso);
        $stmt2->execute();
        $res = $stmt2->get_result();
        while ($fila = $res->fetch_assoc()) {
            $ids[] = (int)$fila['id_usuario'];
        }
        $stmt2->close();

        return array_unique($ids);
    }

    // Devuelve todos los participantes de una pregunta (creador de la pregunta + quienes respondieron)
    public function obtenerParticipantesPregunta($id_pregunta) {
        $ids = [];

        $autor = $this->obtenerAutorPregunta($id_pregunta);
        if ($autor > 0) {
            $ids[] = $autor;
        }

        $stmt = $this->conexion->prepare("SELECT DISTINCT id_usuario FROM respuestas WHERE id_pregunta = ?");
        $stmt->bind_param("i", $id_pregunta);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($fila = $res->fetch_assoc()) {
            $ids[] = (int)$fila['id_usuario'];
        }
        $stmt->close();

        return array_unique($ids);
    }

    public function crearNotificacion($id_usuario_destino, $mensaje, $enlace) {
        $stmt = $this->conexion->prepare("INSERT INTO notificaciones (id_usuario, mensaje, enlace, leida) 
                                          VALUES (?, ?, ?, 0)");
        $stmt->bind_param("iss", $id_usuario_destino, $mensaje, $enlace);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    // --- ACCIONES DE MODERACIÓN ---

    // Cierra la pregunta (permitido para el docente creador del curso O el autor de la pregunta)
    public function cerrarPregunta($id_pregunta, $id_usuario) {
        $stmt = $this->conexion->prepare("UPDATE preguntas p 
                                          JOIN Cursos c ON p.id_curso = c.id_curso 
                                          SET p.cerrada = 1 
                                          WHERE p.id_pregunta = ? AND (c.id_creador = ? OR p.id_usuario = ?)");
        $stmt->bind_param("iii", $id_pregunta, $id_usuario, $id_usuario);
        $exito = $stmt->execute();
        $afectadas = $stmt->affected_rows;
        $stmt->close();

        return ($exito && $afectadas > 0);
    }

    // Marca una respuesta como "Aceptada" (destacada) y cierra el tema (permitido para docente O autor de la pregunta)
    public function aceptarYDestacarRespuesta($id_respuesta, $id_usuario) {
        $stmt_check = $this->conexion->prepare("SELECT r.id_pregunta, r.id_usuario AS id_autor_respuesta, p.titulo, p.cerrada, p.id_usuario AS id_autor_pregunta, c.id_creador
                                                FROM respuestas r
                                                JOIN preguntas p ON r.id_pregunta = p.id_pregunta
                                                JOIN Cursos c ON p.id_curso = c.id_curso
                                                WHERE r.id_respuesta = ? AND (c.id_creador = ? OR p.id_usuario = ?)");
        $stmt_check->bind_param("iii", $id_respuesta, $id_usuario, $id_usuario);
        $stmt_check->execute();
        $datos = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if (!$datos) {
            return false;
        }

        $id_pregunta = (int)$datos['id_pregunta'];

        // Limpiamos cualquier destacada previa de esa pregunta y marcamos la elegida
        $stmt_limpiar = $this->conexion->prepare("UPDATE respuestas SET es_destacada = 0 WHERE id_pregunta = ?");
        $stmt_limpiar->bind_param("i", $id_pregunta);
        $stmt_limpiar->execute();
        $stmt_limpiar->close();

        $stmt_dest = $this->conexion->prepare("UPDATE respuestas SET es_destacada = 1 WHERE id_respuesta = ?");
        $stmt_dest->bind_param("i", $id_respuesta);
        $stmt_dest->execute();
        $stmt_dest->close();

        // Marcamos la pregunta como Resuelta y Cerrada
        $stmt_cerrar = $this->conexion->prepare("UPDATE preguntas SET estado = 'Resuelta', cerrada = 1 WHERE id_pregunta = ?");
        $stmt_cerrar->bind_param("i", $id_pregunta);
        $stmt_cerrar->execute();
        $stmt_cerrar->close();

        return [
            'id_pregunta' => $id_pregunta,
            'id_autor_respuesta' => (int)$datos['id_autor_respuesta'],
            'titulo' => $datos['titulo']
        ];
    }

    // Devuelve el autor de la respuesta destacada de una pregunta (si existe)
    public function obtenerAutorRespuestaDestacada($id_pregunta) {
        $stmt = $this->conexion->prepare("SELECT id_usuario FROM respuestas WHERE id_pregunta = ? AND es_destacada = 1 LIMIT 1");
        $stmt->bind_param("i", $id_pregunta);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila ? (int)$fila['id_usuario'] : 0;
    }

    public function eliminarPregunta($id_pregunta, $id_usuario) {
        $stmt_check = $this->conexion->prepare("SELECT p.id_curso, p.id_usuario AS id_autor_pregunta, p.titulo, c.titulo AS curso_titulo 
                                                FROM preguntas p 
                                                JOIN Cursos c ON p.id_curso = c.id_curso 
                                                WHERE p.id_pregunta = ? AND (p.id_usuario = ? OR c.id_creador = ?)");
        $stmt_check->bind_param("iii", $id_pregunta, $id_usuario, $id_usuario);
        $stmt_check->execute();
        $fila = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if (!$fila) {
            return false;
        }

        $stmt_resp = $this->conexion->prepare("DELETE FROM respuestas WHERE id_pregunta = ?");
        $stmt_resp->bind_param("i", $id_pregunta);
        $stmt_resp->execute();
        $stmt_resp->close();

        $stmt_preg = $this->conexion->prepare("DELETE FROM preguntas WHERE id_pregunta = ?");
        $stmt_preg->bind_param("i", $id_pregunta);
        $stmt_preg->execute();
        $stmt_preg->close();

        return [
            'id_curso' => (int)$fila['id_curso'],
            'id_autor_pregunta' => (int)$fila['id_autor_pregunta'],
            'titulo' => $fila['titulo'],
            'curso_titulo' => $fila['curso_titulo']
        ];
    }

    public function eliminarRespuesta($id_respuesta, $id_usuario) {
        $stmt = $this->conexion->prepare("DELETE r FROM respuestas r 
                                          JOIN preguntas p ON r.id_pregunta = p.id_pregunta 
                                          JOIN Cursos c ON p.id_curso = c.id_curso 
                                          WHERE r.id_respuesta = ? AND (r.id_usuario = ? OR c.id_creador = ?)");
        $stmt->bind_param("iii", $id_respuesta, $id_usuario, $id_usuario);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }
}