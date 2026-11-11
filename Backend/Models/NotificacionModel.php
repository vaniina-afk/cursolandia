<?php
class NotificacionModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Traer todas las notificaciones del usuario (para el historial completo)
    public function obtenerTodasPorUsuario($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT * FROM notificaciones WHERE id_usuario = ? ORDER BY fecha DESC");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    // Traer las últimas N notificaciones (para la campanita del navbar)
    public function obtenerUltimasPorUsuario($id_usuario, $limite = 5) {
        $stmt = $this->conexion->prepare("SELECT * FROM notificaciones WHERE id_usuario = ? ORDER BY fecha DESC LIMIT ?");
        $stmt->bind_param("ii", $id_usuario, $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    // Contar cuántas notificaciones sin leer tiene el usuario
    public function contarNoLeidas($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT COUNT(*) as sin_leer FROM notificaciones WHERE id_usuario = ? AND leida = 0");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $total = (int)$stmt->get_result()->fetch_assoc()['sin_leer'];
        $stmt->close();

        return $total;
    }

    // Verificar pertenencia, marcar como leída y devolver el enlace de destino
    public function leerYMarcar($id_notificacion, $id_usuario) {
        $stmt = $this->conexion->prepare("SELECT enlace FROM notificaciones WHERE id_notificacion = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $id_notificacion, $id_usuario);
        $stmt->execute();
        $noti = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$noti) {
            return false;
        }

        $stmt_upd = $this->conexion->prepare("UPDATE notificaciones SET leida = 1 WHERE id_notificacion = ?");
        $stmt_upd->bind_param("i", $id_notificacion);
        $stmt_upd->execute();
        $stmt_upd->close();

        return $noti['enlace'];
    }
}

if (!function_exists('tiempo_transcurrido')) {
    function tiempo_transcurrido($fecha) {
        $diferencia = time() - strtotime($fecha);
        if ($diferencia < 60) return "Hace instantes";
        if ($diferencia < 3600) return "Hace " . floor($diferencia / 60) . " min";
        if ($diferencia < 86400) return "Hace " . floor($diferencia / 3600) . " hs";
        return "Hace " . floor($diferencia / 86400) . " días";
    }
}