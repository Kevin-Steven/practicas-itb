<?php
require '../../config/config.php';
require_once('../../../TCPDF-main/tcpdf.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID no proporcionado o vacío.");
}

// Obtener y sanitizar el ID
$id = intval($_GET['id']);
if ($id <= 0) {
    die("ID inválido.");
}

// Consulta para obtener los datos del estudiante
$sql = "SELECT d6.id, 
       d6.actividad_economica, 
       d6.provincia, 
       d6.horario_practica, 
       d6.jornada_laboral,
       d6.nombres_representante, 
       d6.cargo_tutor, 
       d6.numero_practicas, 
       d6.numero_telefono, 
       d6.estado, 
       d6.nombre_doc,
       u.nombres, 
       u.apellidos, 
       d5.nombre_entidad_receptora,
       d5.ruc, 
       d5.direccion_entidad_receptora, 
       d5.logo_entidad_receptora, 
       d5.nombre_ciudad, 
       d5.nombre_representante, 
       d5.numero_institucional, 
       d5.correo_representante,
       d2.fecha_inicio, 
       d2.fecha_fin
FROM documento_seis d6
JOIN usuarios u ON d6.usuario_id = u.id
JOIN documento_cinco d5 ON d6.usuario_id = d5.usuario_id
JOIN documento_dos d2 ON d6.usuario_id = d2.usuario_id
WHERE d6.id = $id
LIMIT 1
";

$result = $conn->query($sql);

// Verificar si hay resultados
if ($result->num_rows === 0) {
    die("No se encontraron datos para este estudiante.");
}

// Obtener los datos
$estudiante = $result->fetch_assoc();

$actividad_economica = $estudiante['actividad_economica'] ?? 'N/A';
$provincia = $estudiante['provincia'] ?? 'N/A';
$horario_practica = $estudiante['horario_practica'] ?? 'N/A';
$jornada_laboral = $estudiante['jornada_laboral'] ?? 'N/A';

$nombres_tutor = $estudiante['nombres_representante'] ?? 'N/A';
$cargo_tutor = $estudiante['cargo_tutor'] ?? 'N/A';
$numero_practicas = $estudiante['numero_practicas'] ?? 'N/A';
$numero_telefono = $estudiante['numero_telefono'] ?? 'N/A';
$nombre_doc = $estudiante['nombre_doc'] ?? 'N/A';

$estado = $estudiante['estado'] ?? 'N/A';

// Datos del estudiante
$nombre_completo_estudiante = $estudiante['apellidos'] . ' ' . $estudiante['nombres'];

// Datos de la entidad receptora (documento_cinco)
$nombre_entidad_receptora = $estudiante['nombre_entidad_receptora'] ?? 'N/A';
$ruc = $estudiante['ruc'] ?? 'N/A';
$direccion_entidad_receptora = $estudiante['direccion_entidad_receptora'] ?? 'N/A';
$logo_entidad_receptora = $estudiante['logo_entidad_receptora'] ?? 'N/A';
$nombre_ciudad = $estudiante['nombre_ciudad'] ?? 'N/A';

$nombre_representante_legal = $estudiante['nombre_representante'] ?? 'N/A';
$numero_institucional = $estudiante['numero_institucional'] ?? 'N/A';
$correo_representante = $estudiante['correo_representante'] ?? 'N/A';

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

        $this->Image('../../../images/index.png', 15, 12, 20);

        // Fuente y alineación
        $this->SetFont('times', 'B', 11);
        $this->SetY(10);
        $this->SetX($margen_derecha + 30); // Ajuste de margen derecho
        $this->Cell(0, 1, 'INSTITUTO SUPERIOR TECNOLÓGICO BOLIVARIANO DE TECNOLOGÍA', 0, 1, 'C');

        $this->SetFont('times', '', 11);
        $this->SetX($margen_derecha + 30);

        $html = '<strong>Dirección:</strong> Víctor Manuel Rendón 236 y Pedro Carbo, Guayaquil';
        $this->writeHTMLCell(0, 1, '', '', $html, 0, 1, false, true, 'C');

        $this->SetX($margen_derecha + 30);
        $html = '<strong>Teléfonos:</strong> (04) 5000175 – 1800 ITB-ITB';
        $this->writeHTMLCell(0, 1, '', '', $html, 0, 1, false, true, 'C');

        $this->SetX($margen_derecha + 30);
        $html = '<strong>Correo:</strong> <span style="text-decoration: underline;">info@bolivariano.edu.ec</span> &nbsp;<strong>Web:</strong> <span style="text-decoration: underline;">www.itb.edu.ec</span>';
        $this->writeHTMLCell(0, 1, '', '', $html, 0, 1, false, true, 'C');


        $this->Ln(5);

        if ($this->PageNo() == 1) {
            $this->SetY(25);
        } else {
            $this->SetY(30);
        }
    }

    public function Footer()
    {
        // Dejamos vacío para no tener pie de página
    }

    // Helper para filas de 2 celdas
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
$pdf->SetY(35);

$pdf->SetFont('times', 'B', 12);
$pdf->Cell(0, 1, 'FICHA DE ENTIDAD RECEPTORA', 0, 1, 'C');
$pdf->Ln(3);

$pdf->SetFont('times', '', 12);
$tabla1 = '
<table border="0.5" cellpadding="1" cellspacing="0">
    <tr>
        <td style="font-size: 12px; line-height: 1.7;">
                <strong>Nombre de la entidad receptora:</strong><br>
                ' . $nombre_entidad_receptora . '
        </td>
    </tr>
</table>
';

$tabla2 = '
<table border="0.5" cellpadding="1" cellspacing="0">
    <tr>
        <td style="font-size: 12px; line-height: 1.7;">
                <strong>RUC:</strong><br>
                ' . $ruc . '
        </td>
    </tr>
