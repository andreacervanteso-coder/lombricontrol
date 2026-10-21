
<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba - Lombricontrol</title>
</head>
<body>
    <h1>¡Inicio de sesión exitoso!</h1>

    <p>
        Bienvenido a Lombricontrol,
        <?php echo htmlspecialchars($_SESSION['usuario']); ?>.
    </p>

    <p>Esta es tu página de prueba.</p>

    <a href="cerrar_sesion.php">Cerrar sesión</a>
</body>
</html>
