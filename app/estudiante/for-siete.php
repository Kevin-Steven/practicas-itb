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

$sql_doc_siete = "SELECT 
        u.id AS usuario_id,
        u.nombres,
        u.apellidos,
        u.cedula,
        u.direccion,
        u.telefono,
        u.convencional,
        u.email,
        u.periodo,
        c.carrera AS nombre_carrera,
        cu.paralelo AS nombre_paralelo
    FROM usuarios u
    INNER JOIN carrera c ON u.carrera_id = c.id
    LEFT JOIN cursos cu ON u.curso_id = cu.id
    WHERE u.id = ?";

$stmt_doc_siete = $conn->prepare($sql_doc_siete);
$stmt_doc_siete->bind_param("i", $usuario_id);
$stmt_doc_siete->execute();
$result_doc_siete = $stmt_doc_siete->get_result();

while ($row = $result_doc_siete->fetch_assoc()) {
    $id = $row['usuario_id'] ?? null;
    $nombres = $row['nombres'] ?? null;
    $apellidos = $row['apellidos'] ?? null;
    $cedula = $row['cedula'] ?? null;
    $direccion = $row['direccion'] ?? null;
    $telefono = $row['telefono'] ?? null;
    $convencional = $row['convencional'] ?? null;
    $email = $row['email'] ?? null;
    $periodo = $row['periodo'] ?? null;
    $nombre_carrera = $row['nombre_carrera'] ?? null;
    $nombre_paralelo = $row['nombre_paralelo'] ?? null;
}

$stmt_doc_siete->close();


if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carta de Asignación</title>
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
                    <?php if ($_GET['status'] === 'success'): ?>
                        <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                        <strong class="me-auto">Subida Exitosa</strong>
                    <?php elseif ($_GET['status'] === 'deleted'): ?>
                        <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                        <strong class="me-auto">Documento Eliminado</strong>
                    <?php elseif ($_GET['status'] === 'update'): ?>
                        <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                        <strong class="me-auto">Documento Actualizado</strong>
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
                        case 'success':
                            echo "Los datos se han subido correctamente.";
                            break;
                        case 'deleted':
                            echo "El documento se ha eliminado correctamente.";
                            break;
                        case 'update':
                            echo "El documento se ha actualizado correctamente.";
                            break;
                        case 'invalid_extension':
                            echo "Solo se permiten archivos ZIP.";
                            break;
                        case 'too_large':
                            echo "El archivo supera el tamaño máximo de 20 MB.";
                            break;
                        case 'upload_error':
                            echo "Hubo un error al mover el archivo.";
                            break;
                        case 'db_error':
                            echo "Error al actualizar la base de datos.";
                            break;
                        case 'no_file':
                            echo "No se ha seleccionado ningún archivo.";
                            break;
                        case 'form_error':
                            echo "Error en el envío del formulario.";
                            break;
                        case 'not_found':
                            echo "No se encontraron datos del usuario.";
                            break;
                        case 'missing_data':
                            echo "Faltan datos en el formulario.";
                            break;
                        default:
                            echo "Ocurrió un error desconocido.";
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
            <h1 class="mb-2 text-center fw-bold">Compromiso Ético de Responsabilidad para las Prácticas en el Entorno Laboral Real</h1>

            <h3 class="text-center mt-2 mb-3">Estado del Documento</h3>
            <div class="table-responsive">
                <table class="table table-bordered shadow-lg">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Cédula</th>
                            <th>Dirección</th>
                            <th>Carrera</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- ✅ Aquí tus datos -->
                            <td class="text-center"><?php echo $nombres; ?></td>
                            <td class="text-center"><?php echo $apellidos; ?></td>
                            <td class="text-center"><?php echo $cedula; ?></td>
                            <td class="text-center"><?php echo $direccion; ?></td>
                            <td class="text-center"><?php echo $nombre_carrera; ?></td>
                            <!-- <td class="text-center">
                                <?php
                                // Lógica para asignar la clase de Bootstrap según el estado
                                $badgeClass = '';

                                if ($estado_doc_tres === 'Pendiente') {
                                    $badgeClass = 'badge bg-warning text-dark'; // Amarillo
                                } elseif ($estado_doc_tres === 'Corregir') {
                                    $badgeClass = 'badge bg-danger'; // Rojo
                                } elseif ($estado_doc_tres === 'Aprobado') {
                                    $badgeClass = 'badge bg-success'; // Verde
                                } else {
                                    $badgeClass = 'badge bg-secondary'; // Gris si el estado no es reconocido
                                }
                                ?>

                                <span class="<?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($estado_doc_tres); ?>
                                </span>
                            </td> -->

                            <!-- ✅ Acciones -->
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">

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
                                <form action="../estudiante/pdf/doc-siete-pdf.php" method="GET" target="_blank">
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