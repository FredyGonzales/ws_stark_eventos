<?php
require_once __DIR__ . '/../model/EmpresaModel.php';

class EmpresaController
{
    private $model;

    public function __construct()
    {
        $this->model = new EmpresaModel();
    }

    // GET - Obtener empresa
    public function obtener()
    {
        try {
            $empresa = $this->model->obtenerEmpresa();
            if ($empresa) {
                echo json_encode([
                    "success" => true,
                    "data" => $empresa
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "No se encontró información de la empresa."
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al obtener la empresa: " . $e->getMessage()
            ]);
        }
    }

    // PUT - Actualizar empresa
    public function actualizar($id)
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            $campos = ["ruc_empresa", "gls_empresa", "direccion", "telefono", "correo", "aud_usr_modificacion"];
            foreach ($campos as $campo) {
                if (!isset($data[$campo])) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "Campo '$campo' es obligatorio."]);
                    return;
                }
            }

            $resultado = $this->model->actualizarEmpresa($id, $data);

            if ($resultado) {
                echo json_encode([
                    "success" => true,
                    "message" => "Empresa actualizada correctamente."
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "No se pudo actualizar la empresa."
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error al actualizar: " . $e->getMessage()
            ]);
        }
    }
}
?>