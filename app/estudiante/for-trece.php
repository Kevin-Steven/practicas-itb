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

$sql_doc_trece = "SELECT 
    dt.id,
    dt.estado,

    d3.nombre_entidad_receptora,
    d3.ciudad_entidad_receptora,
    d3.nombres_tutor_receptor,

    d2.hora_practicas,
    d2.fecha_inicio,
    d2.fecha_fin,

    d5.nombre_representante_rrhh,
    d5.numero_institucional,
    d5.correo_institucional,
    d5.logo_entidad_receptora,
    d5.direccion_entidad_receptora,

    d5.estado as estado_doc_cinco

FROM documento_trece dt
LEFT JOIN documento_tres d3 ON dt.usuario_id = d3.usuario_id
LEFT JOIN documento_dos d2 ON dt.usuario_id = d2.usuario_id
LEFT JOIN documento_cinco d5 ON dt.usuario_id = d5.usuario_id
WHERE dt.usuario_id = ?
ORDER BY dt.id DESC
LIMIT 1";

$stmt_doc_trece = $conn->prepare($sql_doc_trece);
$stmt_doc_trece->bind_param("i", $usuario_id);
$stmt_doc_trece->execute();
$result_doc_trece = $stmt_doc_trece->get_result();

while ($row = $result_doc_trece->fetch_assoc()) {
    $id = $row['id'] ?? null;
    $estado = $row['estado'] ?? null;

    $direccion_entidad_receptora = $row['direccion_entidad_receptora'] ?? null;
    $logo_entidad_receptora = $row['logo_entidad_receptora'] ?? null;
    $nombre_representante_rrhh = $row['nombre_representante_rrhh'] ?? null;
    $ciudad_entidad_receptora = $row['ciudad_entidad_receptora'] ?? null;
    $nombres_tutor_receptor = $row['nombres_tutor_receptor'] ?? null;
    $hora_practicas = $row['hora_practicas'] ?? null;
    $fecha_inicio = $row['fecha_inicio'] ?? null;
    $fecha_fin = $row['fecha_fin'] ?? null;
    $numero_institucional = $row['numero_institucional'] ?? null;
    $correo_institucional = $row['correo_institucional'] ?? null;
    $estado_doc_cinco = $row['estado_doc_cinco'] ?? null;
}


$stmt_doc_trece->close();


if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carta de Compromiso</title>
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
            <h1 class="mb-2 text-center fw-bold">Certificación de realización de prácticas laborales</h1>

            <?php if (empty($estado_doc_cinco)): ?>

                <p class="text-center mt-3 mb-3">Debes de completar los datos en la <strong>fase 1</strong> para poder generar este documento</p>
                <?php else: ?>

                <h3 class="text-center mt-2 mb-3">Estado del Documento</h3>
                <div class="table-responsive">
                    <table class="table table-bordered shadow-lg">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Logo de la entidad receptora</th>
                                <th>Dirección de la entidad receptora</th>
                                <th>Ciudad de la entidad receptora</th>
                                <th>Nombres del tutor receptor</th>
                                <th>Hora de prácticas</th>
                                <th>Fecha de inicio</th>
                                <th>Fecha de fin</th>
                                <th>Nombres del representante de RRHH</th>
                                <th>Correo Institucional</th>
                                <th>Teléfono Institucional</th>
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
                                <td class="text-center"><?php echo $direccion_entidad_receptora; ?></td>
                                <td class="text-center"><?php echo $ciudad_entidad_receptora; ?></td>
                                <td class="text-center"><?php echo $nombres_tutor_receptor; ?></td>
                                <td class="text-center"><?php echo $hora_practicas; ?></td>
                                <td class="text-center"><?php echo $fecha_inicio; ?></td>
                                <td class="text-center"><?php echo $fecha_fin; ?></td>
                                <td class="text-center"><?php echo $nombre_representante_rrhh; ?></td>
                                <td class="text-center"><?php echo $correo_institucional; ?></td>
                                <td class="text-center"><?php echo $numero_institucional; ?></td>
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
                                    <form action="../estudiante/pdf/software/doc-trece-pdf.php" method="GET" target="_blank">
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
                                            <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
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

    <?php renderFooterAdmin(); ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js"></script>
    <script src="../js/number.js"></script>
</body>

</html>