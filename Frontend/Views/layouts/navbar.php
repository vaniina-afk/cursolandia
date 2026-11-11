<nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background-color: var(--color-primario);">
    <div class="container d-flex justify-content-between">
        <a class="navbar-brand fw-bold" href="index.php">Cursolandia</a>
        
        <div class="d-flex align-items-center gap-3">
            <?php if (isset($_SESSION['id_usuario'])): ?>
                
                <!-- DROPDOWN DE NOTIFICACIONES -->
                <div class="dropdown me-2">
                    <a href="#" class="text-white text-decoration-none position-relative" id="campanita" data-bs-toggle="dropdown" aria-expanded="false">
                        <span style="font-size: 1.2rem;">🔔</span>
                        <?php if (isset($no_leidas) && $no_leidas > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                <?php echo $no_leidas; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="campanita" style="width: 320px; max-height: 400px; overflow-y: auto; z-index: 1050;">
                        <li><h6 class="dropdown-header fw-bold text-dark border-bottom pb-2">Notificaciones</h6></li>
                        
                        <?php if (isset($result_noti) && $result_noti->num_rows > 0): ?>
                            <?php while($noti = $result_noti->fetch_assoc()): ?>
                                <li>
                                    <a class="dropdown-item py-3 border-bottom text-wrap <?php echo ($noti['leida'] == 0) ? 'bg-light' : ''; ?>" href="leer_notificacion.php?id=<?php echo $noti['id_notificacion']; ?>">
                                        <div class="d-flex flex-column gap-1">
                                            <span class="small" style="line-height: 1.2;"><?php echo htmlspecialchars($noti['mensaje']); ?></span>
                                            <small class="text-primario fw-bold" style="font-size: 0.7rem;">
                                                ⏱️ <?php echo tiempo_transcurrido($noti['fecha']); ?>
                                            </small>
                                        </div>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                            <li><a class="dropdown-item text-center small text-muted py-2" href="notificaciones.php">Ver todas</a></li>
                        <?php else: ?>
                            <li><span class="dropdown-item text-muted small text-center py-3">No tenés notificaciones nuevas.</span></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- FIN NOTIFICACIONES -->

                <!-- Saludo y Botones de Panel/Salir -->
                <div class="d-flex align-items-center">
                    <div class="dropdown me-3 d-none d-md-inline">
                        <a href="#" class="text-white text-decoration-none dropdown-toggle fw-semibold" id="perfilDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="perfilDropdown">
                            <li>
                                <a class="dropdown-item py-2 fw-semibold text-secondary" href="index.php?accion=editar_perfil">
                                    ⚙️ Mi Perfil
                                </a>
                            </li>
                        </ul>
                    </div>

                    <a href="panel.php" class="btn btn-outline-light btn-sm me-2">Mi Panel</a>
                    <a href="index.php?accion=logout" class="btn btn-acento btn-sm">Salir</a>
                </div>

            <?php else: ?>
                <div class="d-flex align-items-center">
                    <a href="index.php?accion=login" class="btn btn-outline-light btn-sm me-2 fw-bold">Iniciar Sesión</a>
                    <a href="index.php?accion=registro" class="btn btn-acento btn-sm fw-bold">Registrarse</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>