<?php
// register.php
session_start();
require_once __DIR__ . '/api/db.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $user     = trim($_POST['usuario'] ?? '');
    $pass     = trim($_POST['contrasena'] ?? '');

    if ($fullname && $email && $user && $pass) {
        // Check if username is taken
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u LIMIT 1");
        $stmt->execute([':u'=>$user]);
        if ($stmt->fetch()) {
            $error = "El nombre de usuario ya existe.";
        } else {
            // Insert user
            // For real applications, you should hash the password
            $json_data = json_encode([]); // Start them with empty data if you like
            $ins = $pdo->prepare("INSERT INTO users (full_name, email, username, password, json_data)
                                  VALUES (:fn, :em, :un, :pw, :jd)");
            $ok = $ins->execute([
                ':fn' => $fullname,
                ':em' => $email,
                ':un' => $user,
                ':pw' => $pass,
                ':jd' => $json_data
            ]);
            if ($ok) {
                $newId = $pdo->lastInsertId();
                // Auto login
                $_SESSION['user_id'] = $newId;
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Error al crear el usuario.";
            }
        }
    } else {
        $error = "Por favor, rellena todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Registro</title>
  <style>
    @import url('https://static.jocarsa.com/fuentes/ubuntu-font-family-0.83/ubuntu.css');
    html, body {
      margin: 0; padding: 0;
      font-family: Ubuntu, sans-serif;
      height: 100%;
      background: DarkSlateGray;
    }
    body {
      display: flex; justify-content: center; align-items: center;
    }
    form {
      width: 320px;
      background: #fff;
      border-radius: 5px;
      box-shadow: 0px 4px 8px rgba(0,0,0,0.3);
      padding: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    form img {
      width: 80px;
      margin-bottom: 20px;
    }
    label {
      width: 100%;
      text-align: left;
      margin-top: 10px;
      font-weight: bold;
      color: #555;
    }
    input[type="text"],
    input[type="password"],
    input[type="email"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      margin-bottom: 10px;
      border: 1px solid lightgrey;
      border-radius: 5px;
      box-sizing: border-box;
      font-family: inherit;
      box-shadow: inset 0px 4px 8px rgba(0,0,0,0.1);
    }
    input[type="submit"] {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      border-radius: 5px;
      border: none;
      background: #2980b9;
      color: white;
      cursor: pointer;
      font-weight: bold;
      box-shadow:
        0 1px #2980b9,
        0 2px #2471a3,
        0 3px #1f618d,
        0 4px #1a5276,
        0 5px #154360,
        0 8px 10px rgba(0,0,0,0.2);
    }
    .error {
      color: red;
      margin-top: 10px;
    }
    .back-link {
      margin-top: 15px;
      text-align: center;
    }
    .back-link a {
      color: #2980b9;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
<form method="POST">
  <img src="https://static.jocarsa.com/logos/grey.png" alt="Logo">

  <label for="full_name">Nombre Completo:</label>
  <input type="text" name="full_name" id="full_name" required>

  <label for="email">Email:</label>
  <input type="email" name="email" id="email" required>

  <label for="usuario">Usuario:</label>
  <input type="text" name="usuario" id="usuario" required>

  <label for="contrasena">Contraseña:</label>
  <input type="password" name="contrasena" id="contrasena" required>

  <input type="submit" value="Registrar">

  <?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div class="back-link">
    ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
  </div>
</form>
</body>
</html>
