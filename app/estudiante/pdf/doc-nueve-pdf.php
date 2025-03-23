<?php
require '../../config/config.php';
require_once('../../../TCPDF-main/tcpdf.php');

// if (!isset($_GET['id']) || empty($_GET['id'])) {
//     die("ID no proporcionado o vacío.");
// }

// $id = intval($_GET['id']);
// if ($id <= 0) {
//     die("ID inválido.");
// }

// $sql = "SELECT u.nombres, u.apellidos, u.cedula, c.carrera AS carrera, u.periodo, d8.motivo_rechazo, d8.departamento,
//         ia.semanas_fecha, ia.horas_realizadas, ia.actividades_realizadas, d2.fecha_inicio, d2.fecha_fin, d2.hora_practicas,
//         d6.nombres_representante as tutor_entidad, d6.numero_telefono as telefono_tutor, d5.nombre_entidad_receptora as nombre_entidad,
//         d8.nombre_doc
//         FROM usuarios u
//         LEFT JOIN documento_ocho d8 ON d8.usuario_id = u.id
//         LEFT JOIN informe_actividades ia ON d8.id = ia.documento_ocho_id
//         LEFT JOIN documento_dos d2 ON u.id = d2.usuario_id
//         LEFT JOIN documento_seis d6 ON u.id = d6.usuario_id
//         LEFT JOIN documento_cinco d5 ON u.id = d5.usuario_id
//         INNER JOIN carrera c ON u.carrera_id = c.id
//         WHERE d8.id = $id";

// $result = $conn->query($sql);

// if ($result->num_rows === 0) {
//     die("No se encontraron datos para este estudiante.");
// }

// $actividades = [];

// // ✅ Guardamos los registros múltiples en el array $actividades
// while ($row = $result->fetch_assoc()) {
//     // Los datos del estudiante se guardan desde la primera fila
//     if (empty($estudiante)) {
//         $estudiante = $row;
//     }

//     // Guardamos cada actividad
//     $actividades[] = [
//         'semanas_fecha' => $row['semanas_fecha'] ?: 'N/A',
//         'horas_realizadas' => $row['horas_realizadas'] ?: 'N/A',
//         'actividades_realizadas' => $row['actividades_realizadas'] ?: 'N/A'
//     ];
// }

// // Extraer datos generales (solo se hace una vez)
// $nombres = $estudiante['apellidos'] . ' ' . $estudiante['nombres'];
// $cedula = $estudiante['cedula'] ?: 'N/A';
// $carrera = $estudiante['carrera'] ?: 'N/A';
// $periodoAcademico = $estudiante['periodo'] ?: 'N/A';
// $horas_practicas = $estudiante['hora_practicas'] ?: 'N/A';
// $nombre_tutor_academico = $estudiante['tutor_entidad'] ?: 'N/A';
// $telefono_tutor = $estudiante['telefono_tutor'] ?: 'N/A';
// $nombre_entidad = $estudiante['nombre_entidad'] ?: 'N/A';
// $departamento = $estudiante['departamento'] ?: 'N/A';
// $motivo_rechazo = $estudiante['motivo_rechazo'] ?: 'N/A';
// $nombre_doc = $estudiante['nombre_doc'] ?: 'N/A';
// $fecha_inicio_larga = $estudiante['fecha_inicio'] ? formato_fecha_larga($estudiante['fecha_inicio']) : 'N/A';
// $fecha_fin_larga = $estudiante['fecha_fin'] ? formato_fecha_larga($estudiante['fecha_fin']) : 'N/A';


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
$pdf->SetY(32);
$pdf->SetMargins(15, 35, 15);


$pdf->SetFont('times', 'B', 12);
$pdf->Cell(0, 1, 'EVALUACIÓN CONDUCTUAL DEL ESTUDIANTE', 0, 1, 'C');
$pdf->SetFont('times', '', 12);   
$pdf->Cell(0, 1, '(SOLO PARA USO DEL SUPERVISOR ENTIDAD RECEPTORA)', 0, 1, 'C');
$pdf->SetFont('times', '', 12);   
$pdf->Cell(0, 1, 'Indique con una “X” la evaluación que usted considere adecuada, basada en el desempeño del', 0, 1, 'C');
$pdf->Cell(0, 1, 'estudiante durante la Práctica Pre-profesional laboral, y teniendo en cuenta la siguiente escala:', 0, 1, 'C');
$html_title = '<table><tr><td>
<strong>5-</strong> Siempre.
</td>
<td>
<strong>4-</strong> Casi siempre.
</td>
<td>
<strong>3-</strong> Ocasionalmente.
</td>
<td>
<strong>2-</strong> Casi nunca.
</td>
<td>
<strong>1-</strong> Nunca.
</td></tr></table>';
$pdf->writeHTMLCell(0, 1, '', '', $html_title, 0, 1, false, true, 'C');

