<?php
require '../../../config/config.php';
require_once('../../../../TCPDF-main/tcpdf.php');

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
            d2.estado, d2.nombre_tutor_academico, d2.cedula_tutor_academico, d2.correo_tutor_academico, d2.nombre_doc
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
$nombre_doc = $estudiante['nombre_doc'] ?: 'N/A';

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
$pdf->SetY(35);

$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 1, 'PLAN DE APRENDIZAJE PRACTICO Y DE ROTACIÓN DEL', 0, 1, 'C');
$pdf->Cell(0, 1, 'ESTUDIANTE EN EL ENTORNO LABORAL', 0, 1, 'C');
$pdf->Cell(0, 1, 'FACULTAD DE CIENCIAS EMPRESARIALES Y SISTEMAS.', 0, 1, 'C');
$pdf->Ln(3);

$pdf->SetFont('times', '', 10);

$html_tabla1 = '
    <table border="0.5" cellpadding="3" cellspacing="0">
        <tr>
            <th colspan="2" style="text-align: center; font-size: 11px;"><strong>DATOS GENERALES DEL ESTUDIANTE</strong></th>
        </tr>
        <tr>
            <td style="font-size: 10px; width: 75%;"><strong>Apellidos y Nombres:</strong></td>
            <td style="font-size: 10px; width: 25%;"><strong>Cédula de identidad:</strong></td>
        </tr>
        <tr>
            <td style="font-size: 10px;">' . $nombres . '</td>
            <td style="font-size: 10px;">' . $cedula . '</td>
        </tr>
    </table>';

$html_tabla2 = '
    <table border="0.5" cellpadding="3" cellspacing="0">
        <tr>
            <td style="font-size: 10px; width: 58%;"><strong>Carrera:</strong></td>
            <td style="font-size: 10px; width: 17%;"><strong>Grupo:</strong></td>
            <td style="font-size: 10px; width: 25%;"><strong>Nivel de Estudio:</strong></td>
        </tr>
        <tr>
            <td style="font-size: 10px;">' . $carrera . '</td>
            <td style="font-size: 10px;">' . $paralelo . '</td>
            <td style="font-size: 10px;">' . $periodoAcademico . '</td>
        </tr>
    </table>';

$html_tabla3 = '
    <table border="0.5" cellpadding="3" cellspacing="0">
        <tr>
            <th colspan="6" style="text-align: start; font-size: 10px;"><strong>Periodo Práctica Preprofesional:</strong></th>
        </tr>
        <tr>
            <td style="font-size: 10px; width: 12%;"><strong>Fecha Inicio:</strong></td>
            <td style="font-size: 10px; width: 25%;">' . $fecha_inicio_larga . '</td>
            <td style="font-size: 10px; width: 12%;"><strong>Fecha Fin:</strong></td>
            <td style="font-size: 10px; width: 26%;">' . $fecha_fin_larga . '</td>
            <td style="font-size: 10px; width: 15%;"><strong>Horas Prácticas:</strong></td>
            <td style="font-size: 10px; width: 10%;">' . $hora_practicas . '</td>
        </tr>
    </table>
    ';

$html_tabla4 = '
    <table border="0.5" cellpadding="3" cellspacing="0">
        <tr>
            <th colspan="3" style="text-align: center; font-size: 11px;"><strong>DATOS GENERALES DE TUTOR ACADÉMICO</strong></th>
        </tr>
        <tr>
            <td style="width: 45%;"><strong>Apellidos y Nombres:</strong></td>
            <td style="width: 25%;"><strong>Cédula de identidad:</strong></td>
            <td style="width: 30%;"><strong>Correo Electrónico:</strong></td>
        </tr>
        <tr>
            <td>'.$nombre_tutor_academico.'</td>
            <td>'.$cedula_tutor_academico.'</td>
            <td>'.$correo_tutor_academico.'</td>
        </tr>
    </table>
    ';

