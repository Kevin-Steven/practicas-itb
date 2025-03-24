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

$usuario = $result_rol->fetch_assoc();
$rol = $usuario['rol'];

// Consulta segura dependiendo del rol
if ($rol === 'estudiante') {
    $sql = "SELECT 
                u.nombres, u.apellidos, u.cedula, c.carrera AS carrera,
                d2.fecha_inicio, d2.hora_inicio, d2.fecha_fin, d2.hora_fin,
                d2.hora_practicas, d2.documento_eva_s, d2.nota_eva_s
            FROM documento_dos d2
            JOIN usuarios u ON d2.usuario_id = u.id
            INNER JOIN carrera c ON u.carrera_id = c.id
            WHERE d2.id = ? AND d2.usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $documento_id, $usuario_id);
} else {
    // El gestor puede ver cualquier documento
    $sql = "SELECT 
                u.nombres, u.apellidos, u.cedula, c.carrera AS carrera,
                d2.fecha_inicio, d2.hora_inicio, d2.fecha_fin, d2.hora_fin,
                d2.hora_practicas, d2.documento_eva_s, d2.nota_eva_s
            FROM documento_dos d2
            JOIN usuarios u ON d2.usuario_id = u.id
            INNER JOIN carrera c ON u.carrera_id = c.id
            WHERE d2.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $documento_id);
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

    return "$dia días del mes de $mes del $anio";
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
$pdf->AddPage();
$pdf->SetY(38);

$pdf->SetFont('times', 'B', 12);
$pdf->Cell(0, 1, 'COMPROMISO ÉTICO DE RESPONSABILIDAD PARA LAS PRÁCTICAS EN EL', 0, 1, 'C');
$pdf->Cell(0, 1, 'ENTORNO LABORAL REAL.', 0, 1, 'C');
$pdf->SetMargins(26, 35, 26);
$pdf->Ln(3);


$pdf->SetFont('times', '', 11);
$html_parrafo = '
<p style=" font-size: 11px; line-height: 1.3;">El presente acuerdo tiene como finalidad proteger las relaciones que vinculan al Instituto Superior
Tecnológico Bolivariano de Tecnología con el estudiante y las entidades receptoras al momento que
se inician actividades de formación académica en las prácticas laborales con el entorno real.</p>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo, 0, 1, 0, true, 'J', true);
$pdf->Ln(4);


$pdf->SetFont('times', '', 8.6);

$html_parrafo_2 = '
   <p style="font-size: 11px; line-height: 1.3;">Yo, <strong>' . $nombres . '</strong>, con numero de cedula <strong>' . $cedula . '</strong>, perteneciente a la
carrera <strong>' . $carrera . '</strong>, de la <strong>Facultad Ciencias Empresariales y Sistemas</strong> me comprometo por medio de este documento cumplir con las disposiciones de la
Institución en la formación práctica en el entorno laboral real, mismas que se disponen en las
siguientes normas:
</p>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_2, 0, 1, 0, true, 'J', true);
$pdf->Ln(4);

$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'NORMAS DISCIPLINARIAS', 0, 1, 'L');
$pdf->SetFont('times', '', 11);

$html_parrafo_3 = '
<p style=" font-size: 11px; line-height: 1.3;">Respeto por la dignidad y derechos de los seres humanos; la diversidad de personas y pueblos, y sus
costumbres y creencias; la autonomía y la libre capacidad de decisión de personas y comunidades,
su integridad e intimidad; y por la equidad y justicia en el trato de los seres humanos, las pautas de
conducta de los actores, deben ser:</p>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_3, 0, 1, 0, true, 'J', true);

$html_parrafo_4 = '<ol type="a" style="font-size:11px;"> 
    <li style="text-align: justify;">Ética profesional, responsabilidad y puntualidad.</li>
    <li style="text-align: justify;">No realizar juicios discriminatorios en base a raza, religión, sexo, edad, educación, nivel social u otra circunstancia, al seleccionar, aceptar realizar o realizar una práctica.</li>
    <li style="text-align: justify;">Brindar información adecuada a los sujetos de la práctica, aclarándose los siguientes aspectos: Que se trata de una práctica supervisada realizada por estudiantes.</li>
    <li style="text-align: justify;">Preservar la confidencialidad de los datos obtenidos, omitiendo cualquier dato que permita la identificación de sujetos o grupos participantes de las prácticas, tanto en los informes orales como escritos, supervisiones individuales o grupales, presentaciones de casos o publicaciones.</li>
    <li style="text-align: justify;">Presentar como resultado de las prácticas el expediente que muestran como evidencia el trabajo realizado con las orientaciones dadas por los responsables de la institución.</li>
    <li style="text-align: justify;">Manifestar los resultados de las prácticas con exactitud a sus docentes supervisores y sin realizar alteraciones en los resultados obtenidos.</li>
    <li style="text-align: justify;">No realizar las prácticas con personas o grupos de personas con los cuales se compartan otros intereses que pudieran generar un conflicto con los intereses de las prácticas.</li>
    <li style="text-align: justify;">No realizar las prácticas con personas o grupos de personas con las cuales se esté manteniendo o se haya mantenido algún tipo de relación que pudiera alterar el desarrollo o los resultados de las prácticas.</li>
    <li style="text-align: justify;">Evaluar las diferencias culturales de las personas o grupos de personas con los que se desarrolla la práctica, conjuntamente con las y los docentes supervisores, a fin de tomarlas en consideración para el adecuado desarrollo de las mismas.</li>
    <li style="text-align: justify;">Manifestar al/la docente supervisor/a con libertad y honestidad cualquier circunstancia de índole personal que el alumno/a considere un severo obstáculo para la realización de las prácticas.</li>
