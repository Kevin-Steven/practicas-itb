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
$sql = "SELECT 
            u.nombres, u.apellidos, u.cedula, c.carrera AS carrera,
            cu.paralelo AS paralelo, u.periodo, d2.estado, d2.fecha_inicio, d5.nombre_entidad_receptora, 
            d5.ruc, d5.direccion_entidad_receptora, d5.logo_entidad_receptora, d5.nombre_ciudad, 
            d5.nombre_representante, d5.numero_institucional, d5.correo_representante, d5.nombre_doc
        FROM documento_dos d2
        JOIN documento_cinco d5 ON d2.usuario_id = d5.usuario_id
        JOIN usuarios u ON d2.usuario_id = u.id
        INNER JOIN carrera c ON u.carrera_id = c.id
        LEFT JOIN cursos cu ON u.curso_id = cu.id  
        WHERE d2.id = $id";

$result = $conn->query($sql);

// Verificar si hay resultados
if ($result->num_rows === 0) {
    die("No se encontraron datos para este estudiante.");
}

// Obtener los datos
$estudiante = $result->fetch_assoc();

// Extraer variables
$nombres = $estudiante['apellidos'] . ' ' . $estudiante['nombres'];
$cedula = $estudiante['cedula'] ?: 'N/A';
$carrera = $estudiante['carrera'] ?: 'N/A';
$paralelo = $estudiante['paralelo'] ?: 'N/A';
$periodoAcademico = $estudiante['periodo'] ?: 'N/A';
$estado = $estudiante['estado'] ?: 'N/A';
$nombre_doc = $estudiante['nombre_doc'] ?: 'N/A';

$nombre_entidad_receptora = $estudiante['nombre_entidad_receptora'] ?: 'N/A';
$ruc = $estudiante['ruc'] ?: 'N/A';
$direccion_entidad_receptora = $estudiante['direccion_entidad_receptora'] ?: 'N/A';
$logo_entidad_receptora = $estudiante['logo_entidad_receptora'] ?: 'N/A';
$nombre_ciudad = $estudiante['nombre_ciudad'] ?: 'N/A';
$nombre_representante = $estudiante['nombre_representante'] ?: 'N/A';
$numero_institucional = $estudiante['numero_institucional'] ?: 'N/A';
$correo_representante = $estudiante['correo_representante'] ?: 'N/A';

$fecha_inicio_larga = $estudiante['fecha_inicio'] ? formato_fecha_larga($estudiante['fecha_inicio']) : 'N/A';


function formato_fecha_larga($fecha)
{
    $meses = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
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
    public function Header(){
    }

    public function Footer(){
    }

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

$pdf->Image('../../uploads/logo-entidad/'. $logo_entidad_receptora, 80, 20, 50, 50, '');

$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 1, 'CARTA DE COMPROMISO', 0, 1, 'C');
$pdf->Ln(3);
$pdf->SetFont('times', 'I', 11);
$pdf->Cell(0, 1, $nombre_ciudad . ', '. $fecha_inicio_larga, 0, 1, 'R');
$pdf->Ln(3);

$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'PhD. Roberto Tolozano Benites ', 0, 1, 'L');
$pdf->SetFont('times', '', 11);
$pdf->Cell(0, 1, 'Rector.', 0, 1, 'L');
$pdf->Cell(0, 1, 'Instituto Superior Tecnológico Bolivariano de Tecnología.', 0, 1, 'L');
$pdf->Cell(0, 1, 'Guayaquil.', 0, 1, 'L');
$pdf->Ln(1);

$html_linea1 = '
<p style="font-size: 11px; line-height: 1.5;">En su despacho.</p>';

$html_linea2 ='<p style=" font-size: 11px; line-height: 3;">De mis consideraciones:</p> ';
$html_linea3 ='<p style=" font-size: 11px; line-height: 1.9; text-indent: 34px;">En mi calidad de representante de Talento Humano de la empresa
<strong>'. $nombre_entidad_receptora .'</strong>, con RUC número <strong>'. $ruc .'</strong>,  con sede en <strong>'. $direccion_entidad_receptora .'</strong>, dedicada a Educación; manifiesto nuestro compromiso de participar como “entidad
receptora”, para la realización de las prácticas laborales del estudiante <strong>'. $nombres .'</strong>,
con cédula de identidad número <strong>'. $cedula .'</strong>, de la carrera <strong>'. $carrera .'</strong>,
 del Instituto Superior Tecnológico Bolivariano de Tecnología. <br> Agradecemos de antemano por la predisposición de su Institución, por la atención prestada y
por el trámite que se le dé a la presente, en función del mejoramiento de la calidad de la Educación
Superior ecuatoriana. Con sentimientos de estima y respeto, me suscribo de usted. <br> Atentamente, 
</p>';

$pdf->writeHTMLCell('','','','',$html_linea1,0, 1, 0,true,'J', true);
$pdf->writeHTMLCell('','','','',$html_linea2,0, 1, 0,true,'J', true);
$pdf->writeHTMLCell('','','','',$html_linea3,0, 1, 0,true,'J', true);
$pdf->Ln(7);

$pdf->Ln(13);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, '__________________________________________________________', 0, 1, 'L');
$pdf->Cell(0, 1, $nombre_representante, 0, 1, 'L');
$pdf->Cell(0, 8, 'Representante de Talento Humano de la entidad receptora', 0, 1, 'L');
$pdf->SetFont('times', '', 11);
$pdf->Cell(0, 8, 'Dirección: '.$direccion_entidad_receptora, 0, 1, 'L');
$pdf->Cell(0, 8, 'Teléfono: '.$numero_institucional, 0, 1, 'L');
$pdf->Cell(0, 8, 'Correo electrónico: '.$correo_representante, 0, 1, 'L');

$pdf->Output($nombre_doc .'.pdf', 'I');
