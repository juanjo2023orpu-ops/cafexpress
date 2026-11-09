<?php
include("../config/conexion.php");

$nom = $_POST['nombre'];
$cat = $_POST['categoria'];
$pre = $_POST['precio'];
$est = $_POST['estado'];
$imagen = $_FILES['imagen'] ?? null;
$tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

if (!$imagen || $imagen['error'] !== UPLOAD_ERR_OK) {
    exit('Debes seleccionar una imagen válida.');
}

$tipo = mime_content_type($imagen['tmp_name']);
if (!isset($tiposPermitidos[$tipo]) || $imagen['size'] > 5 * 1024 * 1024) {
    exit('La imagen debe ser JPG, PNG o WEBP y no superar 5 MB.');
}

$nombreImagen = uniqid('producto_', true) . '.' . $tiposPermitidos[$tipo];
$rutaImagen = dirname(__DIR__) . '/images/' . $nombreImagen;
if (!move_uploaded_file($imagen['tmp_name'], $rutaImagen)) {
    exit('No fue posible guardar la imagen.');
}

$sql = "INSERT INTO productos (nombre, categoria, precio, imagen, estado)
        VALUES ('$nom', '$cat', $pre, '$nombreImagen', '$est')";

if ($conexion->query($sql)) {
    header("Location: ../admin.php");
} else {
    echo "Error al guardar el producto: " . $conexion->error;
}
?>