$html_tabla5 = '
    <table border="0.5" cellpadding="3" cellspacing="0">

        <tr>
            <th colspan="6" style="text-align: center; font-size: 11px;"><strong>DATOS GENERALES DE ENTIDAD FORMADORA</strong></th>
        </tr>
        <tr>
            <td style="width: 25%;"><strong>Entidad Formadora:</strong></td>
            <td style="width: 75%;" colspan="5">Instituto Superior Tecnológico Bolivariano de Tecnología.</td>
        </tr>
        <tr>
            <td><strong>Actividad Económica:</strong></td>
            <td style="width: 50%;" colspan="3">Enseñanza técnica y Profesional de nivel inferior al de la
                enseñanza superior</td>
            <td style="width: 10%;"><strong>RUC:</strong></td>
            <td >0992180021001</td>
        </tr>
        <tr>
            <td><strong>Dirección:</strong></td>
            <td colspan="3">Víctor Manuel Rendon 236 y Pedro Carbo</td>
            <td><strong>Teléfono</strong></td>
            <td>(04) 5000175 – 1800 ITB</td>
        </tr>
        <tr>
            <td><strong>Tutor Entidad Formadora:</strong></td>
            <td colspan="3">Moreira Villafuerte Stiven Yiovanny</td>
            <td><strong>Teléfono</strong></td>
            <td>0968840225</td>
        </tr>
    </table>
    ';

$html_tabla6 = '
    <table border="0.5" cellpadding="4" cellspacing="0">

        <tr>
            <th colspan="6" style="text-align: center; font-size: 10px;"><strong>RESULTADOS DE APRENDIZAJE ESPECÍFICO DEL ESTUDIANTE</strong></th>
        </tr>
        <tr>
            <td><strong>INDICADORES</strong></td>
            <td colspan="5"><strong>CRITERIOS</strong></td>
        </tr>
        <tr>
            <td rowspan="4" ><strong><br><br><br>Conocimientos:</strong></td>
            <td colspan="5">Diseñar e implementar algoritmos utilizando las técnicas de programación lineal, estructurada,
                procedimental y funcional
            </td>
        </tr>
        <tr>
            <td colspan="5">Utilizar las estructuras de datos básicas y compuestas, así como estáticas y dinámicas para la entrada y
                salida de datos, en la implementación de algoritmos que les den solución a problemas de requerimientos de
                software
            </td>
        </tr>
        <tr>
            <td colspan="5">Brindar soporte técnico y de mantenimiento a sistemas de hardware de cómputo.</td>
        </tr>
        <tr>
            <td colspan="5">Diseñar e implementar bases de datos mediante el Modelo-Entidad-Relación</td>
        </tr>

        <tr>
            <td rowspan="5" ><strong><br><br><br>Habilidades:</strong></td>
            <td colspan="5">Aplicar las formas normales en el diseño de bases de datos mediante el Modelo-Entidad-Relación</td>
        </tr>
        <tr>
            <td colspan="5">Optimizar el diseño de bases de datos implementadas.</td>
        </tr>
        <tr>
            <td colspan="5">Identificar componentes de hardware de redes LAN.</td>
        </tr>
        <tr>
            <td colspan="5">Optimizar el diseño de redes LAN</td>
        </tr>
        <tr>
            <td colspan="5">Implementar y monitorear servicios de redes LAN</td>
        </tr>
    </table>
    ';

$pdf->writeHTML($html_tabla1, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 3);
$pdf->writeHTML($html_tabla2, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 3);
$pdf->writeHTML($html_tabla3, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 3);
$pdf->writeHTML($html_tabla4, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 3);
$pdf->writeHTML($html_tabla5, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 3);
$pdf->writeHTML($html_tabla6, true, false, true, false, '');

// ! SEGUNDA PAGINA
$pdf->SetMargins(20, 35, 20);
$pdf->AddPage();
$pdf->SetY(35);

$pdf->SetFont('helvetica', 'B', 10.5);
$pdf->Cell(0, 1, 'RESULTADO DE LA DIAGNÓSTICO INICIAL', 0, 1, 'C');
$pdf->Ln(8);
$pdf->SetFont('times', '', 12);
$pdf->Cell(0, 1, 'Guayaquil, '. $fecha_fin_larga, 0, 1, 'R');
$pdf->Ln(15);

