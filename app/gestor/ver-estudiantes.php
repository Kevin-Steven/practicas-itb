<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
  header("Location: ../../index.php");
  exit();
}

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

// Consulta para obtener inscripciones de los postulantes
$sql = "SELECT u.id, u.nombres, u.apellidos, u.carrera, u.cedula
        FROM usuarios u
        WHERE u.rol = 'estudiante'";

$result = $conn->query($sql);
$estudiantes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ver Documentos</title>
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

      <div class="user-profile dropdown">
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" id="user-profile-toggle" aria-expanded="false">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php">
              <i class='bx bx-user me-2'></i>
              Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php">
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
      <a class="nav-link" href="inicio-gestor.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link active" href="ver-estudiantes.php"><i class='bx bx-user'></i> Estudiantes</a>
      <!-- Módulo Informes con submenú
      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuInformes" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bx-file'></i> Informes</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse" id="submenuInformes">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'informe-tutor.php' ? 'active' : ''; ?>" href="informe-tutor.php">
              <i class="bx bx-file"></i> Informe Tutor
            </a>
          </li>
          <li>
            <a class="nav-link  <?php echo basename($_SERVER['PHP_SELF']) == 'informe-tesis.php' ? 'active' : ''; ?>" href="informe-tesis.php">
              <i class="bx bx-file"></i> Informe Tesis
            </a>
          </li>
          <li>
            <a class="nav-link  <?php echo basename($_SERVER['PHP_SELF']) == 'informe-revisor-tesis.php' ? 'active' : ''; ?>" href="informe-revisor-tesis.php">
              <i class="bx bx-file"></i> Jurado tesis
            </a>
          </li>
        </ul>
      </div> -->
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container mt-2">
      <h1 class="mb-4 text-center fw-bold">Listado de Estudiantes</h1>

      <!-- Fila para búsqueda y filtro de carrera -->
      <div class="row mb-3 align-items-center">
        <!-- Input de búsqueda -->
        <div class="col-md-6 col-12 mb-2 mb-md-0">
          <div class="input-group">
            <span class="input-group-text"><i class='bx bx-search'></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar por cédula, nombre o apellido...">
          </div>
        </div>

        <!-- Select para filtrar por carrera -->
        <div class="col-md-6 col-12">
          <select id="filterCarrera" class="form-select">
            <option selected disabled>Seleccionar Carrera</option>
            <option value="todos">Todos</option>
            <option value="Tecnología Superior en Desarrollo de software">Tecnología Superior en Desarrollo de software</option>
            <option value="Tecnología Superior en Gestión de la Tecnología de la Información">Tecnología Superior en Gestión de la Tecnología de la Información</option>
          </select>
        </div>
      </div>
      <script>
        const postulantesData = <?php echo json_encode($estudiantes); ?>;
      </script>


      <div class="table-responsive">
        <table class="table table-striped" id="postulantesTable">
          <thead class="table-header-fixed">
            <tr>
              <th class="d-none">ID</th>
              <th>Cédula</th>
              <th>Nombre</th>
              <th>Apellido</th>
              <th class="d-none d-sm-table-cell">Carrera</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody id="postulantesBody">
            <tr id="noResultsRow">
              <td colspan="6" class="text-center">No se encontraron resultados.</td>
            </tr>
          </tbody>
        </table>
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
  <script src="../js/search.js" defer></script>

</body>

</html>

<?php $conn->close(); ?>