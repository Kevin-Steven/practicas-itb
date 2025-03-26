<?php
session_start();
require '../../../config/config.php';
require_once('../../../../TCPDF-main/tcpdf.php');

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso no autorizado. Por favor, inicia sesión.");
}

$usuario_id = $_SESSION['usuario_id'];

// Verificar si el ID está presente en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID no proporcionado o vacío.");
}

$documento_id = intval($_GET['id']);
if ($documento_id <= 0) {
    die("ID inválido.");
}

// Obtener el rol del usuario logueado
$sql_rol = "SELECT rol FROM usuarios WHERE id = ?";
$stmt_rol = $conn->prepare($sql_rol);
$stmt_rol->bind_param("i", $usuario_id);
$stmt_rol->execute();
$result_rol = $stmt_rol->get_result();

if ($result_rol->num_rows === 0) {
    die("Usuario no encontrado.");
}

$rol = $result_rol->fetch_assoc()['rol'];

// Consulta condicional según el rol
if ($rol === 'gestor') {
    // El gestor puede ver cualquier documento
    $sql = "SELECT 
            u.nombres, 
            u.apellidos, 
            c.carrera AS carrera, 
            d3.nombre_entidad_receptora,
            d2.fecha_inicio, 
            d2.fecha_fin,
            ddc.opcion_uno,
            ddc.opcion_dos,
            ddc.opcion_tres,
            ddc.opcion_cuatro,
            ddc.opcion_cinco,
            ddc.opcion_seis,
            ddc.img_practicas_puesto_trabajo,
            ddc.img_puesto_trabajo,
            ddc.img_estudiante_tutor_entidad,
            ddc.img_cierre_practicas,
            ddc.motivo_rechazo,
            ddc.estado,
            ddc.nombre_doc
        FROM documento_doce ddc
        INNER JOIN usuarios u ON ddc.usuario_id = u.id
        INNER JOIN carrera c ON u.carrera_id = c.id
        INNER JOIN documento_dos d2 ON ddc.usuario_id = d2.usuario_id
        INNER JOIN documento_tres d3 ON ddc.usuario_id = d3.usuario_id
        WHERE ddc.id = ? AND ddc.usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $documento_id, $usuario_id);
} else {
    // El estudiante solo puede ver su propio documento
    $sql = "SELECT 
            u.nombres, 
            u.apellidos, 
            c.carrera AS carrera, 
            d3.nombre_entidad_receptora,
            d2.fecha_inicio, 
            d2.fecha_fin,
            ddc.opcion_uno,
            ddc.opcion_dos,
            ddc.opcion_tres,
            ddc.opcion_cuatro,
            ddc.opcion_cinco,
            ddc.opcion_seis,
            ddc.img_practicas_puesto_trabajo,
            ddc.img_puesto_trabajo,
            ddc.img_estudiante_tutor_entidad,
            ddc.img_cierre_practicas,
            ddc.motivo_rechazo,
            ddc.estado,
            ddc.nombre_doc
        FROM documento_doce ddc
        INNER JOIN usuarios u ON ddc.usuario_id = u.id
        INNER JOIN carrera c ON u.carrera_id = c.id
        INNER JOIN documento_dos d2 ON ddc.usuario_id = d2.usuario_id
        INNER JOIN documento_tres d3 ON ddc.usuario_id = d3.usuario_id
        WHERE ddc.id = ? AND ddc.usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $documento_id, $usuario_id);
}

// Ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se encontraron datos
if ($result->num_rows === 0) {
    die("No tienes permiso para ver este documento o no existe.");
}

// Obtener los datos
$estudiante = $result->fetch_assoc();


