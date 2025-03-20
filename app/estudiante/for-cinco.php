<?php
session_start();
require '../config/config.php';
require 'sidebar-estudiante.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

$usuario_id = $_SESSION['usuario_id'];

$sql_doc_cinco = "SELECT 
       d5.id,
       d5.estado, 
       d5.nombre_entidad_receptora,
       d5.ruc,
       d5.direccion_entidad_receptora,
       d5.logo_entidad_receptora,
       d5.nombre_ciudad,
       d5.nombre_representante_rrhh,
       d5.numero_representante_rrhh,
       d5.correo_representante
FROM documento_cinco d5
WHERE d5.usuario_id = ?
ORDER BY d5.id DESC";

$stmt_doc_cinco = $conn->prepare($sql_doc_cinco);
$stmt_doc_cinco->bind_param("i", $usuario_id);
$stmt_doc_cinco->execute();
$result_doc_cinco = $stmt_doc_cinco->get_result();

while ($row = $result_doc_cinco->fetch_assoc()) {
    $id = $row['id'] ?? null;
    $estado = $row['estado'] ?? null;
    $nombre_entidad_receptora = $row['nombre_entidad_receptora'] ?? null;
    $ruc = $row['ruc'] ?? null;
    $direccion_entidad_receptora = $row['direccion_entidad_receptora'] ?? null;
    $logo_entidad_receptora = $row['logo_entidad_receptora'] ?? null;
    $nombre_ciudad = $row['nombre_ciudad'] ?? null;
    $nombre_representante_rrhh = $row['nombre_representante_rrhh'] ?? null;
    $numero_representante_rrhh = $row['numero_representante_rrhh'] ?? null;
    $correo_representante = $row['correo_representante'] ?? null;
    $correo_tutor_academico = $row['correo_tutor_academico'] ?? null;
}

$stmt_doc_cinco->close();


if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plan de Aprendizaje</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../../images/favicon.png" type="image/png">

</head>

