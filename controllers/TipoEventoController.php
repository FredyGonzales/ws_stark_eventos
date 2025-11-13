<?php
require_once __DIR__ . '/../model/TipoEventoModel.php';

class TipoEventoController {
    private $model;

    public function __construct() {
        $this->model = new TipoEventoModel();
    }

    // ==============================
    // LISTAR
    // ==============================
    public function listar() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

        $resultado = $this->model->listar($page, $limit);

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

    // ==============================
    // CREAR
    // ==============================
    public function crear() {
        $datos = json_decode(file_get_contents('php://input'), true);

        if (!$datos) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Datos inválidos"]);
            return;
        }

        $resultado = $this->model->crear($datos);

        if ($resultado['success']) {
            echo json_encode([
                "success" => true,
                "message" => "Tipo de evento creado correctamente",
                "id" => $resultado['id']
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => $resultado['error']
            ]);
        }
    }

    // ==============================
    // ACTUALIZAR
    // ==============================
    public function actualizar($id) {
        $datos = json_decode(file_get_contents('php://input'), true);

        if (!$datos) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Datos inválidos"]);
            return;
        }

        $resultado = $this->model->actualizar($id, $datos);

        if ($resultado['success']) {
            echo json_encode([
                "success" => true,
                "message" => "Tipo de evento actualizado correctamente"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => $resultado['error']
            ]);
        }
    }

    // ==============================
    // ELIMINAR
    // ==============================
    public function eliminar($id) {
        $resultado = $this->model->eliminar($id);

        if ($resultado['success']) {
            echo json_encode([
                "success" => true,
                "message" => "Tipo de evento eliminado correctamente"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => $resultado['error']
            ]);
        }
    }
}
?>