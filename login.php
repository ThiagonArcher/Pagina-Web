<?php
session_start();

if (isset($_SESSION['admin_logueado']) && $_SESSION['admin_logueado'] === true) {
    header("Location: admin.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    if ($usuario === "admin" && $password === "admin123") {
        $_SESSION['admin_logueado'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Admin</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #333; font-family: sans-serif; }
        .login-box { background: white; padding: 40px; border-radius: 10px; text-align: center; width: 300px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        input { width: 100%; padding: 12px; margin: 10px 0; box-sizing: border-box; border: 1px solid #ccc; border-radius: 5px; }
        .btn-login { width: 100%; padding: 12px; background: #800000; color: white; border: none; cursor: pointer; border-radius: 5px; font-weight: bold; font-size: 1.1em; }
        .btn-login:hover { background: #a00000; }
        .error { color: red; font-size: 0.9em; margin-bottom: 10px; }
        .back-link { display: block; margin-top: 15px; color: #555; text-decoration: none; }
        h2 { color: #800000; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔒 Acceso Administrativo</h2>
        <form method="POST" action="">
            <label style="display:block; text-align:left; font-weight:bold; color:#555;">Usuario:</label>
            <input type="text" name="usuario" required autocomplete="off">
            
            <label style="display:block; text-align:left; font-weight:bold; color:#555;">Contraseña:</label>
            <input type="password" name="password" required>
            
            <?php if($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            
            <button type="submit" class="btn-login">Entrar</button>
        </form>
        <a href="index.php" class="back-link">← Volver a la Tienda</a>
    </div>
</body>
</html>