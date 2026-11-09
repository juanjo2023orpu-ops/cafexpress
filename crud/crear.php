<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <div class="card col-md-6 mx-auto shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4>Nuevo Producto</h4>
        </div>
        <div class="card-body">
            <form action="guardar.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Nombre del Producto:</label>
                    <input type="text" name="nombre" class="form-control" maxlength="100" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Categoría:</label>
                    <select name="categoria" class="form-select" required>
                        <option value="alimento">Alimento</option>
                        <option value="postre">Postre</option>
                        <option value="bebida">Bebida</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Precio ($):</label>
                    <input type="number" step="0.01" name="precio" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Imagen del Producto:</label>
                    <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                    <small class="text-muted">Formatos permitidos: JPG, PNG o WEBP.</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Estado:</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Guardar Producto</button>
                <a href="../admin.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</body>
</html>