# Cursolandia.com

Portal web 2.0 orientado a la creación, gestión e inscripción de cursos dictados por usuarios de la comunidad. Desarrollado como **Trabajo Integrador** para las asignaturas **Ingeniería de Software** y **Programación III** de la **Tecnicatura Universitaria en Web** (Universidad Nacional de San Luis).

**Autora:** Vanina Belén Muñoz

---

## Tecnologías Utilizadas

* **Backend:** PHP 8+ nativo (arquitectura MVC orientada a objetos, sin frameworks externos).
* **Base de Datos:** MySQL / MariaDB (consultas preparadas con extensión `MySQLi`, modelo normalizado en 3FN, integridad referencial y transacciones ACID).
* **Frontend:** HTML5, CSS3 personalizado, Bootstrap 5 (vía CDN) y JavaScript puro.

---

## Estructura del Proyecto

```text
cursolandia/
├── Backend/
│   ├── Config/
│   │   └── conexion.php              # Configuración de conexión a MySQL
│   ├── Controllers/                  # Controladores MVC (Auth, Curso, Foro, Notificacion, Usuario)
│   └── Models/                       # Modelos de acceso a datos y reglas de negocio
├── Frontend/
│   └── views/                        # Vistas organizadas por módulo (auth, cursos, foro, perfil, layouts)
├── Resources/
│   └── uploads/                      # Fotos de perfil y comprobantes de solicitud Plan PRO
├── uploads/                          # Archivos adjuntos de material de estudio de los cursos
├── cursolandia.sql                   # Copia de seguridad de la BD (estructura + datos de prueba)
├── index.php                         # Front Controller / Enrutador principal
└── README.md                         # Documentación del proyecto
```

---

## Despliegue en Entorno Local (XAMPP)

1. **Clonar o descargar el repositorio** dentro de la carpeta pública de XAMPP:
   ```text
   C:\xampp\htdocs\cursolandia
   ```
