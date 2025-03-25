<?php 
    $host = '137.184.138.191';
    $usuario = 'itb_practicas';
    $clave = '753159456Ab*';
    $baseDeDatos = 'itb_practicas';
    $puerto = '3306';

    $conn = new mysqli($host, $usuario ,$clave, $baseDeDatos, $puerto);

    if($conn -> connect_error){
        die("Error de conexión: " . $conn -> connect_error);
    }
?>