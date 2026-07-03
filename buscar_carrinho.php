<?php
// Arquivo: buscar_carrinho.php (Versão Final - usa Id_Compra IS NULL)
require_once 'conexao.php'; 

header('Content-Type: application/json');

// 1. UTILIZAÇÃO E VERIFICAÇÃO DO ID DA SESSÃO
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
    // Retorna carrinho vazio se não houver usuário logado (evita erro no JS)
    echo json_encode(["success" => true, "carrinho" => ['itens' => [], 'total' => 0.00, 'total_quantidade' => 0]]);
    exit;
}
$idUser = $_SESSION['user_id']; // ID DO USUÁRIO LOGADO

// Inicializa o array de resposta
$carrinho = [
    'itens' => [],
    'total' => 0.00,
    'total_quantidade' => 0 
];

try {
    // 1. BUSCA OS ITENS DETALHADOS (Pedidos)
    // FILTRO: Id_Compra IS NULL (itens não finalizados)
    $sql_itens = "
        SELECT 
            p.Id_Pedidos, 
            p.Quantidade, 
            p.Valor_Pedido, 
            prod.Nome_Produto 
        FROM Pedidos p
        JOIN Produtos prod ON p.Id_Produtos = prod.Id_Produtos
        WHERE p.Id_User = ? AND p.Id_Compra IS NULL
    ";
    
    $stmt_itens = $conn->prepare($sql_itens);
    $stmt_itens->bind_param("i", $idUser);
    $stmt_itens->execute();
    $resultado_itens = $stmt_itens->get_result();
    
    while ($item = $resultado_itens->fetch_assoc()) {
        // Valor total do item (Valor_Pedido * Quantidade)
        $valorItemTotal = $item['Valor_Pedido'];

        $carrinho['itens'][] = [
            'idPedido' => $item['Id_Pedidos'], // Chave corrigida para camelCase
            'nomeProduto' => $item['Nome_Produto'], // Chave corrigida para camelCase
            'quantidade' => (int)$item['Quantidade'],
            'valorTotal' => (float)$valorItemTotal 
        ];
    }
    
    $stmt_itens->close();
    
    // 2. BUSCA O VALOR TOTAL DO CARRINHO (tabela Carrinho)
    $sql_total = "SELECT Valor_Carrinho FROM Carrinho WHERE Id_User = ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("i", $idUser);
    $stmt_total->execute();
    $resultado_total = $stmt_total->get_result();
    
    if ($row_total = $resultado_total->fetch_assoc()) {
        $carrinho['total'] = (float)($row_total['Valor_Carrinho']); 
    }
    
    $stmt_total->close();
    
    // 3. BUSCA A QUANTIDADE TOTAL DE UNIDADES NO CARRINHO
    $sql_qtd = "SELECT COALESCE(SUM(Quantidade), 0) AS total_unidades FROM Pedidos WHERE Id_User = ? AND Id_Compra IS NULL";
    $stmt_qtd = $conn->prepare($sql_qtd);
    $stmt_qtd->bind_param("i", $idUser);
    $stmt_qtd->execute();
    $resultado_qtd = $stmt_qtd->get_result();
    
    if ($row_qtd = $resultado_qtd->fetch_assoc()) {
        $carrinho['total_quantidade'] = (int)$row_qtd['total_unidades'];
    }
    $stmt_qtd->close();

    echo json_encode(["success" => true, "carrinho" => $carrinho]);
    
} catch (Exception $e) {
    http_response_code(500); 
    echo json_encode(["success" => false, "message" => "Erro interno ao buscar carrinho: " . $e->getMessage()]);
}
$conn->close();
?>