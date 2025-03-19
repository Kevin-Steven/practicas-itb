
<?php phpinfo(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tablas con Anchos Fijos</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 40px 350px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed; /* IMPORTANTE para respetar los anchos del colgroup */
        }
        th {
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
            padding: 8px;
        }
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            word-wrap: break-word; /* Si el contenido es largo, se ajusta */
        }
    </style>
</head>
<body>

    <!-- Primera tabla -->
    <table>
        <colgroup>
            <col style="width: 60%;">
            <col style="width: 40%;">
        </colgroup>
        <tr>
            <th colspan="2">DATOS GENERALES DEL ESTUDIANTE</th>
        </tr>
        <tr>
            <td><strong>Apellidos y Nombres:</strong></td>
            <td><strong>Cédula de identidad:</strong></td>
        </tr>
        <tr>
            <td><strong>Castillo Llanos Jostin Emilio</strong></td>
            <td><strong>0951729367</strong></td>
        </tr>
    </table>

    <!-- Segunda tabla -->
    <table>
        <colgroup>
            <col style="width: 70%;">
            <col style="width: 10%;">
            <col style="width: 20%;">
        </colgroup>
        <tr>
            <td><strong>Carrera:</strong></td>
            <td><strong>Grupo:</strong></td>
            <td><strong>Nivel de Estudio:</strong></td>
        </tr>
        <tr>
            <td>Tecnología Superior en Desarrollo de Software</td>
            <td>DH4-DL-A01C</td>
            <td>4TO NIVEL</td>
        </tr>
    </table>

</body>
</html>
