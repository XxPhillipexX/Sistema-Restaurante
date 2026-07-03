<?php
// Arquivo: produto_unico_adm.php (Versão Original com suporte a CORS)
require_once '../conexao.php'; 

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: PUT, DELETE, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 

// --- ÚNICA ALTERAÇÃO: Resposta para o Preflight do navegador ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
// -------------------------------------------------------------

$method = $_SERVER['REQUEST_METHOD'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

function response_error($conn, $message, $code = 400) {
    http_response_code($code);
    echo json_encode(["message" => $message]);
    $conn->close();
    exit;
}

if (!$id) {
    response_error($conn, "ID do produto é obrigatório.", 400);
}

switch ($method) {
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $updates = [];
        $tipos = "";
        $params = [];

        if (isset($data['Valor_produto'])) {
            $updates[] = "Valor_produto = ?";
            // MANTIDA sua lógica de conversão original
            $valor_limpo = str_replace(['R$', ' ', '.'], '', $data['Valor_produto']);
            $valor_final = str_replace(',', '.', $valor_limpo);
            $tipos .= "d"; 
            $params[] = floatval($valor_final); 
        }
        
        if (isset($data['Descricao'])) {
            $updates[] = "Descricao = ?";
            $tipos .= "s"; 
            $params[] = $data['Descricao'];
        }

        if (empty($updates)) {
            response_error($conn, "Nenhum dado fornecido para atualização.");
        }
        
        $tipos .= "i"; 
        $params[] = $id; 

        try {
            $sql = "UPDATE Produtos SET " . implode(", ", $updates) . " WHERE Id_Produtos = ?";
            $stmt = $conn->prepare($sql);
            
            // MANTIDA sua lógica de bind_param dinâmico original
            $bind_params = array_merge([$tipos], $params);
            $refs = [];
            foreach ($bind_params as $key => $value) {
                $refs[$key] = &$bind_params[$key];
            }
            call_user_func_array([$stmt, 'bind_param'], $refs);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(["message" => "Produto atualizado com sucesso."]);
            } else {
                echo json_encode(["message" => "Nenhuma alteração feita ou ID não encontrado."]);
            }
            $stmt->close();
        } catch (Exception $e) {
            response_error($conn, "Erro ao atualizar produto: " . $e->getMessage(), 500);
        }
        break;

    case 'DELETE':
        try {
            $sql = "DELETE FROM Produtos WHERE Id_Produtos = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(["message" => "Produto excluído com sucesso."]);
            } else {
                response_error($conn, "ID de produto não encontrado.", 404);
            }
            $stmt->close();
        } catch (Exception $e) {
             response_error($conn, "Erro ao excluir produto (Chave Estrangeira): " . $e->getMessage(), 500);
        }
        break;

    default:
        response_error($conn, "Método não permitido.", 405);
        break;
}
$conn->close();
?>