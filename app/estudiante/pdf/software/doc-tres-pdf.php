<?php
session_start();
require '../../../config/config.php';
require_once('../../../../TCPDF-main/tcpdf.php');

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso no autorizado. Por favor, inicia sesión.");
}

$usuario_id = $_SESSION['usuario_id'];

// Verificar si el ID del documento está presente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID no proporcionado o vacío.");
}

// Sanitizar el ID
$id = intval($_GET['id']);
if ($id <= 0) {
    die("ID inválido.");
}

// Consultar el rol del usuario
$sql_rol = "SELECT rol FROM usuarios WHERE id = ?";
$stmt_rol = $conn->prepare($sql_rol);
$stmt_rol->bind_param("i", $usuario_id);
$stmt_rol->execute();
$result_rol = $stmt_rol->get_result();

if ($result_rol->num_rows === 0) {
    die("Usuario no encontrado.");
}

$usuario = $result_rol->fetch_assoc();
$rol = $usuario['rol'];

// Consultar el documento dependiendo del rol
if ($rol === 'estudiante') {
    // El estudiante solo puede ver sus propios documentos
    $sql = "SELECT 
                u.nombres, 
                u.apellidos, 
                u.cedula, 
                c.carrera AS carrera,
                d2.fecha_inicio, 
                d2.fecha_fin,
                d2.hora_practicas, 
                d2.nombre_tutor_academico, 
                d3.nombres_tutor_receptor, 
                d3.cargo_tutor_receptor, 
                d3.nombre_entidad_receptora, 
                d3.ciudad_entidad_receptora
            FROM documento_tres d3
            JOIN usuarios u ON d3.usuario_id = u.id
            LEFT JOIN documento_dos d2 ON d3.usuario_id = d2.usuario_id
            INNER JOIN carrera c ON u.carrera_id = c.id
            WHERE d3.id = ? AND d3.usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $usuario_id);
} else {
    // El gestor puede ver cualquier documento
    $sql = "SELECT 
                u.nombres, 
                u.apellidos, 
                u.cedula, 
                c.carrera AS carrera,
                d2.fecha_inicio, 
                d2.fecha_fin,
                d2.hora_practicas, 
                d2.nombre_tutor_academico, 
                d3.nombres_tutor_receptor, 
                d3.cargo_tutor_receptor, 
                d3.nombre_entidad_receptora, 
                d3.ciudad_entidad_receptora
            FROM documento_tres d3
            JOIN usuarios u ON d3.usuario_id = u.id
            LEFT JOIN documento_dos d2 ON d3.usuario_id = d2.usuario_id
            INNER JOIN carrera c ON u.carrera_id = c.id
            WHERE d3.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
}

$stmt->execute();
$result = $stmt->get_result();

// Validar si hay datos
if ($result->num_rows === 0) {
    die("No tienes permiso para ver este documento o no existe.");
}

// Obtener los datos
$estudiante = $result->fetch_assoc();


// Extraer variables
$nombres = $estudiante['apellidos'] . ' ' . $estudiante['nombres'];
$cedula = $estudiante['cedula'] ?: 'N/A';
$carrera = $estudiante['carrera'] ?: 'N/A';
$hora_practicas = $estudiante['hora_practicas'] ?: 'N/A';
$nombre_tutor_academico = $estudiante['nombre_tutor_academico'] ?: 'N/A';
$nombre_tutor_receptor = $estudiante['nombres_tutor_receptor'] ?: 'N/A';
$cargo_tutor_receptor = $estudiante['cargo_tutor_receptor'] ?: 'N/A';
$ciudad_entidad_receptora = $estudiante['ciudad_entidad_receptora'] ?: 'N/A';
$nombre_entidad_receptora = $estudiante['nombre_entidad_receptora'] ?: 'N/A';

$fecha_inicio_larga = $estudiante['fecha_inicio'] ? formato_fecha_larga($estudiante['fecha_inicio']) : 'N/A';
$fecha_inicio_corta = $estudiante['fecha_inicio'] ? formato_fecha_corta($estudiante['fecha_inicio']) : 'N/A';

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
$pdf->SetMargins(23, 35, 17);
$pdf->AddPage();
$pdf->SetY(38);

$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 1, 'ASIGNACIÓN DE ESTUDIANTE A PRÁCTICAS LABORALES', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('times', '', 11);
$pdf->Cell(0, 1, 'Guayaquil, ' . $fecha_inicio_larga, 0, 1, 'R');
$pdf->Ln(2);

$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'Ing. ' . $nombre_tutor_receptor, 0, 1, 'L');
$pdf->Cell(0, 1, $cargo_tutor_receptor, 0, 1, 'L');
$pdf->Cell(0, 1, $nombre_entidad_receptora, 0, 1, 'L');
$pdf->Cell(0, 1, $ciudad_entidad_receptora, 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('times', '', 11);
$html_parrafo = '
<p style="font-size: 11px; line-height: 0.1;">En su despacho.</p>
<p style=" font-size: 11px; line-height: 1.3;">De mis consideraciones:<br>Reciba un cordial saludo de quienes conforman el Instituto Superior Bolivariano de Tecnología (ITB), de la
Facultad de Ciencias Empresariales y Sistemas, y su carrera <strong>' . $carrera . '</strong>.
Se detalla los datos de nuestro estudiante <strong>' . $nombres . '</strong>, con cédula de identidad número
<strong>' . $cedula . '</strong>, que estará bajo la supervisión del: <strong>Ing. ' . $nombre_tutor_academico . '</strong>, con una
duración de <strong>' . $hora_practicas . '</strong>, comenzando el día <strong>' . $fecha_inicio_larga . '</strong> y terminando el día <strong>' . $fecha_fin_larga . '</strong>. 
</p>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo, 0, 1, 0, true, 'J', true);
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

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_2, 0, 1, 0, true, 'J', true);
$pdf->Ln(7);

$html_parrafo_3 = '
<p style=" font-size: 11px; line-height: 1.3;">Agradecemos de antemano por la predisposición de su Institución, por la atención prestada y por el trámite
que se le dé a la presente, en función del mejoramiento de la calidad de la Educación Superior ecuatoriana.
Con sentimientos de estima y respeto, me suscribo de usted.
Atentamente, </p>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_3, 0, 1, 0, true, 'J', true);
$pdf->Ln(6);

$pdf->Ln(30);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, '__________________________________________________________', 0, 1, 'L');
$pdf->Cell(0, 1, 'Ing. Marcelino Pazmiño Manzaba, MSc.', 0, 1, 'L');
$pdf->Cell(0, 1, 'Coordinador de Prácticas de la Facultad de Ciencias', 0, 1, 'L');
$pdf->Cell(0, 1, 'Administrativas y Sistemas ', 0, 1, 'L');
$pdf->Cell(0, 1, 'correo electrónico: practicasfaces@bolivariano.edu.ec', 0, 1, 'L');



$pdf->Output('3 CARTA DE ASIGNACIÓN DE ESTUDIANTE DE DESRROLLO DE SOFTWARE.pdf', 'I');
