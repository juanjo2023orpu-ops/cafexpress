<?php 
include("../config/conexion.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "DELETE FROM productos WHERE id = $id";

    if ($conexion->query($sql)) {
        header("Location: ../admin.php");
        exit();
    } else {
        echo "Error al eliminar el producto: " . $conexion->error;
    }
} else {
    header("Location: ../admin.php");
    exit();
}
?>