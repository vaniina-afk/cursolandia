<?php
// Buscar notificaciones del usuario
$sql_notificaciones = "SELECT * FROM notificaciones WHERE id_usuario = $id_usuario ORDER BY fecha DESC LIMIT 5";
$result_noti = $conexion->query($sql_notificaciones);

// Contar cuántas están sin leer
$sql_no_leidas = "SELECT COUNT(*) as sin_leer FROM notificaciones WHERE id_usuario = $id_usuario AND leida = 0";
$no_leidas = $conexion->query($sql_no_leidas)->fetch_assoc()['sin_leer'];

// Evitamos el error de "Cannot redeclare function" si se incluye más de una vez
if (!function_exists('tiempo_transcurrido')) {
    function tiempo_transcurrido($fecha) {
        $diferencia = time() - strtotime($fecha);
        if ($diferencia < 60) return "Hace instantes";
        if ($diferencia < 3600) return "Hace " . floor($diferencia / 60) . " min";
        if ($diferencia < 86400) return "Hace " . floor($diferencia / 3600) . " hs";
        return "Hace " . floor($diferencia / 86400) . " días";
    }
}
?>