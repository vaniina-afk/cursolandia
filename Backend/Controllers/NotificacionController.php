<?php
require_once __DIR__ . '/../Models/NotificacionModel.php';

class NotificacionController {
    private $notificacionModel;

    public function __construct($conexion) {
        $this->notificacionModel = new NotificacionModel($conexion);
    }

    // 1. Mostrar historial completo de notificaciones
    public function index() {
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: index.php");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];

        // Datos para la campanita del navbar
        $no_leidas = $this->notificacionModel->contarNoLeidas($id_usuario);
        $result_noti = $this->notificacionModel->obtenerUltimasPorUsuario($id_usuario, 5);

        // Listado completo para el cuerpo de la vista
        $notificaciones = $this->notificacionModel->obtenerTodasPorUsuario($id_usuario);

        require_once __DIR__ . '/../../Frontend/views/notificaciones/notificaciones.php';
    }

    // 2. Marcar notificación como leída y redirigir
    public function leer() {
        if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
            header("Location: index.php?accion=panel");
            exit();
        }

        $id_noti = (int)$_GET['id'];
        $id_usuario = (int)$_SESSION['id_usuario'];

        $enlace = $this->notificacionModel->leerYMarcar($id_noti, $id_usuario);

        if ($enlace) {
            // Compatibilidad por si quedaron notificaciones viejas en la BD antes de MVC
            if (strpos($enlace, 'pregunta_foro.php?id=') === 0) {
                $enlace = str_replace('pregunta_foro.php?id=', 'index.php?accion=pregunta_foro&id=', $enlace);
            }
            header("Location: " . $enlace);
        } else {
            header("Location: index.php?accion=panel");
        }
        exit();
    }
}