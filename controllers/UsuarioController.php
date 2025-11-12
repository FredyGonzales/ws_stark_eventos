<?php
require_once __DIR__ . '/../model/UsuarioModel.php';

class UsuarioController {
    private $model;

    public function __construct() {
        $this->model = new UsuarioModel();
    }

    // Listar usuarios para panel administrador con paginación
    public function listarUsuarios() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
        $search = isset($_GET['search']) ? $_GET['search'] : null;

        $resultado = $this->model->listarUsuarios($page, $limit, $estado, $search);

        if (isset($resultado['error'])) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => $resultado['error']
            ]);
            return;
        }

        echo json_encode([
            "success" => true,
            "data" => $resultado
        ]);
    }

    // Actualizar usuario
    public function actualizar($id) {
        $datos = json_decode(file_get_contents('php://input'), true);
        if (!$datos) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Datos inválidos"]);
            return;
        }

        $resultado = $this->model->actualizarUsuario($id, $datos);
        echo json_encode($resultado);
    }

    // ===============================
    // Eliminar usuario
    // ===============================
    public function eliminarUsuario($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID no proporcionado"]);
            return;
        }

        $resultado = $this->model->eliminarUsuario($id);

        if ($resultado) {
            echo json_encode(["success" => true, "message" => "Usuario eliminado correctamente"]);
        } else {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
        }
    }

    // Crear nuevo usuario
public function crearUsuario() {
    $datos = json_decode(file_get_contents('php://input'), true);
    if (!$datos) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Datos inválidos"]);
        return;
    }

    $resultado = $this->model->crearUsuario($datos);

    if ($resultado['success']) {
        http_response_code(201);
    } else {
        http_response_code(400);
    }

    echo json_encode($resultado);
}
    
}