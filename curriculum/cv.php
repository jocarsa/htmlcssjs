<?php
// cv.php
session_start();
if (!isset($_SESSION['user_id'])) {
    // If not logged in, maybe show an error or blank
    echo "No estás autenticado.";
    exit;
}
require_once __DIR__ . '/api/db.php';

// Fetch user data from DB
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT json_data FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "Usuario no encontrado en la base de datos.";
    exit;
}
$datos = json_decode($row['json_data'], true);
?>
<!doctype html>
<html lang="es">
<head>
  <title>Curriculum</title>
  <meta charset="utf-8">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap');
    html {
      background: white;
      font-family: Ubuntu, sans-serif, Arial;
    }
    body {
      background: white;
      width: 800px;
      margin: auto;
      padding: 50px;
    }
    h1, h2, h3, h4, h5 {
      margin: 0px;
    }
    header {
      display: flex;
      gap: 20px;
      align-items: center;
    }
    header img {
      flex: 1;
      border-radius: 1000px;
      width: 200px;
      height: 200px;
      margin: 20px;
    }
    header h1 {
      font-size: 50px;
    }
    header .texto {
      flex: 4;
    }
    header .texto * {
      margin: 0px;
    }
    header .texto h2 {
      font-weight: normal;
      font-size: 15px;
    }
    main {
      display: flex;
      gap: 30px;
    }
    main section:first-child {
      flex: 2;
    }
    main section:last-child {
      flex: 3;
    }
    p {
      text-align: justify;
      margin: 0px;
      font-size: 12px;
    }
    article {
      margin: 20px 0px;
      border-bottom: 1px solid lightgray;
      padding-bottom: 20px;
    }
    .social img {
      width: 30px;
      filter: invert(1);
      margin-right: 10px;
    }
    .social p {
      display: flex;
      align-items: center;
      margin-top: 10px;
    }
    #experiencias article, #formaciones article {
      display: flex;
      gap: 20px;
    }
    #experiencias article img,
    #formaciones article img {
      width: 20%;
    }
  </style>
</head>
<body>
  <header>
    <img src="<?php echo isset($datos['foto'])?$datos['foto']:"https://static.jocarsa.com/fotos/jose_vicente_carratala_curriculum.jpg"; ?>">
    <div class="texto">
      <h1><?php echo htmlspecialchars($datos['nombre'] ?? ""); ?></h1>
      <h2 id="telefono"><?php echo htmlspecialchars($datos['telefono'] ?? ""); ?></h2>
      <h2 id="email"><?php echo htmlspecialchars($datos['email'] ?? ""); ?></h2>
      <h2 id="titulo"><?php echo htmlspecialchars($datos['titulo'] ?? ""); ?></h2>
    </div>
  </header>

  <main>
    <section>
      <article id="sobremi">
        <h3>Sobre mi</h3>
        <p><?php echo htmlspecialchars($datos['sobre_mi'] ?? ""); ?></p>
      </article>
      <article id="habilidades">
        <h3>Habilidades</h3>
        <?php if (!empty($datos['habilidades'])): ?>
          <?php foreach($datos['habilidades'] as $habil): ?>
            <p>
              <span id="nivel">
                <?php echo str_repeat("🔵", intval($habil['nivel'] ?? 0)); ?>
              </span>
              <span id="nombre">
                <?php echo " " . htmlspecialchars($habil['nombre'] ?? ""); ?>
              </span>
            </p>
          <?php endforeach; ?>
        <?php endif; ?>
      </article>
      <article id="idiomas">
        <h3>Idiomas</h3>
        <?php if (!empty($datos['idiomas'])): ?>
          <?php foreach($datos['idiomas'] as $idioma): ?>
            <p>
              <span id="nivel">
                <?php echo str_repeat("🔵", intval($idioma['nivel'] ?? 0)); ?>
              </span>
              <span id="nombre">
                <?php echo " " . htmlspecialchars($idioma['nombre'] ?? ""); ?>
              </span>
            </p>
          <?php endforeach; ?>
        <?php endif; ?>
      </article>
      <article id="certificaciones">
        <h3>Certificaciones</h3>
        <?php if (!empty($datos['certificaciones'])): ?>
          <?php foreach($datos['certificaciones'] as $cert): ?>
            <p>
              <span id="nivel">
                <?php echo str_repeat("🔵", intval($cert['nivel'] ?? 0)); ?>
              </span>
              <span id="nombre">
                <?php echo " " . htmlspecialchars($cert['nombre'] ?? ""); ?>
              </span>
            </p>
          <?php endforeach; ?>
        <?php endif; ?>
      </article>
      <article class="social">
        <h3>En las redes</h3>
        <?php if (!empty($datos['redes_sociales'])): ?>
          <?php foreach($datos['redes_sociales'] as $rs): ?>
            <p>
              <img src="<?php echo htmlspecialchars($rs['icono'] ?? ""); ?>">
              <span id="nombre">
                <?php echo " " . htmlspecialchars($rs['url'] ?? ""); ?>
              </span>
            </p>
          <?php endforeach; ?>
        <?php endif; ?>
      </article>
    </section>

    <section>
      <div id="experiencias">
        <h3>Experiencia profesional</h3>
        <?php if (!empty($datos['experiencia_profesional'])): ?>
          <?php foreach($datos['experiencia_profesional'] as $exp): ?>
            <article>
              <img src="<?php echo htmlspecialchars($exp['logo'] ?? ""); ?>">
              <div class="texto">
                <h4><?php echo htmlspecialchars($exp['puesto'] ?? ""); ?></h4>
                <h5><?php echo htmlspecialchars($exp['empresa'] ?? ""); ?></h5>
                <time><?php echo htmlspecialchars($exp['fecha'] ?? ""); ?></time>
                <p><?php echo htmlspecialchars($exp['descripcion'] ?? ""); ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div id="formaciones">
        <h3>Formación</h3>
        <?php if (!empty($datos['formacion'])): ?>
          <?php foreach($datos['formacion'] as $f): ?>
            <article>
              <img src="<?php echo htmlspecialchars($f['logo'] ?? ""); ?>">
              <div class="texto">
                <h4><?php echo htmlspecialchars($f['titulo'] ?? ""); ?></h4>
                <h5><?php echo htmlspecialchars($f['institucion'] ?? ""); ?></h5>
                <time><?php echo htmlspecialchars($f['fecha'] ?? ""); ?></time>
                <p><?php echo htmlspecialchars($f['descripcion'] ?? ""); ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>
</body>
</html>
