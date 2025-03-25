<?php
session_start();
require '../../../config/config.php';
require_once('../../../../TCPDF-main/tcpdf.php');

// Validar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso no autorizado. Por favor, inicia sesión.");
}

$usuario_id = $_SESSION['usuario_id'];

// Validar ID por GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID no proporcionado o vacío.");
}

$id = intval($_GET['id']);
if ($id <= 0) {
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

if ($rol === 'gestor') {
    // Gestor puede ver cualquier documento
    $sql = "SELECT 
                dt.id,
                dt.nombre_doc,

                u.nombres, u.apellidos, u.cedula, c.carrera AS carrera,

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
                d5.direccion_entidad_receptora

            FROM documento_trece dt
            LEFT JOIN documento_tres d3 ON dt.usuario_id = d3.usuario_id
            LEFT JOIN documento_dos d2 ON dt.usuario_id = d2.usuario_id
            LEFT JOIN documento_cinco d5 ON dt.usuario_id = d5.usuario_id
            LEFT JOIN usuarios u ON dt.usuario_id = u.id
            INNER JOIN carrera c ON u.carrera_id = c.id
            WHERE dt.id = ? AND dt.usuario_id = ?
            ORDER BY dt.id DESC
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $usuario_id);
} else {
    // Estudiante solo puede ver su propio documento
    $sql = "SELECT 
                dt.id,
                dt.nombre_doc,

                u.nombres, u.apellidos, u.cedula, c.carrera AS carrera,

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
                d5.direccion_entidad_receptora

            FROM documento_trece dt
            LEFT JOIN documento_tres d3 ON dt.usuario_id = d3.usuario_id
            LEFT JOIN documento_dos d2 ON dt.usuario_id = d2.usuario_id
            LEFT JOIN documento_cinco d5 ON dt.usuario_id = d5.usuario_id
            LEFT JOIN usuarios u ON dt.usuario_id = u.id
            INNER JOIN carrera c ON u.carrera_id = c.id
            WHERE dt.id = ? AND dt.usuario_id = ?
            ORDER BY dt.id DESC
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $usuario_id);
}

// Ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No tienes permiso para ver este documento o no existe.");
}

$estudiante = $result->fetch_assoc();


// Extraer variables
$nombres = strtoupper($estudiante['apellidos'] . ' ' . $estudiante['nombres']);
$cedula = $estudiante['cedula'] ?: 'N/A';
$carrera = $estudiante['carrera'] ?: 'N/A';
$nombre_doc = $estudiante['nombre_doc'] ?: 'N/A';

$nombre_entidad_receptora = $estudiante['nombre_entidad_receptora'] ?: 'N/A';
$direccion_entidad_receptora = $estudiante['direccion_entidad_receptora'] ?: 'N/A';
$logo_entidad_receptora = $estudiante['logo_entidad_receptora'] ?: 'N/A';
$nombre_ciudad = $estudiante['ciudad_entidad_receptora'] ?: 'N/A';
$nombres_representante_rrhh = $estudiante['nombre_representante_rrhh'] ?: 'N/A';
$numero_institucional = $estudiante['numero_institucional'] ?: 'N/A';
$correo_institucional = $estudiante['correo_institucional'] ?: 'N/A';
$hora_practicas = $estudiante['hora_practicas'] ?: 'N/A';
$nombres_tutor_receptor = $estudiante['nombres_tutor_receptor'] ?: 'N/A';



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
    public function Header() {}

    public function Footer() {}

    public function MultiCellRow($data, $widths, $height)
    {
        $nb = 0;
        foreach ($data as $key => $value) {
            $nb = max($nb, $this->getNumLines($value, $widths[$key]));
        }
        $h = $height * $nb;
        $this->CustomCheckPageBreak($h);

        foreach ($data as $key => $value) {
            $w = $widths[$key];
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->setCellPaddings(1, 0, 1, 0);
            $this->MultiCell($w, $height, trim($value), 0, 'L', 0, 0, '', '', true, 0, false, true, $h, 'M', true);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    public function CustomCheckPageBreak($h)
    {
        if ($this->GetY() + $h > ($this->getPageHeight() - $this->getBreakMargin())) {
            $this->AddPage($this->CurOrientation);
            $this->SetY(25);
        }
    }
}

// Inicializar TCPDF
$pdf = new CustomPDF();
$pdf->SetMargins(23, 35, 23);
$pdf->AddPage();
$pdf->SetY(62);

$pdf->Image('../../../uploads/logo-entidad/' . $logo_entidad_receptora, 85, 20, 40, 40, '');

$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 1, 'CERTIFICACIÓN DE REALIZACIÓN DE PRÁCTICAS LABORALES', 0, 1, 'C');
$pdf->Ln(15);
$pdf->SetFont('times', 'I', 11);
$pdf->Cell(0, 1, 'Guayaquil, ' . $fecha_fin_larga, 0, 1, 'R');
$pdf->Ln(15);
$pdf->SetFont('times', '', 12);

$html_linea1 = '<p style=" line-height: 1.9; text-indent: 34px;">Por medio de la presente, certifico que el (la) estudiante <strong>' . $nombres . '</strong>, con cédula de identidad número <strong>' . $cedula . '</strong>, de la carrera de <strong>' . $carrera . '</strong>, del Instituto Superior Tecnológico
Bolivariano de Tecnología, realizó sus prácticas laborales en la entidad receptora <strong>' . $nombre_entidad_receptora . '</strong>, ubicada en la ciudad de <strong>' . $nombre_ciudad . '</strong>, bajo la supervisión
de: <strong>Ing. ' . $nombres_tutor_receptor . '</strong>, con una duración de <strong>' . $hora_practicas . '</strong> horas, comenzando el
día <strong>' . $fecha_inicio_larga . '</strong> y terminando el día <strong>' . $fecha_fin_larga . '</strong>. </p>';

$html_linea2 = '<p style=" line-height: 1.9; text-indent: 34px;">Esta información se pone a consideración para los fines pertinentes. </p>';
$html_linea3 = '<p style=" line-height: 1.9; text-indent: 34px;">Atentamente, </p>';

$pdf->writeHTMLCell('', '', '', '', $html_linea1, 0, 1, 0, true, 'J', true);
$pdf->writeHTMLCell('', '', '', '', $html_linea2, 0, 1, 0, true, 'J', true);
$pdf->writeHTMLCell('', '', '', '', $html_linea3, 0, 1, 0, true, 'J', true);
$pdf->Ln(7);

$pdf->Ln(13);
$pdf->SetFont('times', '', 11);
$pdf->Cell(0, 1, '__________________________________________________________', 0, 1, 'L');
$pdf->Cell(0, 1, $nombres_representante_rrhh, 0, 1, 'L');
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 3, 'Responsable de la Práctica Preprofesional Laboral por parte de la ', 0, 1, 'L');
$pdf->Cell(0, 3, 'entidad receptora', 0, 1, 'L');
$pdf->SetFont('times', '', 11);
$pdf->Cell(0, 2, 'Dirección: ' . $direccion_entidad_receptora, 0, 1, 'L');
$pdf->Cell(0, 2, 'Teléfono: ' . $numero_institucional, 0, 1, 'L');
$pdf->Cell(0, 2, 'Correo electrónico: ' . $correo_institucional, 0, 1, 'L');

$pdf->Output($nombre_doc . '.pdf', 'I');