</ol>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_4, 0, 1, 0, true, '', true);

$pdf->Ln(4);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'NORMAS DE SEGURIDAD Y PROTECCIÓN', 0, 1, 'L');
$pdf->SetFont('times', '', 11);
$html_parrafo_5 = '
<p style=" font-size: 11px; line-height: 1.3;">Para evitar los accidentes y cumplir con las normas sanitarias y de salud en los procesos de las
prácticas en el entorno laboral, se toman en consideración ciertos puntos básicos que van acorde las
disposiciones de la entidad receptora que protejan al estudiante y evitar riesgos que afecten a los
actores del proceso de prácticas durante su ejecución, deberá cumplir con lo siguiente: </p>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_5, 0, 1, 0, true, 'J', true);

$html_parrafo_6 = '<ol type="a" style="font-size: 11px;">
    <li style="text-align: justify;">Cumplir con las normas y reglamento de la institución receptora de las prácticas.</li>
    <li style="text-align: justify;">Usar correctamente los medios de protección personal y colectiva proporcionados por la entidad receptora y cuidar de su conservación en el área de trabajo.</li>
    <li style="text-align: justify;">Cuidar de su higiene personal para prevenir el contagio de enfermedades y someterse a los reconocimientos médicos periódicos programados por la empresa.</li>
    <li style="text-align: justify;">Mantener libre de obstáculos salidas de emergencia, extintores de incendio y tableros de electricidad, o cualquier riesgo que se identifique sea notificado al personal responsable para que se resuelva.</li>
    <li style="text-align: justify;">Los carteles distribuidos en distintos sectores son normas de seguridad, y como tal, se deben respetar. No obstaculizar su visualización.</li>
    <li style="text-align: justify;">Siempre caminar, no correr.</li>
    <li style="text-align: justify;">No obstruya los pasillos ni zonas de tránsito.</li>
    <li style="text-align: justify;">No introducir, consumir bebidas alcohólicas, sustancias psicotrópicas, el porte y uso de armas, objetos cortopunzantes peligrosos, ni otras substancias tóxicas a los centros de prácticas, ni presentarse o permanecer en los mismos en estado de embriaguez o bajo los efectos de dichas substancias.</li>
</ol>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_6, 0, 1, 0, true, '', true);

$pdf->Ln(4);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'DE LA RESPONSABILIDAD DEL ESTUDIANTE CON LAS PRÁCTICAS EN EL', 0, 1, 'C');
$pdf->Cell(0, 1, 'ENTORNO LABORAL REAL.', 0, 1, 'C');
$pdf->Ln(4);
$pdf->Cell(0, 1, 'DEBERES DEL ESTUDIANTE.', 0, 1, 'L');
$pdf->SetFont('times', '', 11);

$html_parrafo_7 = '
<p style=" font-size: 11px; line-height: 1.3;">Todo estudiante del Instituto Superior Tecnológico Bolivariano de Tecnología ITB, deberá cumplir
con lo siguiente:</p>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_7, 0, 1, 0, true, 'J', true);


