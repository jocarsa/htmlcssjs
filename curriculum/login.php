<?php
// login.php
session_start();
require_once __DIR__ . '/api/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user     = $_POST['usuario'] ?? '';
    $password = $_POST['contrasena'] ?? '';

    if ($user && $password) {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            // Compare plain password (for demo). For real apps: use password_verify() with hashed password
            if ($row['password'] === $password) {
                $_SESSION['user_id'] = $row['id'];
                // Go to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
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
  <title>Iniciar Sesión</title>
  <style>
    /* Adapted style closer to your dashboard aesthetic */
    @import url('https://static.jocarsa.com/fuentes/ubuntu-font-family-0.83/ubuntu.css');
    html, body {
      margin: 0;
      padding: 0;
      font-family: Ubuntu, sans-serif;
      height: 100%;
      background: DarkSlateGray;
    }
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      color: #333;
    }
    form {
      width: 300px;
      background: #ffffff;
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
    input[type="password"] {
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
    .check-row {
      display: flex;
      align-items: center;
      align-self: flex-start;
      margin-top: 10px;
    }
    .error {
      color: red;
      margin-top: 10px;
    }
    .register-link {
      margin-top: 15px;
      text-align: center;
    }
    .register-link a {
      color: #2980b9;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>

<form method="POST">
  <img src="https://static.jocarsa.com/logos/grey.png" alt="Logo">
  
  <label for="usuario">Usuario:</label>
  <input type="text" name="usuario" id="usuario" required>

  <label for="contrasena">Contraseña:</label>
  <input type="password" name="contrasena" id="contrasena" required>

  <div class="check-row">
    <input type="checkbox" name="recuerdame" style="margin-right:5px;"> <span style="font-size:13px;">Recuérdame</span>
  </div>

  <input type="submit" value="Iniciar Sesión">

  <?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div class="register-link">
    ¿No tienes cuenta? <a href="register.php">Regístrate</a>
  </div>
</form>

</body>
</html>
