<?php
function renderLayoutAdmin($primer_nombre, $primer_apellido, $foto_perfil)
{
    // Detecta el archivo actual para resaltar la opción activa en el menú
    $currentFile = basename($_SERVER['PHP_SELF']);

    // Sidebar: Clases activas según el archivo actual
    $isInicioActive = ($currentFile === 'inicio-administrador.php') ? 'active' : '';
    $isModificarRolActive = ($currentFile === 'modificar-rol.php') ? 'active' : '';
    $isRestaurarClaveActive = ($currentFile === 'restaurar-claves.php') ? 'active' : '';
    ?>

    <!-- Sidebar -->
    <div class="sidebar z-2" id="sidebar">
        <div class="profile">
            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil">
            <h5><?php echo htmlspecialchars($primer_nombre) . ' ' . htmlspecialchars($primer_apellido); ?></h5>
            <p><?php echo ucfirst(htmlspecialchars($_SESSION['usuario_rol'])); ?></p>
        </div>

        <nav class="nav flex-column">
            <a class="nav-link <?php echo $isInicioActive; ?>" href="inicio-administrador.php">
                <i class='bx bx-home-alt'></i> Inicio
            </a>
            <a class="nav-link <?php echo $isModificarRolActive; ?>" href="modificar-rol.php">
                <i class='bx bx-user'></i> Modificar Rol
            </a>
            <a class="nav-link <?php echo $isRestaurarClaveActive; ?>" href="restaurar-claves.php">
                <i class='bx bx-lock'></i> Restaurar clave
            </a>
        </nav>
    </div>

    <!-- Topbar -->
    <div class="topbar z-1">
        <div class="menu-toggle">
            <i class='bx bx-menu'></i>
        </div>
        <div class="topbar-right">
            <div class="input-group search-bar">
                <span class="input-group-text" id="search-icon"><i class='bx bx-search'></i></span>
                <input type="text" id="search" class="form-control" placeholder="Buscar">
            </div>
            <i class='bx bx-envelope'></i>
            <i class='bx bx-bell'></i>

            <!-- Menú desplegable de usuario -->
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
                        <a class="dropdown-item d-flex align-items-center" href="cambioClave.php">
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

function renderFooterAdmin()
{
    ?>
    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light text-center">
        <div class="container">
            <p class="mb-0">&copy; 2025 Gestoria de Practicas Profesionales - Instituto Superior Tecnológico Bolivariano de Tecnología.</p>
        </div>
    </footer>
<?php
}
?>
