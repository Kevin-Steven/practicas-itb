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
        'for-ocho.php' => 'Plan de Rotación',
    ];

    $fase2_files = [
        'for-nueve.php' => 'For 9',
        'for-diez.php' => 'For 10',
        'for-once.php' => 'For 11',
        'for-doce.php' => 'For 12',
        'for-trece.php' => 'For 13',
        'for-catorce.php' => 'For 14',
        'for-quince.php' => 'For 15',
        'for-diecis.php' => 'For 16',
    ];

    // Detectar si está en una página de Fase 1 o Fase 2 para abrir el collapse
    $fase1_open = in_array($currentFile, array_keys($fase1_files)) ? 'show' : '';
    $fase2_open = in_array($currentFile, array_keys($fase2_files)) ? 'show' : '';
    ?>

    <div class="sidebar z-2" id="sidebar">
        <div class="profile">
            <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
            <h5><?php echo $primer_nombre . ' ' . $primer_apellido; ?></h5>
            <p><?php echo ucfirst($_SESSION['usuario_rol']); ?></p>
        </div>

        <nav class="nav flex-column">
            <!-- Inicio -->
            <a class="nav-link <?php echo ($currentFile == 'inicio-estudiante.php') ? 'active' : ''; ?>" href="inicio-estudiante.php">
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
                        $active = ($currentFile == $file) ? 'active' : '';
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
                        $active = ($currentFile == $file) ? 'active' : '';
                        echo "<li><a class='nav-link $active' href='$file'><i class='bx bx-file'></i> $label</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </nav>
    </div>

    <?php
}
?>
