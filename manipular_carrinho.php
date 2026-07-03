<?php
// Arquivo: manipular_carrinho.php (Versão Final e Corrigida)
require_once 'conexao.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    echo json_encode(["success" => false, "message" => "Método não permitido."]);
    exit;
}

// 1. UTILIZA O ID DA SESSÃO
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Sessão expirada ou usuário não logado."]);
    exit;
}
$idUser = $_SESSION['user_id']; // ID DO USUÁRIO LOGADO

// 2. LEITURA E DECODIFICAÇÃO DO JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// --- CHECAGEM DE ROBUSTEZ JSON ---
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || empty($data)) {
    http_response_code(400); 
    echo json_encode(["success" => false, "message" => "Erro ao decodificar JSON. Dados podem estar ausentes ou mal formatados."]);
    exit;
}
// --------------------------------

$idPedido = filter_var($data['idPedido'] ?? null, FILTER_VALIDATE_INT);
$acao = $data['acao'] ?? ''; // 'aumentar', 'diminuir', 'remover'

// 3. VALIDAÇÃO DOS DADOS RECEBIDOS
if (!$idPedido || !in_array($acao, ['aumentar', 'diminuir', 'remover'])) {
    echo json_encode(["success" => false, "message" => "Dados inválidos para a manipulação do carrinho. (ID: {$idPedido}, Ação: {$acao})"]);
    exit;
}

try {
    $conn->begin_transaction();
    $success = false;
    $message = "";

    // 4. BUSCA O ITEM PARA VERIFICAR A QUANTIDADE ATUAL
    $stmt_item = $conn->prepare("SELECT Quantidade FROM Pedidos WHERE Id_Pedidos = ? AND Id_User = ? AND Id_Compra IS NULL");
    $stmt_item->bind_param("ii", $idPedido, $idUser); 
    $stmt_item->execute();
    $resultado_item = $stmt_item->get_result();
    $item = $resultado_item->fetch_assoc();
    $stmt_item->close();
    
    if ($acao === 'remover') {
        // Ação 'remover': remove o item inteiro
        $stmt = $conn->prepare("DELETE FROM Pedidos WHERE Id_Pedidos = ? AND Id_User = ? AND Id_Compra IS NULL");
        $stmt->bind_param("ii", $idPedido, $idUser);
        
        if ($stmt->execute()) {
            $success = true;
            $message = "Item removido do carrinho.";
        } else {
            throw new Exception("Erro ao remover item: " . $stmt->error);
        }
        $stmt->close();
        
    } elseif ($item) {
        // 5. Ações 'aumentar' e 'diminuir'
        
        if ($acao === 'aumentar') {
            $stmt = $conn->prepare("UPDATE Pedidos SET Quantidade = Quantidade + 1 WHERE Id_Pedidos = ? AND Id_User = ? AND Id_Compra IS NULL");
            $stmt->bind_param("ii", $idPedido, $idUser); 
            
            if ($stmt->execute()) {
                $success = true;
                $message = "Quantidade do item aumentada.";
            } else {
                throw new Exception("Erro ao aumentar quantidade: " . $stmt->error);
            }
            $stmt->close();
            
        } elseif ($acao === 'diminuir') {
            
            if ($item['Quantidade'] > 1) {
                // Diminui a quantidade se for maior que 1
                $stmt = $conn->prepare("UPDATE Pedidos SET Quantidade = Quantidade - 1 WHERE Id_Pedidos = ? AND Id_User = ? AND Id_Compra IS NULL");
                $stmt->bind_param("ii", $idPedido, $idUser); 

                if ($stmt->execute()) {
                    $success = true;
                    $message = "Quantidade do item diminuída.";
                } else {
                    throw new Exception("Erro ao diminuir quantidade: " . $stmt->error);
                }
                $stmt->close();

            } elseif ($item['Quantidade'] == 1) {
                // Se for 1, remove (para evitar quantidade 0)
                $stmt = $conn->prepare("DELETE FROM Pedidos WHERE Id_Pedidos = ? AND Id_User = ? AND Id_Compra IS NULL");
                $stmt->bind_param("ii", $idPedido, $idUser); 
                
                if ($stmt->execute()) {
                    $success = true;
                    $message = "Item removido (quantidade era 1).";
                } else {
                    throw new Exception("Erro ao remover último item: " . $stmt->error);
                }
                $stmt->close();

            } else {
                throw new Exception("Quantidade de item inválida (menor que 1).");
            }
        }
    } else {
        throw new Exception("Item de pedido não encontrado ou já finalizado.");
    }
    
    // 6. RECALCULA O CARRINHO APÓS A MANIPULAÇÃO
    if ($success) {
        $conn->query("CALL RecalcularCarrinho(" . $idUser . ")"); 
    }
    
    $conn->commit(); 
    
    echo json_encode(["success" => $success, "message" => $message]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    // Erro 45000: Erro customizado da procedure FinalizarCompra.
    if (strpos($e->getMessage(), '45000') !== false) {
        $message = "Transação falhou: " . substr($e->getMessage(), strpos($e->getMessage(), ']') + 2);
    } else {
        $message = "Erro interno: " . $e->getMessage();
    }
    echo json_encode(["success" => false, "message" => $message]);
}
$conn->close();
?>