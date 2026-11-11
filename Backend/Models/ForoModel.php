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
                                          ORDER BY r.es_destacada DESC, r.fecha_creacion ASC");
        $stmt->bind_param("i", $id_pregunta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
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

    public function crearRespuesta($id_pregunta, $id_usuario, $cuerpo, $es_destacada) {
        $stmt = $this->conexion->prepare("INSERT INTO respuestas (id_pregunta, id_usuario, cuerpo, es_destacada) 
                                          VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $id_pregunta, $id_usuario, $cuerpo, $es_destacada);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
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

    public function crearNotificacion($id_usuario_destino, $mensaje, $enlace) {
        $stmt = $this->conexion->prepare("INSERT INTO notificaciones (id_usuario, mensaje, enlace, leida) 
                                          VALUES (?, ?, ?, 0)");
        $stmt->bind_param("iss", $id_usuario_destino, $mensaje, $enlace);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    // --- ACCIONES DE MODERACIÓN ---

    public function cerrarPregunta($id_pregunta, $id_usuario) {
        $stmt = $this->conexion->prepare("UPDATE preguntas p 
                                          JOIN Cursos c ON p.id_curso = c.id_curso 
                                          SET p.cerrada = 1 
                                          WHERE p.id_pregunta = ? AND c.id_creador = ?");
        $stmt->bind_param("ii", $id_pregunta, $id_usuario);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    public function alternarDestacadaRespuesta($id_respuesta, $id_usuario) {
        $stmt = $this->conexion->prepare("UPDATE respuestas r 
                                          JOIN preguntas p ON r.id_pregunta = p.id_pregunta 
                                          JOIN Cursos c ON p.id_curso = c.id_curso 
                                          SET r.es_destacada = NOT r.es_destacada 
                                          WHERE r.id_respuesta = ? AND c.id_creador = ?");
        $stmt->bind_param("ii", $id_respuesta, $id_usuario);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    public function eliminarPregunta($id_pregunta, $id_usuario) {
        $stmt_check = $this->conexion->prepare("SELECT p.id_curso 
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

        $id_curso = (int)$fila['id_curso'];

        $stmt_resp = $this->conexion->prepare("DELETE FROM respuestas WHERE id_pregunta = ?");
        $stmt_resp->bind_param("i", $id_pregunta);
        $stmt_resp->execute();
        $stmt_resp->close();

        $stmt_preg = $this->conexion->prepare("DELETE FROM preguntas WHERE id_pregunta = ?");
        $stmt_preg->bind_param("i", $id_pregunta);
        $stmt_preg->execute();
        $stmt_preg->close();

        return $id_curso;
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