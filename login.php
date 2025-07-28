<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];

    // Usuario y clave fijos
    if ($usuario === "jorgeledesma" && $clave === "220893") {
        $_SESSION['usuario'] = $usuario;
        header("Location: index.php"); // Redirige a la página principal protegida
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <style>
        body {
            background: #f5f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: #fff;
            padding: 32px 28px 24px 28px;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            min-width: 320px;
        }
        .login-container h2 {
            margin-bottom: 18px;
            color: #222;
            text-align: center;
        }
        form {
            max-width: 320px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 18px;
            width: 100%;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #555;
            font-weight: 500;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
            background: #f9fafb;
            transition: border 0.2s;
            text-align: center;
            margin: 0;
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            border: 1.5px solid #007bff;
            outline: none;
            background: #fff;
        }
        .login-btn {
            width: 100%;
            padding: 10px 0;
            background: linear-gradient(90deg, #007bff 60%, #0056b3 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 8px;
        }
        .login-btn:hover {
            background: linear-gradient(90deg, #0056b3 60%, #007bff 100%);
        }
        .error-msg {
            color: #c00;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar sesión</h2>
        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" style="max-width: 320px; margin: 0 auto;">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" name="usuario" id="usuario" required autocomplete="username" style="text-align: center; width: 100%;">
            </div>
            <div class="form-group">
                <label for="clave">Contraseña</label>
                <input type="password" name="clave" id="clave" required autocomplete="current-password" style="text-align: center; width: 100%;">
            </div>
            <button class="login-btn" type="submit" style="width: 100%;">Entrar</button>
        </form>
    </div>
</body>
</html>