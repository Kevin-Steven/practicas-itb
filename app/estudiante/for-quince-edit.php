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
       dq.id,
       dq.img_estudiante_area_trabajo,
       dq.img_estudiante_area_trabajo_herramientas,
       dq.img_estudiante_supervisor_entidad,
       dq.motivo_rechazo,
       dq.estado
FROM documento_quince dq
WHERE dq.usuario_id = ?
ORDER BY dq.id DESC
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
    $img_estudiante_area_trabajo = $row['img_estudiante_area_trabajo'];
    $img_estudiante_area_trabajo_herramientas = $row['img_estudiante_area_trabajo_herramientas'];
    $img_estudiante_supervisor_entidad = $row['img_estudiante_supervisor_entidad'];
}

$stmt_doc_doce->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Evidencias de Prácticas</title>
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
            <h1 class="mb-2 text-center fw-bold">Evidencias Del Estudiante En La Ejecución De Prácticas</h1>

            <div class="card shadow-lg container-fluid">
                <div class="card-body">
                    <form action="../estudiante/logic/documento-quince-actualizar.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">
                        <h3 class="text-center mb-3">Evidencias</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Evidencia 1: Foto del estudiante que evidencie las actividades que realiza durante la práctica en el área de trabajo asignada. </label>
                                <input type="file" class="form-control" name="img_estudiante_area_trabajo" accept="image/*">
                                <?php if (!empty($img_estudiante_area_trabajo)): ?>
                                    <a href="<?php echo $img_estudiante_area_trabajo; ?>" target="_blank" class="d-block mt-2">Ver imagen</a>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Evidencia 2: Foto del estudiante en su puesto de trabajo y las herramientas que utiliza para el desarrollo de las actividades asignadas. </label>
                                <input type="file" class="form-control" name="img_estudiante_area_trabajo_herramientas" accept="image/*">
                                <?php if (!empty($img_estudiante_area_trabajo_herramientas)): ?>
                                    <a href="<?php echo $img_estudiante_area_trabajo_herramientas; ?>" target="_blank" class="d-block mt-2">Ver imagen</a>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Evidencia 3: Foto del estudiante y el supervisor de la empresa que evidencien las actividades realizadas durante la práctica en el área de trabajo asignada.</label>
                                <input type="file" class="form-control" name="img_estudiante_supervisor_entidad" accept="image/*">
                                <?php if (!empty($img_estudiante_supervisor_entidad)): ?>
                                    <a href="<?php echo $img_estudiante_supervisor_entidad; ?>" target="_blank" class="d-block mt-2">Ver imagen</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Botón de Enviar -->
                        <div class="container-fluid text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                            <button type="submit" class="btn">Actualizar Datos</button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>