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

$sql_doc_cuatro = "SELECT 
       dc.id,
       dc.estado,
       dc.pdf_escaneado,
       dc.motivo_rechazo
FROM documento_cuatro dc
WHERE dc.usuario_id = ?
ORDER BY dc.id DESC";

$stmt_doc_cuatro = $conn->prepare($sql_doc_cuatro);
$stmt_doc_cuatro->bind_param("i", $usuario_id);
$stmt_doc_cuatro->execute();
$result_tema = $stmt_doc_cuatro->get_result();

while ($row = $result_tema->fetch_assoc()) {
    $id = $row['id'] ?? null;
    $estado = $row['estado'] ?? null;
    $pdf_escaneado = $row['pdf_escaneado'] ?? null;
    $motivo_rechazo = $row['motivo_rechazo'] ?? null;
}

$stmt_doc_cuatro->close();


if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perfil de Egreso</title>
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
            <h1 class="mb-2 text-center fw-bold">Perfil de Egreso Desarrollo de Software</h1>


            <div class="card shadow-lg container-fluid">
                <div class="card-body">
                    <form action="../estudiante/logic/documento-cuatro-actualizar.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 class="card-title text-center">Subir Documento Escaneado</h2>

                                <div class="mb-2">
                                    <label class="form-label fw-bold">Documento actual:</label><br>
                                    <a href="<?php echo $pdf_escaneado; ?>" target="_blank">Ver PDF</a>
                                </div>

                                <div class="mb-2">
                                    <label for="pdf-escaneado" class="form-label fw-bold">Subir nuevo PDF Escaneado:</label>
                                    <input type="file" class="form-control" id="pdf-escaneado" name="pdf-escaneado" accept="application/pdf">
                                </div>
                            </div>
                            <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">
                        </div>

                        <div class="text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                            <button type="submit" class="btn">Actualizar Documento</button>
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