<body>
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

    <?php renderSidebarEstudiante($primer_nombre, $primer_apellido, $foto_perfil); ?>

    <!-- Toast -->
    <?php if (isset($_GET['status'])): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <?php
                    // Determinar el tipo de icono según el estado
                    $success_status = ['success', 'update', 'deleted'];
                    if (in_array($_GET['status'], $success_status)) : ?>
                        <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                        <strong class="me-auto">
                            <?php
                            switch ($_GET['status']) {
                                case 'success':
                                    echo 'Registro Exitoso';
                                    break;
                                case 'update':
                                    echo 'Actualización Exitosa';
                                    break;
                                case 'deleted':
                                    echo 'Eliminación Exitosa';
                                    break;
                            }
                            ?>
                        </strong>
                    <?php else: ?>
                        <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                        <strong class="me-auto">Error</strong>
                    <?php endif; ?>
                    <small>Justo ahora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>

                <div class="toast-body">
                    <?php
                    switch ($_GET['status']) {
                        // Éxitos
                        case 'success':
                            echo "Los datos de la entidad receptora se han registrado correctamente.";
                            break;
                        case 'update':
                            echo "Los datos se han actualizado correctamente.";
                            break;
                        case 'deleted':
                            echo "El documento se ha eliminado correctamente.";
                            break;

                        // Errores específicos
                        case 'invalid_user':
                            echo "Usuario inválido. Debes iniciar sesión nuevamente.";
                            break;
                        case 'missing_data':
                            echo "Faltan datos en el formulario. Revisa que todos los campos estén completos.";
                            break;
                        case 'invalid_ruc':
                            echo "El RUC ingresado no es válido. Debe contener exactamente 13 dígitos.";
                            break;
                        case 'invalid_email':
                            echo "El correo electrónico del representante no es válido.";
                            break;
                        case 'invalid_phone':
                            echo "El número de teléfono no es válido. Debe contener solo números (10 dígitos).";
                            break;
                        case 'invalid_extension':
                            echo "Solo se permiten archivos de imagen: PNG, JPG, JPEG o GIF para el logo.";
                            break;
                        case 'no_logo_file':
                            echo "Debes seleccionar el logo de la entidad receptora.";
                            break;
                        case 'upload_error':
                            echo "Hubo un error al subir el logo. Intenta nuevamente.";
                            break;
                        case 'db_error':
                            echo "Error al guardar los datos en la base de datos.";
                            break;
                        case 'prepare_error':
                            echo "Error en la preparación de la consulta SQL.";
                            break;

                        // Otros casos
                        case 'not_found':
                            echo "No se encontraron datos del usuario.";
                            break;
                        default:
                            echo "Ocurrió un error inesperado. Intenta nuevamente.";
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <!-- Content -->
    <div class="content" id="content">
        <div class="container">
            <h1 class="mb-2 text-center fw-bold">Carta de Compromiso</h1>

            <?php if (empty($estado) || $estado === 'Corregir'): ?>

                <div class="card shadow-lg container-fluid">
                    <div class="card-body">
                        <form action="../estudiante/logic/documento-cinco.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <h2 class="card-title text-center mb-3">Datos de la Entidad Receptora</h2>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label for="logo-entidad" class="form-label fw-bold">Logo de la entidad receptora:</label>
                                        <input type="file" class="form-control" id="logo-entidad" name="logo-entidad" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="ciudad" class="form-label fw-bold">Ciudad:</label>
                                        <input type="text" class="form-control" id="ciudad" name="ciudad" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="nombre_entidad" class="form-label fw-bold">Nombre entidad receptora:</label>
                                        <input type="text" class="form-control" id="nombre_entidad" name="nombre_entidad" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="ruc" class="form-label fw-bold">RUC:</label>
                                        <input type="text" class="form-control" id="ruc" name="ruc" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="direccion-entidad" class="form-label fw-bold">Dirección de la entidad receptora:</label>
                                        <input type="text" class="form-control" id="direccion-entidad" name="direccion-entidad" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label for="nombres-representante-rrhh" class="form-label fw-bold">Nombres del representante de RRHH:</label>
                                        <input type="text" class="form-control" id="nombres-representante-rrhh" name="nombres-representante-rrhh" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="correo-entidad" class="form-label fw-bold">Correo electrónico de la entidad receptora:</label>
                                        <input type="email" class="form-control" id="correo-entidad" name="correo-entidad" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="numero_representante_rrhh" class="form-label fw-bold">Teléfono:</label>
                                        <input type="number" class="form-control" id="numero_representante_rrhh" name="numero_representante_rrhh" required>
                                    </div>
                                </div>
                                <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">

                            </div>

                            <div class="text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                                <button type="submit" class="btn">Enviar Datos</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <h3 class="text-center mt-2 mb-3">Estado del Documento</h3>
                <div class="table-responsive">
                    <table class="table table-bordered shadow-lg">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Logo de la entidad receptora</th>
                                <th>Ciudad</th>
                                <th>Nombre entidad receptora</th>
                                <th>RUC</th>
                                <th>Dirección de la entidad receptora</th>
                                <th>Nombres del representante de RRHH</th>
                                <th>Correo electrónico de la entidad receptora</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- ✅ Aquí tus datos -->
                                <td class="text-center">
                                    <a href="../uploads/logo-entidad/<?php echo $logo_entidad_receptora; ?>" target="_blank">
                                        Ver Logo
                                    </a>
                                </td>
                                <td class="text-center"><?php echo $nombre_ciudad; ?></td>
                                <td class="text-center"><?php echo $nombre_entidad_receptora; ?></td>
                                <td class="text-center"><?php echo $ruc; ?></td>
                                <td class="text-center"><?php echo $direccion_entidad_receptora; ?></td>
                                <td class="text-center"><?php echo $nombre_representante_rrhh; ?></td>
                                <td class="text-center"><?php echo $correo_representante; ?></td>
                                <td class="text-center"><?php echo $numero_representante_rrhh; ?></td>
                                <td class="text-center">
                                    <?php
                                    // Lógica para asignar la clase de Bootstrap según el estado
                                    $badgeClass = '';

                                    if ($estado === 'Pendiente') {
                                        $badgeClass = 'badge bg-warning text-dark';
                                    } elseif ($estado === 'Corregir') {
                                        $badgeClass = 'badge bg-danger';
                                    } elseif ($estado === 'Aprobado') {
                                        $badgeClass = 'badge bg-success';
                                    } else {
                                        $badgeClass = 'badge bg-secondary';
                                    }
                                    ?>

                                    <span class="<?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($estado); ?>
                                    </span>
                                </td>

                                <!-- ✅ Acciones -->
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-warning" onclick="window.location.href='for-cinco-edit.php?id=<?php echo $id; ?>'">
                                            <i class='bx bx-edit-alt'></i>
                                        </button>

                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalImprimir<?php echo $id; ?>">
                                            <i class='bx bxs-file-pdf'></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>

                        <!-- ✅ Modal fuera de la tabla -->
                        <div class="modal fade" id="modalImprimir<?php echo $id; ?>" tabindex="-1" aria-labelledby="modalImprimirLabel<?php echo $id; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="../estudiante/pdf/doc-cinco-pdf.php" method="GET" target="_blank">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalImprimirLabel<?php echo $id; ?>">¿Desea generar el documento?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Se generará un documento en formato PDF.</p>
                                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Aceptar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </table>
                </div>
        </div>
    <?php endif; ?>
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
    <script src="../js/expLaboral.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>