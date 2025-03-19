<?php
session_start();

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
  header("Location: ../../index.php");
  exit();
}

// Verificar si la foto de perfil está configurada en la sesión
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

$mensaje = '';
$tipo_mensaje = 'danger';
if (isset($_SESSION['mensaje'])) {
  $mensaje = $_SESSION['mensaje'];

  if (isset($_SESSION['tipo_mensaje']) && $_SESSION['tipo_mensaje'] == 'success') {
    $tipo_mensaje = 'success';
  }
  unset($_SESSION['mensaje']);
  unset($_SESSION['tipo_mensaje']);
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cambio de Clave</title>
  <link href="../gestor/estilos-gestor.css" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="icon" href="../../images/favicon.png" type="image/png">
</head>

<body>
  <!-- Topbar con ícono de menú hamburguesa (fuera del menú) -->
  <div class="topbar z-1">
    <div class="menu-toggle">
      <i class='bx bx-menu'></i>
    </div>
    <div class="topbar-right">
      <div class="input-group search-bar">
        <span class="input-group-text" id="search-icon"><i class='bx bx-search'></i></span>
        <input type="text" id="search" class="form-control" placeholder="Search">
      </div>
      <!-- Iconos adicionales a la derecha -->
      <i class='bx bx-envelope'></i>
      <i class='bx bx-bell'></i>
      <!-- Menú desplegable para el usuario -->
      <div class="user-profile dropdown">
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil.php">
              <i class='bx bx-user me-2'></i>
              Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambio-clave.php">
              <i class='bx bx-lock me-2'></i>
              Cambio de Clave
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php">
              <i class='bx bx-log-out me-2'></i>
              Cerrar Sesión
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="sidebar z-2" id="sidebar">
    <div class="profile">
      <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
      <h5><?php echo $primer_nombre . ' ' . $primer_apellido; ?></h5>
      <p><?php echo ucfirst($_SESSION['usuario_rol']); ?></p>
    </div>
    <nav class="nav flex-column">
      <a class="nav-link" href="inicio-estudiante.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuFase1" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bxs-folder-open'></i> Fase 1</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse" id="submenuFase1">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-uno.php' ? 'active' : ''; ?>" href="for-uno.php">
              <i class="bx bx-file"></i> For 1
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-dos.php' ? 'active' : ''; ?>" href="for-dos.php">
              <i class="bx bx-file"></i> For 2
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-tres.php' ? 'active' : ''; ?>" href="for-tres.php">
              <i class="bx bx-file"></i> For 3
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-cuatro.php' ? 'active' : ''; ?>" href="for-cuatro.php">
              <i class="bx bx-file"></i> For 4
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-cinco.php' ? 'active' : ''; ?>" href="for-cinco.php">
              <i class="bx bx-file"></i> For 5
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-seis.php' ? 'active' : ''; ?>" href="for-seis.php">
              <i class="bx bx-file"></i> For 6
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-siete.php' ? 'active' : ''; ?>" href="for-siete.php">
              <i class="bx bx-file"></i> For 7
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-ocho.php' ? 'active' : ''; ?>" href="for-ocho.php">
              <i class="bx bx-file"></i> For 8
            </a>
          </li>

        </ul>
      </div>

      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuFase2" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bxs-folder-open'></i> Fase 2</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse" id="submenuFase2">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-nueve.php' ? 'active' : ''; ?>" href="for-nueve.php">
              <i class="bx bx-file"></i> For 9
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-diez.php' ? 'active' : ''; ?>" href="for-diez.php">
              <i class="bx bx-file"></i> For 10
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-once.php' ? 'active' : ''; ?>" href="for-once.php">
              <i class="bx bx-file"></i> For 11
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-doce.php' ? 'active' : ''; ?>" href="for-doce.php">
              <i class="bx bx-file"></i> For 12
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-trece.php' ? 'active' : ''; ?>" href="for-trece.php">
              <i class="bx bx-file"></i> For 13
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-catorce.php' ? 'active' : ''; ?>" href="for-catorce.php">
              <i class="bx bx-file"></i> For 14
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-quince.php' ? 'active' : ''; ?>" href="for-quince.php">
              <i class="bx bx-file"></i> For 15
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-diecis.php' ? 'active' : ''; ?>" href="for-diecis.php">
              <i class="bx bx-file"></i> For 16
            </a>
          </li>

        </ul>
      </div>
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <form action="logica-cambio-clave.php" class="formularioCambioClave" method="post">
            <h2 class="title-crd text-center mb-4 fw-bold">Cambio de Contraseña</h2>

            <!-- Mostrar el mensaje si es que existe -->
            <?php if (!empty($mensaje)): ?>
              <div class="alert alert-<?php echo $tipo_mensaje; ?>" role="alert">
                <?php echo $mensaje; ?>
              </div>
            <?php endif; ?>

            <!-- Campo para la clave actual -->
            <div class="form-group mb-3">
              <label for="actualPassword">Clave Actual</label>
              <input type="password" class="form-control" id="actualPassword" name="actualPassword" required>
            </div>

            <!-- Campo para la nueva clave -->
            <div class="form-group mb-3">
              <label for="newPassword">Nueva Clave</label>
              <input type="password" class="form-control" id="newPassword" name="newPassword" required>
            </div>

            <!-- Campo para confirmar la nueva clave -->
            <div class="form-group mb-5">
              <label for="confirmPassword">Confirmar Nueva Clave</label>
              <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
            </div>

            <!-- Botón para enviar el formulario -->
            <div class="d-grid gap-2 mt-4 mb-2">
              <button type="submit" class="btn">Actualizar Clave</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer mt-auto py-3 bg-light text-center">
    <div class="container">
      <p class="mb-0">&copy; 2025 Gestoria de Practicas Profesionales - Instituto Superior Tecnológico Bolivariano de Tecnología.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/sidebar.js"></script>

</body>

</html>