<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { 
    echo json_encode(['success' => false, 'message' => 'No autorizado']); 
    exit(); 
}

require_once 'database.php';

// Recibimos el tipo (dni o ruc) y el número
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'dni';
$numero = isset($_GET['numero']) ? trim($_GET['numero']) : '';

if (empty($numero)) {
    echo json_encode(['success' => false, 'message' => 'Número no válido']);
    exit;
}

// Validar longitud
if ($tipo === 'dni' && strlen($numero) !== 8) {
    echo json_encode(['success' => false, 'message' => 'El DNI debe tener 8 dígitos']);
    exit;
}
if ($tipo === 'ruc' && strlen($numero) !== 11) {
    echo json_encode(['success' => false, 'message' => 'El RUC debe tener 11 dígitos']);
    exit;
}

// ==========================================
// 1. BUSCAR EN NUESTRA CACHÉ (BD LOCAL)
// ==========================================
$stmt = $pdo->prepare("SELECT * FROM consultas_documentos WHERE tipo = ? AND numero = ?");
$stmt->execute([$tipo, $numero]);
$cache = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cache) {
    // Devolvemos los datos guardados SIN gastar token
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'from_cache' => true,
        'data' => json_decode($cache['datos_json'], true)
    ]);
    exit;
}

// ==========================================
// 2. SI NO ESTÁ EN CACHÉ, CONSULTAR LA API
// ==========================================
if (empty(API_PERU_TOKEN)) {
    echo json_encode(['success' => false, 'message' => 'Token de API no configurado']);
    exit;
}

$url = "https://apiperu.dev/api/{$tipo}/{$numero}?api_token=" . API_PERU_TOKEN;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: application/json');

if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión con la API (Código: ' . $httpCode . ')']);
    exit;
}

$data = json_decode($response, true);

if (isset($data['success']) && $data['success'] === true) {
    // ==========================================
    // 3. GUARDAR EN LA CACHÉ (BD LOCAL)
    // ==========================================
    $nombreCompleto = '';
    if (isset($data['data']['nombre_completo'])) {
        $nombreCompleto = $data['data']['nombre_completo'];
    } elseif (isset($data['data']['nombres']) && isset($data['data']['apellido_paterno'])) {
        $nombreCompleto = $data['data']['nombres'] . ' ' . $data['data']['apellido_paterno'] . ' ' . ($data['data']['apellido_materno'] ?? '');
    } elseif (isset($data['data']['razon_social'])) {
        $nombreCompleto = $data['data']['razon_social'];
    }

    $direccion = $data['data']['direccion'] ?? null;
    $telefono = $data['data']['telefono'] ?? null;

    try {
        $stmt = $pdo->prepare("INSERT INTO consultas_documentos (tipo, numero, nombre_completo, direccion, telefono, datos_json) 
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                nombre_completo = VALUES(nombre_completo),
                direccion = VALUES(direccion),
                telefono = VALUES(telefono),
                datos_json = VALUES(datos_json),
                fecha_consulta = CURRENT_TIMESTAMP");
        $stmt->execute([$tipo, $numero, $nombreCompleto, $direccion, $telefono, json_encode($data['data'])]);
    } catch (PDOException $e) {
        // Si falla el guardado, no pasa nada, igual devolvemos los datos
    }

    echo json_encode($data);
} else {
    echo json_encode(['success' => false, 'message' => $data['message'] ?? 'No se encontraron datos']);
}
?>