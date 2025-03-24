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

$sql_doc_nueve = "SELECT 
       dn.id,
       dn.opcion_uno_puntaje,
       dn.opcion_dos_puntaje,
       dn.opcion_tres_puntaje,
       dn.opcion_cuatro_puntaje,
       dn.opcion_cinco_puntaje,
       dn.opcion_seis_puntaje,
       dn.opcion_siete_puntaje,
       dn.opcion_ocho_puntaje,
       dn.opcion_nueve_puntaje,
       dn.opcion_diez_puntaje,
       dn.opcion_once_puntaje,
       dn.opcion_doce_puntaje,
       dn.opcion_trece_puntaje,
       dn.opcion_catorce_puntaje,
       dn.opcion_quince_puntaje,
       dn.motivo_rechazo,
       dn.estado
FROM documento_nueve dn
WHERE dn.usuario_id = ?
ORDER BY dn.id DESC
LIMIT 1";

$stmt_doc_nueve = $conn->prepare($sql_doc_nueve);
$stmt_doc_nueve->bind_param("i", $usuario_id);
$stmt_doc_nueve->execute();
$result_doc_nueve = $stmt_doc_nueve->get_result();

$estado = null;

if ($row = $result_doc_nueve->fetch_assoc()) {
    $id = $row['id'];
    $estado = $row['estado'] ?? null;
    $motivo_rechazo = $row['motivo_rechazo'] ?? null;
    // Puntajes de cada pregunta
    $opcion_uno_puntaje = $row['opcion_uno_puntaje'];
    $opcion_dos_puntaje = $row['opcion_dos_puntaje'];
    $opcion_tres_puntaje = $row['opcion_tres_puntaje'];
    $opcion_cuatro_puntaje = $row['opcion_cuatro_puntaje'];
    $opcion_cinco_puntaje = $row['opcion_cinco_puntaje'];
    $opcion_seis_puntaje = $row['opcion_seis_puntaje'];
    $opcion_siete_puntaje = $row['opcion_siete_puntaje'];
    $opcion_ocho_puntaje = $row['opcion_ocho_puntaje'];
    $opcion_nueve_puntaje = $row['opcion_nueve_puntaje'];
    $opcion_diez_puntaje = $row['opcion_diez_puntaje'];
    $opcion_once_puntaje = $row['opcion_once_puntaje'];
    $opcion_doce_puntaje = $row['opcion_doce_puntaje'];
    $opcion_trece_puntaje = $row['opcion_trece_puntaje'];
    $opcion_catorce_puntaje = $row['opcion_catorce_puntaje'];
    $opcion_quince_puntaje = $row['opcion_quince_puntaje'];
}

