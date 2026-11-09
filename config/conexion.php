<?php
    // Conexión básica usando mysqli procedural
    $conexion = mysqli_connect("localhost", "root", "", "cafeteria_express");

    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
?>