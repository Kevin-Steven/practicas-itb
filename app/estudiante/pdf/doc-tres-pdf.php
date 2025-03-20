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
            u.nombres, u.apellidos, u.email, u.cedula, u.direccion, u.telefono, u.convencional, c.carrera AS carrera,
            cu.paralelo AS paralelo, u.periodo, d2.estado, d2.fecha_inicio, d2.hora_inicio, d2.fecha_fin, d2.hora_fin,
            d2.hora_practicas, d2.documento_eva_s, d2.nota_eva_s,
            d2.estado, d2.nombre_tutor_academico, d2.cedula_tutor_academico, d2.correo_tutor_academico
        FROM documento_dos d2
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
$direccion = $estudiante['direccion'] ?: 'N/A';
$telefono = $estudiante['telefono'] ?: 'N/A';
$convencional = $estudiante['convencional'] ?: 'NO APLICA';
$email = $estudiante['email'] ?: 'N/A';
$carrera = $estudiante['carrera'] ?: 'N/A';
$paralelo = $estudiante['paralelo'] ?: 'N/A';
$periodoAcademico = $estudiante['periodo'] ?: 'N/A';
$estado = $estudiante['estado'] ?: 'N/A';
$hora_practicas = $estudiante['hora_practicas'] ?: 'N/A';
$calificacion = $estudiante['nota_eva_s'] ?: 'N/A';
$eva_s = $estudiante['documento_eva_s'] ?: 'N/A';
$nombre_tutor_academico = $estudiante['nombre_tutor_academico'] ?: 'N/A';
$cedula_tutor_academico = $estudiante['cedula_tutor_academico'] ?: 'N/A';
$correo_tutor_academico = $estudiante['correo_tutor_academico'] ?: 'N/A';


$fecha_inicio_larga = $estudiante['fecha_inicio'] ? formato_fecha_larga($estudiante['fecha_inicio']) : 'N/A';
$fecha_inicio_corta = $estudiante['fecha_inicio'] ? formato_fecha_corta($estudiante['fecha_inicio']) : 'N/A';

$fecha_fin_larga = $estudiante['fecha_fin'] ? formato_fecha_larga($estudiante['fecha_fin']) : 'N/A';
$fecha_fin_corta = $estudiante['fecha_fin'] ? formato_fecha_corta($estudiante['fecha_fin']) : 'N/A';

$hora_inicio = $estudiante['hora_inicio'] ? formato_hora($estudiante['hora_inicio']) : 'N/A';
$hora_fin = $estudiante['hora_fin'] ? formato_hora($estudiante['hora_fin']) : 'N/A';

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

// Formato corto: "28/10/2024"
function formato_fecha_corta($fecha)
{
    $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fecha_obj) return 'N/A';

    return $fecha_obj->format('d/m/Y');
}


// Función para convertir hora a "18:16"
function formato_hora($hora)
{
    $hora_obj = DateTime::createFromFormat('H:i:s', $hora);
    if (!$hora_obj) {
        // Intentar si viene como 'H:i'
        $hora_obj = DateTime::createFromFormat('H:i', $hora);
        if (!$hora_obj) return 'N/A';
    }

    return $hora_obj->format('H:i');
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
$pdf->SetMargins(23, 35, 17);
$pdf->AddPage();
$pdf->SetY(38);

$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 1, 'ASIGNACIÓN DE ESTUDIANTE A PRÁCTICAS LABORALES', 0, 1, 'C');
$pdf->Ln(3);
$pdf->SetFont('times', '', 11);
$pdf->Cell(0, 1, 'Guayaquil, '. $fecha_inicio_larga, 0, 1, 'R');
$pdf->Ln(3);