$pdf->Ln(3);

$pdf->SetFont('times', '', 11); 

$html_tabla = '
<table border="0.5" cellpadding="2" cellspacing="0">
    <tr>
        <th colspan="2" width="70%" style="text-align: center;"><strong>INDICADORES:</strong></th>
        <th width="4%" style="text-align: center;"><strong>5</strong></th>
        <th width="4%" style="text-align: center;"><strong>4</strong></th>
        <th width="4%" style="text-align: center;"><strong>3</strong></th>
        <th width="4%" style="text-align: center;"><strong>2</strong></th>
        <th width="4%" style="text-align: center;"><strong>1</strong></th>
        <th width="10%" style="text-align: center;"><strong>Puntaje</strong></th>
    </tr>
    <tr>
        <td rowspan="4" width="20%"><br><br><br><br><strong>Disciplina</strong></td>
        <td width="50%">Asiste puntualmente a su práctica.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Se presenta con adecuado porte y respeto en el área laboral asignada.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Manifiesta una actitud de servicio, cooperación y trabajo en equipo.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Actúa siguiendo la ética profesional y normas de principios morales.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>

    <tr>
        <td rowspan="4"><br><br><br><br><strong>Integración al ambiente laboral</strong></td>
        <td>Cumple con las Normas, Políticas, procedimientos y cultura organizacional.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Establece una comunicación profesional efectiva y asertiva en el área asignada.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Trabaja en iniciativa y soluciones integrales acorde a su asignación de práctica.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Demuestra capacidad de adaptación y desenvolvimiento al área asignada.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>

    <tr>
        <td rowspan="7"><br><br><br><br><br><br><br><strong>Conocimientos y habilidades profesionales</strong></td>
        <td>Aplica adecuadamente los conocimientos teóricos y prácticos del perfil profesional.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Demuestra adecuadamente las destrezas y habilidades acordes al perfil profesional.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Genera soluciones y propuestas halladas en el área de asignación de práctica.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Comunica asertivamente situaciones para la mejora continua del área asignada.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Demuestra capacidad resolutiva a casos reales del área asignada.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Demuestra proactividad en adquirir nuevos conocimientos en el área asignada de prácticas.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
    <tr>
        <td>Aporta destreza académica en reuniones de trabajo del área asignada.</td>
        <td style="text-align: center;"><font face="dejavusans">☒</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><font face="dejavusans">☐</font></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>

    <tr>
        <td colspan="2"></td>
        <td colspan="5" align="center";><strong>Promedio</strong></td>
        <td style="text-align: center;"><strong>5</strong></td>
    </tr>
</table>';
$pdf->writeHTML($html_tabla, true, false, true, false, '');

$pdf->SetFont('times', 'B', 12);
$pdf->Cell(0, 1, 'EVALUACIÓN:', 0, 1, 'C');
$pdf->Ln(3);
$pdf->SetFont('times', '', 11);
$html_evaluacion = '<table>
<tr>
    <td width="40%" style="text-align: center;"><strong>Satisfactoria</strong> (>= 3 puntos de promedio)</td>
    <td width="10%" style="text-align: center;"><font face="dejavusans">☒</font></td>
    <td width="40%" style="text-align: center;"><strong>Insatisfactoria</strong> (< 3 puntos de promedio)</td>
    <td width="10%" style="text-align: center;"><font face="dejavusans">☐</font></td>
</tr>
</table>';
$pdf->writeHTMLCell(0, 1, '', '', $html_evaluacion, 0, 1, false, true, 'C');
$pdf->Ln(7);
$firmas = '  <table width="100%" style="font-size: 11px;">
  <tr>
    <!-- Firma del Estudiante -->
    <td style="text-align: center; width: 45%;">
      <div>____________________________________</div>
      Firma del Estudiante
    </td>

    <td style="width: 10%;"></td>

    <!-- Firma del Tutor de la Entidad Receptora -->
    <td style="text-align: center; width: 45%;">
      <div>____________________________________</div>
      Firma y sello del Tutor Entidad Receptora
    </td>
  </tr>
</table>';
$pdf->writeHTMLCell(0, 1, '', '', $firmas, 0, 1, false, true, 'C');

$pdf->Ln(10);

$pdf->Output('informe-actividades.pdf', 'I');