// Extraer variables
$nombres = $estudiante['apellidos'] . ' ' . $estudiante['nombres'];
$carrera = $estudiante['carrera'] ?: 'N/A';
$nombre_doc = $estudiante['nombre_doc'] ?: 'N/A';
$fecha_inicio_larga = $estudiante['fecha_inicio'] ? formato_fecha_larga($estudiante['fecha_inicio']) : 'N/A';
$fecha_fin_larga = $estudiante['fecha_fin'] ? formato_fecha_larga($estudiante['fecha_fin']) : 'N/A';
$nombre_entidad_receptora = $estudiante['nombre_entidad_receptora'] ?: 'N/A';

$opcion_uno = $estudiante['opcion_uno'] ?: null;
$opcion_dos = $estudiante['opcion_dos'] ?: null;
$opcion_tres = $estudiante['opcion_tres'] ?: null;
$opcion_cuatro = $estudiante['opcion_cuatro'] ?: null;
$opcion_cinco = $estudiante['opcion_cinco'] ?: null;
$opcion_seis = $estudiante['opcion_seis'] ?: null;

$img_practicas_puesto_trabajo = $estudiante['img_practicas_puesto_trabajo'] ?: null;
$img_puesto_trabajo = $estudiante['img_puesto_trabajo'] ?: null;
$img_estudiante_tutor_entidad = $estudiante['img_estudiante_tutor_entidad'] ?: null;
$img_cierre_practicas = $estudiante['img_cierre_practicas'] ?: null;

$puntajes = [
    $opcion_uno,
    $opcion_dos,
    $opcion_tres,
    $opcion_cuatro,
    $opcion_cinco,
    $opcion_seis,
];

$cumple = array_sum($puntajes);
$no_cumple = 6 - $cumple;



function generarChecks($valor, $columna)
{
    return ($valor == $columna) ? '☒' : '☐';
}

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
        $margen_derecha = 10;

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
$pdf->AddPage();
$pdf->SetMargins(23, 20, 23);
$pdf->SetY(33);

$pdf->SetFont('times', 'B', 12);
$pdf->Cell(0, 1, 'SUPERVISIÓN DE LA PRÁCTICA LABORAL AL ESTUDIANTE', 0, 1, 'C');
$pdf->SetFont('times', '', 12);
$pdf->Cell(0, 1, '(SOLO PARA USO DEL SUPERVISOR ENTIDAD RECEPTORA)', 0, 1, 'C');
$pdf->Ln(5);

$info = '<p>Indique con una “X” la evaluación que usted considere adecuada, en el momento de la supervisión
durante la Práctica laboral, teniendo en cuenta el cumplimiento de los siguientes indicadores: </p>';
$pdf->writeHTML($info, true, false, true, false, '');
$pdf->Ln(3);

$pdf->SetFont('times', '', 12);

