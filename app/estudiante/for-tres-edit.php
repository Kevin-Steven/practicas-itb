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

$sql_doc_tres = "SELECT
    d3.id,
    d3.estado, 
    d3.nombre_entidad_receptora,
    d3.departamento_entidad_receptora, 
    d3.nombres_tutor_receptor, 
    d3.cargo_tutor_receptor, 
    d3.numero_telefono_tutor_receptor, 
    d3.ciudad_entidad_receptora,
    d3.motivo_rechazo
FROM documento_tres d3
WHERE d3.usuario_id = ? 
ORDER BY d3.id DESC";

$stmt_doc_tres = $conn->prepare($sql_doc_tres);
$stmt_doc_tres->bind_param("i", $usuario_id);
$stmt_doc_tres->execute();
$result_doc_tres = $stmt_doc_tres->get_result();

$estado = $estado ?? null;

while ($row = $result_doc_tres->fetch_assoc()) {
    $id = $row['id'] ?? null;
    $estado = $row['estado'] ?? null;
    $nombre_entidad_receptora = $row['nombre_entidad_receptora'] ?? null;
    $departamento_entidad_receptora = $row['departamento_entidad_receptora'] ?? null;
    $nombres_tutor_receptor = $row['nombres_tutor_receptor'] ?? null;
    $cargo_tutor_receptor = $row['cargo_tutor_receptor'] ?? null;
    $numero_telefono_tutor_receptor = $row['numero_telefono_tutor_receptor'] ?? null;
    $ciudad_entidad_receptora = $row['ciudad_entidad_receptora'] ?? null;
    $motivo_rechazo = $row['motivo_rechazo'] ?? null;
}

$stmt_doc_tres->close();

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
    <?php renderSidebarEstudiante($primer_nombre, $primer_apellido, $foto_perfil); ?>
    <!-- Content -->
    <div class="content" id="content">
        <div class="container">
            <h1 class="mb-2 text-center fw-bold">Asignación de Estudiante a Prácticas Laborales</h1>
            <div class="card shadow-lg container-fluid">
                <div class="card-body">
                    <form action="../estudiante/logic/documento-tres-actualizar.php" class="enviar-tema" method="POST">
                        <div class="row">
                            <h2 class="card-title text-center">Datos de la Entidad Receptora</h2>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="nombre_entidad_receptora" class="form-label fw-bold">Nombre Entidad Receptora:</label>
                                    <input type="text" class="form-control" id="nombre_entidad_receptora" name="nombre_entidad_receptora" placeholder="Ej. Empresa de Software S.A." value="<?php echo $nombre_entidad_receptora; ?>" required>
                                </div>

                                <div class="mb-2">
                                    <label for="nombres_tutor_receptor" class="form-label fw-bold">Nombres Tutor Entidad Receptora:</label>
                                    <input type="text" class="form-control" id="nombres_tutor_receptor" name="nombres_tutor_receptor" placeholder="Ej. Juan Pérez" value="<?php echo $nombres_tutor_receptor; ?>" required>
                                </div>

                                <div class="mb-2">
                                    <label for="cargo_tutor_receptor" class="form-label fw-bold">Cargo Tutor Entidad Receptora:</label>
                                    <input type="text" class="form-control" id="cargo_tutor_receptor" name="cargo_tutor_receptor" placeholder="Ej. Gerente de Recursos Humanos" value="<?php echo $cargo_tutor_receptor; ?>" required>
                                </div>

                                <div class="mb-2">
                                    <label for="ciudad_entidad_receptora" class="form-label fw-bold">Ciudad Entidad Receptora:</label>
                                    <input type="text" class="form-control" id="ciudad_entidad_receptora" name="ciudad_entidad_receptora" placeholder="Ej. Quito" value="<?php echo $ciudad_entidad_receptora; ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="departamento_entidad_receptora" class="form-label fw-bold">Departamento Entidad Receptora:</label>
                                    <input type="text" class="form-control" id="departamento_entidad_receptora" name="departamento_entidad_receptora" placeholder="Ej. Sistemas" value="<?php echo $departamento_entidad_receptora; ?>" required>
                                </div>

                                <div class="mb-2">
                                    <label for="numero_telefono_tutor_receptor" class="form-label fw-bold">Número de Teléfono Tutor Entidad Receptora:</label>
                                    <input type="text" class="form-control" id="numero_telefono_tutor_receptor" name="numero_telefono_tutor_receptor" placeholder="Ej. 0987654321" pattern="[0-9]{10}" title="Debe contener 10 dígitos numéricos" value="<?php echo $numero_telefono_tutor_receptor; ?>" required>
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
</body>

</html>