<?php
require_once __DIR__ . '/../Models/CursoModel.php';

class CursoController {
    private $cursoModel;
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->cursoModel = new CursoModel($conexion);
    }

    // Muestra la pantalla principal (Inicio)
    public function index() {
        // 1. Pedimos los cursos al Modelo
        $resultado_cursos = $this->cursoModel->obtenerCursosActivos();
        $inscripciones = [];

        // 2. Si hay sesión activa, buscamos sus inscripciones y notificaciones
        if (isset($_SESSION['id_usuario'])) {
            $id_usuario = (int)$_SESSION['id_usuario'];
            $inscripciones = $this->cursoModel->obtenerIdsCursosInscriptos($id_usuario);

            // Puente temporal para que siga funcionando la campanita hasta migrar Notificaciones
            $conexion = $this->conexion;
            if (file_exists(__DIR__ . '/../../includes/logica_notificaciones.php')) {
                require_once __DIR__ . '/../../includes/logica_notificaciones.php';
            }
        }

        // 3. Llamamos a la Vista (ya tiene acceso a $resultado_cursos e $inscripciones)
        require_once __DIR__ . '/../../Frontend/views/home.php';
    }
}