<?php
class CursoModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // --- MÉTODOS PARA EL CATÁLOGO (INICIO) ---
    public function obtenerCursosActivos($id_usuario = 0, $busqueda = '', $id_etiqueta = 0) {
        $sql = "SELECT c.*, u.nombre, u.apellido, (u.plan_pro = 1 AND (u.vencimiento_pro IS NULL OR u.vencimiento_pro >= CURDATE())) AS pro_vigente,
                       GROUP_CONCAT(DISTINCT e.nombre ORDER BY e.nombre SEPARATOR ', ') AS lista_etiquetas,
                       COUNT(DISTINCT ui.id_interes) AS coincidencias_interes
                FROM Cursos c
                JOIN Usuarios u ON c.id_creador = u.id_usuario
                LEFT JOIN curso_etiqueta ce ON c.id_curso = ce.id_curso
                LEFT JOIN etiquetas e ON ce.id_etiqueta = e.id_etiqueta
                LEFT JOIN intereses i ON LOWER(TRIM(e.nombre)) = LOWER(TRIM(i.nombre))
                LEFT JOIN usuario_interes ui ON i.id_interes = ui.id_interes AND ui.id_usuario = ?
                WHERE c.activo = 1";

        $tipos = "i";
        $params = [$id_usuario];

        if ($busqueda !== '') {
            $sql .= " AND c.titulo LIKE ?";
            $tipos .= "s";
            $params[] = '%' . $busqueda . '%';
        }

        if ($id_etiqueta > 0) {
            $sql .= " AND EXISTS (
                        SELECT 1 FROM curso_etiqueta ce2 
                        WHERE ce2.id_curso = c.id_curso AND ce2.id_etiqueta = ?
                      )";
            $tipos .= "i";
            $params[] = $id_etiqueta;
        }

        $sql .= " GROUP BY c.id_curso
                  ORDER BY pro_vigente DESC, coincidencias_interes DESC, c.id_curso DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param($tipos, ...$params);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    public function obtenerEstadosInscripcionesUsuario($id_usuario) {
        $estados = [];
        $stmt = $this->conexion->prepare("SELECT id_curso, estado FROM Inscripciones WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($fila = $resultado->fetch_assoc()) {
            $estados[(int)$fila['id_curso']] = $fila['estado'];
        }
        $stmt->close();

        return $estados;
    }

    // --- MÉTODOS PARA EL PANEL ---

    public function obtenerCursosInscritosPorUsuario($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT c.id_curso, c.titulo, c.activo, i.estado, u.nombre, u.apellido 
                                          FROM Inscripciones i 
                                          JOIN Cursos c ON i.id_curso = c.id_curso 
                                          JOIN Usuarios u ON c.id_creador = u.id_usuario 
                                          WHERE i.id_usuario = ? AND i.estado IN ('Aceptado', 'Pendiente')
                                          ORDER BY FIELD(i.estado, 'Aceptado', 'Pendiente'), i.fecha_inscripcion DESC");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    public function obtenerCursosCreadosPorUsuario($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT c.id_curso, c.titulo, c.cupo, c.fecha_inicio, c.es_privado, c.costo,
                                                 (SELECT COUNT(*) FROM Inscripciones i WHERE i.id_curso = c.id_curso AND i.estado = 'Pendiente') AS solicitudes_pendientes
                                          FROM Cursos c
                                          WHERE c.id_creador = ? AND c.activo = 1 
                                          ORDER BY c.fecha_inicio DESC");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    // --- MÉTODOS PARA CREAR, EDITAR Y BORRAR CURSOS ---

    public function esUsuarioPro($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT plan_pro, vencimiento_pro FROM Usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$fila || (int)$fila['plan_pro'] === 0) {
            return 0;
        }
        if (!empty($fila['vencimiento_pro']) && $fila['vencimiento_pro'] < date('Y-m-d')) {
            return 0;
        }
        return 1;
    }

    public function contarCursosActivosCreados($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT COUNT(*) as total FROM Cursos WHERE id_creador = ? AND activo = 1");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $total = (int)$stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        return $total;
    }

    public function crearCurso($id_creador, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $es_privado = 0) {
        $stmt = $this->conexion->prepare("INSERT INTO Cursos (id_creador, titulo, descripcion, costo, cupo, fecha_inicio, fecha_fin, es_privado) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdissi", $id_creador, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $es_privado);
        $exito = $stmt->execute();
        $id_nuevo = $exito ? $this->conexion->insert_id : false;
        $stmt->close();

        return $id_nuevo;
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

    public function actualizarCurso($id_curso, $id_creador, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $es_privado = 0) {
        $stmt = $this->conexion->prepare("UPDATE Cursos 
                                          SET titulo = ?, descripcion = ?, costo = ?, cupo = ?, fecha_inicio = ?, fecha_fin = ?, es_privado = ? 
                                          WHERE id_curso = ? AND id_creador = ?");
        $stmt->bind_param("ssdissiii", $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $es_privado, $id_curso, $id_creador);
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
        // 1. Verificar si ya existe una inscripción o solicitud previa
        $stmt_check = $this->conexion->prepare("SELECT id_inscripcion FROM Inscripciones WHERE id_curso = ? AND id_usuario = ?");
        $stmt_check->bind_param("ii", $id_curso, $id_usuario);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $stmt_check->close();
            return 'ya_inscripto';
        }
        $stmt_check->close();

        // 2. Obtener datos del curso (cupo, costo, es_privado, creador, título)
        $stmt_curso = $this->conexion->prepare("SELECT titulo, id_creador, costo, cupo, es_privado FROM Cursos WHERE id_curso = ? AND activo = 1");
        $stmt_curso->bind_param("i", $id_curso);
        $stmt_curso->execute();
        $curso_data = $stmt_curso->get_result()->fetch_assoc();
        $stmt_curso->close();

        if (!$curso_data || ($curso_data['cupo'] !== null && $curso_data['cupo'] <= 0)) {
            return 'lleno';
        }

        $requiere_aprobacion = ((int)$curso_data['es_privado'] === 1 || ($curso_data['costo'] !== null && (float)$curso_data['costo'] > 0));

        // 3A. Si es privado o pago: queda en estado 'Pendiente' y se notifica al creador
        if ($requiere_aprobacion) {
            $stmt_ins = $this->conexion->prepare("INSERT INTO Inscripciones (id_curso, id_usuario, estado) VALUES (?, ?, 'Pendiente')");
            $stmt_ins->bind_param("ii", $id_curso, $id_usuario);
            $exito = $stmt_ins->execute();
            $stmt_ins->close();

            if ($exito) {
                $stmt_al = $this->conexion->prepare("SELECT nombre, apellido FROM Usuarios WHERE id_usuario = ?");
                $stmt_al->bind_param("i", $id_usuario);
                $stmt_al->execute();
                $al = $stmt_al->get_result()->fetch_assoc();
                $stmt_al->close();

                $nombre_alumno = $al ? ($al['nombre'] . ' ' . $al['apellido']) : 'Un alumno';
                $tipo_texto = ((int)$curso_data['es_privado'] === 1) ? 'curso privado' : 'curso pago';
                $mensaje = "📩 $nombre_alumno solicitó inscribirse a tu $tipo_texto '" . $curso_data['titulo'] . "'.";
                $enlace = "index.php?accion=curso_interno&id=" . $id_curso;
                $id_creador = (int)$curso_data['id_creador'];

                $stmt_noti = $this->conexion->prepare("INSERT INTO notificaciones (id_usuario, mensaje, enlace, leida) VALUES (?, ?, ?, 0)");
                $stmt_noti->bind_param("iss", $id_creador, $mensaje, $enlace);
                $stmt_noti->execute();
                $stmt_noti->close();

                return 'pendiente';
            }
            return 'error';
        }

        // 3B. Si es público y gratuito: inscripción directa 'Aceptado' con transacción ACID
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
        $stmt_check = $this->conexion->prepare("SELECT id_inscripcion, estado FROM Inscripciones WHERE id_curso = ? AND id_usuario = ?");
        $stmt_check->bind_param("ii", $id_curso, $id_usuario);
        $stmt_check->execute();
        $fila = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if (!$fila) {
            return false;
        }

        $stmt_del = $this->conexion->prepare("DELETE FROM Inscripciones WHERE id_curso = ? AND id_usuario = ?");
        $stmt_del->bind_param("ii", $id_curso, $id_usuario);
        $stmt_del->execute();
        $stmt_del->close();

        // Solo devolvemos el cupo si el alumno estaba Aceptado (las pendientes aún no ocupaban cupo)
        if ($fila['estado'] === 'Aceptado') {
            $stmt_cupo = $this->conexion->prepare("UPDATE Cursos SET cupo = cupo + 1 WHERE id_curso = ? AND cupo IS NOT NULL");
            $stmt_cupo->bind_param("i", $id_curso);
            $stmt_cupo->execute();
            $stmt_cupo->close();
        }

        return true;
    }

    public function obtenerSolicitudesPendientesCurso($id_curso) {
        $stmt = $this->conexion->prepare("SELECT i.id_inscripcion, i.id_usuario, i.fecha_inscripcion, u.nombre, u.apellido, u.email, u.imagen_perfil 
                                          FROM Inscripciones i
                                          JOIN Usuarios u ON i.id_usuario = u.id_usuario
                                          WHERE i.id_curso = ? AND i.estado = 'Pendiente'
                                          ORDER BY i.fecha_inscripcion ASC");
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    public function responderSolicitudInscripcion($id_inscripcion, $id_curso, $id_creador, $decision) {
        // Verificar que el curso pertenezca al creador y traer datos de la solicitud
        $stmt_check = $this->conexion->prepare("SELECT c.titulo, c.cupo, i.id_usuario 
                                                FROM Inscripciones i
                                                JOIN Cursos c ON i.id_curso = c.id_curso
                                                WHERE i.id_inscripcion = ? AND i.id_curso = ? AND c.id_creador = ? AND i.estado = 'Pendiente'");
        $stmt_check->bind_param("iii", $id_inscripcion, $id_curso, $id_creador);
        $stmt_check->execute();
        $datos = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if (!$datos) {
            return 'error';
        }

        $id_alumno = (int)$datos['id_usuario'];
        $titulo_curso = $datos['titulo'];

        if ($decision === 'aceptar') {
            if ($datos['cupo'] !== null && (int)$datos['cupo'] <= 0) {
                return 'lleno';
            }

            $this->conexion->begin_transaction();
            try {
                $stmt_upd = $this->conexion->prepare("UPDATE Inscripciones SET estado = 'Aceptado' WHERE id_inscripcion = ?");
                $stmt_upd->bind_param("i", $id_inscripcion);
                $stmt_upd->execute();
                $stmt_upd->close();

                if ($datos['cupo'] !== null) {
                    $stmt_cupo = $this->conexion->prepare("UPDATE Cursos SET cupo = cupo - 1 WHERE id_curso = ?");
                    $stmt_cupo->bind_param("i", $id_curso);
                    $stmt_cupo->execute();
                    $stmt_cupo->close();
                }

                $mensaje = "🎉 ¡Tu solicitud de inscripción al curso '$titulo_curso' fue aceptada! Ya podés ingresar al aula.";
                $enlace = "index.php?accion=curso_interno&id=" . $id_curso;
                $stmt_noti = $this->conexion->prepare("INSERT INTO notificaciones (id_usuario, mensaje, enlace, leida) VALUES (?, ?, ?, 0)");
                $stmt_noti->bind_param("iss", $id_alumno, $mensaje, $enlace);
                $stmt_noti->execute();
                $stmt_noti->close();

                $this->conexion->commit();
                return 'aceptado';
            } catch (mysqli_sql_exception $e) {
                $this->conexion->rollback();
                return 'error';
            }
        } elseif ($decision === 'rechazar') {
            $stmt_rec = $this->conexion->prepare("UPDATE Inscripciones SET estado = 'Rechazado' WHERE id_inscripcion = ?");
            $stmt_rec->bind_param("i", $id_inscripcion);
            $stmt_rec->execute();
            $stmt_rec->close();

            $mensaje = "Tu solicitud de inscripción al curso '$titulo_curso' fue rechazada por el profesor.";
            $enlace = "index.php";
            $stmt_noti = $this->conexion->prepare("INSERT INTO notificaciones (id_usuario, mensaje, enlace, leida) VALUES (?, ?, ?, 0)");
            $stmt_noti->bind_param("iss", $id_alumno, $mensaje, $enlace);
            $stmt_noti->execute();
            $stmt_noti->close();

            return 'rechazado';
        }

        return 'error';
    }

    // --- MÉTODOS PARA EL AULA INTERNA Y MATERIALES ---

    public function obtenerCursoActivoPorId($id_curso) {
        $stmt = $this->conexion->prepare("SELECT c.*, u.nombre AS prof_nombre, u.apellido AS prof_apellido 
                                          FROM Cursos c 
                                          JOIN Usuarios u ON c.id_creador = u.id_usuario 
                                          WHERE c.id_curso = ? AND c.activo = 1");
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $curso = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $curso;
    }

    public function verificarAccesoCurso($id_curso, $id_usuario, $es_admin, $id_creador) {
        if ($id_creador == $id_usuario || $es_admin == 1) {
            return true;
        }

        $stmt = $this->conexion->prepare("SELECT id_inscripcion FROM Inscripciones 
                                          WHERE id_curso = ? AND id_usuario = ? AND estado = 'Aceptado'");
        $stmt->bind_param("ii", $id_curso, $id_usuario);
        $stmt->execute();
        $tiene_acceso = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $tiene_acceso;
    }

    public function obtenerMaterialesPorCurso($id_curso) {
        $stmt = $this->conexion->prepare("SELECT * FROM Materiales WHERE id_curso = ?");
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    public function obtenerAlumnosInscriptos($id_curso) {
        $stmt = $this->conexion->prepare("SELECT u.nombre, u.apellido, u.email, u.imagen_perfil 
                                          FROM Inscripciones i
                                          JOIN Usuarios u ON i.id_usuario = u.id_usuario
                                          WHERE i.id_curso = ? AND i.estado = 'Aceptado'");
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    public function obtenerPreguntasForoPorCurso($id_curso) {
        $stmt = $this->conexion->prepare("SELECT p.*, u.nombre, u.apellido 
                                          FROM preguntas p 
                                          JOIN Usuarios u ON p.id_usuario = u.id_usuario 
                                          WHERE p.id_curso = ? 
                                          ORDER BY p.fecha_creacion DESC");
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    public function esCreadorCurso($id_curso, $id_usuario) {
        $stmt = $this->conexion->prepare("SELECT id_curso FROM Cursos WHERE id_curso = ? AND id_creador = ?");
        $stmt->bind_param("ii", $id_curso, $id_usuario);
        $stmt->execute();
        $es_creador = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $es_creador;
    }

    public function agregarMaterial($id_curso, $nombre_archivo, $ruta_archivo, $tamanio_mb) {
        $stmt = $this->conexion->prepare("INSERT INTO Materiales (id_curso, nombre_archivo, ruta_archivo, tamanio_mb) 
                                          VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issd", $id_curso, $nombre_archivo, $ruta_archivo, $tamanio_mb);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    public function obtenerMaterialPorIdYCurso($id_material, $id_curso) {
        $stmt = $this->conexion->prepare("SELECT ruta_archivo FROM Materiales WHERE id_material = ? AND id_curso = ?");
        $stmt->bind_param("ii", $id_material, $id_curso);
        $stmt->execute();
        $material = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $material;
    }

    public function eliminarMaterial($id_material) {
        $stmt = $this->conexion->prepare("DELETE FROM Materiales WHERE id_material = ?");
        $stmt->bind_param("i", $id_material);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    // --- MÉTODOS PARA ETIQUETAS DE CURSOS ---

    public function obtenerTodasLasEtiquetas() {
        $sql = "SELECT id_etiqueta, nombre FROM etiquetas ORDER BY nombre ASC";
        return $this->conexion->query($sql);
    }

    public function obtenerIdsEtiquetasPorCurso($id_curso) {
        $ids = [];
        $stmt = $this->conexion->prepare("SELECT id_etiqueta FROM curso_etiqueta WHERE id_curso = ?");
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($fila = $res->fetch_assoc()) {
            $ids[] = (int)$fila['id_etiqueta'];
        }
        $stmt->close();
        return $ids;
    }

    public function guardarEtiquetasCurso($id_curso, $ids_etiquetas = [], $nuevas_etiquetas_texto = '') {
        // 1. Limpiamos asignaciones previas
        $stmt_del = $this->conexion->prepare("DELETE FROM curso_etiqueta WHERE id_curso = ?");
        $stmt_del->bind_param("i", $id_curso);
        $stmt_del->execute();
        $stmt_del->close();

        $ids_finales = array_map('intval', $ids_etiquetas);

        // 2. Si el profesor escribió etiquetas nuevas separadas por coma, las creamos si no existen
        if (trim($nuevas_etiquetas_texto) !== '') {
            $partes = explode(',', $nuevas_etiquetas_texto);
            foreach ($partes as $nombre_raw) {
                $nombre = trim($nombre_raw);
                if ($nombre === '') continue;

                $stmt_busca = $this->conexion->prepare("SELECT id_etiqueta FROM etiquetas WHERE LOWER(nombre) = LOWER(?)");
                $stmt_busca->bind_param("s", $nombre);
                $stmt_busca->execute();
                $existente = $stmt_busca->get_result()->fetch_assoc();
                $stmt_busca->close();

                if ($existente) {
                    $ids_finales[] = (int)$existente['id_etiqueta'];
                } else {
                    $stmt_ins_et = $this->conexion->prepare("INSERT INTO etiquetas (nombre) VALUES (?)");
                    $stmt_ins_et->bind_param("s", $nombre);
                    if ($stmt_ins_et->execute()) {
                        $ids_finales[] = (int)$this->conexion->insert_id;
                    }
                    $stmt_ins_et->close();
                }
            }
        }

        // 3. Insertamos en la tabla intermedia curso_etiqueta sin duplicados
        $ids_finales = array_unique($ids_finales);
        if (!empty($ids_finales)) {
            $stmt_ce = $this->conexion->prepare("INSERT INTO curso_etiqueta (id_curso, id_etiqueta) VALUES (?, ?)");
            foreach ($ids_finales as $id_et) {
                if ($id_et > 0) {
                    $stmt_ce->bind_param("ii", $id_curso, $id_et);
                    $stmt_ce->execute();
                }
            }
            $stmt_ce->close();
        }
    }
}