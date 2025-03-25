<?php
session_start();
require '../config/config.php';
require 'sidebar-estudiante.php';
require '../admin/sidebar-admin.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';
$usuario_id = $_SESSION['usuario_id'];

if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

$sql_doc_doce = "SELECT 
       ddc.id,
       ddc.opcion_uno,
       ddc.opcion_dos,
       ddc.opcion_tres,
       ddc.opcion_cuatro,
       ddc.opcion_cinco,
       ddc.opcion_seis,
       ddc.img_practicas_puesto_trabajo,
       ddc.img_puesto_trabajo,
       ddc.img_estudiante_tutor_entidad,
       ddc.img_cierre_practicas,
       ddc.motivo_rechazo,
       ddc.estado
FROM documento_doce ddc
WHERE ddc.usuario_id = ?
ORDER BY ddc.id DESC
LIMIT 1";

$stmt_doc_doce = $conn->prepare($sql_doc_doce);
$stmt_doc_doce->bind_param("i", $usuario_id);
$stmt_doc_doce->execute();
$result_doc_doce = $stmt_doc_doce->get_result();

$estado = null;

if ($row = $result_doc_doce->fetch_assoc()) {
    $id = $row['id'];
    $estado = $row['estado'] ?? null;
    $motivo_rechazo = $row['motivo_rechazo'] ?? null;
    // Puntajes de cada pregunta
    $opcion_uno = $row['opcion_uno'];
    $opcion_dos = $row['opcion_dos'];
    $opcion_tres = $row['opcion_tres'];
    $opcion_cuatro = $row['opcion_cuatro'];
    $opcion_cinco = $row['opcion_cinco'];
    $opcion_seis = $row['opcion_seis'];
    $img_practicas_puesto_trabajo = $row['img_practicas_puesto_trabajo'];
    $img_puesto_trabajo = $row['img_puesto_trabajo'];
    $img_estudiante_tutor_entidad = $row['img_estudiante_tutor_entidad'];
    $img_cierre_practicas = $row['img_cierre_practicas'];
}

$stmt_doc_doce->close();

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Supervisión Entidad</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../../images/favicon.png" type="image/png">

</head>

