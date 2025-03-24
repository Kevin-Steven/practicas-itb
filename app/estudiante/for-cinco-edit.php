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

$sql_doc_cinco = "SELECT 
       d5.id,
       d5.estado, 
       d5.ruc,
       d5.direccion_entidad_receptora,
       d5.logo_entidad_receptora,
       d5.nombre_representante_rrhh,
       d5.numero_institucional,
       d5.correo_institucional
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
    $ruc = $row['ruc'] ?? null;
    $direccion_entidad_receptora = $row['direccion_entidad_receptora'] ?? null;
    $logo_entidad_receptora = $row['logo_entidad_receptora'] ?? null;
    $nombre_representante_rrhh = $row['nombre_representante_rrhh'] ?? null;
    $numero_institucional = $row['numero_institucional'] ?? null;
    $correo_institucional = $row['correo_institucional'] ?? null;
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
    <?php renderSidebarEstudiante($primer_nombre, $primer_apellido, $foto_perfil); ?>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container">
            <h1 class="mb-2 text-center fw-bold">Carta de Compromiso</h1>


            <div class="card shadow-lg container-fluid">
                <div class="card-body">
                    <form action="../estudiante/logic/documento-cinco-actualizar.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <h2 class="card-title text-center mb-3">Datos de la Entidad Receptora</h2>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="logo-entidad" class="form-label fw-bold">Logo de la entidad receptora:</label>
                                    <input type="file" class="form-control" id="logo-entidad" name="logo-entidad" value="<?php echo $logo_entidad_receptora; ?>">
                                    <a href="../uploads/logo-entidad/<?php echo $logo_entidad_receptora; ?>" target="_blank">
                                        Ver Imagen
                                    </a>
                                </div>
                                <div class="mb-2">
                                    <label for="ruc" class="form-label fw-bold">RUC:</label>
                                    <input type="text" class="form-control" maxlength="13" oninput="validateInput(this)" id="ruc" name="ruc" value="<?php echo $ruc; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="direccion-entidad" class="form-label fw-bold">Dirección de la entidad receptora:</label>
                                    <input type="text" class="form-control" id="direccion-entidad" name="direccion-entidad" value="<?php echo $direccion_entidad_receptora; ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="nombres-representante-rrhh" class="form-label fw-bold">Nombres del representante de RRHH:</label>
                                    <input type="text" class="form-control" id="nombres-representante-rrhh" name="nombres-representante-rrhh" value="<?php echo $nombre_representante_rrhh; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="correo-institucional" class="form-label fw-bold">Correo Institucional:</label>
                                    <input type="email" class="form-control" id="correo-institucional" name="correo-institucional" value="<?php echo $correo_institucional; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="numero_institucional" class="form-label fw-bold">Teléfono Institucional:</label>
                                    <input type="text" class="form-control" id="numero_institucional" maxlength="10" name="numero_institucional" value="<?php echo $numero_institucional; ?>" oninput="validateInput(this)">
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

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js"></script>
    <script src="../js/number.js"></script>
</body>

</html>