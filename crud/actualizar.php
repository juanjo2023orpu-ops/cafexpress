<?php
include("../config/conexion.php");

$id  = intval($_POST['id']);
$nom = $_POST['nombre'];
$cat = $_POST['categoria'];
$pre = $_POST['precio'];
$est = $_POST['estado'];

$imagenSql = '';
$imagen = $_FILES['imagen'] ?? null;
if ($imagen && $imagen['error'] !== UPLOAD_ERR_NO_FILE) {
    $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tipo = $imagen['error'] === UPLOAD_ERR_OK ? mime_content_type($imagen['tmp_name']) : '';
    if ($imagen['error'] !== UPLOAD_ERR_OK || !isset($tiposPermitidos[$tipo]) || $imagen['size'] > 5 * 1024 * 1024) {
        exit('La imagen debe ser JPG, PNG o WEBP y no superar 5 MB.');
    }

    $nombreImagen = uniqid('producto_', true) . '.' . $tiposPermitidos[$tipo];
    $rutaImagen = dirname(__DIR__) . '/images/' . $nombreImagen;
    if (!move_uploaded_file($imagen['tmp_name'], $rutaImagen)) {
        exit('No fue posible guardar la imagen.');
    }
    $imagenSql = ", imagen = '$nombreImagen'";
}

$sql = "UPDATE productos SET nombre = '$nom', categoria = '$cat', precio = $pre$imagenSql, estado = '$est' WHERE id = $id";

if ($conexion->query($sql)) {
    header("Location: ../admin.php");
} else {
    echo "Error al actualizar el producto: " . $conexion->error;
}
?>