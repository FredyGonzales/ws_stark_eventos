<?php
include "../bd/conexion.php";

$pdo = new Conexion();

/* =======================================================
   📘 MÉTODO GET – LEER PROVEEDOR(ES)
   ======================================================= */
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id_proveedor'])) {
        $id_proveedor = $_GET['id_proveedor'];

        $stmt = $pdo->prepare("
            SELECT p.*, u.gls_correo AS correo_usuario, u.estado_usuario
            FROM stark_eventos.stark_proveedor p
            LEFT JOIN stark_eventos.stark_usuario u ON u.id_usuario = p.id_usuario
            WHERE p.id_proveedor = :id_proveedor
        ");
        $stmt->bindParam(':id_proveedor', $id_proveedor);
        $stmt->execute();
        $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($proveedor) {
            header("HTTP/1.1 200 OK");
            echo json_encode($proveedor);
        } else {
            header("HTTP/1.1 404 Not Found");
            echo json_encode(['error' => 'Proveedor no encontrado']);
        }
    } else {
        $stmt = $pdo->prepare("
            SELECT p.*, u.gls_correo AS correo_usuario
            FROM stark_eventos.stark_proveedor p
            LEFT JOIN stark_eventos.stark_usuario u ON u.id_usuario = p.id_usuario
            ORDER BY p.id_proveedor DESC
        ");
        $stmt->execute();
        $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode($proveedores);
        exit;
    }
}

/* =======================================================
   🟢 MÉTODO POST – INSERTAR PROVEEDOR + USUARIO
   ======================================================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        $pdo->beginTransaction();

        // 1️⃣ Insertar usuario
        $sqlUsuario = "INSERT INTO stark_eventos.stark_usuario
                       (cod_usuario,
                       tipo_documento,
                       num_documento,
                       nombre_completo,
                       apellido_paterno,
                       apellido_materno,
                       tipo_usuario,
                       gls_correo, password, estado_usuario, telefono, aud_usr_registro, aud_fec_registro)
                       VALUES (
                       :cod_usuario,
                       :tipo_documento,
                       :num_documento,
                       :nombre_completo,
                       :apellido_paterno,
                       :apellido_materno,
                       'PROVEEDOR',
                       :gls_correo, aes_encrypt(:password,'AES'), 'ACTIVO', :telefono, :aud_usr_registro, NOW())";
        $stmtUsuario = $pdo->prepare($sqlUsuario);
        $stmtUsuario->bindValue(':cod_usuario', $input['codigo_usuario']);
        $stmtUsuario->bindValue(':tipo_documento', $input['tipo_doc']);
        $stmtUsuario->bindValue(':num_documento', $input['num_doc']);
        $stmtUsuario->bindValue(':nombre_completo', $input['nombres']);
        $stmtUsuario->bindValue(':apellido_paterno', $input['ape_paterno']);
        $stmtUsuario->bindValue(':apellido_materno', $input['ape_materno']);
        $stmtUsuario->bindValue(':gls_correo', $input['correo']);
        $stmtUsuario->bindValue(':password', $input['password']);
        $stmtUsuario->bindValue(':aud_usr_registro', $input['codigo_usuario']);
        $stmtUsuario->bindValue(':telefono', $input['telefono']);
        $stmtUsuario->execute();
        $idUsuario = $pdo->lastInsertId();

        // 2️⃣ Insertar proveedor
        $sqlProveedor = "INSERT INTO stark_eventos.stark_proveedor
                         (cod_proveedor, nombre_proveedor, estado_proveedor, id_usuario, aud_usr_registro, aud_fec_registro)
                         VALUES (:cod_proveedor, :nombre_proveedor, 'ACTIVO', :id_usuario, :aud_usr_registro, NOW())";
        $stmtProveedor = $pdo->prepare($sqlProveedor);
        $stmtProveedor->bindValue(':cod_proveedor', $input['cod_proveedor']);
        $stmtProveedor->bindValue(':nombre_proveedor', $input['nombre_proveedor']);
        $stmtProveedor->bindValue(':id_usuario', $idUsuario);
        $stmtProveedor->bindValue(':aud_usr_registro', $input['codigo_usuario']);
        $stmtProveedor->execute();
        $idProveedor = $pdo->lastInsertId();

        $pdo->commit();

        header("HTTP/1.1 201 Created");
        echo json_encode([
            'success' => true,
            'message' => 'Proveedor y usuario creados correctamente',
            'id_proveedor' => $idProveedor,
            'id_usuario' => $idUsuario
        ]);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['error' => 'Error al crear el proveedor: ' . $e->getMessage()]);
        exit;
    }
}

/* =======================================================
   🟡 MÉTODO PUT – ACTUALIZAR PROVEEDOR + USUARIO
   ======================================================= */
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    try {
        if (isset($_GET['id_proveedor'])) {
            $id_proveedor = $_GET['id_proveedor'];
            parse_str(file_get_contents("php://input"), $_PUT);

            $pdo->beginTransaction();

            // 1️⃣ Obtener ID de usuario del proveedor
            $stmt = $pdo->prepare("SELECT id_usuario FROM stark_eventos.stark_proveedor WHERE id_proveedor = :id_proveedor");
            $stmt->bindParam(':id_proveedor', $id_proveedor);
            $stmt->execute();
            $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$proveedor) {
                throw new Exception("Proveedor no encontrado");
            }

            // 2️⃣ Actualizar proveedor
            $sqlProv = "UPDATE stark_eventos.stark_proveedor
                        SET nombre_proveedor = :nombre_proveedor,
                            estado_proveedor = :estado_proveedor,
                            aud_usr_modificacion = :aud_usr_modificacion,
                            aud_fec_modificacion = NOW()
                        WHERE id_proveedor = :id_proveedor";
            $stmtProv = $pdo->prepare($sqlProv);
            $stmtProv->bindValue(':nombre_proveedor', $_PUT['nombre_proveedor']);
            $stmtProv->bindValue(':estado_proveedor', $_PUT['estado_proveedor']);
            $stmtProv->bindValue(':aud_usr_modificacion', $_PUT['aud_usr_modificacion']);
            $stmtProv->bindValue(':id_proveedor', $id_proveedor);
            $stmtProv->execute();

            // 3️⃣ Actualizar usuario
            $sqlUser = "UPDATE stark_eventos.stark_usuario
                        SET gls_correo = :gls_correo,
                            estado_usuario = :estado_usuario,
                            aud_usr_modificacion = :aud_usr_modificacion,
                            aud_fec_modificacion = NOW()
                        WHERE id_usuario = :id_usuario";
            $stmtUser = $pdo->prepare($sqlUser);
            $stmtUser->bindValue(':gls_correo', $_PUT['correo']);
            $stmtUser->bindValue(':estado_usuario', $_PUT['estado_proveedor']);
            $stmtUser->bindValue(':aud_usr_modificacion', $_PUT['aud_usr_modificacion']);
            $stmtUser->bindValue(':id_usuario', $proveedor['id_usuario']);
            $stmtUser->execute();

            $pdo->commit();

            header("HTTP/1.1 200 OK");
            echo json_encode(['success' => 'Proveedor y usuario actualizados correctamente']);
            exit;
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(['error' => 'ID del proveedor no proporcionado']);
            exit;
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        exit;
    }
}

