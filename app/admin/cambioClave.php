<?php
session_start();
require 'sidebar-admin.php';
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
<?php renderLayoutAdmin($primer_nombre, $primer_apellido, $foto_perfil); ?>


  <!-- Content -->
  <div class="content" id="content">
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <form action="logica-cambio-clave.php" class="formularioCambioClave" method="post">
            <h2 class="title-crd text-center mb-4 fw-bold">Cambio de Contraseña</h2>

            <!-- Mostrar el mensaje si existe -->
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
              <button type="submit" class="btn btn-primary">Actualizar Clave</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php renderFooterAdmin(); ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/sidebar.js"></script>

</body>

</html>