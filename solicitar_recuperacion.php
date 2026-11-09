<?php
session_start();
require_once 'config/conexion.php';
require_once 'config/app.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensaje = '';
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $emailInput = $_POST['email'] ?? '';
    $email = is_string($emailInput) ? trim($emailInput) : '';

    if (!is_string($csrfToken) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Escribe un correo electrónico válido.';
    } else {
        $stmt = mysqli_prepare($conexion, 'SELECT id, nombre FROM personas WHERE email = ? LIMIT 1');
        if (!$stmt) {
            die('Error al preparar la solicitud: ' . mysqli_error($conexion));
        }

        mysqli_stmt_bind_param($stmt, 's', $email);
        if (!mysqli_stmt_execute($stmt)) {
            die('Error al buscar la cuenta: ' . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_bind_result($stmt, $personaId, $nombre);
        $personaEncontrada = mysqli_stmt_fetch($stmt);
        if ($personaEncontrada === false) {
            die('Error al leer la cuenta: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);

        if ($personaEncontrada) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);

            if (!mysqli_begin_transaction($conexion)) {
                die('Error al iniciar la solicitud: ' . mysqli_error($conexion));
            }

            try {
                $borrar = mysqli_prepare($conexion, 'DELETE FROM restablecimiento_contrasenas WHERE persona_id = ?');
                if (!$borrar) {
                    throw new RuntimeException('Error al preparar la renovación del enlace: ' . mysqli_error($conexion));
                }
                mysqli_stmt_bind_param($borrar, 'i', $personaId);
                if (!mysqli_stmt_execute($borrar)) {
                    throw new RuntimeException('Error al renovar el enlace: ' . mysqli_stmt_error($borrar));
                }
                mysqli_stmt_close($borrar);

                $insertar = mysqli_prepare(
                    $conexion,
                    'INSERT INTO restablecimiento_contrasenas (persona_id, token_hash, vence_en) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
                );
                if (!$insertar) {
                    throw new RuntimeException('Error al preparar el enlace: ' . mysqli_error($conexion));
                }
                mysqli_stmt_bind_param($insertar, 'is', $personaId, $tokenHash);
                if (!mysqli_stmt_execute($insertar)) {
                    throw new RuntimeException('Error al guardar el enlace: ' . mysqli_stmt_error($insertar));
                }
                mysqli_stmt_close($insertar);

                if (!mysqli_commit($conexion)) {
                    throw new RuntimeException('Error al guardar la solicitud: ' . mysqli_error($conexion));
                }
            } catch (Throwable $exception) {
                mysqli_rollback($conexion);
                throw $exception;
            }

            $urlRestablecimiento = rtrim(APP_BASE_URL, '/') . '/restablecer_contrasena.php?token=' . rawurlencode($token);
            $asunto = 'Restablecimiento de contraseña - ' . APP_NAME;
            $cuerpo = "Hola {$nombre},\n\n";
            $cuerpo .= "Recibimos una solicitud para restablecer la contraseña de tu cuenta. ";
            $cuerpo .= "Abre este enlace dentro de la próxima hora para elegir una nueva:\n\n";
            $cuerpo .= $urlRestablecimiento . "\n\n";
            $cuerpo .= "Si no solicitaste este cambio, puedes ignorar este mensaje.\n";
            $headers = "From: " . APP_NAME . " <" . APP_MAIL_FROM . ">\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            if (!mail($email, $asunto, $cuerpo, $headers)) {
                error_log('No fue posible enviar el correo de restablecimiento a la cuenta ' . $personaId . '.');
                $borrarToken = mysqli_prepare($conexion, 'DELETE FROM restablecimiento_contrasenas WHERE token_hash = ?');
                if (!$borrarToken) {
                    die('Error al preparar la limpieza del enlace: ' . mysqli_error($conexion));
                }
                mysqli_stmt_bind_param($borrarToken, 's', $tokenHash);
                if (!mysqli_stmt_execute($borrarToken)) {
                    die('Error al limpiar el enlace no enviado: ' . mysqli_stmt_error($borrarToken));
                }
                mysqli_stmt_close($borrarToken);
            }
        }

        $mensaje = 'Si existe una cuenta asociada a ese correo, enviaremos un enlace para restablecer la contraseña. Revisa también la carpeta de correo no deseado.';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $email = '';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - Cafetería Express</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 480px;">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Recuperar contraseña</h1>
                <p class="text-muted">Escribe el correo asociado a tu cuenta. Te enviaremos un enlace para elegir una contraseña nueva.</p>

                <?php if ($mensaje !== ''): ?>
                    <div class="alert alert-info" role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="POST" action="solicitar_recuperacion.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-4">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Enviar enlace</button>
                </form>
                <p class="text-center mt-3 mb-0"><a href="login.php">Volver al inicio de sesión</a></p>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/includes/whatsapp.php'; ?>
</body>
</html>
