<?php
session_start();
require_once "config/conexion.php";

// Consulta agregando el campo 'imagen' para productos activos
$sqlProductos = "SELECT nombre, categoria, precio, imagen FROM productos WHERE estado = 'activo' LIMIT 6";
$resProductos = mysqli_query($conexion, $sqlProductos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cafetería Express - Inicio</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Barra de navegación con botón de acceso -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-3">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">☕ Cafetería Express</a>
        <div class="ms-auto">
            <?php if (isset($_SESSION['rol'])): ?>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <a href="admin.php" class="btn btn-outline-light btn-sm me-2">Panel Admin</a>
                <?php else: ?>
                    <a href="cliente.php" class="btn btn-outline-light btn-sm me-2">Mi Cuenta</a>
                <?php endif; ?>
                <a href="login.php?logout=1" class="btn btn-danger btn-sm">Salir</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-sm px-3">Iniciar Sesión</a>
                <a href="registro.php" class="btn btn-outline-light btn-sm ms-2">Crear cuenta</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Banner de bienvenida -->
<header class="bg-white py-5 text-center shadow-sm border-bottom">
    <div class="container">
        <h1 class="display-5 fw-bold">El mejor café en cada momento</h1>
        <p class="lead text-muted">Variedad en bebidas artesanales, alimentos frescos y postres del día.</p>
        <div class="d-inline-flex gap-2 text-start bg-light p-3 rounded border">
            <div>📍 <strong>Ubicación:</strong> Calle Principal #10-20</div>
            <div class="ms-4">🕒 <strong>Horario:</strong> Lunes a Sábado de 7:00 AM a 8:00 PM</div>
        </div>
    </div>
</header>

<!-- Menú Destacado con Imágenes -->
<main class="container my-5">
    <h3 class="text-center mb-4">Nuestros Productos Destacados</h3>
    
    <div class="row g-4">
        <?php if ($resProductos && mysqli_num_rows($resProductos) > 0): ?>
            <?php while ($prod = mysqli_fetch_assoc($resProductos)): ?>
                <?php 
                    // Verificar si tiene nombre de imagen, de lo contrario colocar una por defecto
                    $rutaImagen = (!empty($prod['imagen'])) ? "images/" . $prod['imagen'] : "images/default.png";
                ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 overflow-hidden">
                        <!-- Imagen del producto -->
                        <img src="<?= htmlspecialchars($rutaImagen); ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($prod['nombre']); ?>" 
                             style="height: 200px; object-fit: cover;">
                        
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <span class="badge bg-secondary text-uppercase mb-2"><?= $prod['categoria']; ?></span>
                                <h5 class="card-title"><?= htmlspecialchars($prod['nombre']); ?></h5>
                            </div>
                            <p class="card-text text-primary fw-bold fs-5 mt-2 mb-0">
                                $<?= number_format($prod['precio'], 2); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted">
                <p>No hay productos disponibles por el momento.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Llamado a la acción -->
    <div class="text-center mt-5 p-4 bg-white rounded shadow-sm border">
        <h5>¿Deseas realizar un pedido?</h5>
        <p class="text-muted mb-3">Inicia sesión o crea una cuenta de cliente para comprar en línea.</p>
        <a href="login.php" class="btn btn-success me-2">Ingresar a mi cuenta</a>
        <a href="registro.php" class="btn btn-outline-success">Crear cuenta</a>
    </div>
</main>

<footer class="bg-dark text-white text-center py-3 mt-5">
    <small>Cafetería Express &copy; Sistema de Gestión de Ventas</small>
</footer>

<?php include __DIR__ . '/includes/whatsapp.php'; ?>
</body>
</html>