/* =======================================================
   🔴 MÉTODO DELETE – ELIMINAR PROVEEDOR + USUARIO
   ======================================================= */
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    if (isset($_GET['id_proveedor'])) {
        $id_proveedor = $_GET['id_proveedor'];

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT id_usuario FROM stark_eventos.stark_proveedor WHERE id_proveedor = :id_proveedor");
            $stmt->bindParam(':id_proveedor', $id_proveedor);
            $stmt->execute();
            $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($proveedor) {
                // 1️⃣ Eliminar proveedor
                $stmtDelProv = $pdo->prepare("DELETE FROM stark_eventos.stark_proveedor WHERE id_proveedor = :id_proveedor");
                $stmtDelProv->bindParam(':id_proveedor', $id_proveedor);
                $stmtDelProv->execute();

                // 2️⃣ Eliminar usuario asociado
                $stmtDelUser = $pdo->prepare("DELETE FROM stark_eventos.stark_usuario WHERE id_usuario = :id_usuario");
                $stmtDelUser->bindParam(':id_usuario', $proveedor['id_usuario']);
                $stmtDelUser->execute();

                $pdo->commit();
                header("HTTP/1.1 200 OK");
                echo json_encode(['message' => 'Proveedor y usuario eliminados correctamente']);
                exit;
            } else {
                header("HTTP/1.1 404 Not Found");
                echo json_encode(['error' => 'Proveedor no encontrado']);
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);
            exit;
        }

    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['error' => 'ID del proveedor no proporcionado']);
        exit;
    }
}
?>