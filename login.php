<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['usuario'];
    $password = $_POST['contrasena'];

    if ($username === 'jocarsa' && $password === 'jocarsa') {
        $_SESSION['loggedin'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>jocarsa | darksalmon</title>
    <link rel="icon" href="../darksalmon.png" type="image/x-icon">
    <!-- PNG Favicon for Browsers -->
    <link rel="icon" type="image/png" sizes="32x32" href="../darksalmon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../darksalmon.png">
    <!-- Apple Touch Icon (iOS) -->
    <link rel="apple-touch-icon" sizes="180x180" href="../darksalmon.png">
    <style>
        @import url('https://static.jocarsa.com/fuentes/ubuntu-font-family-0.83/ubuntu.css');
        * {
          font-family: Ubuntu, sans-serif;
        }
        html, body {
            padding: 0;
            margin: 0;
            height: 100%;
            background: DarkSalmon;
            background: linear-gradient(0deg, rgba(124,80,65,1) 0%, rgba(233,150,122,1) 100%);
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        form {
            width: 250px;
            border: 1px solid lightgrey;
            border-radius: 150px 150px 5px 5px;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.3);
            background: white;
            padding: 20px;
            font-size: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        form img {
            width: 100%;
            border-radius: 300px;
            box-sizing: content-box;
        }
        form h2 {
            margin-top: 0;
            color: DarkSalmon;
        }
        label {
            width: 100%;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid lightgrey;
            border-radius: 5px;
            box-shadow: inset 0px 4px 8px rgba(0,0,0,0.1);
            margin-top: 5px;
            box-sizing: border-box;
        }
        .relieve {
            background-color: rgba(0,0,0,0.3);
            box-shadow: 0 4px 0 rgba(255,255,255,0.7),
                        0 6px 6px rgba(0,0,0,0.2);
            transition: all 0.2s ease;
            border: none;
        }
        .relieve:active {
            box-shadow: 0 0 0 #357ab7,
                        0 2px 4px rgba(0,0,0,0.2);
        }
        .login-btn {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;
            color: white;
            background: darksalmon;
        }
        .login-btn:hover {
            filter: brightness(110%);
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <form method="POST" action="login.php">
        <img src="../darksalmon.png" alt="Logo">
        <h2>jocarsa | darksalmon</h2>
        <label for="username">Usuario:</label>
        <input type="text" name="usuario" id="username" required>
        <label for="password">Contraseña:</label>
        <input type="password" name="contrasena" id="password" required>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <input class="relieve login-btn" type="submit" value="Iniciar sesión">
    </form>
</body>
</html>

