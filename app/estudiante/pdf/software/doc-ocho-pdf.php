<?php
session_start();
require '../../../config/config.php';
require_once('../../../../TCPDF-main/tcpdf.php');

// Verificar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso no autorizado. Por favor, inicia sesión.");
}

$usuario_id = $_SESSION['usuario_id'];

// Verificar y sanitizar el ID del documento
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID no proporcionado o vacío.");
}

$documento_id = intval($_GET['id']);
if ($documento_id <= 0) {
    die("ID inválido.");
}

// Obtener el rol del usuario
$sql_rol = "SELECT rol FROM usuarios WHERE id = ?";
$stmt_rol = $conn->prepare($sql_rol);
$stmt_rol->bind_param("i", $usuario_id);
$stmt_rol->execute();
$result_rol = $stmt_rol->get_result();

if ($result_rol->num_rows === 0) {
    die("Usuario no encontrado.");
}

$rol = $result_rol->fetch_assoc()['rol'];

// Consulta: depende del rol (estudiante solo puede ver su documento)
if ($rol === 'estudiante') {
    $sql = "SELECT u.nombres, u.apellidos, u.cedula, c.carrera AS carrera, u.periodo, d8.motivo_rechazo, d3.departamento_entidad_receptora,
            ia.semana_inicio, ia.semana_fin, ia.horas_realizadas, ia.actividades_realizadas, d2.fecha_inicio, d2.fecha_fin, d2.hora_practicas,
            d3.nombres_tutor_receptor as tutor_entidad, d3.numero_telefono_tutor_receptor, d3.nombre_entidad_receptora as nombre_entidad,
            d8.nombre_doc
            FROM usuarios u
            LEFT JOIN documento_ocho d8 ON d8.usuario_id = u.id
            LEFT JOIN informe_actividades ia ON d8.id = ia.documento_ocho_id
            LEFT JOIN documento_dos d2 ON u.id = d2.usuario_id
            LEFT JOIN documento_tres d3 ON u.id = d3.usuario_id
            LEFT JOIN documento_seis d6 ON u.id = d6.usuario_id
            LEFT JOIN documento_cinco d5 ON u.id = d5.usuario_id
            INNER JOIN carrera c ON u.carrera_id = c.id
            WHERE d8.id = ? AND u.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $documento_id, $usuario_id);
} else {
    // El gestor puede ver cualquier documento
    $sql = "SELECT u.nombres, u.apellidos, u.cedula, c.carrera AS carrera, u.periodo, d8.motivo_rechazo, d3.departamento_entidad_receptora,
            ia.semana_inicio, ia.semana_fin, ia.horas_realizadas, ia.actividades_realizadas, d2.fecha_inicio, d2.fecha_fin, d2.hora_practicas,
            d3.nombres_tutor_receptor as tutor_entidad, d3.numero_telefono_tutor_receptor, d3.nombre_entidad_receptora as nombre_entidad,
            d8.nombre_doc
            FROM usuarios u
            LEFT JOIN documento_ocho d8 ON d8.usuario_id = u.id
            LEFT JOIN informe_actividades ia ON d8.id = ia.documento_ocho_id
            LEFT JOIN documento_dos d2 ON u.id = d2.usuario_id
            LEFT JOIN documento_tres d3 ON u.id = d3.usuario_id
            LEFT JOIN documento_seis d6 ON u.id = d6.usuario_id
            LEFT JOIN documento_cinco d5 ON u.id = d5.usuario_id
            INNER JOIN carrera c ON u.carrera_id = c.id
            WHERE d8.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $documento_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No tienes permiso para ver este documento o no existe.");
}

$actividades = [];

// ✅ Guardamos los registros múltiples en el array $actividades
while ($row = $result->fetch_assoc()) {
    // Los datos del estudiante se guardan desde la primera fila
    if (empty($estudiante)) {
        $estudiante = $row;
    }

    // Guardamos cada actividad
    $actividades[] = [
        'semana_inicio' => $row['semana_inicio'] ?: 'N/A',
        'semana_fin' => $row['semana_fin'] ?: 'N/A',
        'horas_realizadas' => $row['horas_realizadas'] ?: 'N/A',
        'actividades_realizadas' => $row['actividades_realizadas'] ?: 'N/A'
    ];
}