$html_parrafo_8 = '<ol type="a" style="font-size: 11px;">
    <li style="text-align: justify;">Debe cumplir con el reglamento interno para el funcionamiento de las prácticas laborales y demás normativas legales aplicadas por el Instituto Superior Tecnológico Bolivariano de Tecnología ITB.</li>
    <li style="text-align: justify;">El estudiante deberá asistir a las reuniones para las cuales sea citado, ya sea por el Docente tutor y/o por el Supervisor de la entidad receptora.</li>
    <li style="text-align: justify;">Deberá actuar con ética, valores y responsabilidad en el desarrollo de sus prácticas.</li>
    <li style="text-align: justify;">El estudiante deberá acatar y cumplir las disposiciones reglamentarias vigentes en la Institución y/o entidad receptora donde realiza la práctica laboral.</li>
    <li style="text-align: justify;">Deberá mantener una correcta presentación personal, consecuente con su calidad de estudiante y futuro profesional durante el desarrollo de su práctica.</li>
    <li style="text-align: justify;">El estudiante deberá mantener un contacto permanente con el Supervisor y entregar oportunamente, a quien corresponda, toda la documentación que le sea solicitada.</li>
    <li style="text-align: justify;">Una vez terminada la práctica laboral, el estudiante tendrá hasta 05 días laborables para entregar el expediente para su validación por parte del docente tutor, previo informe que entregará al coordinador responsable de prácticas de la facultad para su aprobación.</li>
    <li style="text-align: justify;">Deberá justificar oportunamente ante el supervisor, sus eventuales inasistencias a actividades relacionadas con la práctica.</li>
    <li style="text-align: justify;">El estudiante deberá informar de inmediato, a quien corresponda, acerca de problemas o irregularidades que se estuviesen presentando en el desarrollo de sus actividades como estudiante practicante.</li>
</ol>
';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_8, 0, 1, 0, true, '', true);

// TODO -> DERECHOS DEL ESTUDIANTE
$pdf->Ln(4);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'DERECHOS DEL ESTUDIANTE.', 0, 1, 'L');
$pdf->SetFont('times', '', 11);

$html_parrafo_9 = '
<p style=" font-size: 11px; line-height: 1.3;">El estudiante practicante tiene los siguientes derechos:</p>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_9, 0, 1, 0, true, 'J', true);


$html_parrafo_10 = '<ol type="a" style="font-size: 11px;">
    <li style="text-align: justify;">Ser tratado con dignidad, respeto y sin discriminación en su lugar de realización de las prácticas laborales durante el tiempo de la ejecución con la entidad receptora.</li>
    <li style="text-align: justify;">Recibir asesoramiento oportuno de parte del docente tutor para el cabal cumplimiento de sus prácticas laborales. </li>
    <li style="text-align: justify;">Ser evaluado objetivamente y recibir información oportuna de los resultados obtenidos en la realización de sus prácticas laborales</li>
</ol>
';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_10, 0, 1, 0, true, '', true);

// TODO -> PROHIBICIONES DEL ESTUDIANTE
$pdf->Ln(4);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'PROHIBICIONES DEL ESTUDIANTE.', 0, 1, 'L');
$pdf->SetFont('times', '', 11);

$html_parrafo_11 = '<ol type="a">
    <li style="text-align: justify;">No deberá incurrir en indisciplina, agresiones, escándalos o cualquier acto que lo comprometa o esté involucrado.</li>
    <li style="text-align: justify;">Abandono del lugar de las prácticas sin previo aviso o autorización alguna del supervisor de la entidad receptora.</li>
    <li style="text-align: justify;">Porte y uso de armas, o de cualquier objeto considerado como peligroso.</li>
    <li style="text-align: justify;">Tener, hacer uso o consumo de drogas, alcohol, consumo de cigarrillos dentro de las instalaciones de la entidad receptora.</li>
    <li style="text-align: justify;">Propagación de rumores falsos del personal de la entidad receptora o de la institución.</li>
    <li style="text-align: justify;">Falsificación y/o manipulación de documentos que sean de propiedad de la entidad receptora o de algún representante de la misma.</li>
    <li style="text-align: justify;">Fraude, hurto y/o cobros indebidos en las instalaciones de la entidad receptora.</li>
    <li style="text-align: justify;">Actividades no académicas.</li>
    <li style="text-align: justify;">Presencia de personas extrañas sin autorización de algún responsable de la entidad receptora.</li>
</ol>
';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_11, 0, 1, 0, true, '', true);

// TODO -> FALTA GRAVE.
$pdf->Ln(4);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'FALTA GRAVE.', 0, 1, 'L');
$pdf->SetFont('times', '', 11);

$html_parrafo_12 = '
<p style=" font-size: 11px; line-height: 1.3;">En la realización de las prácticas laborales serán consideradas faltas graves:</p>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_12, 0, 1, 0, true, 'J', true);

$html_parrafo_13 = '<ol type="a">
    <li style="text-align: justify;">No acatar las disposiciones, normas que establezca la Institución y respetando los acuerdos contractuales de las entidades receptoras para el ejercicio de las prácticas laborales.</li>
    <li style="text-align: justify;">El estudiante que tenga 3 faltas que no sean justificables, no podrá continuar con las prácticas, deberá de reprogramarse nuevamente.</li>
    <li style="text-align: justify;">Los atrasos laborales reiterados de los estudiantes (3 atrasos de los estudiantes equivalen a una falta de su práctica).</li>
    <li style="text-align: justify;">Incurrir en actitudes o conductas que entorpezcan el adecuado desarrollo de sus actividades y responsabilidades comprometidas como estudiante practicante y que atenten contra las normas de ética profesional.</li>
    <li style="text-align: justify;">Cuando el estudiante difunda información de carácter confidencial de la entidad receptora.</li>
