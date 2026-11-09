<?php
session_start();
require_once __DIR__ . '/config/conexion.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$nombre = '';
$email = '';
$telefono = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $nombreInput = $_POST['nombre'] ?? '';
    $emailInput = $_POST['email'] ?? '';
    $telefonoInput = $_POST['telefono'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $confirmarPasswordInput = $_POST['confirmar_password'] ?? '';

    $nombre = is_string($nombreInput) ? trim($nombreInput) : '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $telefono = is_string($telefonoInput) ? trim($telefonoInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';
    $confirmarPassword = is_string($confirmarPasswordInput) ? $confirmarPasswordInput : '';

    if (!is_string($csrfToken) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    } elseif ($nombre === '' || strlen($nombre) > 100) {
        $error = 'Escribe tu nombre (máximo 100 caracteres).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $error = 'Escribe un correo electrónico válido (máximo 100 caracteres).';
    } elseif (strlen($telefono) > 20) {
        $error = 'El teléfono no puede superar los 20 caracteres.';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $error = 'La contraseña debe tener entre 8 y 72 caracteres.';
    } elseif ($password !== $confirmarPassword) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($passwordHash === false) {
            $error = 'No fue posible proteger la contraseña. Inténtalo de nuevo.';
        } else {
            try {
                $stmt = mysqli_prepare(
                    $conexion,
                    "INSERT INTO personas (nombre, telefono, email, password, rol) VALUES (?, ?, ?, ?, 'cliente')"
                );

                if (!$stmt) {
                    throw new RuntimeException('No fue posible preparar el registro: ' . mysqli_error($conexion));
                }

                $telefonoRegistro = $telefono !== '' ? $telefono : null;
                mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $telefonoRegistro, $email, $passwordHash);
                mysqli_stmt_execute($stmt);
                $usuarioId = mysqli_insert_id($conexion);
                mysqli_stmt_close($stmt);

                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $usuarioId;
                $_SESSION['nombre'] = $nombre;
                $_SESSION['rol'] = 'cliente';
                header('Location: cliente.php');
                exit();
            } catch (mysqli_sql_exception $exception) {
                if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                    mysqli_stmt_close($stmt);
                }

                if ((int) $exception->getCode() === 1062) {
                    $error = 'Ya existe una cuenta con ese correo electrónico.';
                } else {
                    error_log('Error al registrar una cuenta de cliente: ' . $exception->getMessage());
                    $error = 'No fue posible crear tu cuenta en este momento. Inténtalo más tarde.';
                }
            } catch (RuntimeException $exception) {
                error_log($exception->getMessage());
                $error = 'No fue posible crear tu cuenta en este momento. Inténtalo más tarde.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - Cafetería Express</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 520px;">
            <div class="card-body p-4">
                <h1 class="h3 mb-2">Crear cuenta</h1>
                <p class="text-muted mb-4">Regístrate para ingresar a Cafetería Express.</p>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="POST" action="registro.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>" autocomplete="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" maxlength="100" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono <span class="text-muted">(opcional)</span></label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" maxlength="20" value="<?= htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8') ?>" autocomplete="tel">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                        <div class="form-text">Usa entre 8 y 72 caracteres.</div>
                    </div>

                    <div class="mb-4">
                        <label for="confirmar_password" class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" minlength="8" maxlength="72" autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Crear mi cuenta</button>
                </form>

                <p class="text-center mt-3 mb-0">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
                <p class="text-center mt-2 mb-0"><a href="index.php">Volver a la cafetería</a></p>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/includes/whatsapp.php'; ?>
</body>
</html>
