<?php
session_start();

// Permitir el panel solo a administradores autenticados.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit();
}

include("config/conexion.php");

// Consulta a la tabla de productos
$sql = "SELECT * FROM productos";
$resultado = $conexion->query($sql); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Cliente - Cafetería Express</title>
    <!-- CSS de Bootstrap 5 -->
    <link href="js/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Incluimos el mapa de navegación o menú -->
    <?php if (file_exists('menu.html')) { include_once('menu.html'); } ?>

    <div class="container py-4">

        <!-- CABECERA PRINCIPAL CON BOTÓN DE AGREGAR PRODUCTO NUEVO -->
        <div class="text-center mb-4">
            <h1 class="fw-bold">El mejor café en cada momento</h1>
            <p class="text-muted">Variedad en bebidas artesanales, alimentos frescos y postres del día.</p>
            
            <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-white shadow-sm rounded">
                <span class="fs-5 fw-bold text-success">cliente</span>
                <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-white shadow-sm rounded">
               
                <div>
                    
                    
                    <a href="login.php?logout=1" class="btn btn-outline-danger">Cerrar Sesión</a>
                </div>
            </div>
            </div>
        </div>

        <!-- SECCIÓN DE PRODUCTOS DESTACADOS -->
        <h3 class="text-center mb-4 fw-bold">Nuestros Productos Destacados</h3>
       

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-3">
                        
                        <!-- Imagen del Producto -->
                        <?php if (!empty($fila['imagen'])): ?>
                            <img src="images/<?php echo htmlspecialchars($fila['imagen']); ?>" class="card-img-top rounded-top" alt="<?php echo htmlspecialchars($fila['nombre']); ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded-top" style="height: 200px;">
                                Sin Imagen
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="card-title"><?php echo htmlspecialchars($fila['nombre']); ?></h5>
                            </div>
                            <p class="card-text text-primary fw-bold fs-5 mt-2 mb-0">
                                $<?php echo number_format((float) $fila['precio'], 2); ?>
                            </p>
                        </div>
                        

                        
                    </div>
                </div>
            <?php } ?>
        </div>

    </div>

    <!-- Script de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include __DIR__ . '/includes/whatsapp.php'; ?>
</body>
</html>