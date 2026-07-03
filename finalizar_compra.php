<?php
// Arquivo: finalizar_compra.php (VERSÃO FINAL E CORRIGIDA)
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
$idUser = $_SESSION['user_id']; 

$data = json_decode(file_get_contents('php://input'), true);

// 2. VALIDAÇÃO DOS DADOS DO CLIENTE RECEBIDOS DO JS
$nome = $data['nome'] ?? '';
$endereco = $data['endereco'] ?? '';
$telefone = $data['telefone'] ?? '';
$cpf = $data['cpf'] ?? '';
$pagamento = $data['pagamento'] ?? '';

if (empty($nome) || empty($endereco) || empty($telefone) || empty($pagamento)) {
    http_response_code(400); 
    echo json_encode(["success" => false, "message" => "Dados do cliente incompletos ou inválidos."]);
    exit;
}

try {
    $conn->begin_transaction();
    
    // A. BUSCAR O VALOR TOTAL ATUAL DO CARRINHO
    $sql_carrinho = "SELECT Valor_Carrinho FROM Carrinho WHERE Id_User = ?";
    $stmt_carrinho = $conn->prepare($sql_carrinho);
    $stmt_carrinho->bind_param("i", $idUser);
    $stmt_carrinho->execute();
    $resultado_carrinho = $stmt_carrinho->get_result();
    $row_carrinho = $resultado_carrinho->fetch_assoc();
    $valor_total = $row_carrinho['Valor_Carrinho'] ?? 0;
    $stmt_carrinho->close();

    if ($valor_total == 0) {
        throw new Exception("O carrinho está vazio. Impossível finalizar.");
    }

    // B. INSERIR NA TABELA COMPRA (singular)
    $sql_insert_compra = "INSERT INTO Compra (Data_Compra, Valor_Total, Id_User, Nome_Cliente, Endereco_Cliente, Telefone_Cliente, CPF_Cliente, Forma_Pagamento) 
                          VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert_compra = $conn->prepare($sql_insert_compra);
    $stmt_insert_compra->bind_param("sisssss", $valor_total, $idUser, $nome, $endereco, $telefone, $cpf, $pagamento); 
    
    if (!$stmt_insert_compra->execute()) {
        throw new Exception("Erro ao registrar a compra na tabela Compra: " . $stmt_insert_compra->error);
    }
    $idCompra = $conn->insert_id;
    $stmt_insert_compra->close();
    
    
    // C. PASSO CRUCIAL: MARCAR ITENS DO CARRINHO COMO CONCLUÍDOS
    // Atualiza o Id_Compra nos pedidos ATIVOS (Id_Compra IS NULL) para o ID da compra recém-criada.
    $stmt_update_pedidos = $conn->prepare("UPDATE Pedidos SET Id_Compra = ? WHERE Id_User = ? AND Id_Compra IS NULL");
    $stmt_update_pedidos->bind_param("ii", $idCompra, $idUser); 
    
    if (!$stmt_update_pedidos->execute()) {
        throw new Exception("Erro ao vincular itens à compra: " . $stmt_update_pedidos->error);
    }
    $stmt_update_pedidos->close();

    // D. RECALCULAR CARRINHO (Isto zera o valor no Carrinho, pois agora não há pedidos ativos)
    if (!$conn->query("CALL RecalcularCarrinho(" . $idUser . ")")) {
        throw new Exception("Erro ao recalcular o carrinho: " . $conn->error);
    }
    
    $conn->commit(); 
    
    echo json_encode(["success" => true, "message" => "Pedido finalizado com sucesso! ID da Compra: " . $idCompra]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro interno: " . $e->getMessage()]);
}
$conn->close();
?>