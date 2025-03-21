<?php
session_start();
require '../config/config.php';
require 'sidebar-admin.php';
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

$usuario_actual_id = $_SESSION['usuario_id'];

$cedula = '';
$usuario = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cedula'])) {
    $cedula = mysqli_real_escape_string($conn, $_POST['cedula']);

    // Buscar el usuario por cédula
    $sql = "SELECT id, nombres, apellidos FROM usuarios WHERE cedula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    } else {
        header("Location: restaurar-claves.php?status=error");
        exit();
    }
}

// Si se envía el formulario para cambiar la clave
if (isset($_POST['nuevo_password'], $_POST['usuario_id'])) {
    $usuario_id = $_POST['usuario_id'];

    $nuevo_password_sanitizado = mysqli_real_escape_string($conn, $_POST['nuevo_password']);
    $nuevo_password = password_hash($nuevo_password_sanitizado, PASSWORD_BCRYPT);

    $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_password, $usuario_id);

    if ($stmt->execute()) {
        header("Location: restaurar-claves.php?status=success");
        exit();
    } else {
        header("Location: restaurar-claves.php?status=error");
        exit();
    }
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modificar Clave</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php renderLayoutAdmin($primer_nombre, $primer_apellido, $foto_perfil); ?>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-lg" id="card-restaurar">
                        <div class="card-body">
                            <h2 class="mb-4 fw-bold text-center">Restaurar Clave de Postulantes</h2>

                            <!-- Toast -->
                            <?php if (isset($_GET['status'])): ?>
                                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                                    <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                                        <div class="toast-header">
                                            <?php if ($_GET['status'] === 'success'): ?>
                                                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                                                <strong class="me-auto">Actualización Exitosa</strong>
                                            <?php elseif ($_GET['status'] === 'error'): ?>
                                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                                <strong class="me-auto">Usuario No Encontrado</strong>
                                            <?php elseif ($_GET['status'] === 'invalid_request'): ?>
                                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                                <strong class="me-auto">Error en el Formulario</strong>
                                            <?php endif; ?>
                                            <small>Justo ahora</small>
                                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                        </div>
                                        <div class="toast-body">
                                            <?php
                                            switch ($_GET['status']) {
                                                case 'success':
                                                    echo "Contraseña actualizada con éxito.";
                                                    break;
                                                case 'error':
                                                    echo "No se encontró ningún usuario con esa cédula.";
                                                    break;
                                                case 'invalid_request':
                                                    echo "Hubo un error en el envío del formulario.";
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

                            <!-- Formulario de búsqueda por cédula -->
                            <form action="restaurar-claves.php" method="POST">
                                <div class="mb-3">
                                    <label for="cedula" class="form-label fw-bold">Cédula del Postulante</label>
                                    <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Ingrese la cédula" required oninput="validateInput(this)" maxlength="10" value="<?php echo $cedula; ?>">
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn">Buscar</button>
                                </div>
                            </form>

                            <!-- Si el usuario es encontrado, mostrar el campo para cambiar la clave -->
                            <?php if ($usuario): ?>
                                <h3 class="mt-5 mb-3 text-center fw-bold">Cambiar Clave para: <?php echo $usuario['nombres'] . ' ' . $usuario['apellidos']; ?></h3>

                                <form id="updatePasswordForm" action="restaurar-claves.php" method="POST">
                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">

                                    <div class="mb-3">
                                        <label for="nuevo_password" class="form-label fw-bold">Nueva Clave</label>
                                        <input type="password" class="form-control" id="nuevo_password" name="nuevo_password" placeholder="Ingrese la nueva clave" required>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <!-- Botón que activa el modal -->
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModal">
                                            Actualizar Clave
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas actualizar la clave?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="updatePasswordForm">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/number.js" defer></script>
</body>

</html>