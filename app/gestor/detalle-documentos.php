<?php
session_start();
require '../config/config.php';
require 'sidebar-gestor.php';
if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
  header("Location: ../../index.php");
  exit();
}

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

if (isset($_GET['id'])) {
  $estudiante_id = $_GET['id'];

  $sql = "SELECT 
    u.nombres, 
    u.apellidos, 
    u.email, 
    c.carrera AS carrera,  -- El campo carrera lo obtienes de la tabla carrera
    u.telefono, 
    d1.id AS id_uno, 
    d1.estado AS estado_uno, 
    d1.nombre_doc AS nombre_doc_uno,
    d2.id AS id_dos, 
    d2.estado AS estado_dos, 
    d2.nombre_doc AS nombre_doc_dos
FROM usuarios u
LEFT JOIN carrera c ON u.carrera_id = c.id               -- Únete a la tabla carrera
LEFT JOIN documento_uno d1 ON u.id = d1.usuario_id
LEFT JOIN documento_dos d2 ON u.id = d2.usuario_id
WHERE u.id = ?
ORDER BY d1.fecha_subida DESC
LIMIT 1";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $estudiante_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $documento = $result->fetch_assoc();

    // Aquí creamos el array de documentos
    $documentos = [
      [
        'id' => $documento['id_uno'],
        'nombre' => $documento['nombre_doc_uno'],
        'estado' => $documento['estado_uno'],
        'pdf' => "../../app/estudiante/pdf/software/doc-uno-pdf.php?id=" . $documento['id_uno'],
        'tipo' => 'uno'
      ],
      [
        'id' => $documento['id_dos'],
        'nombre' => $documento['nombre_doc_dos'],
        'estado' => $documento['estado_dos'],
        'pdf' => "../../app/estudiante/pdf/software/doc-dos-pdf.php?id=" . $documento['id_dos'],
        'tipo' => 'dos'
      ],
      // Aquí continúas hasta el documento 16...
    ];
  }
} else {
  echo "No se encontraron detalles para este estudiante.";
  exit();
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detalle Inscripción</title>
  <link href="estilos-gestor.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="icon" href="../../images/favicon.png" type="image/png">

</head>

<body>

  <!-- Topbar -->
  <div class="topbar z-1">
    <div class="menu-toggle">
      <i class='bx bx-menu'></i>
    </div>
    <div class="topbar-right">

      <div class="user-profile dropdown">
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" id="user-profile-toggle" aria-expanded="false">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1'></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li><a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php"><i class='bx bx-user me-2'></i>Perfil</a></li>
          <li><a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php"><i class='bx bx-lock me-2'></i>Cambio de Clave</a></li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php"><i class='bx bx-log-out me-2'></i>Cerrar Sesión</a></li>
        </ul>
      </div>
    </div>
  </div>

  <?php renderSidebarGestor($primer_nombre, $primer_apellido, $foto_perfil); ?>

  <!-- Toast -->
  <?php if (isset($_GET['status'])): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <?php if ($_GET['status'] === 'success'): ?>
            <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
            <strong class="me-auto">Acción Exitosa</strong>
          <?php elseif ($_GET['status'] === 'db_error'): ?>
            <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
            <strong class="me-auto">Error de Base de Datos</strong>
          <?php elseif ($_GET['status'] === 'error'): ?>
            <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
            <strong class="me-auto">Error de Datos</strong>
          <?php elseif ($_GET['status'] === 'error_tipo'): ?>
            <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
            <strong class="me-auto">Tipo de Documento Inválido</strong>
          <?php elseif ($_GET['status'] === 'no_user'): ?>
            <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
            <strong class="me-auto">Usuario No Encontrado</strong>
          <?php else: ?>
            <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
            <strong class="me-auto">Error</strong>
          <?php endif; ?>

          <small>Justo ahora</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>

        <div class="toast-body">
          <?php
          switch ($_GET['status']) {
            case 'success':
              echo "El documento fue actualizado correctamente.";
              break;
            case 'db_error':
              echo "Hubo un problema al actualizar en la base de datos. Inténtalo nuevamente.";
              break;
            case 'error':
              echo "Faltan datos requeridos para realizar la acción.";
              break;
            case 'error_tipo':
              echo "El tipo de documento especificado es inválido.";
              break;
            case 'no_user':
              echo "No se encontró el usuario correspondiente a este documento.";
              break;
            default:
              echo "Ha ocurrido un error desconocido.";
              break;
          }
          ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container mt-4">
      <h1 class="mb-1 text-center fw-bold">Estado de Documentos</h1>
      <h5 class="text-center mb-3"><strong>Estudiante:</strong> <?php echo $documento['nombres'] . ' ' . $documento['apellidos']; ?></h3>


        <div class="table-responsive">
          <table class="table table-bordered shadow-lg">
            <thead class="table-light text-center">
              <tr>
                <th>Nombre del Documento</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documentos as $doc): ?>
                <tr>
                  <td class="text-center"><?php echo $doc['nombre'] ?? 'Documento no subido'; ?></td>
                  <td class="text-center">
                    <?php if (!empty($doc['estado'])): ?>
                      <span class="badge
                      <?php
                      if ($doc['estado'] === 'Pendiente') echo 'bg-warning text-dark';
                      elseif ($doc['estado'] === 'Aprobado') echo 'bg-success';
                      elseif ($doc['estado'] === 'Corregir') echo 'bg-danger';
                      else echo 'bg-secondary';
                      ?>">
                        <?php echo htmlspecialchars($doc['estado']); ?>
                      </span>
                    <?php else: ?>
                      <span class="badge bg-secondary">No subido</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?php if (!empty($doc['id'])): ?>
                      <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-primary btn-aprobar"
                          data-id="<?php echo $doc['id']; ?>"
                          data-tipo="<?php echo $doc['tipo']; ?>">
                          <i class='bx bxs-check-circle'></i>
                        </button>

                        <button type="button" class="btn btn-warning btn-corregir"
                          data-id="<?php echo $doc['id']; ?>"
                          data-tipo="<?php echo $doc['tipo']; ?>">
                          <i class='bx bx-edit'></i>
                        </button>

                        <a href="<?php echo $doc['pdf']; ?>" target="_blank" class="btn btn-danger">
                          <i class='text-white bx bxs-file-pdf'></i>
                        </a>
                      </div>
                    <?php else: ?>
                      <span class="text-muted">Sin acciones</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <!-- Modal de confirmación de acción -->
              <div class="modal fade" id="modalConfirmAction" tabindex="-1" aria-labelledby="modalConfirmTitle" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form action="../gestor/logic/procesar-documentos.php" method="POST" id="modalConfirmForm">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modalConfirmTitle">Confirmar Acción</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                      </div>
                      <div class="modal-body">
                        <p id="modalConfirmBody">¿Estás seguro de realizar esta acción?</p>

                        <!-- Textarea para motivo_rechazo SOLO si es Corregir -->
                        <div id="motivoRechazoGroup" class="mb-3" style="display: none;">
                          <label for="motivoRechazo" class="form-label fw-bold">Motivo de rechazo:</label>
                          <textarea class="form-control" id="motivoRechazo" name="motivo_rechazo" rows="3" placeholder="Escribe el motivo de rechazo..."></textarea>
                        </div>

                      </div>
                      <div class="modal-footer">
                        <input type="hidden" name="accion" id="modalActionInput">
                        <input type="hidden" name="id_documento" id="modalIdInput">
                        <input type="hidden" name="tipo_documento" id="modalTipoInput">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Confirmar</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>


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
  <script src="../js/abrirModalDoc.js"></script>
  <script src="../js/toast.js"></script>

</body>

</html>

<?php $conn->close(); ?>