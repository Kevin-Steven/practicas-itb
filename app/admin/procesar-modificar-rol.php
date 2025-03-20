<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Tablas con Anchos Fijos</title>
</head>

<body>

    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
            /* IMPORTANTE para respetar los anchos del colgroup */
        }

        th {
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
            padding: 8px;
        }

        td {
            border: 1px solid #000;
            text-align: left;
            word-wrap: break-word;
            padding-inline: 5px;
        }
    </style>
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

    <!-- tercera tabla -->
    <table>
        <colgroup>
            <col style="width: 15%;">
            <col style="width: 30%;">
            <col style="width: 15%;">
            <col style="width: 30%;">
            <col style="width: 30%;">
            <col style="width: 10%;">
        </colgroup>
        <tr>
            <th colspan="6" style="text-align: start;"><strong>Periodo Práctica Preprofesional:</strong></th>
        </tr>
        <tr>
            <td><strong>Fecha Inicio:</strong></td>
            <td>28 de octubre del 2024</td>
            <td><strong>Fecha Fin:</strong></td>
            <td>28 de febrero del 2025</td>
            <td><strong>Horas Prácticas:</strong></td>
            <td>240</td>
        </tr>
    </table>

    <!-- cuarta tabla -->
    <table>
        <colgroup>
            <col style="width: 50%;">
            <col style="width: 30%;">
            <col style="width: 40%;">
        </colgroup>
        <tr>
            <th colspan="3">DATOS GENERALES DE TUTOR ACADÉMICO</th>
        </tr>
        <tr>
            <td><strong>Apellidos y Nombres:</strong></td>
            <td><strong>Cédula de identidad:</strong></td>
            <td><strong>Correo Electrónico:</strong></td>
        </tr>
        <tr>
            <td><strong>Castillo Llanos Jostin Emilio</strong></td>
            <td><strong>0951729367</strong></td>
            <td><strong>symoreira@itb.edu.ec</strong></td>
        </tr>
    </table>

    <!-- quinta tabla -->
    <table>

        <tr>
            <th colspan="6"><strong>DATOS GENERALES DE ENTIDAD FORMADORA</strong></th>
        </tr>
        <tr>
            <td><strong>Entidad Formadora:</strong></td>
            <td colspan="5">Instituto Superior Tecnológico Bolivariano de Tecnología.</td>
        </tr>
        <tr>
            <td><strong>Actividad Económica:</strong></td>
            <td colspan="3">Enseñanza técnica y Profesional de nivel inferior al de la
                enseñanza superior</td>
            <td><strong>RUC:</strong></td>
            <td>0992180021001</td>
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

    <!-- sexta tabla -->
    <table>

        <tr>
            <th colspan="6"><strong>RESULTADOS DE APRENDIZAJE ESPECÍFICO DEL ESTUDIANTE</strong></th>
        </tr>
        <tr>
            <td><strong>INDICADORES</strong></td>
            <td colspan="5"><strong>CRITERIOS</strong></td>
        </tr>
        <tr>
            <td rowspan="4"><strong>Conocimientos:</strong></td>
            <td colspan="5">Diseñar e implementar algoritmos utilizando las técnicas de programación lineal, estructurada,
                procedimental y funcional <br><br>
            </td>
        </tr>
        <tr>
            <td colspan="5">Utilizar las estructuras de datos básicas y compuestas, así como estáticas y dinámicas para la entrada y
                salida de datos, en la implementación de algoritmos que les den solución a problemas de requerimientos de
                software <br><br>
            </td>
        </tr>
        <tr>
            <td colspan="5">Brindar soporte técnico y de mantenimiento a sistemas de hardware de cómputo. <br><br>
            </td>
        </tr>
        <tr>
            <td colspan="5">Diseñar e implementar bases de datos mediante el Modelo-Entidad-Relación <br><br>
        </tr>

        <tr>
            <td rowspan="5"><strong>Habilidades:</strong></td>
            <td colspan="5">Aplicar las formas normales en el diseño de bases de datos mediante el Modelo-Entidad-Relación<br><br>
            </td>
        </tr>
        <tr>
            <td colspan="5">Optimizar el diseño de bases de datos implementadas. <br><br>
            </td>
        </tr>
        <tr>
            <td colspan="5">Identificar componentes de hardware de redes LAN.<br><br>
            </td>
        </tr>
        <tr>
            <td colspan="5">Optimizar el diseño de redes LAN <br><br></td>
        </tr>
        <tr>
            <td colspan="5">Implementar y monitorear servicios de redes LAN<br><br></td>
        </tr>
    </table>

</body>

</html>