$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'Ing. Johanna Maritza Cornejo González', 0, 1, 'L');
$pdf->Cell(0, 1, 'Jefe Administrativo.', 0, 1, 'L');
$pdf->Cell(0, 1, 'Cooperativa de Transporte Brisas de Santay Panorama', 0, 1, 'L');
$pdf->Cell(0, 1, 'Durán', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('times', '', 11);
$html_parrafo = '
<p style="font-size: 11px; line-height: 0.1;">En su despacho.</p>
<p style=" font-size: 11px; line-height: 1.3;">De mis consideraciones:<br>Reciba un cordial saludo de quienes conforman el Instituto Superior Bolivariano de Tecnología (ITB), de la
Facultad de Ciencias Empresariales y Sistemas, y su carrera <strong>'. $carrera .'</strong>.
Se detalla los datos de nuestro estudiante <strong>'. $nombres .'</strong>, con cédula de identidad número
<strong>'. $cedula .'</strong>, que estará bajo la supervisión del: <strong>Ing. '. $nombre_tutor_academico .'</strong>, con una
duración de <strong>'. $hora_practicas .'</strong>, comenzando el día <strong>'. $fecha_inicio_larga .'</strong> y terminando el día <strong>'. $fecha_fin_larga .'</strong>. 
</p>';

$pdf->writeHTMLCell('','','','',$html_parrafo,0, 1, 0,true,'J', true);
$pdf->Ln(7);


$pdf->SetFont('times', '', 8.6);

$html_parrafo_2 = '
   <p style="font-size: 11px; line-height: 0.1;">Adicionalmente, se relacionan las <strong>destrezas y habilidades del estudiante: </strong>.</p>
   <ul style="font-size: 11px; line-height: 1.3;">
    <li>Diseñar e implementar algoritmos utilizando las técnicas de programación lineal, estructurada, procedimental y funcional.</li>
    <li>Utilizar las estructuras de datos básicas y compuestas, así como estáticas y dinámicas para la entrada y salida de datos, en la implementación de algoritmos que les den solución a problemas de requerimientos de software.</li>
    <li>Brindar soporte técnico y de mantenimiento a sistemas de hardware de cómputo.</li>
    <li>Diseñar e implementar bases de datos mediante el Modelo-Entidad-Relación</li>  
    <li>Aplicar las formas normales en el diseño de bases de datos mediante el Modelo-Entidad-Relación.</li>  
    <li>Optimizar el diseño de bases de datos implementadas.</li>  
    <li>Identificar componentes de hardware de redes LAN.</li>  
    <li>Optimizar el diseño de redes LAN.</li>  
    <li>Implementar y monitorear servicios de redes LAN.</li>  
   </ul>';

$pdf->writeHTMLCell('','','','',$html_parrafo_2,0, 1, 0,true,'J', true);
$pdf->Ln(7);

$html_parrafo_3 = '
<p style=" font-size: 11px; line-height: 1.3;">Agradecemos de antemano por la predisposición de su Institución, por la atención prestada y por el trámite
que se le dé a la presente, en función del mejoramiento de la calidad de la Educación Superior ecuatoriana.
Con sentimientos de estima y respeto, me suscribo de usted.
Atentamente, </p>';

$pdf->writeHTMLCell('','','','',$html_parrafo_3,0, 1, 0,true,'J', true);
$pdf->Ln(6);

$pdf->Ln(30);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, '__________________________________________________________', 0, 1, 'L');
$pdf->Cell(0, 1, 'Ing. Marcelino Pazmiño Manzaba, MSc.', 0, 1, 'L');
$pdf->Cell(0, 1, 'Coordinador de Prácticas de la Facultad de Ciencias', 0, 1, 'L');
$pdf->Cell(0, 1, 'Administrativas y Sistemas ', 0, 1, 'L');
$pdf->Cell(0, 1, 'correo electrónico: practicasfaces@bolivariano.edu.ec', 0, 1, 'L');



$pdf->Output('3 CARTA DE ASIGNACIÓN DE ESTUDIANTE DE DESRROLLO DE SOFTWARE.pdf', 'I');