2. **Iniciar servicios:** Abrir el Panel de Control de XAMPP e iniciar **Apache** y **MySQL**.
3. **Crear e importar la base de datos:**
   * Ingresar a [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
   * Crear una nueva base de datos llamada `cursolandia` (cotejamiento `utf8mb4_unicode_ci`).
   * Seleccionar la base `cursolandia`, ir a la pestaña **Importar**, elegir el archivo `cursolandia.sql` ubicado en la raíz del proyecto y ejecutar la importación.
4. **Verificar configuración de conexión:**
   Revisar que el archivo `Backend/Config/conexion.php` contenga las credenciales locales por defecto:
   ```php
   $host = "localhost";
   $usuario = "root";
   $password = "";
   $base_datos = "cursolandia";
   ```
5. **Permisos de carpetas:** Verificar que los directorios `uploads/` y `Resources/uploads/` existan y tengan permisos de escritura para la carga de materiales, avatares y comprobantes.
6. **Ejecutar la aplicación:** Abrir en el navegador [http://localhost/cursolandia/](http://localhost/cursolandia/).

---

## Usuarios de Prueba

La base de datos incluida en `cursolandia.sql` cuenta con usuarios precargados para probar todos los roles y reglas de negocio del sistema. **La contraseña para todas las cuentas es `123456`:**

| Rol en el Sistema | Nombre y Apellido | Correo Electrónico | Contraseña |
| :--- | :--- | :--- | :--- |
| **Administrador** | Admin Sistema | `admin@cursolandia.com` | `123456` |
| **Docente / Usuario PRO** | Vaniina Muñoz | `vaniinabelen@gmail.com` | `123456` |
| **Usuario Gratuito (Alumno/Docente)** | Alumno Prueba | `alumno@mail.com` | `123456` |
| **Usuario Gratuito (Alumno/Docente)** | Alumno2 Prueba | `alumno2@mail.com` | `123456` |
| **Usuario Gratuito (Alumno)** | Usuario Vista | `usuario@mail.com` | `123456` |

---

## Funcionalidades Principales Implementadas

* **Gestión de Usuarios y Roles:** Registro con selección/creación de intereses, inicio de sesión con contraseñas encriptadas (`password_hash`), edición de perfil, antecedentes, avatar y cambio de contraseña.
* **Planes Gratuito y PRO:** Solicitud de actualización a Plan PRO adjuntando comprobante de pago; panel de administración para aprobar con fecha de vencimiento, rechazar o revocar suscripciones.
* **Gestión de Cursos:** Creación, edición y baja lógica de cursos gratuitos o pagos, públicos o privados, con etiquetas, cupos opcionales y rango de vigencia (`fecha_inicio` a `fecha_fin`). Límite de 3 cursos activos creados e inscriptos para cuentas gratuitas (ilimitado en Plan PRO).
* **Catálogo Inteligente:** Búsqueda por título y filtros combinables por etiqueta, tipo de acceso (público/privado) y precio (gratis/pago). Priorización visual de cursos vigentes, destacados de creadores PRO y recomendaciones por coincidencia entre intereses del usuario y etiquetas del curso.
* **Aula Interna y Materiales:** Subida y descarga de material de estudio con control de cuotas para cuentas gratuitas (máximo 5 archivos, 5 MB por archivo y 10 MB totales por curso) y almacenamiento sin límites para usuarios PRO. Gestión de solicitudes pendientes en cursos pagos o privados.
* **Foro y Notificaciones:** Creación de consultas, respuestas con bloqueo de doble respuesta consecutiva, edición de respuestas propias con etiqueta `(editado)`, aceptación de respuesta y cierre de tema por docente o autor de la pregunta, y notificaciones automáticas ante cada evento.

---

## Informe de Problemas Encontrados y Soluciones Aplicadas

1. **Control de concurrencia en inscripciones con cupo limitado:**
   * *Problema:* Si dos alumnos intentaban inscribirse simultáneamente al último lugar disponible de un curso, podía producirse una condición de carrera y quedar un cupo negativo.
   * *Solución:* Se encapsuló el alta de inscripción y el descuento de cupo dentro de transacciones ACID (`begin_transaction()`, `commit()`, `rollback()`) en `CursoModel`, respaldadas por restricciones `CHECK` en la base de datos.
2. **Restricciones diferenciadas por plan en la tabla de materiales:**
   * *Problema:* Una restricción `CHECK (tamanio_mb <= 5)` definida directamente en MySQL bloqueaba las subidas de archivos mayores a 5 MB incluso para usuarios con Plan PRO.
   * *Solución:* Se ajustó la restricción de integridad en SQL a `tamanio_mb > 0` y se implementó la validación diferencial por plan tanto en el cliente (JavaScript) como en el servidor (`CursoController::subirMaterial()`), aplicando los topes de 5 archivos, 5 MB individuales y 10 MB totales únicamente a cuentas gratuitas.
3. **Manejo de archivos que exceden el límite del servidor (`post_max_size`):**
   * *Problema:* Al intentar subir archivos que superaban la configuración `post_max_size` de `php.ini`, PHP descartaba los arreglos `$_POST` y `$_FILES`, provocando que el controlador perdiera el `id_curso` y redirigiera al panel general. * *Solución:* Se ampliaron los parámetros en `php.ini`, se envió `id_curso` como parámetro auxiliar en el `action` del formulario y se agregó detección explícita de `$_SERVER['CONTENT_LENGTH']` para devolver un mensaje de error claro dentro del aula.
4. **Desfasaje de zona horaria entre PHP y MySQL:**
   * *Problema:* La configuración regional por defecto de PHP en XAMPP (`Europe/Berlin`) generaba diferencias horarias nocturnas respecto a la fecha local de MySQL (`CURDATE()`), afectando el ordenamiento de cursos finalizados y vencimientos PRO.
   * *Solución:* Se configuró `date_default_timezone_set('America/Argentina/Buenos_Aires')` en `index.php` y se unificó la fecha de referencia enviada desde PHP en las consultas SQL.
5. **Reglas de turno y notificaciones en el foro:**
   * *Problema:* Se requería impedir que un mismo usuario publicara dos respuestas seguidas y garantizar notificaciones diferenciadas según cada acción del hilo.
   * *Solución:* Se implementó la consulta de la última respuesta cronológica (`obtenerUltimaRespuesta()`) para bloquear el formulario de nueva respuesta cuando el último autor coincide con el usuario en sesión, habilitando en su lugar un modal de edición que marca el mensaje como `(editado)`.
6. **Gestión de rutas de archivos multimedia:**
   * *Problema:* La mezcla entre materiales académicos y fotos de perfil generaba rutas inconsistentes e íconos rotos en perfiles con avatar por defecto.
   * *Solución:* Se separaron los directorios (`uploads/` para materiales y `Resources/uploads/` para avatares y comprobantes) y se incorporó validación física con `file_exists()` para mostrar un avatar generado con la inicial del usuario cuando no posee imagen cargada.