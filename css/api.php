<?php
/**
 * api.php
 *
 * Single file para:
 *  1) Crear (si no existe) una base de datos SQLite (data.db).
 *  2) Crear tablas básicas del modelo de RRHH.
 *  3) Servir un CRUD en JSON según la ruta (endpoint) y método (GET, POST, PUT, DELETE).
 *
 * Ejemplos de llamadas:
 *  GET    api.php?endpoint=Empleados            -> Lista todos los empleados
 *  POST   api.php?endpoint=Empleados           -> Crea un nuevo empleado (en body JSON)
 *  PUT    api.php?endpoint=Empleados&id=123    -> Actualiza el empleado con id=123 (en body JSON)
 *  DELETE api.php?endpoint=Empleados&id=123    -> Borra el empleado con id=123
 *
 * Ajusta tu front-end (JavaScript/Fetch) para enviar los datos y métodos correspondientes.
 */

header('Content-Type: application/json; charset=utf-8');

// 1. Conectar o crear la base de datos SQLite
$dbFile = __DIR__ . '/data.db';
$existe = file_exists($dbFile); // ¿Existe ya el fichero?

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al conectar la base de datos: " . $e->getMessage()]);
    exit;
}

// 2. Si la BD no existía, creamos las tablas
if (!$existe) {
    crearTablas($pdo);
}

// 3. Detectamos el endpoint y el método HTTP
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : null;
$method   = $_SERVER['REQUEST_METHOD']; // GET, POST, PUT, DELETE
$id       = isset($_GET['id']) ? intval($_GET['id']) : null;

// 4. Parsear el cuerpo de la petición (para POST/PUT/DELETE)
$inputData = [];
if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
    // Asumimos que llega JSON en el body
    $json = file_get_contents("php://input");
    $inputData = json_decode($json, true);
    if (!is_array($inputData)) {
        $inputData = []; // Por si no se ha enviado JSON válido
    }
}

// 5. Enrutamos según $endpoint
switch ($endpoint) {
    case 'Empleados':
        crudGenerico($pdo, 'empleados', $method, $id, $inputData);
        break;
    case 'Asistencia':
        crudGenerico($pdo, 'asistencia', $method, $id, $inputData);
        break;
    case 'Vacaciones':
        crudGenerico($pdo, 'vacaciones', $method, $id, $inputData);
        break;
    case 'Contratos':
        crudGenerico($pdo, 'contratos', $method, $id, $inputData);
        break;
    case 'Incidencias':
        crudGenerico($pdo, 'incidencias', $method, $id, $inputData);
        break;
    // Endpoint 'menu' para poblar el menú lateral
    case 'menu':
        $menu = [
            ["etiqueta" => "Empleados"],
            ["etiqueta" => "Asistencia"],
            ["etiqueta" => "Vacaciones"],
            ["etiqueta" => "Contratos"],
            ["etiqueta" => "Incidencias"]
        ];
        echo json_encode($menu);
        break;
    default:
        echo json_encode(["error" => "Endpoint no válido"]);
        break;
}

exit;

/**
 * crea las tablas iniciales en la BD
 */
function crearTablas(PDO $pdo)
{
    $queries = [
        // Empleados
        "CREATE TABLE IF NOT EXISTS empleados (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT,
            apellido TEXT,
            departamento TEXT,
            tipo_contrato TEXT
        )",
        // Asistencia
        "CREATE TABLE IF NOT EXISTS asistencia (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            empleado_id INTEGER,
            fecha TEXT,
            hora_entrada TEXT,
            hora_salida TEXT
        )",
        // Vacaciones
        "CREATE TABLE IF NOT EXISTS vacaciones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            empleado_id INTEGER,
            fecha_inicio TEXT,
            fecha_fin TEXT,
            aprobado INTEGER
        )",
        // Contratos
        "CREATE TABLE IF NOT EXISTS contratos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            empleado_id INTEGER,
            tipo_contrato TEXT,
            fecha_firma TEXT,
            salario_anual REAL
        )",
        // Incidencias
        "CREATE TABLE IF NOT EXISTS incidencias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            empleado_id INTEGER,
            tipo TEXT,
            fecha TEXT
        )"
    ];
    try {
        foreach ($queries as $q) {
            $pdo->exec($q);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error al crear tablas: " . $e->getMessage()]);
        exit;
    }
}

/**
 * CRUD genérico para cualquier tabla (sirve de ejemplo).
 *
 * @param PDO    $pdo       - Conexión PDO a la BD
 * @param string $tabla     - Nombre de la tabla
 * @param string $method    - GET, POST, PUT, DELETE
 * @param int    $id        - ID (si viene por GET)
 * @param array  $inputData - Datos recibidos por JSON en el body
 */
function crudGenerico(PDO $pdo, $tabla, $method, $id, $inputData)
{
    switch ($method) {
        case 'GET':
            // Si hay $id => devolver 1 registro; si no => devolver todos
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM $tabla WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $fila = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($fila ? $fila : []);
            } else {
                $stmt = $pdo->query("SELECT * FROM $tabla");
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($results);
            }
            break;

        case 'POST':
            // Crear nuevo registro
            if (empty($inputData)) {
                echo json_encode(["error" => "No se recibieron datos para insertar"]);
                return;
            }
            // Construimos SQL dinámico
            $columns = array_keys($inputData);
            $placeholders = array_map(fn($col) => ':' . $col, $columns);

            $sql = "INSERT INTO $tabla (" . implode(',', $columns) . ")
                    VALUES (" . implode(',', $placeholders) . ")";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($inputData);
                // Devolvemos el ID insertado
                $lastId = $pdo->lastInsertId();
                echo json_encode(["success" => true, "id" => $lastId]);
            } catch (PDOException $e) {
                echo json_encode(["error" => $e->getMessage()]);
            }
            break;

        case 'PUT':
            // Actualizar un registro existente
            if (!$id) {
                echo json_encode(["error" => "Falta parámetro ID para actualizar"]);
                return;
            }
            if (empty($inputData)) {
                echo json_encode(["error" => "No se recibieron datos para actualizar"]);
                return;
            }
            // Construimos SQL dinámico
            $sets = [];
            foreach ($inputData as $col => $val) {
                $sets[] = "$col=:$col";
            }
            $sql = "UPDATE $tabla SET " . implode(',', $sets) . " WHERE id=:id";
            try {
                $stmt = $pdo->prepare($sql);
                $inputData['id'] = $id; // Añadimos el id en parámetros
                $stmt->execute($inputData);
                echo json_encode(["success" => true, "id" => $id]);
            } catch (PDOException $e) {
                echo json_encode(["error" => $e->getMessage()]);
            }
            break;

        case 'DELETE':
            // Borrar un registro
            if (!$id) {
                echo json_encode(["error" => "Falta parámetro ID para eliminar"]);
                return;
            }
            $sql = "DELETE FROM $tabla WHERE id=:id";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id' => $id]);
                echo json_encode(["success" => true, "id" => $id]);
            } catch (PDOException $e) {
                echo json_encode(["error" => $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(["error" => "Método no soportado ($method)"]);
            break;
    }
}

