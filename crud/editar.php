<?php
session_start();
include("../config/conexion.php");

// Recibir el ID del producto
if (!isset($_GET['id'])) {
    header("Location: ../admin.php");
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM productos WHERE id = $id";
$resultado = $conexion->query($sql);

if (!$resultado || $resultado->num_rows == 0) {
    echo "Producto no encontrado.";
    exit();
}

$prod = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light container py-5">
    <div class="card col-md-6 mx-auto shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h4 class="m-0">Editar Producto</h4>
        </div>
        <div class="card-body">
            <form action="actualizar.php" method="POST" enctype="multipart/form-data">
                <!-- ID oculto importante para la consulta SQL -->
                <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre del Producto:</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($prod['nombre']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Categoría:</label>
                    <select name="categoria" class="form-select" required>
                        <option value="alimento" <?php if($prod['categoria'] == 'alimento') echo 'selected'; ?>>Alimento</option>
                        <option value="postre" <?php if($prod['categoria'] == 'postre') echo 'selected'; ?>>Postre</option>
                        <option value="bebida" <?php if($prod['categoria'] == 'bebida') echo 'selected'; ?>>Bebida</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Precio ($):</label>
                    <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $prod['precio']; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Imagen del Producto:</label>
                    <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">Deja vacío para conservar la imagen actual.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Estado:</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo" <?php if($prod['estado'] == 'activo') echo 'selected'; ?>>Activo</option>
                        <option value="inactivo" <?php if($prod['estado'] == 'inactivo') echo 'selected'; ?>>Inactivo</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-warning w-100 fw-bold mb-2">Guardar Cambios</button>
                <a href="../admin.php" class="btn btn-secondary w-100">Cancelar</a>
            </form>
        </div>
    </div>
</body>
</html>