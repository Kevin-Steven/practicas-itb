<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener los datos del usuario
$sql = $sql = "SELECT 
u.nombres,
u.apellidos,
u.email,
u.cedula,
u.telefono,
u.convencional,
u.foto_perfil,
u.periodo,
c.carrera AS nombre_carrera,
cu.paralelo AS nombre_paralelo
FROM usuarios u
INNER JOIN carrera c ON u.carrera_id = c.id
LEFT JOIN cursos cu ON u.curso_id = cu.id
WHERE u.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

$primer_nombre = explode(' ', $usuario['nombres'])[0];
$primer_apellido = explode(' ', $usuario['apellidos'])[0];

$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

$stmt->close();

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nombres = strtoupper(mysqli_real_escape_string($conn, $_POST['nombres']));
  $apellidos = strtoupper(mysqli_real_escape_string($conn, $_POST['apellidos']));
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $cedula = $_POST['cedula'];
  $telefono = $_POST['telefono'];
  $convencional = $_POST['convencional'];

  // Verificar si se ha subido una imagen de perfil
  if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
    $target_dir = "../photos/";
    $foto_perfil = $target_dir . basename($_FILES["foto_perfil"]["name"]);
    move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $foto_perfil);

    // Actualizar la variable de sesión con la nueva foto
    $_SESSION['usuario_foto'] = $foto_perfil;
  } else {
    $foto_perfil = $usuario['foto_perfil'];
  }

  // Redirigir para evitar reenvíos de formularios
  header("Location: perfil.php");
  exit();
}

$conn->close();
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil</title>
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
      <div class="user-profile dropdown">
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil.php"><i class='bx bx-user me-2'></i>Perfil</a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambio-clave.php"><i class='bx bx-lock me-2'></i>Cambio de Clave</a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php"><i class='bx bx-log-out me-2'></i>Cerrar Sesión</a>
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
    <div class="container mt-3">
      <div class="row justify-content-center">
        <!-- Columna del Perfil del Usuario -->
        <div class="col-md-8 mb-4">
          <div class="card">
            <div class="card-header text-center">
              <h2 class="card-title fw-bold">Mis Datos</h2>
            </div>
            <div class="card-body">

              <!-- Formulario para actualizar datos -->
              <form action="actualizar-perfil.php" method="POST" class="formulario-perfil" enctype="multipart/form-data">
                <?php if (isset($_GET['status'])): ?>
                  <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                      <div class="toast-header">
                        <i class='bx bx-send me-2'></i>
                        <strong class="me-auto">Estado de Actualización</strong>
                        <small>Justo ahora</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                      </div>
                      <div class="toast-body">
                        <?php if ($_GET['status'] == 'success'): ?>
                          Perfil actualizado correctamente.
                        <?php elseif ($_GET['status'] == 'invalid_phone'): ?>
                          El número de teléfono o convencional debe tener 10 dígitos.
                        <?php elseif ($_GET['status'] == 'no_changes'): ?>
                          No se realizaron cambios en los datos.
                        <?php else: ?>
                          Hubo un error al actualizar el perfil.
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>

                  <script>
                    document.addEventListener('DOMContentLoaded', (event) => {
                      var toastEl = document.getElementById('liveToast');
                      var toast = new bootstrap.Toast(toastEl);
                      toast.show();
                    });
                  </script>
                <?php endif; ?>

                <!-- Imagen del perfil -->
                <div class="text-center mb-3">
                  <img id="preview" src="<?php echo $usuario['foto_perfil'] ? $usuario['foto_perfil'] : '../../images/user.png'; ?>" alt="Perfil" class="rounded-circle" width="120">
                </div>

                <!-- Botón personalizado para subir foto -->
                <div class="text-center mb-2 boton-subir-perfil">
                  <label for="foto_perfil" class="btn-upload">
                    <i class='bx bx-camera text-white'></i> Cargar Foto
                  </label>
                  <input type="file" id="foto_perfil" name="foto_perfil" class="file-input" accept="image/*" onchange="previewImage(event)">
                  <input type="hidden" name="foto_actual" value="<?php echo $usuario['foto_perfil']; ?>">
                </div>

                <!-- Distribución en dos columnas -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="nombres" class="form-label"><strong>Nombres:</strong></label>
                    <input type="text" name="nombres" class="form-control" value="<?php echo htmlspecialchars($usuario['nombres']); ?>" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="apellidos" class="form-label"><strong>Apellidos:</strong></label>
                    <input type="text" name="apellidos" class="form-control" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="email" class="form-label"><strong>Email:</strong></label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="cedula" class="form-label"><strong>Cédula:</strong></label>
                    <input type="text" name="cedula" class="form-control" maxlength="10" oninput="validateInput(this)" value="<?php echo htmlspecialchars($usuario['cedula']); ?>" readonly>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label"><strong>Teléfono:</strong></label>
                    <input type="text" name="telefono" class="form-control" maxlength="10" oninput="validateInput(this)" value="<?php echo htmlspecialchars($usuario['telefono']); ?>" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="convencional" class="form-label"><strong>Convencional (Opcional):</strong></label>
                    <input type="text" name="convencional" class="form-control" maxlength="9" value="<?php echo !empty($usuario['convencional']) ? htmlspecialchars($usuario['convencional']) : 'NO APLICA'; ?>">
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="carrera" class="form-label"><strong>Carrera:</strong></label>
                    <input type="text" name="carrera" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre_carrera']); ?>" readonly>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="paralelo" class="form-label"><strong>Paralelo:</strong></label>
                    <input type="text" name="paralelo" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre_paralelo']); ?>" readonly>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="periodo" class="form-label"><strong>Periodo Académico:</strong></label>
                    <input type="text" name="periodo" class="form-control" value="<?php echo htmlspecialchars($usuario['periodo']); ?>" readonly>
                  </div>
                </div>


                <div class="text-center mt-3">
                  <button type="submit" class="btn w-50">Actualizar</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- Footer -->
  <footer class="footer mt-auto py-3 bg-light text-center">
    <div class="container">
      <p class="mb-0">&copy; 2025 2025 Gestoria de Practicas Profesionales - Instituto Superior Tecnológico Bolivariano de Tecnología.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/previewImage.js" defer></script>
  <script src="../js/sidebar.js"></script>
  <script src="../js/number.js" defer></script>
</body>

</html>