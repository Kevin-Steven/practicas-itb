<?php
session_start();
require 'sidebar-gestor.php';
if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
  header("Location: ../../index.php");
  exit();
}

// Obtener el primer nombre y el primer apellido
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

// Verificar si la foto de perfil está configurada en la sesión
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inicio - Gestor</title>
  <link href="estilos-gestor.css" rel="stylesheet">
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
      
      <!-- Menú desplegable para el usuario -->
      <div class="user-profile dropdown">
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" id="user-profile-toggle" aria-expanded="false">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i> <!-- Ícono agregado -->
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php">
              <i class='bx bx-user me-2'></i> <!-- Ícono para "Perfil" -->
              Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php">
              <i class='bx bx-lock me-2'></i> <!-- Ícono para "Cambio de Clave" -->
              Cambio de Clave
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php">
              <i class='bx bx-log-out me-2'></i> <!-- Ícono para "Cerrar Sesión" -->
              Cerrar Sesión
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <?php renderSidebarGestor($primer_nombre, $primer_apellido, $foto_perfil); ?>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container-fluid py-2">
      <div class="row justify-content-center">
        <div class="col-md-8 text-center">
          <h1 class="display-5 fw-bold mb-2">Bienvenido a tu panel de administración</h1>
          <p class="lead mb-4">Desde este panel podrás revisar la documentación de los estudiantes y generar reportes para el proceso de practicas profesionales.</p>

          <div class="row justify-content-center">
            <div class="col-md-4 mb-3">
              <div class="card card-principal h-100">
                <div class="card-body text-center">
                  <i class='bx bx-file bx-lg mb-3'></i>
                  <h5 class="card-title">Ver Listado</h5>
                  <p class="card-text">Revisa el listado de postulantes aprobados.</p>
                  <a href="listado-postulantes.php" class="btn">Acceder</a>
                </div>
              </div>
            </div>

            <div class="col-md-4 mb-3">
              <div class="card card-principal h-100">
                <div class="card-body text-center">
                  <i class='bx bx-user bx-lg mb-3'></i>
                  <h5 class="card-title">Ver Inscripciones</h5>
                  <p class="card-text">Revisa el estado de las inscripciones realizadas.</p>
                  <a href="ver-inscripciones.php" class="btn">Acceder</a>
                </div>
              </div>
            </div>

            <div class="col-md-4 mb-3">
              <div class="card card-principal h-100">
                <div class="card-body text-center">
                  <i class='bx bx-line-chart bx-lg mb-3'></i>
                  <h5 class="card-title">Generar Reportes</h5>
                  <p class="card-text">Crea reportes sobre los procesos de titulación.</p>
                  <a href="generar-reportes.php" class="btn">Acceder</a>
                </div>
              </div>
            </div>

          </div>
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