// Extraer datos generales (solo se hace una vez)
$nombres = $estudiante['apellidos'] . ' ' . $estudiante['nombres'];
$cedula = $estudiante['cedula'] ?: 'N/A';
$carrera = $estudiante['carrera'] ?: 'N/A';
$periodoAcademico = $estudiante['periodo'] ?: 'N/A';
$horas_practicas = $estudiante['hora_practicas'] ?: 'N/A';
$nombre_tutor_academico = $estudiante['tutor_entidad'] ?: 'N/A';
$telefono_tutor = $estudiante['numero_telefono_tutor_receptor'] ?: 'N/A';
$nombre_entidad = $estudiante['nombre_entidad'] ?: 'N/A';
$departamento = $estudiante['departamento_entidad_receptora'] ?: 'N/A';
$motivo_rechazo = $estudiante['motivo_rechazo'] ?: 'N/A';
$nombre_doc = $estudiante['nombre_doc'] ?: 'N/A';
$fecha_inicio_larga = $estudiante['fecha_inicio'] ? formato_fecha_larga($estudiante['fecha_inicio']) : 'N/A';
$fecha_fin_larga = $estudiante['fecha_fin'] ? formato_fecha_larga($estudiante['fecha_fin']) : 'N/A';


function formato_fecha_larga($fecha)
{
    $meses = [
        'enero',
        'febrero',
        'marzo',
        'abril',
        'mayo',
        'junio',
        'julio',
        'agosto',
        'septiembre',
        'octubre',
        'noviembre',
        'diciembre'
    ];

    $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fecha_obj) return 'N/A';

    $dia = $fecha_obj->format('d');
    $mes = $meses[(int)$fecha_obj->format('m') - 1];
    $anio = $fecha_obj->format('Y');

    return "$dia de $mes del $anio";
}

class CustomPDF extends TCPDF
{
    public function Header()
    {
        $margen_derecha = 10; // Ajusta este valor según necesites

        $this->Image('../../../../images/index.png', 15, 12, 20);

        // Fuente y alineación
        $this->SetFont('times', 'B', 11);
        $this->SetY(10);
        $this->SetX($margen_derecha + 20); // Ajuste de margen derecho
        $this->Cell(0, 1, 'INSTITUTO SUPERIOR TECNOLÓGICO BOLIVARIANO DE TECNOLOGÍA', 0, 1, 'C');

        $this->SetFont('times', '', 11);
        $this->SetX($margen_derecha + 20);

        $html = '<strong>Dirección:</strong> Víctor Manuel Rendón 236 y Pedro Carbo, Guayaquil';
        $this->writeHTMLCell(0, 1, '', '', $html, 0, 1, false, true, 'C');

        $this->SetX($margen_derecha + 20);
        $html = '<strong>Teléfonos:</strong> (04) 5000175 – 1800 ITB-ITB';
        $this->writeHTMLCell(0, 1, '', '', $html, 0, 1, false, true, 'C');

        $this->SetX($margen_derecha + 20);
        $html = '<strong>Correo:</strong> <span style="text-decoration: underline;">info@bolivariano.edu.ec</span> &nbsp;<strong>Web:</strong> <span style="text-decoration: underline;">www.itb.edu.ec</span>';
        $this->writeHTMLCell(0, 1, '', '', $html, 0, 1, false, true, 'C');


        $this->Ln(5);

        if ($this->PageNo() == 1) {
            $this->SetY(25);
        } else {
            $this->SetY(30);
        }
    }

    public function Footer() {}
}

// Inicializar TCPDF
$pdf = new CustomPDF();
$pdf->AddPage();
$pdf->SetMargins(15, 35, 15);
$pdf->SetY(35);


$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'INFORME DE ACTIVIDADES', 0, 1, 'C');
$pdf->Ln(3);

$pdf->SetFont('times', '', 10);