<body>
    <?php renderSidebarEstudiante($primer_nombre, $primer_apellido, $foto_perfil); ?>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container">
            <h1 class="mb-2 text-center fw-bold">Supervisión de la Práctica Laboral al Estudiante</h1>

            <form action="../estudiante/logic/documento-doce-actualizar.php" class="enviar-tema" method="POST" enctype="multipart/form-data">

                <p class="text-center">
                    Indique con una “X” la evaluación que usted considere adecuada, en el momento de la supervisión
                    durante la Práctica laboral, teniendo en cuenta el cumplimiento de los siguientes indicadores:
                </p>
                <p class="text-center">
                    <strong>Supervisado</strong> ◉ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <strong>No Supervisado</strong> ○

                </p>

                <!-- Campo oculto -->
                <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">

                <div class="table-responsive mb-4">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" colspan="1">INDICADORES:</th>
                                <th>Cumple</th>
                                <th>No Cumple</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- DISCIPLINA -->
                            <tr>
                                <td class="text-start">El estudiante se encuentra en el área de trabajo asignada. </td>
                                <td><input type="radio" name="pregunta1" value="1" required <?php echo $opcion_uno == 1 ? 'checked' : ''; ?>></td>
                                <td><input type="radio" name="pregunta1" value="0" <?php echo $opcion_uno == 0 ? 'checked' : ''; ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">El estudiante se observa con la vestimenta adecuada según el área de trabajo.</td>
                                <td><input type="radio" name="pregunta2" value="1" required <?php echo $opcion_dos == 1 ? 'checked' : ''; ?>></td>
                                <td><input type="radio" name="pregunta2" value="0" <?php echo $opcion_dos == 0 ? 'checked' : ''; ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">El estudiante cuenta con los recursos necesarios para realizar sus prácticas.</td>
                                <td><input type="radio" name="pregunta3" value="1" required <?php echo $opcion_tres == 1 ? 'checked' : ''; ?>></td>
                                <td><input type="radio" name="pregunta3" value="0" <?php echo $opcion_tres == 0 ? 'checked' : ''; ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Existencia del docente que asigne y controle las actividades del estudiante.</td>
                                <td><input type="radio" name="pregunta4" value="1" required <?php echo $opcion_cuatro == 1 ? 'checked' : ''; ?>></td>
                                <td><input type="radio" name="pregunta4" value="0" <?php echo $opcion_cuatro == 0 ? 'checked' : ''; ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Los formatos de la carpeta de prácticas pre-profesionales laborales se han ido completando adecuadamente.</td>
                                <td><input type="radio" name="pregunta5" value="1" required <?php echo $opcion_cinco == 1 ? 'checked' : ''; ?>></td>
                                <td><input type="radio" name="pregunta5" value="0" <?php echo $opcion_cinco == 0 ? 'checked' : ''; ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Las actividades que realiza el estudiante están relacionadas con el objeto de la profesión.</td>
                                <td><input type="radio" name="pregunta6" value="1" required <?php echo $opcion_seis == 1 ? 'checked' : ''; ?>></td>
                                <td><input type="radio" name="pregunta6" value="0" <?php echo $opcion_seis == 0 ? 'checked' : ''; ?>></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3 class="text-center mb-3">Evidencias</h3>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Evidencia 1: Realización de prácticas en el puesto de trabajo</label>
                        <input type="file" class="form-control" name="img_practicas_puesto_trabajo" accept="image/*">
                        <?php if (!empty($img_practicas_puesto_trabajo)): ?>
                            <a href="<?php echo $img_practicas_puesto_trabajo; ?>" target="_blank" class="d-block mt-2">Ver imagen</a>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Evidencia 2: Puesto de trabajo</label>
                        <input type="file" class="form-control" name="img_puesto_trabajo" accept="image/*">
                        <?php if (!empty($img_puesto_trabajo)): ?>
                            <a href="<?php echo $img_puesto_trabajo; ?>" target="_blank" class="d-block mt-2">Ver imagen</a>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Evidencia 3: Estudiante y tutor de las prácticas (Entidad Receptora)</label>
                        <input type="file" class="form-control" name="img_estudiante_tutor_entidad" accept="image/*">
                        <?php if (!empty($img_estudiante_tutor_entidad)): ?>
                            <a href="<?php echo $img_estudiante_tutor_entidad; ?>" target="_blank" class="d-block mt-2">Ver imagen</a>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Evidencia 4: Cierre de prácticas laborales (Culminación de Prácticas)</label>
                        <input type="file" class="form-control" name="img_cierre_practicas" accept="image/*">
                        <?php if (!empty($img_cierre_practicas)): ?>
                            <a href="<?php echo $img_cierre_practicas; ?>" target="_blank" class="d-block mt-2">Ver imagen</a>
                        <?php endif; ?>
                    </div>
                </div>


                <!-- Botón de Enviar -->
                <div class="container-fluid text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                    <button type="submit" class="btn btn-primary">Actualizar Datos</button>
                </div>

            </form>
        </div>
    </div>

    <!-- Toast -->
    <?php if (isset($_GET['status'])): ?>
        <?php
        $status = $_GET['status'];

        // Icono + Título según el estado
        if ($status === 'success') {
            $icon = "<i class='bx bx-check-circle fs-4 me-2 text-success'></i>";
            $title = "Formulario enviado";
        } elseif ($status === 'deleted') {
            $icon = "<i class='bx bx-check-circle fs-4 me-2 text-success'></i>";
            $title = "Documento Eliminado";
        } elseif ($status === 'update') {
            $icon = "<i class='bx bx-check-circle fs-4 me-2 text-success'></i>";
            $title = "Documento Actualizado";
        } elseif ($status === 'missing_data') {
            $icon = "<i class='bx bx-error-circle fs-4 me-2 text-danger'></i>";
            $title = "Campos incompletos";
        } elseif ($status === 'db_error') {
            $icon = "<i class='bx bx-error-circle fs-4 me-2 text-danger'></i>";
            $title = "Error de Base de Datos";
        } else {
            $icon = "<i class='bx bx-error-circle fs-4 me-2 text-danger'></i>";
            $title = "Error";
        }

        // Mensaje del cuerpo del toast
        $message = match ($status) {
            'success' => "Evaluación final enviada correctamente.",
            'deleted' => "El documento se ha eliminado correctamente.",
            'update' => "El documento se ha actualizado correctamente.",
            'missing_data' => "Debes responder todas las preguntas antes de enviar.",
            'db_error' => "Hubo un error al guardar los datos en la base de datos.",
            default => "Ocurrió un error inesperado."
        };
        ?>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <?= $icon ?>
                    <strong class="me-auto"><?= $title ?></strong>
                    <small>Justo ahora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    <?= $message ?>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>