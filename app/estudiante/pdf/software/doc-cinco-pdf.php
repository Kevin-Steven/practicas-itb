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

// Consulta segura adaptada al rol
if ($rol === 'gestor') {
    // Gestor puede ver cualquier documento
    $sql = "SELECT 
                u.nombres, u.apellidos, u.cedula, c.carrera AS carrera,
                cu.paralelo AS paralelo, u.periodo, d2.estado, d2.fecha_inicio, d3.nombre_entidad_receptora, 
                d5.ruc, d5.direccion_entidad_receptora, d5.logo_entidad_receptora, d3.ciudad_entidad_receptora, 
                d5.nombre_representante_rrhh, d5.numero_institucional, d5.correo_institucional, d5.nombre_doc
            FROM documento_dos d2
            LEFT JOIN documento_tres d3 ON d2.usuario_id = d3.usuario_id
            JOIN documento_cinco d5 ON d2.usuario_id = d5.usuario_id
            JOIN usuarios u ON d2.usuario_id = u.id
            INNER JOIN carrera c ON u.carrera_id = c.id
            LEFT JOIN cursos cu ON u.curso_id = cu.id  
            WHERE d2.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
} else {
    // Estudiante solo puede ver su propio documento
    $sql = "SELECT 
                u.nombres, u.apellidos, u.cedula, c.carrera AS carrera,
                cu.paralelo AS paralelo, u.periodo, d2.estado, d2.fecha_inicio, d3.nombre_entidad_receptora, 
                d5.ruc, d5.direccion_entidad_receptora, d5.logo_entidad_receptora, d3.ciudad_entidad_receptora, 
                d5.nombre_representante_rrhh, d5.numero_institucional, d5.correo_institucional, d5.nombre_doc
            FROM documento_dos d2
            LEFT JOIN documento_tres d3 ON d2.usuario_id = d3.usuario_id
            JOIN documento_cinco d5 ON d2.usuario_id = d5.usuario_id
            JOIN usuarios u ON d2.usuario_id = u.id
            INNER JOIN carrera c ON u.carrera_id = c.id
            LEFT JOIN cursos cu ON u.curso_id = cu.id  
            WHERE d2.id = ? AND d2.usuario_id = ?";
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
$nombre_ciudad = $estudiante['ciudad_entidad_receptora'] ?: 'N/A';
$nombre_representante_rrhh = $estudiante['nombre_representante_rrhh'] ?: 'N/A';
$numero_institucional = $estudiante['numero_institucional'] ?: 'N/A';
$correo_institucional = $estudiante['correo_institucional'] ?: 'N/A';

$fecha_inicio_larga = $estudiante['fecha_inicio'] ? formato_fecha_larga($estudiante['fecha_inicio']) : 'N/A';


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
$pdf->Cell(0, 1, 'CARTA DE COMPROMISO', 0, 1, 'C');
$pdf->Ln(3);
$pdf->SetFont('times', 'I', 11);
$pdf->Cell(0, 1, $nombre_ciudad . ', ' . $fecha_inicio_larga, 0, 1, 'R');
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

$html_linea2 = '<p style=" font-size: 11px; line-height: 3;">De mis consideraciones:</p> ';
$html_linea3 = '<p style=" font-size: 11px; line-height: 1.9; text-indent: 34px;">En mi calidad de representante de Talento Humano de la empresa
<strong>' . $nombre_entidad_receptora . '</strong>, con RUC número <strong>' . $ruc . '</strong>,  con sede en <strong>' . $direccion_entidad_receptora . '</strong>, dedicada a Educación; manifiesto nuestro compromiso de participar como “entidad
receptora”, para la realización de las prácticas laborales del estudiante <strong>' . $nombres . '</strong>,
con cédula de identidad número <strong>' . $cedula . '</strong>, de la carrera <strong>' . $carrera . '</strong>,
 del Instituto Superior Tecnológico Bolivariano de Tecnología. <br> Agradecemos de antemano por la predisposición de su Institución, por la atención prestada y
por el trámite que se le dé a la presente, en función del mejoramiento de la calidad de la Educación
Superior ecuatoriana. Con sentimientos de estima y respeto, me suscribo de usted. <br> Atentamente, 
</p>';

$pdf->writeHTMLCell('', '', '', '', $html_linea1, 0, 1, 0, true, 'J', true);
$pdf->writeHTMLCell('', '', '', '', $html_linea2, 0, 1, 0, true, 'J', true);
$pdf->writeHTMLCell('', '', '', '', $html_linea3, 0, 1, 0, true, 'J', true);
$pdf->Ln(7);

$pdf->Ln(13);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, '__________________________________________________________', 0, 1, 'L');
$pdf->Cell(0, 1, $nombre_representante_rrhh, 0, 1, 'L');
$pdf->Cell(0, 8, 'Representante de Talento Humano de la entidad receptora', 0, 1, 'L');
$pdf->SetFont('times', '', 11);
$pdf->Cell(0, 8, 'Dirección: ' . $direccion_entidad_receptora, 0, 1, 'L');
$pdf->Cell(0, 8, 'Teléfono: ' . $numero_institucional, 0, 1, 'L');
$pdf->Cell(0, 8, 'Correo electrónico: ' . $correo_institucional, 0, 1, 'L');

$pdf->Output($nombre_doc . '.pdf', 'I');
