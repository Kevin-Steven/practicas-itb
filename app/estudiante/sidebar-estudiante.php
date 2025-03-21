<?php
function renderSidebarEstudiante($primer_nombre, $primer_apellido, $foto_perfil)
{
    // Detectamos el archivo actual
    $currentFile = basename($_SERVER['PHP_SELF']);

    // Listas de los archivos por fase
    $fase1_files = [
        'for-uno.php' => 'Ficha de Estudiante',
        'for-dos.php' => 'Plan de Aprendizaje',
        'for-tres.php' => 'Carta de Asignación',
        'for-cuatro.php' => 'Perfil de Egreso',
        'for-cinco.php' => 'Carta de Compromiso',
        'for-seis.php' => 'Ficha de Entidad',
        'for-siete.php' => 'Compromiso Ético',
    ];

    $fase2_files = [
        'for-ocho.php' => 'Informe Actividades',
        'for-nueve.php' => 'Evaluación Conductual',
        'for-diez.php' => 'Evaluación Final',
        'for-once.php' => 'Supervisión Académico',
        'for-doce.php' => 'Supervisión Entidad',
        'for-trece.php' => 'Certificado Prácticas',
        'for-catorce.php' => 'Base Legal',
        'for-quince.php' => 'Evidencia de Prácticas',
        'for-diecis.php' => 'Plan de Rotación',
    ];

    // Detectar si está en una página de Fase 1 o Fase 2 para abrir el collapse
    $fase1_open = in_array($currentFile, array_keys($fase1_files)) ? 'show' : '';
    $fase2_open = in_array($currentFile, array_keys($fase2_files)) ? 'show' : '';
    ?>

    <!-- Sidebar -->
    <div class="sidebar z-2" id="sidebar">
        <div class="profile">
            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil">
            <h5><?php echo htmlspecialchars($primer_nombre . ' ' . $primer_apellido); ?></h5>
            <p><?php echo ucfirst(htmlspecialchars($_SESSION['usuario_rol'])); ?></p>
        </div>

        <nav class="nav flex-column">
            <!-- Inicio -->
            <a class="nav-link <?php echo ($currentFile === 'inicio-estudiante.php') ? 'active' : ''; ?>" href="inicio-estudiante.php">
                <i class='bx bx-home-alt'></i> Inicio
            </a>

            <!-- Fase 1 -->
            <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuFase1" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo $fase1_open ? 'true' : 'false'; ?>" aria-controls="submenuFase1">
                <span><i class='bx bxs-folder-open'></i> Fase 1</span>
                <i class="bx bx-chevron-down"></i>
            </a>
            <div class="collapse <?php echo $fase1_open; ?>" id="submenuFase1">
                <ul class="list-unstyled ps-4">
                    <?php
                    foreach ($fase1_files as $file => $label) {
                        $active = ($currentFile === $file) ? 'active' : '';
                        echo "<li><a class='nav-link $active' href='$file'><i class='bx bx-file'></i> $label</a></li>";
                    }
                    ?>
                </ul>
            </div>

            <!-- Fase 2 -->
            <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuFase2" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo $fase2_open ? 'true' : 'false'; ?>" aria-controls="submenuFase2">
                <span><i class='bx bxs-folder-open'></i> Fase 2</span>
                <i class="bx bx-chevron-down"></i>
            </a>
            <div class="collapse <?php echo $fase2_open; ?>" id="submenuFase2">
                <ul class="list-unstyled ps-4">
                    <?php
                    foreach ($fase2_files as $file => $label) {
                        $active = ($currentFile === $file) ? 'active' : '';
                        echo "<li><a class='nav-link $active' href='$file'><i class='bx bx-file'></i> $label</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </nav>
    </div>

    <!-- Topbar -->
    <div class="topbar z-1">
        <div class="menu-toggle">
            <i class='bx bx-menu'></i>
        </div>

        <div class="topbar-right">
            <!-- Menú desplegable para el usuario -->
            <div class="user-profile dropdown">
                <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil">
                    <span><?php echo htmlspecialchars($primer_nombre) . ' ' . htmlspecialchars($primer_apellido); ?></span>
                    <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
                </div>

                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="perfil.php">
                            <i class='bx bx-user me-2'></i> Perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="cambio-clave.php">
                            <i class='bx bx-lock me-2'></i> Cambio de Clave
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php">
                            <i class='bx bx-log-out me-2'></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

<?php
}
?>
