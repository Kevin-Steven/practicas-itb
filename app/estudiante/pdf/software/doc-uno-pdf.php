<?php
session_start();
require '../../../config/config.php';
require_once('../../../../TCPDF-main/tcpdf.php');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    die("No has iniciado sesión.");
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener y sanitizar el ID del documento
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID no proporcionado o vacío.");
}

$documento_id = intval($_GET['id']);
if ($documento_id <= 0) {
    die("ID inválido.");
}

// Consultar el rol del usuario
$sql_rol = "SELECT rol FROM usuarios WHERE id = ?";
$stmt_rol = $conn->prepare($sql_rol);
$stmt_rol->bind_param("i", $usuario_id);
$stmt_rol->execute();
$result_rol = $stmt_rol->get_result();
$usuario = $result_rol->fetch_assoc();

if (!$usuario) {
    die("Usuario no encontrado.");
}

$rol = $usuario['rol'];

// Si el rol es estudiante, validar que el documento le pertenece
if ($rol === 'estudiante') {
    $sql = "SELECT d1.*, u.*, c.carrera AS carrera, cu.paralelo AS paralelo
            FROM documento_uno d1
            JOIN usuarios u ON d1.usuario_id = u.id
            INNER JOIN carrera c ON u.carrera_id = c.id
            LEFT JOIN cursos cu ON u.curso_id = cu.id
            WHERE d1.id = ? AND d1.usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $documento_id, $usuario_id);
} else {
    // Si es gestor o administrador, puede acceder a cualquier documento
    $sql = "SELECT d1.*, u.*, c.carrera AS carrera, cu.paralelo AS paralelo
            FROM documento_uno d1
            JOIN usuarios u ON d1.usuario_id = u.id
            INNER JOIN carrera c ON u.carrera_id = c.id
            LEFT JOIN cursos cu ON u.curso_id = cu.id
            WHERE d1.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $documento_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No tienes permiso para ver este documento.");
}

$estudiante = $result->fetch_assoc();


// Extraer variables
$nombres = $estudiante['nombres'] . ' ' . $estudiante['apellidos'];
$cedula = $estudiante['cedula'] ?: 'N/A';
$direccion = $estudiante['direccion'] ?: 'N/A';
$telefono = $estudiante['telefono'] ?: 'N/A';
$convencional = $estudiante['convencional'] ?: 'NO APLICA';
$email = $estudiante['email'] ?: 'N/A';
$carrera = $estudiante['carrera'] ?: 'N/A';
$paralelo = $estudiante['paralelo'] ?: 'N/A';
$promedio = $estudiante['promedio_notas'] ?: 'N/A';
$foto_perfil_path = $estudiante['foto_perfil'] ?: 'Sin Foto';
$periodoAcademico = $estudiante['periodo'] ?: 'N/A';
$nombre_doc = $estudiante['nombre_doc'] ?: 'N/A';
$foto_perfil = '../../' . $foto_perfil_path;


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
$pdf->SetY(30);

// Configurar la fuente para el título principal
$pdf->SetFont('times', 'B', 12);
$pdf->Cell(0, 10, 'FICHA DEL ESTUDIANTE', 0, 1, 'C');
$pdf->Ln(3);

// Subtítulo "DATOS GENERALES"
$pdf->SetFont('times', 'B', 11);
$pdf->SetX(60); 
$pdf->Cell(0, 6, 'DATOS GENERALES', 0, 1, 'L'); // Cambia 'C' (centrado) a 'L' (alineado a la izquierda desde SetX)
$pdf->Ln(3);

// Definir márgenes
$margin_left = 24;
$margin_right = 120;
$cell_width = 115;
$cell_height = 15;
$cell_width_large = 160;

$espaciado = 3;

// Apellidos y Nombres
$pdf->SetX($margin_left);
$pdf->SetFont('times', '', 12);
$pdf->setCellPaddings(1, 1, 1, 2); // Agrega un pequeño padding arriba y abajo
$pdf->MultiCell($cell_width, $cell_height, "Apellidos y Nombres:\n$nombres", 1, 'L');
$pdf->Ln($espaciado);

// Cédula de identidad
$pdf->SetX($margin_left);
$pdf->SetFont('times', '', 12);
$pdf->MultiCell($cell_width, $cell_height, "Cédula de identidad:\n$cedula", 1, 'L');
$pdf->Ln($espaciado);

// Dirección del domicilio
$pdf->SetX($margin_left);
$pdf->SetFont('times', '', 12);
$pdf->MultiCell($cell_width_large, $cell_height, "Dirección del domicilio:\n$direccion", 1, 'L');
$pdf->Ln($espaciado);

// Definir el ancho de cada celda para que estén en la misma línea
$cell_half_width = $cell_width_large / 2;

// Teléfonos (en la misma línea, pero con salto de línea dentro de cada celda)
$pdf->SetX($margin_left);
$pdf->SetFont('times', '', 12);