$html_tabla1 = '
    <table border="0.5" cellpadding="2" cellspacing="0">
        <tr>
            <th colspan="2" style="text-align: center; font-size: 10px;"><strong>DATOS GENERALES DEL ESTUDIANTE</strong></th>
        </tr>
        <tr>
            <td style="width: 70%;"><strong>Apellidos y Nombres:</strong><br>' . $nombres . '</td>
            <td style="width: 30%;"><strong>Cédula de identidad:</strong><br>' . $cedula . '</td>
        </tr>

    </table>
    <table border="0.5" cellpadding="2" cellspacing="0">
        <tr>
            <td><strong>Carrera:</strong><br>' . $carrera . '</td>
        </tr>

    </table>

    <table border="0.5" cellpadding="2" cellspacing="0">
        <tr>
            <th colspan="6" style="text-align: center; font-size: 10px;"><strong>PERIODO PRÁCTICA PREPROFESIONAL</strong></th>
        </tr>
        <tr>
            <td style="font-size: 10px; width: 12%;"><strong>Fecha Inicio:</strong></td>
            <td style="font-size: 10px; width: 25%;">' . $fecha_inicio_larga . '</td>
            <td style="font-size: 10px; width: 12%;"><strong>Fecha Fin:</strong></td>
            <td style="font-size: 10px; width: 26%;">' . $fecha_fin_larga . '</td>
            <td style="font-size: 10px; width: 15%;"><strong>Horas Prácticas:</strong></td>
            <td style="font-size: 10px; width: 10%;">' . $horas_practicas . '</td>
        </tr>
    </table>

    <table border="0.5" cellpadding="2" cellspacing="0">

        <tr>
            <th colspan="6" style="text-align: center; font-size: 10px;"><strong>DATOS GENERALES DE ENTIDAD FORMADORA</strong></th>
        </tr>
        <tr>
            <td style="width: 25%;"><strong>Entidad receptora:</strong></td>
            <td style="width: 75%;" colspan="5">' . $nombre_entidad . '</td>
        </tr>
        <tr>
            <td><strong>Departamento /Área y/o Rotación:</strong></td>
            <td style="width: 75%;" colspan="5">' . $departamento . '</td>
        </tr>
        <tr>
            <td><strong>Tutor entidad receptora</strong></td>
            <td colspan="3">Ing. ' . $nombre_tutor_academico . '</td>
            <td><strong>Teléfono</strong></td>
            <td>' . $telefono_tutor . '</td>
        </tr>
    </table>

    <table border="0.5" cellpadding="3" cellspacing="0" width="100%">
    <!-- Título -->
    <tr style="text-align: center; font-size: 11px; font-weight: bold;">
        <td colspan="3">Registro de actividades</td>
    </tr>

    <!-- Encabezados -->
    <tr style="text-align: center; font-weight: bold;">
        <td style="width: 30%;">Semanas/Fecha:</td>
        <td style="width: 20%;">Horas realizadas</td>
        <td style="width: 50%;">Actividades realizadas</td>
    </tr>
';
$meses_es = [
    1  => 'enero',
    2  => 'febrero',
    3  => 'marzo',
    4  => 'abril',
    5  => 'mayo',
    6  => 'junio',
    7  => 'julio',
    8  => 'agosto',
    9  => 'septiembre',
    10 => 'octubre',
    11 => 'noviembre',
    12 => 'diciembre'
];

foreach ($actividades as $actividad) {
    // Crear objetos DateTime desde los valores que ya tienes
    $inicio = new DateTime($actividad['semana_inicio']);
    $fin = new DateTime($actividad['semana_fin']);

    // Mes de la fecha final para el texto
    $numero_mes_fin = (int) $fin->format('n');
    $mes_nombre = $meses_es[$numero_mes_fin];

    // Formatear el texto de la fecha como "Fecha 03 al 07, Marzo de 2025"
    $formato_fecha = 'Fecha ' . $inicio->format('d') . ' al ' . $fin->format('d') . ', ' . ucfirst($mes_nombre) . ' de ' . $fin->format('Y');

    // Ahora generas la fila de la tabla con el formato bonito
    $html_tabla1 .= '
    <tr>
        <td>' . htmlspecialchars($formato_fecha) . '</td>
        <td style="text-align: center;">' . htmlspecialchars($actividad['horas_realizadas']) . ' horas</td>
        <td>' . htmlspecialchars($actividad['actividades_realizadas']) . '</td>
    </tr>
    ';
}

$html_tabla1 .= '</table>';


$html_tabla2 = '
    <table width="100%" style="font-size: 11px;">
  <tr>
    <!-- Firma del Estudiante -->
    <td style="text-align: center; width: 45%;">
      <div>____________________________________</div>
      <strong>Firma del Estudiante</strong>
    </td>

    <td style="width: 10%;"></td>

    <!-- Firma del Tutor de la Entidad Receptora -->
    <td style="text-align: center; width: 45%;">
      <div>____________________________________</div>
      <strong>Firma y sello del Tutor de la Entidad <br>Receptora</strong>
    </td>
  </tr>
</table>
';

$pdf->writeHTML($html_tabla1, true, false, true, false, '');
$pdf->Ln(10);
$pdf->writeHTML($html_tabla2, true, false, true, false, '');

$pdf->Output($nombre_doc . '.pdf', 'I');