</table>
';

$tabla3 = '
<table border="0.5" cellpadding="1" cellspacing="0">
    <tr>
        <td style="font-size: 12px; line-height: 1.7;">
                <strong>Actividad económica principal:</strong><br>
                ' . $actividad_economica . '
        </td>
    </tr>
</table>
';

$tabla4 = '
<table border="0.5" cellpadding="1" cellspacing="0">
    <tr>
        <td style="font-size: 12px; line-height: 1.7;">
                <strong>Dirección:</strong><br>
                ' . $direccion_entidad_receptora . '
        </td>
    </tr>
</table>
';

$tabla5 = '
<table border="0.5" cellpadding="1" cellspacing="0" width="100%">
    <tr>
        <td style="width: 50%; font-size: 12px; line-height: 1.7;">
            <strong>Ciudad:</strong><br>
            ' . $nombre_ciudad . '
        </td>
        <td style="width: 50%; font-size: 12px; line-height: 1.7;">
            <strong>Provincia:</strong><br>
            ' . $provincia . '
        </td>
    </tr>
</table>
';

$tabla6 = '
<table border="0.5" cellpadding="1" cellspacing="0" width="100%">
    <tr>
        <td style="width: 50%; font-size: 12px; line-height: 1.7;">
            <strong>Fecha de inicio de la práctica:</strong><br>
            ' . $fecha_inicio_larga . '
        </td>
        <td style="width: 50%; font-size: 12px; line-height: 1.7;">
            <strong>Fecha de culminación de la práctica:</strong><br>
            ' . $fecha_fin_larga . '
        </td>
    </tr>
</table>
';

$tabla7 = '
<table border="0.5" cellpadding="1" cellspacing="0" width="100%">
    <tr>
        <td style="width: 50%; font-size: 12px; line-height: 1.7;">
            <strong>Horario de la práctica:</strong><br>
            ' . $horario_practica . '
        </td>
        <td style="width: 50%; font-size: 12px; line-height: 1.7;">
            <strong>Jornada laboral:</strong><br>
            ' . $jornada_laboral . '
        </td>
    </tr>
</table>
';

$tabla8 = '
<table border="0.5" cellpadding="1" cellspacing="0">
    <tr>
        <td style="font-size: 12px; line-height: 1.7;">
                <strong>Nombres y Apellidos del tutor de la entidad receptora:</strong><br>
                ' . $nombres_tutor . '
        </td>
    </tr>
</table>
';

$tabla9 = '
<table border="0.5" cellpadding="1" cellspacing="0" width="100%">
    <tr>
        <td style="width: 50%; font-size: 12px;">
            <strong>Cargo del tutor de la entidad receptora:</strong><br>
            ' . $cargo_tutor . '
        </td>
        <td style="width: 50%; font-size: 12px;">
            <strong>Número de prácticas:</strong><br>
            ' . $numero_practicas . '
        </td>
    </tr>
</table>
';

$tabla10 = '
<table border="0.5" cellpadding="1" cellspacing="0" width="100%">
    <tr>
        <td style="width: 50%; font-size: 12px; line-height: 1.7;">
            <strong>Número de teléfono institucional: </strong><br>
            ' . $numero_institucional . '
        </td>
        <td style="width: 50%; font-size: 12px; line-height: 1.7;">
            <strong>Número de teléfono celular:</strong><br>
            ' . $numero_telefono . '
        </td>
    </tr>
</table>
';

$tabla11 = '
<table border="0.5" cellpadding="1" cellspacing="0">
    <tr>
        <td style="font-size: 12px; line-height: 1.7;">
                <strong>Dirección de correo electrónico:</strong><br>
                ' . $correo_representante . '
        </td>
    </tr>
</table>
';

$pdf->writeHTMLCell('','', '', '', $tabla1, 0, 1, 0, true, 'J', '','');
$pdf->Ln(4);
$pdf->writeHTMLCell('','', '', '', $tabla2, 0, 1, 0, true, 'J', '','');
$pdf->Ln(4);
$pdf->writeHTMLCell('','', '', '', $tabla3, 0, 1, 0, true, 'J', '','');
$pdf->Ln(4);
$pdf->writeHTMLCell('','', '', '', $tabla4, 0, 1, 0, true, 'J', '','');
$pdf->Ln(4);
$pdf->writeHTMLCell('','', '', '', $tabla5, 0, 1, 0, true, 'J', '','');
$pdf->Ln(4);
$pdf->writeHTMLCell('','', '', '', $tabla6, 0, 1, 0, true, 'J', '','');
$pdf->Ln(4);
$pdf->writeHTMLCell('','', '', '', $tabla7, 0, 1, 0, true, 'J', '','');
$pdf->Ln(4);
$pdf->writeHTMLCell('','', '', '', $tabla8, 0, 1, 0, true, 'J', '','');
$pdf->Ln(4);
$pdf->writeHTMLCell('','', '', '', $tabla9, 0, 1, 0, true, 'J', '','');
$pdf->Ln(4);
$pdf->writeHTMLCell('','', '', '', $tabla10, 0, 1, 0, true, 'J', '','');
$pdf->Ln(4);
$pdf->writeHTMLCell('','', '', '', $tabla11, 0, 1, 0, true, 'J', '','');


$pdf->Ln(7);

$pdf->Ln(10);
$pdf->SetFont('times', 'B', 12);
$pdf->Cell(0, 1, $nombre_completo_estudiante , 0, 1, 'C');
$pdf->Cell(0, 1, '_____________________________________', 0, 1, 'C');

$pdf->Output($nombre_doc . '.pdf', 'I');
