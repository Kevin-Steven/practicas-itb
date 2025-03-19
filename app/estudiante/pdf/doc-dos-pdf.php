<?php
require_once('../../TCPDF-main/tcpdf.php');

// 1) Crear la clase CustomPDF (si necesitas personalizar encabezados/pies)
class CustomPDF extends TCPDF {
    public function Header() {
        // Tu encabezado personalizado si lo deseas
    }
    public function Footer() {
        // Tu pie de página personalizado si lo deseas
    }
}

// 2) Instanciar el PDF y configurar
$pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// 3) Construir el HTML de la tabla
$html = '
<style>
    table {
        border-collapse: separate;
        border-spacing: 4px;  /* Espacio entre celdas */
        width: 100%;
        font-size: 12pt;
    }
    th, td {
        border: 1px solid #000;
        padding: 8px;       /* Aumenta el padding interno */
        line-height: 1.5;
    }
    th {
        background-color: #eaeaea;
    }
</style>

<h3 style="text-align:center; margin-bottom:10px;">DATOS GENERALES DEL ESTUDIANTE</h3>

<table>
    <tr>
        <td colspan="2"><strong>Apellidos y Nombres:</strong></td>
        <td colspan="2"><strong>Cédula de Identidad:</strong></td>
    </tr>
    <tr>
        <td colspan="4" style="height:20px;"></td>
    </tr>
    <tr>
        <td><strong>Carrera:</strong></td>
        <td><strong>Grupo:</strong></td>
        <td><strong>Nivel de Estudio:</strong></td>
        <td><strong>Seleccione el Nombre de la Carrera:</strong></td>
    </tr>
    <tr>
        <td colspan="4" style="height:20px;"></td>
    </tr>
    <tr>
        <td colspan="4"><strong>Periodo Práctica Preprofesional:</strong></td>
    </tr>
    <tr>
        <td><strong>Fecha Inicio:</strong><br>28 de octubre del 2024</td>
        <td><strong>Fecha Fin:</strong><br>20 de diciembre del 2024</td>
        <td><strong>Horas Prácticas:</strong><br>240</td>
        <td></td>
    </tr>
</table>
';


// 4) Renderizar el HTML en el PDF
$pdf->writeHTML($html, true, false, true, false, '');

// 5) Output del PDF
$pdf->Output('mi_tabla_ejemplo.pdf', 'I');
