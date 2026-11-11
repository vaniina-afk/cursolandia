<?php
class CursoModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // --- MÉTODOS PARA EL CATÁLOGO (INICIO) ---

    public function obtenerCursosActivos() {
        $sql = "SELECT c.*, u.nombre, u.apellido 
                FROM Cursos c 
                JOIN Usuarios u ON c.id_creador = u.id_usuario 
                WHERE c.activo = 1 
                ORDER BY c.fecha_inicio DESC";
        return $this->conexion->query($sql);
    }

    public function obtenerIdsCursosInscriptos($id_usuario) {
        $inscripciones = [];
        $stmt = $this->conexion->prepare("SELECT id_curso FROM Inscripciones WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($fila = $resultado->fetch_assoc()) {
            $inscripciones[] = $fila['id_curso'];
        }
        $stmt->close();

        return $inscripciones;
    }

    // --- MÉTODOS PARA EL PANEL ---

    public function obtenerCursosInscritosPorUsuario($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT c.id_curso, c.titulo, c.activo, u.nombre, u.apellido 
                                          FROM Inscripciones i 
                                          JOIN Cursos c ON i.id_curso = c.id_curso 
                                          JOIN Usuarios u ON c.id_creador = u.id_usuario 
                                          WHERE i.id_usuario = ? AND i.estado = 'Aceptado'");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    public function obtenerCursosCreadosPorUsuario($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT id_curso, titulo, cupo, fecha_inicio 
                                          FROM Cursos 
                                          WHERE id_creador = ? AND activo = 1 
                                          ORDER BY fecha_inicio DESC");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    // --- MÉTODOS PARA CREAR, EDITAR Y BORRAR CURSOS ---

    public function esUsuarioPro($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT plan_pro FROM Usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila ? (int)$fila['plan_pro'] : 0;
    }

    public function contarCursosActivosCreados($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT COUNT(*) as total FROM Cursos WHERE id_creador = ? AND activo = 1");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $total = (int)$stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        return $total;
    }

    public function crearCurso($id_creador, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin) {
        $stmt = $this->conexion->prepare("INSERT INTO Cursos (id_creador, titulo, descripcion, costo, cupo, fecha_inicio, fecha_fin) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdiss", $id_creador, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    public function obtenerCursoPorIdYCreador($id_curso, $id_creador) {
        $stmt = $this->conexion->prepare("SELECT * FROM Cursos WHERE id_curso = ? AND id_creador = ? AND activo = 1");
        $stmt->bind_param("ii", $id_curso, $id_creador);
        $stmt->execute();
        $curso = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $curso;
    }

    public function contarInscriptosAceptados($id_curso) {
        $stmt = $this->conexion->prepare("SELECT COUNT(*) as total FROM Inscripciones WHERE id_curso = ? AND estado = 'Aceptado'");
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $total = (int)$stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        return $total;
    }

    public function actualizarCurso($id_curso, $id_creador, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin) {
        $stmt = $this->conexion->prepare("UPDATE Cursos 
                                          SET titulo = ?, descripcion = ?, costo = ?, cupo = ?, fecha_inicio = ?, fecha_fin = ? 
                                          WHERE id_curso = ? AND id_creador = ?");
        $stmt->bind_param("ssdissii", $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $id_curso, $id_creador);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    public function desactivarCurso($id_curso, $id_creador) {
        $stmt = $this->conexion->prepare("UPDATE Cursos SET activo = 0 WHERE id_curso = ? AND id_creador = ?");
        $stmt->bind_param("ii", $id_curso, $id_creador);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    // --- MÉTODOS PARA INSCRIPCIONES Y BAJAS ---

    public function inscribirUsuario($id_curso, $id_usuario) {
        // 1. Verificar si ya está inscripto
        $stmt_check = $this->conexion->prepare("SELECT id_inscripcion FROM Inscripciones WHERE id_curso = ? AND id_usuario = ?");
        $stmt_check->bind_param("ii", $id_curso, $id_usuario);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $stmt_check->close();
            return 'ya_inscripto';
        }
        $stmt_check->close();

        // 2. Verificar cupo disponible
        $stmt_cupo = $this->conexion->prepare("SELECT cupo FROM Cursos WHERE id_curso = ?");
        $stmt_cupo->bind_param("i", $id_curso);
        $stmt_cupo->execute();
        $curso_data = $stmt_cupo->get_result()->fetch_assoc();
        $stmt_cupo->close();

        if (!$curso_data || ($curso_data['cupo'] !== null && $curso_data['cupo'] <= 0)) {
            return 'lleno';
        }

        // 3. Transacción ACID
        $this->conexion->begin_transaction();
        try {
            $stmt_ins = $this->conexion->prepare("INSERT INTO Inscripciones (id_curso, id_usuario, estado) VALUES (?, ?, 'Aceptado')");
            $stmt_ins->bind_param("ii", $id_curso, $id_usuario);
            $stmt_ins->execute();
            $stmt_ins->close();

            if ($curso_data['cupo'] !== null) {
                $stmt_upd = $this->conexion->prepare("UPDATE Cursos SET cupo = cupo - 1 WHERE id_curso = ?");
                $stmt_upd->bind_param("i", $id_curso);
                $stmt_upd->execute();
                $stmt_upd->close();
            }

            $this->conexion->commit();
            return 'ok';
        } catch (mysqli_sql_exception $e) {
            $this->conexion->rollback();
            return 'lleno';
        }
    }

    public function darDeBajaUsuario($id_curso, $id_usuario) {
        $stmt_check = $this->conexion->prepare("SELECT id_inscripcion FROM Inscripciones WHERE id_curso = ? AND id_usuario = ?");
        $stmt_check->bind_param("ii", $id_curso, $id_usuario);
        $stmt_check->execute();
        $existe = $stmt_check->get_result()->num_rows > 0;
        $stmt_check->close();

        if (!$existe) {
            return false;
        }

        $stmt_del = $this->conexion->prepare("DELETE FROM Inscripciones WHERE id_curso = ? AND id_usuario = ?");
        $stmt_del->bind_param("ii", $id_curso, $id_usuario);
        $stmt_del->execute();
        $stmt_del->close();

        $stmt_cupo = $this->conexion->prepare("UPDATE Cursos SET cupo = cupo + 1 WHERE id_curso = ? AND cupo IS NOT NULL");
        $stmt_cupo->bind_param("i", $id_curso);
        $stmt_cupo->execute();
        $stmt_cupo->close();

        return true;
    }
}