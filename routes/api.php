<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/EstadisticasController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';


// Obtener la URI solicitada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// === LOGIN (Público) ===
if (strpos($uri, '/api/login') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['correo']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Faltan credenciales"]);
        exit;
    }

    $auth = new AuthController();
    $auth->login($data['correo'], $data['password']);
    exit;
}

// === RUTA PROTEGIDA: ADMIN/USUARIOS (con paginación, búsqueda y filtro) ===
if (strpos($uri, '/api/admin/usuarios') !== false && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $decoded = validarToken('ADMIN');

    require_once __DIR__ . '/../model/UsuarioModel.php';
    $usuarioModel = new UsuarioModel();

    // Parámetros opcionales
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;

    $resultado = $usuarioModel->listarUsuarios($page, $limit, $estado, $search);

    echo json_encode([
        "success" => true,
        "message" => "Acceso autorizado",
        "usuario" => $decoded,
        "data" => $resultado
    ]);
    exit;
}

// Obtener totales para el panel de inicio (ADMIN)
if (strpos($uri, '/api/admin/estadisticas') !== false && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $decoded = validarToken('ADMIN');
    $controller = new EstadisticasController();
    $controller->listarTotales();
    exit;
}

// === RUTA PROTEGIDA: ADMIN/USUARIOS ===
if (strpos($uri, '/api/admin/usuarios') !== false && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $decoded = validarToken('ADMIN'); // Valida token

    require_once __DIR__ . '/../controllers/UsuarioController.php';
    $controller = new UsuarioController();
    $controller->listarUsuarios();
    exit;
}
// PUT /api/admin/usuarios/:id
if (preg_match('#/api/admin/usuarios/(\d+)#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $decoded = validarToken('ADMIN');
    $usuarioId = (int)$matches[1];
    $controller = new UsuarioController();
    $controller->actualizar($usuarioId);
    exit;
}

// DELETE /api/admin/usuarios/:id
if (preg_match('#/api/admin/usuarios/(\d+)#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $decoded = validarToken('ADMIN'); // Valida token
    $usuarioId = (int)$matches[1];
    require_once __DIR__ . '/../controllers/UsuarioController.php';
    $controller = new UsuarioController();
    $controller->eliminarUsuario($usuarioId);
    exit;
}

// POST /api/admin/usuarios
if (strpos($uri, '/api/admin/usuarios') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $decoded = validarToken('ADMIN'); // Valida token
    require_once __DIR__ . '/../controllers/UsuarioController.php';
    $controller = new UsuarioController();
    $controller->crearUsuario();
    exit;
}

// === Si no coincide ninguna ruta ===
http_response_code(404);
echo json_encode([
    "success" => false,
    "message" => "Endpoint no encontrado"
]);
exit;