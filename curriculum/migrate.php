<?php
// migrate.php
// Run this once to set up the database with an initial user.

session_start();
require_once __DIR__ . '/api/db.php';

// 1) Create users table if not exists
$createSQL = "CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    json_data TEXT
)";
$pdo->exec($createSQL);

// 2) Check if a user with username 'jocarsa' already exists
$stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM users WHERE username = :u");
$stmt->execute([':u' => 'jocarsa']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row['cnt'] == 0) {
    // Insert the default user and default JSON
    $defaultJSON = json_encode([
      "nombre" => "Jose Vicente Carratalá Sanchis",
      "telefono" => "+34 620 89 17 18",
      "email" => "info@josevicentecarratala.com",
      "titulo" => "Profesor, desarrollador y diseñador",
      "foto" => "https://static.jocarsa.com/fotos/jose_vicente_carratala_curriculum.jpg",
      "sobre_mi" => "Profesional con amplia experiencia en formación...",
      "habilidades" => [
        ["nombre"=>"Programación en Python","nivel"=>5],
        ["nombre"=>"Desarrollo web con HTML5 y CSS3","nivel"=>5],
        ["nombre"=>"Diseño y animación 3D","nivel"=>4],
        ["nombre"=>"Docencia y formación","nivel"=>5]
      ],
      "idiomas" => [
        ["nombre"=>"Español","nivel"=>5],
        ["nombre"=>"Inglés","nivel"=>4]
      ],
      "certificaciones" => [
        ["nombre"=>"Especialista en Creación de Entornos Virtuales Aplicados al Patrimonio","nivel"=>5]
      ],
      "redes_sociales" => [
        [
          "nombre"=>"LinkedIn",
          "url"=>"https://linkedin.com/in/jvcarratala",
          "icono"=>"https://static.jocarsa.com/social/linkedin.png"
        ],
        [
          "nombre"=>"Facebook",
          "url"=>"https://facebook.com/carratala",
          "icono"=>"https://static.jocarsa.com/social/facebook.png"
        ],
        [
          "nombre"=>"Instagram",
          "url"=>"https://instagram.com/jvcarratala",
          "icono"=>"https://static.jocarsa.com/social/instagram.png"
        ]
      ],
      "experiencia_profesional" => [
        [
          "puesto"=>"Profesor",
          "empresa"=>"Mastermedia",
          "logo"=>"https://scontent.fvlc1-2.fna.fbcdn.net/...",
          "fecha"=>"2019 - Actualidad",
          "descripcion"=>"Impartición de cursos..."
        ],
        [
          "puesto"=>"Desarrollador y diseñador freelance",
          "empresa"=>"Autónomo",
          "logo"=>"https://static.jocarsa.com/logos/jocarsa | Indigo.svg",
          "fecha"=>"2015 - Actualidad",
          "descripcion"=>"Desarrollo de soluciones..."
        ]
      ],
      "formacion" => [
        [
          "titulo"=>"Especialista en Creación de Entornos Virtuales...",
          "institucion"=>"Universidad de Alicante",
          "fecha"=>"2025",
          "descripcion"=>"Formación especializada en la creación de entornos virtuales...",
          "logo"=>"https://cdn.worldvectorlogo.com/logos/universidad-de-alicante-1.svg"
        ],
        [
          "titulo"=>"Máster en Inteligencia Artificial y Big Data",
          "institucion"=>"AIDIMME",
          "fecha"=>"2024",
          "descripcion"=>"Estudios avanzados en la aplicación de inteligencia artificial...",
          "logo"=>"https://yt3.googleusercontent.com/..."
        ]
      ],
      "publicaciones" => [
        [
          "titulo"=>"Aprende programación con Python: Desarrolla tus propias aplicaciones",
          "fecha"=>"2023",
          "descripcion"=>"Guía práctica para aprender a programar en Python..."
        ],
        [
          "titulo"=>"Aprende programación con C++: Desarrolla tus propias aplicaciones",
          "fecha"=>"2023",
          "descripcion"=>"Libro orientado a enseñar programación en C++..."
        ]
      ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $insertSQL = "INSERT INTO users (full_name, email, username, password, json_data)
                  VALUES (:fn, :em, :un, :pw, :json)";
    $insertStmt = $pdo->prepare($insertSQL);
    $insertStmt->execute([
      ':fn' => "Jose Vicente Carratala",
      ':em' => "info@josevicentecarratala.com",
      ':un' => "jocarsa",
      ':pw' => "jocarsa",  // For demo only; ideally hash it!
      ':json' => $defaultJSON
    ]);

    echo "Table created and default user inserted!";
} else {
    echo "Table/users already exist. Nothing to do.";
}
?>
