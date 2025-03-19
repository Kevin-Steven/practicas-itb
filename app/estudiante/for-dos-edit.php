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

$sql_doc_dos = "SELECT 
       d2.id,
       d2.estado, 
       d2.fecha_inicio,
       d2.hora_inicio,
       d2.fecha_fin,
       d2.hora_fin,
       d2.documento_eva_s,
       d2.hora_practicas,
       d2.nota_eva_s
FROM documento_dos d2
WHERE d2.usuario_id = ?
ORDER BY d2.id DESC";

$stmt_doc_dos = $conn->prepare($sql_doc_dos);
$stmt_doc_dos->bind_param("i", $usuario_id);
$stmt_doc_dos->execute();
$result_tema = $stmt_doc_dos->get_result();

while ($row = $result_tema->fetch_assoc()) {
    $id = $row['id'] ?? null;
    $estado = $row['estado'] ?? null;
    $fecha_inicio = $row['fecha_inicio'] ?? null;
    $hora_inicio = $row['hora_inicio'] ?? null;
    $fecha_fin = $row['fecha_fin'] ?? null;
    $hora_fin = $row['hora_fin'] ?? null;
    $documento_eva_s = $row['documento_eva_s'] ?? null;
    $horas_practicas = $row['hora_practicas'] ?? null;
    $nota_eva_s = $row['nota_eva_s'] ?? null;
}

$stmt_doc_dos->close();


if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>For 2</title>
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
            <h1 class="mb-2 text-center fw-bold">Datos Generales del Estudiante</h1>

            <div class="card shadow-lg container-fluid">
                <div class="card-body">
                    <form action="../estudiante/logic/documento-dos-actualizar.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <h2 class="card-title text-center">Periodo Práctica <br>Preprofesional</h2>
                                <div class="mb-2">
                                    <label for="fecha_inicio" class="form-label fw-bold">Fecha Inicio:</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="hora_inicio" class="form-label fw-bold">Hora Inicio:</label>
                                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="<?php echo $hora_inicio; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="fecha_fin" class="form-label fw-bold">Fecha Fin:</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="hora_fin" class="form-label fw-bold">Hora Fin:</label>
                                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" value="<?php echo $hora_fin; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="horas_practicas" class="form-label fw-bold">Horas Prácticas:</label>
                                    <input type="number" class="form-control" id="horas_practicas" name="horas_practicas" step="0.01" placeholder="ej. 240" value="<?php echo $horas_practicas; ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h2 class="card-title text-center">Resultados Extraídos del EVA-S del Diagnóstico Inicial</h2>
                                <div class="mb-2">
                                    <label for="eva-s" class="form-label fw-bold">Subir EVA-S:</label>
                                    <input type="file" class="form-control" id="eva_s" name="eva_s" value="<?php echo $documento_eva_s; ?>">
                                    <a href="../uploads/eva-s/<?php echo $documento_eva_s; ?>" target="_blank">
                                        Ver Documento
                                    </a>
                                </div>
                                <div class="mb-2">
                                    <label for="nota_eva-s" class="form-label fw-bold">Nota EVA-S:</label>
                                    <input type="number" min="0" max="100" class="form-control" id="nota_eva-s" name="nota_eva-s" step="0.01" placeholder="ej. 100" value="<?php echo $nota_eva_s; ?>">
                                </div>
                            </div>
                            <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">

                        </div>

                        <div class="text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                            <button type="submit" class="btn">Actualizar Datos</button>
                        </div>
                    </form>
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
    <script src="../js/expLaboral.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>