</ol>

';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_13, 0, 1, 0, true, '', true);


// TODO -> DE LAS SANCIONES AL ESTUDIANTE.
$pdf->Ln(4);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'DE LAS SANCIONES AL ESTUDIANTE.', 0, 1, 'L');
$pdf->SetFont('times', '', 11);

$html_parrafo_14 = '
<p style=" font-size: 11px; line-height: 1.3;">En el caso que el estudiante incumpla con este documento, Reglamento de prácticas y de las
disposiciones enmarcadas del mismo, se aplicarán las siguientes sanciones: </p>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_14, 0, 1, 0, true, 'J', true);

$html_parrafo_15 = '<ol type="a">
    <li style="text-align: justify;">El estudiante que se retire de las prácticas, sin la autorización del Coordinador responsable de cada facultad, deberá esperar el siguiente período académico para nuevamente iniciarlas.</li>
    <li style="text-align: justify;">No serán validadas, ni acumulables las horas que hubiere realizado durante las prácticas que abandonó.</li>
    <li style="text-align: justify;">Los estudiantes que postulen a las prácticas y que hayan sido seleccionados, no podrán abandonar las mismas bajo ningún concepto, salvo por causas de fuerza mayor que considere el Instituto Superior Tecnológico Bolivariano de Tecnología ITB y su coordinador responsable.</li>
    <li style="text-align: justify;">Son causales de reprobación de las Prácticas laborales, el no cumplimiento de las normas del Instituto Superior Tecnológico Bolivariano de Tecnología ITB y de la entidad receptora de Práctica.</li>
    <li style="text-align: justify;">Por abandono de las prácticas, por motivos que se consideren como irresponsabilidad.</li>
    <li style="text-align: justify;">Reducción de 24 horas de su registro cumplido de prácticas cuando se considere una justificación aceptable por el docente Tutor o el Coordinador de prácticas de la facultad correspondiente.</li>
    <li style="text-align: justify;">Suspensión temporal del desarrollo de las prácticas laborales y/o pasantías por un semestre, cuando el estudiante incumple en el procedimiento de todas las orientaciones dadas por el coordinador de prácticas de la facultad y/o el docente tutor asignado.</li>
    <li style="text-align: justify;">El estudiante que una vez terminada la práctica tendrá 5 días para entregar la carpeta (expediente) sino lo hiciere será sancionado como reprobado y tendrá que volver a realizar en otro periodo académico.</li>
</ol>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_15, 0, 1, 0, true, '', true);

// TODO -> ACEPTACIÓN DE COMPROMISO
$pdf->Ln(4);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 1, 'Aceptación de compromiso', 0, 1, 'L');
$pdf->SetFont('times', '', 11);

$html_parrafo_16 = '
<p style=" font-size: 11px; line-height: 1.3;">“Libre y voluntariamente, previo cumplimiento de todos y cada uno de los puntos planteados; declaro
conocer, comprender y aceptar los términos establecidos por la Guía de Compromiso Ético de
Responsabilidad y Normas de Seguridad para el cumplimiento de Prácticas en el entorno laboral real
y me comprometo a llevar adelante mis prácticas laborales bajo sus lineamientos”.<br><br>
Dado en Guayaquil, a los ' . $fecha_inicio_larga . '.
</p>';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_16, 0, 1, 0, true, '', true);

$pdf->Ln(30);

$html_parrafo_17 = '<table width="100%" border="0" cellspacing="0" cellpadding="20">
    <tr>
        <!-- Firma del Estudiante -->
        <td style="text-align: center; vertical-align: bottom;">
            ___________________________<br>
            Firma del estudiante de<br>Prácticas
        </td>
        
        <!-- Firma del Docente Tutor -->
        <td style="text-align: center; vertical-align: bottom;">
            ___________________________<br>
            Firma del Docente Tutor de<br>Prácticas.
        </td>
    </tr>
    <tr>
        <br>
        <br>
        <br>
        <br>
        <br>
        <td colspan="2" style="text-align: center;">
            ___________________________________<br>
            Firma del Coordinador de Prácticas<br>responsable de la Facultad
        </td>
    </tr>
</table>
';

$pdf->writeHTMLCell('', '', '', '', $html_parrafo_17, 0, 1, 0, true, '', true);

$pdf->Output('3 CARTA DE ASIGNACIÓN DE ESTUDIANTE DE DESRROLLO DE SOFTWARE.pdf', 'I');
