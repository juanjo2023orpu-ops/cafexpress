<?php
session_start();
require_once "config/conexion.php";

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SESSION['rol'])) {
    $_SESSION = [];
    session_regenerate_id(true);
}

$error = "";

// Procesar formulario al presionar el botón de ingreso
if (isset($_POST['btnIngresar'])) {
    $emailInput = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $password = is_string($password) ? $password : '';
    $stmt = mysqli_prepare(
        $conexion,
        'SELECT id, nombre, password, rol FROM personas WHERE email = ? LIMIT 1'
    );

    if (!$stmt) {
        die('Error al consultar el usuario: ' . mysqli_error($conexion));
    }

    mysqli_stmt_bind_param($stmt, 's', $email);
    if (!mysqli_stmt_execute($stmt)) {
        die('Error al consultar el usuario: ' . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_bind_result($stmt, $usuarioId, $nombre, $passwordGuardada, $rol);
    $usuarioEncontrado = mysqli_stmt_fetch($stmt);
    if ($usuarioEncontrado === false) {
        die('Error al leer el usuario: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);

    $autenticado = false;
    if ($usuarioEncontrado) {
        $esHashValido = is_string($passwordGuardada) && password_verify($password, $passwordGuardada);
        $autenticado = $esHashValido;

        // Migra contraseñas antiguas en texto plano al primer inicio de sesión válido.
        if (!$autenticado && is_string($passwordGuardada) && hash_equals($passwordGuardada, $password)) {
            $autenticado = true;
        }

        if ($autenticado && (!$esHashValido || password_needs_rehash($passwordGuardada, PASSWORD_DEFAULT))) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            if ($passwordHash === false) {
                die('Error al generar el hash de la contraseña.');
            }

            $update = mysqli_prepare($conexion, 'UPDATE personas SET password = ? WHERE id = ?');

            if (!$update) {
                die('Error al preparar la actualización de contraseña: ' . mysqli_error($conexion));
            }

            mysqli_stmt_bind_param($update, 'si', $passwordHash, $usuarioId);
            if (!mysqli_stmt_execute($update)) {
                die('Error al actualizar la contraseña: ' . mysqli_stmt_error($update));
            }
            mysqli_stmt_close($update);
        }
    }

    if ($autenticado) {
        $_SESSION['usuario_id'] = $usuarioId;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['rol'] = $rol;

        if ($rol === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: cliente.php");
        }
        exit();
    } else {
        $error = "Credenciales inválidas. Revisa tu correo o contraseña.";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Cafetería Express</title>
    <!-- Bootstrap 5 CSS y Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3efe9;
        }
        .card-login {
            max-width: 420px;
            border-radius: 1rem;
        }
        .btn-cafe {
            background-color: #6f4e37;
            border-color: #6f4e37;
            color: #ffffff;
        }
        .btn-cafe:hover {
            background-color: #5a3d2b;
            border-color: #5a3d2b;
            color: #ffffff;
        }
        .icono-cafe {
            width: 65px;
            height: 65px;
            background-color: #f7ede2;
            color: #6f4e37;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-4">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
            
            <div class="card card-login shadow-sm border-0 mx-auto bg-white p-3 p-sm-4">
                <div class="card-body">
                    
                    <!-- Encabezado con Icono -->
                    <div class="text-center mb-4">
                        <div class="icono-cafe mb-2 shadow-sm">
                            <i class="bi bi-cup-hot-fill"></i>
                        </div>
                        <h4 class="fw-bold mb-1 text-dark">Cafetería Express</h4>
                        <p class="text-muted small">Ingresa tus credenciales para continuar</p>
                    </div>

                    <!-- Mensaje de Error -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center py-2 px-3 small border-0" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div><?= htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario -->
                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" 
                                        name="email" 
                                       class="form-control border-start-0 ps-0" 
                                       placeholder="nombre@ejemplo.com" 
                                       required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-bold">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" 
                                       name="password" 
                                       class="form-control border-start-0 ps-0" 
                                       placeholder="••••••••" 
                                       required>
                            </div>
                        </div>

                        <button type="submit" name="btnIngresar" class="btn btn-cafe w-100 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                        </button>
                    </form>
                    <p class="text-center small mt-3 mb-0">
                        <a href="solicitar_recuperacion.php">¿Olvidaste tu contraseña?</a>
                    </p>
                    <p class="text-center small mt-2 mb-0">
                        ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
                    </p>

                    <!-- Enlace de Retorno -->
                    <div class="text-center mt-4 pt-2 border-top">
                        <a href="index.php" class="text-decoration-none text-muted small">
                            <i class="bi bi-arrow-left"></i> Volver a la cafetería
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include __DIR__ . '/includes/whatsapp.php'; ?>
</body>
</html>