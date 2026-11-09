<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$tokenValido = is_string($token) && preg_match('/\A[a-f0-9]{64}\z/', $token) === 1;
$error = '';
$restablecido = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmacion = $_POST['confirmacion'] ?? '';
    $password = is_string($password) ? $password : '';
    $confirmacion = is_string($confirmacion) ? $confirmacion : '';

    if (!is_string($csrfToken) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error = 'La sesión del formulario venció. Abre de nuevo el enlace recibido por correo.';
    } elseif (!$tokenValido) {
        $error = 'El enlace no es válido o ya venció. Solicita uno nuevo.';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $error = 'La contraseña debe tener al menos 8 caracteres y no superar 72 bytes.';
    } elseif (!hash_equals($password, $confirmacion)) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            die('No fue posible generar el hash de la contraseña.');
        }

        if (!mysqli_begin_transaction($conexion)) {
            die('Error al iniciar el restablecimiento: ' . mysqli_error($conexion));
        }

        try {
            $tokenHash = hash('sha256', $token);
            $stmt = mysqli_prepare(
                $conexion,
                'SELECT id, persona_id FROM restablecimiento_contrasenas WHERE token_hash = ? AND vence_en > NOW() LIMIT 1 FOR UPDATE'
            );
            if (!$stmt) {
                throw new RuntimeException('Error al preparar la validación del enlace: ' . mysqli_error($conexion));
            }

            mysqli_stmt_bind_param($stmt, 's', $tokenHash);
            if (!mysqli_stmt_execute($stmt)) {
                throw new RuntimeException('Error al validar el enlace: ' . mysqli_stmt_error($stmt));
            }

            mysqli_stmt_bind_result($stmt, $restablecimientoId, $personaId);
            $enlaceEncontrado = mysqli_stmt_fetch($stmt);
            if ($enlaceEncontrado === false) {
                throw new RuntimeException('Error al leer el enlace: ' . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);

            if (!$enlaceEncontrado) {
                mysqli_rollback($conexion);
                $error = 'El enlace no es válido o ya venció. Solicita uno nuevo.';
            } else {
                $actualizar = mysqli_prepare($conexion, 'UPDATE personas SET password = ? WHERE id = ?');
                if (!$actualizar) {
                    throw new RuntimeException('Error al preparar el cambio de contraseña: ' . mysqli_error($conexion));
                }
                mysqli_stmt_bind_param($actualizar, 'si', $passwordHash, $personaId);
                if (!mysqli_stmt_execute($actualizar)) {
                    throw new RuntimeException('Error al cambiar la contraseña: ' . mysqli_stmt_error($actualizar));
                }
                mysqli_stmt_close($actualizar);

                $borrar = mysqli_prepare($conexion, 'DELETE FROM restablecimiento_contrasenas WHERE persona_id = ?');
                if (!$borrar) {
                    throw new RuntimeException('Error al preparar el cierre del enlace: ' . mysqli_error($conexion));
                }
                mysqli_stmt_bind_param($borrar, 'i', $personaId);
                if (!mysqli_stmt_execute($borrar)) {
                    throw new RuntimeException('Error al cerrar los enlaces de restablecimiento: ' . mysqli_stmt_error($borrar));
                }
                mysqli_stmt_close($borrar);

                if (!mysqli_commit($conexion)) {
                    throw new RuntimeException('Error al guardar la nueva contraseña: ' . mysqli_error($conexion));
                }

                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $restablecido = true;
            }
        } catch (Throwable $exception) {
            mysqli_rollback($conexion);
            throw $exception;
        }
    }
} elseif ($tokenValido) {
    $tokenHash = hash('sha256', $token);
    $stmt = mysqli_prepare(
        $conexion,
        'SELECT id FROM restablecimiento_contrasenas WHERE token_hash = ? AND vence_en > NOW() LIMIT 1'
    );
    if (!$stmt) {
        die('Error al preparar la validación del enlace: ' . mysqli_error($conexion));
    }
    mysqli_stmt_bind_param($stmt, 's', $tokenHash);
    if (!mysqli_stmt_execute($stmt)) {
        die('Error al validar el enlace: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_bind_result($stmt, $idRestablecimiento);
    $enlaceEncontrado = mysqli_stmt_fetch($stmt);
    if ($enlaceEncontrado === false) {
        die('Error al leer el enlace: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    if (!$enlaceEncontrado) {
        $tokenValido = false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elegir contraseña nueva - Cafetería Express</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 480px;">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Elegir contraseña nueva</h1>

                <?php if ($restablecido): ?>
                    <div class="alert alert-success" role="status">Tu contraseña fue actualizada. Ya puedes iniciar sesión.</div>
                    <a href="login.php" class="btn btn-primary w-100">Ir a iniciar sesión</a>
                <?php elseif (!$tokenValido): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error !== '' ? $error : 'El enlace no es válido o ya venció. Solicita uno nuevo.', ENT_QUOTES, 'UTF-8') ?></div>
                    <a href="solicitar_recuperacion.php" class="btn btn-primary w-100">Solicitar otro enlace</a>
                <?php else: ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <form method="POST" action="restablecer_contrasena.php">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                        </div>
                        <div class="mb-4">
                            <label for="confirmacion" class="form-label">Confirmar contraseña</label>
                            <input type="password" class="form-control" id="confirmacion" name="confirmacion" minlength="8" maxlength="72" autocomplete="new-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar contraseña</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/includes/whatsapp.php'; ?>
</body>
</html>