$pdf->SetFont('times', 'B', 12);
$pdf->Cell(0, 1, 'Ing. Stiven Yiovanny Moreira Villafuerte', 0, 1, 'L');
$pdf->Cell(0, 1, 'Facultad de Ciencias Empresariales y Sistema', 0, 1, 'L');
$pdf->Cell(0, 1, 'Instituto Superior Tecnológico Bolivariano de Tecnología.', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('times', '', 12);
$html_parrafo = '
<p style=" font-size: 12px; line-height: 1.8;">De mi consideración: <br>Por medio de la presente se da informe sobre el resultado de la evaluación inicial realizada en la
    plataforma digital del entorno virtual de aprendizajes (eva-s) para dar inicio a la realización de las
    prácticas laborales por parte del estudiante <strong>' . $nombres . '</strong>, con número de
    cédula <strong>' . $cedula . '</strong>, de la carrera <strong>' . $carrera . '</strong> del
    <strong>' . $paralelo . '</strong>, con la fecha de inicio <strong>' . $fecha_inicio_corta . '</strong>, y finaliza <strong>' . $fecha_fin_corta . '</strong>. Como se detalla a continuación:
</p>';

$pdf->writeHTMLCell(
    '',               // Ancho del contenido (210mm ancho total - 20mm izq - 20mm der = 170mm)
    '',                // Alto automático
    '',                // Posición X (margen izquierdo)
    '',                // Posición Y (automático, sigue después de lo anterior)
    $html_parrafo,     // Contenido HTML
    0,                 // Sin borde
    1,                 // Salto de línea después de escribir
    0,                 // Sin relleno
    true,              // Reset height
    'J',               // Alineación Justificada
    true               // Auto padding
);

$pdf->Ln(7);


$pdf->SetFont('helvetica', '', 8.6);

$html_tabla7 = '
    <table border="0.5" cellpadding="6" cellspacing="0">
        <tr>
            <td style="width: 25%; background-color: #8EAADB; color: white; text-align: center;"><strong>Nombres del estudiante </strong></td>
            <td style="width: 13%; background-color: #8EAADB; color: white; text-align: center;"><strong>Cédula</strong></td>
            <td style="width: 11%; background-color: #8EAADB; color: white; text-align: center;"><strong>Estado</strong></td>
            <td style="width: 16%; background-color: #8EAADB; color: white; text-align: center;"><strong>Comenzado el </strong></td>
            <td style="width: 15%; background-color: #8EAADB; color: white; text-align: center;"><strong>Finalizado</strong></td>
            <td style="width: 20%; background-color: #8EAADB; color: white; text-align: center;"><strong>Calificación '.$calificacion.'/100</strong></td>
        </tr>
        <tr>
            <td style="text-align: center;">'.$nombres.'</td>
            <td style="text-align: center;">'.$cedula.'</td>
            <td style="text-align: center;">Finalizado</td>
            <td style="text-align: center;">'.$fecha_inicio_larga. ' ' . $hora_inicio.'</td>
            <td style="text-align: center;">'.$fecha_fin_larga. ' ' . $hora_fin.'</td>
            <td style="text-align: center;">'.$calificacion.'/100</td>
        </tr>
    </table>
';

    $pdf->writeHTMLCell(
        '',               // Ancho del contenido (210mm ancho total - 20mm izq - 20mm der = 170mm)
        '',                // Alto automático
        '',                // Posición X (margen izquierdo)
        '',                // Posición Y (automático, sigue después de lo anterior)
        $html_tabla7,     // Contenido HTML
        0,                 // Sin borde
        1,                 // Salto de línea después de escribir
        0,                 // Sin relleno
        true,              // Reset height
        '',               // Alineación Justificada
        ''               // Auto padding
    );

$pdf->Ln(7);
$pdf->SetFont('times', '', 12);
$html_parrafo_2 = '
<p style=" font-size: 12px; line-height: 1.8;">Particular que informo para los fines pertinentes. Se adjunta documento diagnóstico de evaluación del estudiante.</p>';

$pdf->writeHTMLCell(
    '','', '', '', $html_parrafo_2, 0, 1, 0, true, 'J', '',''
);

$pdf->Ln(60);
$pdf->SetFont('times', 'B', 12);
$pdf->Cell(0, 1, '_________________________________', 0, 1, 'C');
$pdf->Cell(0, 1, 'FIRMA Y SELLO DEL', 0, 1, 'C');
$pdf->Cell(0, 1, 'COORDINADOR PRÁCTICA', 0, 1, 'C');

$pdf->SetMargins(20, 35, 20);
$pdf->AddPage();
$pdf->SetY(35);

$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(0, 1, 'RESULTADOS EXTRAÍDOS DEL EVA-S DEL', 0, 1, 'C');
$pdf->Cell(0, 1, 'DIAGNÓSTICO INICIAL', 0, 1, 'C');
$pdf->Ln(10);


$pdf->Image('../../../uploads/eva-s/'. $eva_s, 30, 60, 150, 90, '');


$pdf->Output($nombre_doc .'.pdf', 'I');
