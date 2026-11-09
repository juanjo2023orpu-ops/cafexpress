<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este script solo se puede ejecutar desde la consola.\n");
}

require_once dirname(__DIR__) . '/config/conexion.php';

if (!mysqli_begin_transaction($conexion)) {
    throw new RuntimeException('No fue posible iniciar la transacción: ' . mysqli_error($conexion));
}

try {
    $consulta = mysqli_prepare($conexion, 'SELECT id, password FROM personas WHERE password IS NOT NULL');
    if (!$consulta) {
        throw new RuntimeException('No fue posible consultar las contraseñas: ' . mysqli_error($conexion));
    }

    if (!mysqli_stmt_execute($consulta)) {
        throw new RuntimeException('No fue posible consultar las contraseñas: ' . mysqli_stmt_error($consulta));
    }

    if (!mysqli_stmt_bind_result($consulta, $usuarioId, $passwordGuardada)) {
        throw new RuntimeException('No fue posible leer las contraseñas: ' . mysqli_stmt_error($consulta));
    }

    $usuarios = [];
    while (($resultado = mysqli_stmt_fetch($consulta)) === true) {
        $usuarios[] = ['id' => $usuarioId, 'password' => $passwordGuardada];
    }
    if ($resultado === false) {
        throw new RuntimeException('No fue posible leer las contraseñas: ' . mysqli_stmt_error($consulta));
    }
    mysqli_stmt_close($consulta);

    $actualizacion = mysqli_prepare($conexion, 'UPDATE personas SET password = ? WHERE id = ?');
    if (!$actualizacion) {
        throw new RuntimeException('No fue posible preparar la actualización: ' . mysqli_error($conexion));
    }

    $passwordHash = '';
    $id = 0;
    if (!mysqli_stmt_bind_param($actualizacion, 'si', $passwordHash, $id)) {
        throw new RuntimeException('No fue posible vincular los datos: ' . mysqli_stmt_error($actualizacion));
    }
    $migradas = 0;

    foreach ($usuarios as $usuario) {
        $informacionHash = password_get_info($usuario['password']);
        if (isset($informacionHash['algo']) && $informacionHash['algo'] !== null) {
            continue;
        }

        $passwordHash = password_hash($usuario['password'], PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            throw new RuntimeException('No fue posible generar el hash para el usuario ' . $usuario['id'] . '.');
        }

        $id = $usuario['id'];
        if (!mysqli_stmt_execute($actualizacion)) {
            throw new RuntimeException('No fue posible actualizar el usuario ' . $id . ': ' . mysqli_stmt_error($actualizacion));
        }

        $migradas++;
    }

    mysqli_stmt_close($actualizacion);

    if (!mysqli_commit($conexion)) {
        throw new RuntimeException('No fue posible confirmar la migración: ' . mysqli_error($conexion));
    }

    echo "Migración completada. Contraseñas convertidas: {$migradas}.\n";
} catch (Throwable $error) {
    mysqli_rollback($conexion);
    throw $error;
}
