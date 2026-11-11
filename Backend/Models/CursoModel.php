<?php
class CursoModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Trae todos los cursos activos junto con el nombre y apellido del creador
    public function obtenerCursosActivos() {
        $sql = "SELECT c.*, u.nombre, u.apellido 
                FROM Cursos c 
                JOIN Usuarios u ON c.id_creador = u.id_usuario 
                WHERE c.activo = 1 
                ORDER BY c.fecha_inicio DESC";
        return $this->conexion->query($sql);
    }

    // Devuelve un array con los IDs de los cursos en los que el usuario ya está inscripto
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
}