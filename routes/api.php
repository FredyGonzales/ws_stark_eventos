<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/EstadisticasController.php';
require_once __DIR__ . '/../controllers/TipoEventoController.php';
require_once __DIR__ . '/../controllers/EmpresaController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// ============================================================
// OBTENER URI
// ============================================================
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ---------- TIPO DE EVENTOS ----------
if (strpos($uri, '/ws_stark_eventos/public/api/admin/tipo_eventos') !== false) {
    $decoded = validarToken('ADMIN');
    $controller = new TipoEventoController();

    // LISTAR (GET)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Captura parámetros de paginación si existen
        $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

        $controller->listar($page, $limit);
        exit;
    }

    // CREAR (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->crear();
        exit;
    }

    // ACTUALIZAR / ELIMINAR (PUT / DELETE)
    if (preg_match('#/api/admin/tipo_eventos/(\d+)#', $uri, $matches)) {
        $id = (int)$matches[1];

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $controller->actualizar($id);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $controller->eliminar($id);
            exit;
        }
    }
}


// ============================================================
// LOGIN (PÚBLICO)
// ============================================================
if ($uri === '/ws_stark_eventos/public/api/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['correo']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Faltan credenciales"]);
        exit;
    }

    $auth = new AuthController();
    $auth->login($data['correo'], $data['password']);
    exit;
}


// ============================================================
// RUTAS ADMINISTRATIVAS (Protegidas con Token)
// ============================================================

// ---------- ESTADÍSTICAS ----------
if ($uri === '/ws_stark_eventos/public/api/admin/estadisticas' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $decoded = validarToken('ADMIN');
    $controller = new EstadisticasController();
    $controller->listarTotales();
    exit;
}

// ---------- USUARIOS ----------
if (strpos($uri, '/ws_stark_eventos/public/api/admin/usuarios') !== false) {
    require_once __DIR__ . '/../controllers/UsuarioController.php';
    $decoded = validarToken('ADMIN');
    $controller = new UsuarioController();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->listarUsuarios();
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->crearUsuario();
        exit;
    }

    if (preg_match('#/api/admin/usuarios/(\d+)#', $uri, $matches)) {
        $id = (int)$matches[1];

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $controller->actualizar($id);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $controller->eliminarUsuario($id);
            exit;
        }
    }
}

// =========================================
// RUTA ADMIN: EMPRESA
// =========================================
if (strpos($uri, '/api/admin/empresa') !== false) {
    $decoded = validarToken('ADMIN');
    $controller = new EmpresaController();

    // Obtener datos de la empresa
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->obtener();
        exit;
    }

    // Actualizar empresa
    if (preg_match('#/api/admin/empresa/(\d+)#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'PUT') {
        $controller->actualizar((int)$matches[1]);
        exit;
    }
}

// ============================================================
// ENDPOINT NO ENCONTRADO
// ============================================================
http_response_code(404);
echo json_encode([
    "success" => false,
    "message" => "Endpoint no encontrado"
]);
exit;