$html_tabla1 = '
    <table border="0.5" cellpadding="3" cellspacing="0">
        <thead>
            <tr>
                <th colspan="2" width="70%" align="center"><strong>INDICADORES:</strong></th>
                <th width="15%" align="center"><strong>Cumple</strong></th>
                <th width="15%" align="center"><strong>No Cumple</strong></th>
            </tr>
        </thead>
        <tbody>
            <!-- Conocimientos -->
            <tr>
                <td width="70%">El estudiante se encuentra en el área de trabajo asignada. </td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_uno, 1) . '</font></td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_uno, 0) . '</font></td>
            </tr>
            <tr>
                <td width="70%">El estudiante se observa con la vestimenta adecuada según el área de trabajo.</td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_dos, 1) . '</font></td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_dos, 0) . '</font></td>
            </tr>
            <tr>
                <td width="70%">El estudiante cuenta con los recursos necesarios para realizar sus prácticas.</td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_tres, 1) . '</font></td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_tres, 0) . '</font></td>
            </tr>
            <tr>
                <td width="70%">Existencia del docente que asigne y controle las actividades del estudiante.</td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_cuatro, 1) . '</font></td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_cuatro, 0) . '</font></td>
            </tr>
            <tr>
                <td width="70%">Los formatos de la carpeta de prácticas pre-profesionales laborales se han ido completando adecuadamente.</td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_cinco, 1) . '</font></td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_cinco, 0) . '</font></td>
            </tr>
            <tr>
                <td width="70%">Las actividades que realiza el estudiante están relacionadas con el objeto de la profesión.</td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_seis, 1) . '</font></td>
                <td width="15%" align="center"><font face="dejavusans">' . generarChecks($opcion_seis, 0) . '</font></td>
            </tr>

    
            <!-- Promedio total -->
            <tr>
                <td align="center"><strong>TOTAL</strong></td>
                <td align="center"><strong>' . $cumple . '</strong></td>
                <td align="center"><strong>' . $no_cumple . '</strong></td>
            </tr>
        </tbody>
    </table>
    <table border="0.5" cellpadding="3" cellspacing="0">
        <tr>
            <td colspan="2" align="center" style="font-size: 11px;"><strong>EJECUCION DE LA PRACTICA:</strong></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 11px; text-align: justify;">El estudiante <strong>' . $nombres . '</strong>, de la carrera de <strong>' . $carrera . '</strong> realiza las siguientes actividades: <strong>Administración de sistemas y parametrización de redes WLAN y LAN.</strong></td>
        </tr>
    </table>
    <table border="0.5" cellpadding="3" cellspacing="0">
        <tr>
            <td colspan="2" align="center" style="font-size: 11px;"><strong>OBSERVACIONES:</strong></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 11px; text-align: justify;">Mediante la supervisión realizada al estudiante se verificó que cumple con los indicadores relacionados con el objeto de su profesión.</td>
        </tr>
    </table>';


$firmas = '  <table width="100%" style="font-size: 11px;">
  <tr>
    <td style="text-align: center; width: 45%;">
      <div>____________________________________</div>
      <strong>Firma y sello supervisor de la<br> entidad receptora</strong>
    </td>

    <td style="width: 10%;"></td>

    <!-- Firma del Tutor de la Entidad Receptora -->
    <td style="text-align: center; width: 45%;">
      <div>____________________________________</div>
      <strong>Firma y sello del Docente Tutor</strong>
    </td>
  </tr>
</table>';
$pdf->writeHTML($html_tabla1, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY + 5);
$pdf->writeHTMLCell(0, 1, '', '', $firmas, 0, 1, false, true, 'C');

$pdf->AddPage();
$pdf->SetY(40);
$pdf->SetFont('times', 'B', 12);
$pdf->Cell(0, 1, 'EVIDENCIAS', 0, 1, 'L');
$pdf->Ln(5);

$html_evidencias = '
<table border="0.5" cellpadding="5" cellspacing="0">
    <tr>
        <td width="50%" align="center" valign="middle" height="100">
            <img src="../../' . $img_practicas_puesto_trabajo . '" width="auto" height="150">
        </td>
        <td width="50%" align="center" valign="middle" height="100">
            <img src="../../' . $img_puesto_trabajo . '" width="auto" height="150">
        </td>
    </tr>
    <tr>
        <td align="center"><strong>Realización de prácticas en el puesto de trabajo</strong></td>
        <td align="center"><strong>Puesto de trabajo</strong></td>
    </tr>
    <tr>
        <td width="50%" align="center" valign="middle" height="100">
            <img src="../../' . $img_estudiante_tutor_entidad . '" width="auto" height="150">
        </td>
        <td width="50%" align="center" valign="middle" height="100">
            <img src="../../' . $img_cierre_practicas . '" width="auto" height="150">
        </td>
    </tr>
    <tr>
        <td align="center"><strong>Estudiante y tutor de las prácticas (Entidad Receptora)</strong></td>
        <td align="center"><strong>Cierre de prácticas laborales (Culminación de Prácticas)</strong></td>
    </tr>
</table>';

$pdf->writeHTML($html_evidencias, true, false, true, false, '');
$pdf->Output($nombre_doc . '.pdf', 'I');
