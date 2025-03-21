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

// Obtener la lista de usuarios de la base de datos, excluyendo al administrador actual
$sql = "SELECT id, nombres, apellidos, cedula, estado, rol FROM usuarios WHERE id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_actual_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestionar Usuarios</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php renderLayoutAdmin($primer_nombre, $primer_apellido, $foto_perfil); ?>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container py-2">
            <h1 class="mb-4 fw-bold text-center">Gestionar Usuarios</h1>

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="input-group mb-4">
                        <span class="input-group-text"><i class='bx bx-search'></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar por cédula, nombre o apellido...">
                    </div>

                </div>
                <!-- Tabla de usuarios -->
                <div class="table-responsive">
                    <table class="table table-striped" id="usuariosTable">
                        <thead class="table-light">
                            <tr>
                                <th class="d-none">ID</th>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th class="d-none d-md-table-cell">Rol Actual</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="usuario-row">
                                        <td class="d-none"><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nombres']); ?></td>
                                        <td><?php echo htmlspecialchars($row['apellidos']); ?></td>
                                        <td class="d-none d-md-table-cell"><?php echo ucfirst(htmlspecialchars($row['rol'])); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($row['estado'])); ?></td>

                                        <!-- Botones de acciones -->
                                        <td class="text-center">
                                            <!-- Botón para MODIFICAR ROL -->
                                            <button type="button"
                                                class="btn btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalModificar<?php echo $row['id']; ?>">
                                                <i class='bx bx-save'></i>
                                            </button>

                                            <!-- Botón para INHABILITAR USUARIO -->
                                            <button type="button"
                                                class="btn btn-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalInhabilitar<?php echo $row['id']; ?>">
                                                <i class='bx bx-user-x'></i>
                                            </button>

                                            <!-- Botón para HABILITAR USUARIO -->
                                            <button type="button"
                                                class="btn btn-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalHabilitar<?php echo $row['id']; ?>">
                                                <i class='bx bx-user-check'></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal MODIFICAR ROL -->
                                    <div class="modal fade" id="modalModificar<?php echo $row['id']; ?>" tabindex="-1"
                                        aria-labelledby="modalModificarLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="../admin/logic/modificar-rol.php" method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalModificarLabel<?php echo $row['id']; ?>">
                                                            ¿Desea modificar el rol del siguiente usuario? <br>
                                                            <strong><?php echo $row['nombres'] . ' ' . $row['apellidos']; ?></strong>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <div class="mb-3">
                                                            <label for="nuevo_rol" class="form-label fw-bold">Nuevo Rol:</label>
                                                            <select class="form-select" name="nuevo_rol" required>
                                                                <option value="">Seleccione un rol</option>
                                                                <option value="administrador">Administrador</option>
                                                                <option value="gestor">Gestor</option>
                                                                <option value="estudiante">Estudiante</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary">Aceptar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal INHABILITAR USUARIO -->
                                    <div class="modal fade" id="modalInhabilitar<?php echo $row['id']; ?>" tabindex="-1"
                                        aria-labelledby="modalInhabilitarLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="../admin/logic/inhabilitar-usuario.php" method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalInhabilitarLabel<?php echo $row['id']; ?>">
                                                            ¿Desea inhabilitar al siguiente usuario? <br>
                                                            <strong><?php echo $row['nombres'] . ' ' . $row['apellidos']; ?></strong>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <p>Se cambiará el estado a <strong>inactivo</strong>.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-danger">Aceptar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal HABILITAR USUARIO -->
                                    <div class="modal fade" id="modalHabilitar<?php echo $row['id']; ?>" tabindex="-1"
                                        aria-labelledby="modalHabilitarLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="../admin/logic/habilitar-usuario.php" method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalHabilitarLabel<?php echo $row['id']; ?>">
                                                            ¿Desea habilitar al siguiente usuario? <br>
                                                            <strong><?php echo $row['nombres'] . ' ' . $row['apellidos']; ?></strong>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <p>Se cambiará el estado a <strong>activo</strong>.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-success">Aceptar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No se encontraron usuarios registrados.</td>
                                </tr>
                            <?php endif; ?>

                            <!-- Fila de "No resultados" -->
                            <tr id="noResultsRow" style="display: none;">
                                <td colspan="7" class="text-center">No se encontraron resultados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>


            </div>
        </div>

    </div>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <?php
        $status = $_GET['status'];

        // Define íconos, colores y encabezados según el estado
        $toastIcon = '';
        $toastColor = '';
        $toastTitle = '';
        $toastMessage = '';

        switch ($status) {
            case 'success':
                $toastIcon = 'bx bx-check-circle';
                $toastColor = 'text-success';
                $toastTitle = 'Actualización Exitosa';
                $toastMessage = 'El rol del usuario se actualizó correctamente.';
                break;

            case 'inhabilitado':
                $toastIcon = 'bx bx-user-x';
                $toastColor = 'text-warning';
                $toastTitle = 'Usuario Inhabilitado';
                $toastMessage = 'El usuario fue inhabilitado exitosamente.';
                break;

            case 'habilitado':
                $toastIcon = 'bx bx-user-check';
                $toastColor = 'text-success';
                $toastTitle = 'Usuario Habilitado';
                $toastMessage = 'El usuario fue habilitado exitosamente.';
                break;

            case 'error':
                $toastIcon = 'bx bx-error-circle';
                $toastColor = 'text-danger';
                $toastTitle = 'Error en el Proceso';
                $toastMessage = 'Hubo un problema al procesar la solicitud.';
                break;

            case 'db_error':
                $toastIcon = 'bx bx-error-circle';
                $toastColor = 'text-danger';
                $toastTitle = 'Error de Base de Datos';
                $toastMessage = 'Error en la base de datos. Intente nuevamente.';
                break;

            case 'invalid_request':
                $toastIcon = 'bx bx-error-circle';
                $toastColor = 'text-danger';
                $toastTitle = 'Petición Inválida';
                $toastMessage = 'La petición enviada es inválida.';
                break;

            default:
                $toastIcon = 'bx bx-error-circle';
                $toastColor = 'text-danger';
                $toastTitle = 'Estado Desconocido';
                $toastMessage = 'Ha ocurrido un error inesperado.';
                break;
        }
        ?>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="<?php echo $toastIcon; ?> fs-4 me-2 <?php echo $toastColor; ?>"></i>
                    <strong class="me-auto"><?php echo $toastTitle; ?></strong>
                    <small class="text-muted">Justo ahora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    <?php echo $toastMessage; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <?php renderFooterAdmin(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/buscarUsuario.js" defer></script>
</body>

</html>