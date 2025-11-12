<?php
require_once __DIR__ . '/../model/EstadisticasModel.php';
require_once __DIR__ . '/../src/jwt_utils.php';

class EstadisticasController {
    public function listarTotales() {
        try {
            $model = new EstadisticasModel();
            $data = $model->obtenerTotales();

            echo json_encode([
                "success" => true,
                "data" => $data
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }
}
?>