$stmt_doc_nueve->close();

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Evaluación Conductual</title>
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
            <h1 class="mb-2 text-center fw-bold">Actualizar Evaluación Conductual del Estudiante</h1>

            <form action="../estudiante/logic/documento-nueve-actualizar.php" class="enviar-tema" method="POST" enctype="multipart/form-data">

                <p class="text-center">
                    Indique la evaluación que usted considere adecuada, basada en el desempeño del estudiante durante la Práctica Pre-profesional laboral, y teniendo en cuenta la siguiente escala:
                </p>
                <p class="text-center">
                    <strong>5</strong> - Siempre. &nbsp;&nbsp;
                    <strong>4</strong> - Casi siempre. &nbsp;&nbsp;
                    <strong>3</strong> - Ocasionalmente. &nbsp;&nbsp;
                    <strong>2</strong> - Casi nunca. &nbsp;&nbsp;
                    <strong>1</strong> - Nunca.
                </p>


                <!-- Campo oculto -->
                <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">

                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" colspan="2">INDICADORES:</th>
                                <th>5</th>
                                <th>4</th>
                                <th>3</th>
                                <th>2</th>
                                <th>1</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- DISCIPLINA -->
                            <tr>
                                <td rowspan="4" class="fw-bold align-middle">Disciplina</td>
                                <td class="text-start">Asiste puntualmente a su práctica.</td>
                                <td><input type="radio" name="pregunta1" value="5" <?= ($opcion_uno_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta1" value="4" <?= ($opcion_uno_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta1" value="3" <?= ($opcion_uno_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta1" value="2" <?= ($opcion_uno_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta1" value="1" <?= ($opcion_uno_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Se presenta con adecuado porte y respeto en el área laboral asignada.</td>
                                <td><input type="radio" name="pregunta2" value="5" <?= ($opcion_dos_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta2" value="4" <?= ($opcion_dos_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta2" value="3" <?= ($opcion_dos_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta2" value="2" <?= ($opcion_dos_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta2" value="1" <?= ($opcion_dos_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Manifiesta una actitud de servicio, cooperación y trabajo en equipo.</td>
                                <td><input type="radio" name="pregunta3" value="5" <?= ($opcion_tres_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta3" value="4" <?= ($opcion_tres_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta3" value="3" <?= ($opcion_tres_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta3" value="2" <?= ($opcion_tres_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta3" value="1" <?= ($opcion_tres_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Actúa siguiendo la ética profesional y normas de principios morales.</td>
                                <td><input type="radio" name="pregunta4" value="5" <?= ($opcion_cuatro_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta4" value="4" <?= ($opcion_cuatro_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta4" value="3" <?= ($opcion_cuatro_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta4" value="2" <?= ($opcion_cuatro_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta4" value="1" <?= ($opcion_cuatro_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>

                            <!-- INTEGRACIÓN AL AMBIENTE LABORAL -->
                            <tr>
                                <td rowspan="4" class="fw-bold align-middle">Integración al<br> ambiente laboral</td>
                                <td class="text-start">Cumple con las Normas, Políticas, procedimientos y cultura organizacional.</td>
                                <td><input type="radio" name="pregunta5" value="5" <?= ($opcion_cinco_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta5" value="4" <?= ($opcion_cinco_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta5" value="3" <?= ($opcion_cinco_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta5" value="2" <?= ($opcion_cinco_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta5" value="1" <?= ($opcion_cinco_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Establece una comunicación profesional efectiva y asertiva en el área asignada.</td>
                                <td><input type="radio" name="pregunta6" value="5" <?= ($opcion_seis_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta6" value="4" <?= ($opcion_seis_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta6" value="3" <?= ($opcion_seis_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta6" value="2" <?= ($opcion_seis_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta6" value="1" <?= ($opcion_seis_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Trabaja en iniciativa y soluciones integrales acorde a su asignación de práctica.</td>
                                <td><input type="radio" name="pregunta7" value="5" <?= ($opcion_siete_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta7" value="4" <?= ($opcion_siete_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta7" value="3" <?= ($opcion_siete_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta7" value="2" <?= ($opcion_siete_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta7" value="1" <?= ($opcion_siete_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Demuestra capacidad de adaptación y desenvolvimiento al área asignada.</td>
                                <td><input type="radio" name="pregunta8" value="5" <?= ($opcion_ocho_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta8" value="4" <?= ($opcion_ocho_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta8" value="3" <?= ($opcion_ocho_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta8" value="2" <?= ($opcion_ocho_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta8" value="1" <?= ($opcion_ocho_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>

                            <!-- CONOCIMIENTOS Y HABILIDADES PROFESIONALES -->
                            <tr>
                                <td rowspan="7" class="fw-bold align-middle">Conocimientos y<br> habilidades profesionales</td>
                                <td class="text-start">Aplica adecuadamente los conocimientos teóricos y prácticos del perfil profesional.</td>
                                <td><input type="radio" name="pregunta9" value="5" <?= ($opcion_nueve_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta9" value="4" <?= ($opcion_nueve_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta9" value="3" <?= ($opcion_nueve_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta9" value="2" <?= ($opcion_nueve_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta9" value="1" <?= ($opcion_nueve_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Demuestra adecuadamente las destrezas y habilidades acordes al perfil profesional.</td>
                                <td><input type="radio" name="pregunta10" value="5" <?= ($opcion_diez_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta10" value="4" <?= ($opcion_diez_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta10" value="3" <?= ($opcion_diez_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta10" value="2" <?= ($opcion_diez_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta10" value="1" <?= ($opcion_diez_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Genera soluciones y propuestas halladas en el área de asignación de práctica.</td>
                                <td><input type="radio" name="pregunta11" value="5" <?= ($opcion_once_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta11" value="4" <?= ($opcion_once_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta11" value="3" <?= ($opcion_once_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta11" value="2" <?= ($opcion_once_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta11" value="1" <?= ($opcion_once_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Comunica asertivamente situaciones para la mejora continua del área asignada.</td>
                                <td><input type="radio" name="pregunta12" value="5" <?= ($opcion_doce_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta12" value="4" <?= ($opcion_doce_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta12" value="3" <?= ($opcion_doce_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta12" value="2" <?= ($opcion_doce_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta12" value="1" <?= ($opcion_doce_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Demuestra capacidad resolutiva a casos reales del área asignada.</td>
                                <td><input type="radio" name="pregunta13" value="5" <?= ($opcion_trece_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta13" value="4" <?= ($opcion_trece_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta13" value="3" <?= ($opcion_trece_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta13" value="2" <?= ($opcion_trece_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta13" value="1" <?= ($opcion_trece_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Demuestra proactividad en adquirir nuevos conocimientos en el área asignada de prácticas.</td>
                                <td><input type="radio" name="pregunta14" value="5" <?= ($opcion_catorce_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta14" value="4" <?= ($opcion_catorce_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta14" value="3" <?= ($opcion_catorce_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta14" value="2" <?= ($opcion_catorce_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta14" value="1" <?= ($opcion_catorce_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>
                            <tr>
                                <td class="text-start">Aporta destreza académica en reuniones de trabajo del área asignada.</td>
                                <td><input type="radio" name="pregunta15" value="5" <?= ($opcion_quince_puntaje == 5) ? 'checked' : '' ?> required></td>
                                <td><input type="radio" name="pregunta15" value="4" <?= ($opcion_quince_puntaje == 4) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta15" value="3" <?= ($opcion_quince_puntaje == 3) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta15" value="2" <?= ($opcion_quince_puntaje == 2) ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="pregunta15" value="1" <?= ($opcion_quince_puntaje == 1) ? 'checked' : '' ?>></td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- Botón de Enviar -->
                <div class="container-fluid text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                    <button type="submit" class="btn btn-primary">Actualizar Datos</button>
                </div>

            </form>


        </div>
    </div>

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>