// Primera celda: Teléfono convencional
$telefono_convencional = "Teléfono convencional:\n$convencional";
$pdf->MultiCell($cell_half_width, $cell_height, $telefono_convencional, 1, 'L');

// Posicionar la segunda celda en la misma línea
$pdf->SetY($pdf->GetY() - $cell_height); // Retrocede una línea
$pdf->SetX($margin_left + $cell_half_width);

// Segunda celda: Teléfono celular
$telefono_celular = "Teléfono celular:\n$telefono";
$pdf->MultiCell($cell_half_width, $cell_height, $telefono_celular, 1, 'L');

// Espaciado entre filas
$pdf->Ln($espaciado);

// Correo electrónico
$pdf->SetX($margin_left);
$pdf->SetFont('times', '', 12);
$pdf->MultiCell($cell_width_large, $cell_height, "Correo electrónico:\n$email", 1, 'L');
$pdf->Ln($espaciado);

$cell_width_img = 40;
$cell_height_img = 45;
$pos_x_img = 144;  // Posición en X
$pos_y_img = 40;   // Posición en Y

// Dibujar celda vacía con bordes
$pdf->SetXY($pos_x_img, $pos_y_img);
$pdf->Cell($cell_width_img, $cell_height_img, '', 1, 1, 'C');

// Insertar la imagen dentro de la celda
$pdf->Image($foto_perfil, $pos_x_img + 2, $pos_y_img + 2, $cell_width_img - 4, $cell_height_img - 4);
$pdf->Ln(60);

$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 6, 'DATOS ACADÉMICOS', 0, 1, 'C');
$pdf->Ln(3);

$pdf->SetX($margin_left);
$pdf->SetFont('times', '', 12);
$pdf->setCellPaddings(1, 1, 1, 2); // ! Agrega un pequeño padding arriba y abajo
$pdf->MultiCell($cell_width_large, $cell_height, "Carrera:\n$carrera", 1, 'L');
$pdf->Ln($espaciado);

// TODO: PARALELO Y PERIODO ACADEMICO (en la misma línea, pero con salto de línea dentro de cada celda)
$pdf->SetX($margin_left);
$pdf->SetFont('times', '', 12);
// Primera celda: Teléfono convencional
$telefono_convencional = "Paralelo:\n$paralelo";
$pdf->MultiCell($cell_half_width, $cell_height, $telefono_convencional, 1, 'L');
// Posicionar la segunda celda en la misma línea
$pdf->SetY($pdf->GetY() - $cell_height); // Retrocede una línea
$pdf->SetX($margin_left + $cell_half_width);
// Segunda celda: Teléfono celular
$telefono_celular = "Periodo académico (Nivel):\n$periodoAcademico";
$pdf->MultiCell($cell_half_width, $cell_height, $telefono_celular, 1, 'L');
$pdf->Ln($espaciado);

$pdf->SetX($margin_left);
$pdf->SetFont('times', '', 12);
$pdf->setCellPaddings(1, 1, 1, 2); // Agrega un pequeño padding arriba y abajo
$pdf->MultiCell($cell_width_large, $cell_height, "Promedio de notas:\n$promedio", 1, 'L');
$pdf->Ln($espaciado);

$pdf->Ln(3);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 6, 'EXPERIENCIA LABORAL', 0, 1, 'C');
$pdf->Ln(3);


// TODO => TABLA DE EXPERIENCIA
$pdf->SetFont('times', '', 12);
// Ajusta los anchos para las nuevas columnas
$widths = [60, 40, 60];
$height = 7;
$pdf->SetX($margin_left);
$headers = ['Últimos lugares donde ha laborado:', 'Periodo de tiempo (meses):', 'Funciones realizadas:'];
$pdf->MultiCellRow($headers, $widths, $height);

// Verificar si el ID está presente en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID no proporcionado o vacío.");
}

// Obtener y sanitizar el ID
$id = intval($_GET['id']);
if ($id <= 0) {
    die("ID inválido.");
}

$sql = "SELECT 
            d1.id,
            el.lugar_laborado,
            el.periodo_tiempo_meses,
            el.funciones_realizadas
        FROM documento_uno d1
        LEFT JOIN experiencia_laboral el ON d1.id = el.documento_uno_id
        WHERE d1.id = $id";
$result = $conn->query($sql);

$pdf->SetFont('times', '', 12);

while ($row = $result->fetch_assoc()) {
    $pdf->SetX($margin_left);
    $lugar = $row['lugar_laborado'] ? $row['lugar_laborado'] : 'NO APLICA';
    $periodo_tiempo = $row['periodo_tiempo_meses'] ? $row['periodo_tiempo_meses'] : 'NO APLICA';
    $funciones_realizadas = $row['funciones_realizadas'] ? $row['funciones_realizadas'] : 'NO APLICA';

    $pdf->MultiCellRow(
        [
            $lugar,
            $periodo_tiempo,
            $funciones_realizadas,
        ],
        $widths,
        $height
    );
}

// Salida del PDF
$pdf->Output($nombre_doc .'.pdf', 'I');
exit();
