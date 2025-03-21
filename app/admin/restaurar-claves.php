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
        <div class="container-fluid py-3">
            <div class="row justify-content-center">
                <div class="col-md-6 d-grid">
                    <h1 class="fw-bold text-center mb-2">Restaurar Contraseña</h1>
                    <p class="text-center mb-4">Desde este apartado podrás <strong>restaurar la contraseña</strong> de los usuarios registrados.</h5>

                    <!-- Tarjeta para restaurar clave -->
                    <div class="card shadow-lg" id="card-restaurar">
                        <div class="card-body d-grid gap-3">

                            <!-- Título -->

                            <!-- Formulario de búsqueda -->
                            <form action="restaurar-claves.php" method="POST" class="d-grid gap-3">
                                <div>
                                    <label for="cedula" class="form-label fw-bold">Cédula del Usuario</label>
                                    <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Ingrese la cédula" required
                                        oninput="validateInput(this)" maxlength="10" value="<?php echo $cedula; ?>">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn">Buscar Usuario</button>
                                </div>
                            </form>

                            <!-- Mostrar formulario para cambiar contraseña si encuentra usuario -->
                            <?php if ($usuario): ?>
                                <div class="d-grid gap-3 mt-4">
                                    <h5 class="fw-bold text-center">Cambiar Contraseña para: <?php echo $usuario['nombres'] . ' ' . $usuario['apellidos']; ?></h4>

                                    <form id="updatePasswordForm" action="restaurar-claves.php" method="POST" class="d-grid gap-3">
                                        <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">

                                        <div>
                                            <label for="nuevo_password" class="form-label fw-bold">Nueva Contraseña</label>
                                            <input type="password"
                                                class="form-control"
                                                id="nuevo_password"
                                                name="nuevo_password"
                                                placeholder="Ingrese la nueva contraseña"
                                                required>
                                        </div>

                                        <div class="d-grid">
                                            <button type="button"
                                                class="btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmModal">
                                                Actualizar Contraseña
                                            </button>
                                        </div>
                                    </form>
                                </div>
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

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/number.js" defer></script>
</body>

</html>