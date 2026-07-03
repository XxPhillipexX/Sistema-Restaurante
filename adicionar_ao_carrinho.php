<?php
// Arquivo: adicionar_ao_carrinho.php (Versão Final - usa Id_Compra IS NULL)
require_once 'conexao.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    echo json_encode(["success" => false, "message" => "Método não permitido."]);
    exit;
}

// 1. UTILIZAÇÃO E VERIFICAÇÃO DO ID DA SESSÃO
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Sessão expirada ou usuário não logado."]);
    exit;
}
$idUser = $_SESSION['user_id']; // ID DO USUÁRIO LOGADO

$data = json_decode(file_get_contents('php://input'), true);

$produtoId = filter_var($data['produtoId'] ?? null, FILTER_VALIDATE_INT);
$quantidade = filter_var($data['quantidade'] ?? 1, FILTER_VALIDATE_INT);

if (!$produtoId || $quantidade < 1) {
    echo json_encode(["success" => false, "message" => "Dados do produto inválidos."]);
    exit;
}

try {
    $conn->begin_transaction();

    // 1. VERIFICAR SE O ITEM JÁ EXISTE NO CARRINHO
    // FILTRO ADICIONADO: Id_Compra IS NULL (apenas itens que não foram finalizados)
    $sql_check = "SELECT Id_Pedidos, Quantidade FROM Pedidos WHERE Id_Produtos = ? AND Id_User = ? AND Id_Compra IS NULL";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $produtoId, $idUser); 
    $stmt_check->execute();
    $resultado_check = $stmt_check->get_result();

    if ($row = $resultado_check->fetch_assoc()) {
        // 2. SE EXISTIR: ATUALIZAR QUANTIDADE (UPDATE)
        $id_pedido_existente = $row['Id_Pedidos'];
        $nova_quantidade = $row['Quantidade'] + $quantidade;

        // FILTRO ADICIONADO: Id_Compra IS NULL
        $stmt_update = $conn->prepare("UPDATE Pedidos SET Quantidade = ? WHERE Id_Pedidos = ? AND Id_User = ? AND Id_Compra IS NULL");
        $stmt_update->bind_param("iii", $nova_quantidade, $id_pedido_existente, $idUser); 

        if ($stmt_update->execute()) {
             // Recalcula o carrinho após a atualização
             $conn->query("CALL RecalcularCarrinho(" . $idUser . ")"); 
             echo json_encode(["success" => true, "message" => "Quantidade do item atualizada."]);
        } else {
            throw new Exception("Erro ao atualizar item: " . $stmt_update->error);
        }
        $stmt_update->close();
        
    } else {
        // 3. SE NÃO EXISTIR: INSERIR NOVO REGISTRO (INSERT)
        // Id_Compra será NULL por padrão.
        $stmt_insert = $conn->prepare("INSERT INTO Pedidos (Quantidade, Data_pedido, Id_Produtos, Id_User) VALUES (?, NOW(), ?, ?)");
        $stmt_insert->bind_param("iii", $quantidade, $produtoId, $idUser); 

        if ($stmt_insert->execute()) {
            // Recalcula o carrinho após a inserção (os triggers do DB também ajudam)
            $conn->query("CALL RecalcularCarrinho(" . $idUser . ")");
            echo json_encode(["success" => true, "message" => "Item adicionado."]);
        } else {
            throw new Exception("Erro ao adicionar item: " . $stmt_insert->error);
        }
        $stmt_insert->close();
    }
    
    $conn->commit();
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500); 
    echo json_encode(["success" => false, "message" => "Erro interno: " . $e->getMessage()]);
}
$conn->close();
?>