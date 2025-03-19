<?php
function renderSidebarGestor($primer_nombre, $primer_apellido, $foto_perfil)
{
    // Detectamos el archivo actual
    $currentFile = basename($_SERVER['PHP_SELF']);

    // Activos
    $isInicioActive = ($currentFile == 'inicio-gestor.php') ? 'active' : '';
    $isEstudiantesActive = ($currentFile == 'ver-estudiantes.php') ? 'active' : '';
    ?>

    <div class="sidebar z-2" id="sidebar">
        <div class="profile">
            <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
            <h5><?php echo $primer_nombre . ' ' . $primer_apellido; ?></h5>
            <p><?php echo ucfirst($_SESSION['usuario_rol']); ?></p>
        </div>

        <nav class="nav flex-column">
            <a class="nav-link <?php echo $isInicioActive; ?>" href="inicio-gestor.php">
                <i class='bx bx-home-alt'></i> Inicio
            </a>
            <a class="nav-link <?php echo $isEstudiantesActive; ?>" href="ver-estudiantes.php">
                <i class='bx bx-user'></i> Estudiantes
            </a>
        </nav>
    </div>

    <?php
}
?>
