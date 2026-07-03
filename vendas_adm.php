<?php
// Arquivo: vendas_adm.php (Versão Atualizada - conta vendas CONCLUÍDAS via Id_Compra)
require_once '../conexao.php'; 

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET"); 

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); 
    echo json_encode(["message" => "Método não permitido."]);
    exit;
}

try {
    
    // 1. BUSCA MAIS VENDIDOS
    $sql_mais = "
        SELECT 
            p.Nome_Produto, 
            c.Nome_Categoria,
            SUM(pd.Quantidade) AS Total_Vendas 
        FROM Pedidos pd
        JOIN Produtos p ON pd.Id_Produtos = p.Id_Produtos
        JOIN Categorias c ON p.Id_Categoria = c.Id_Categoria
        -- FILTRO ESSENCIAL: Contar apenas pedidos vinculados a uma Compra
        WHERE pd.Id_Compra IS NOT NULL 
        GROUP BY p.Nome_Produto, c.Nome_Categoria
        ORDER BY Total_Vendas DESC
        LIMIT 3";
        
    $resultado_mais = $conn->query($sql_mais);
    $maisVendidos = [];
    if ($resultado_mais) {
        while ($row = $resultado_mais->fetch_assoc()) {
            $maisVendidos[] = $row; 
        }
    }

    // 2. BUSCA MENOS VENDIDOS
    $sql_menos = "
        SELECT 
            p.Nome_Produto, 
            c.Nome_Categoria,
            SUM(pd.Quantidade) AS Total_Vendas 
        FROM Pedidos pd
        JOIN Produtos p ON pd.Id_Produtos = p.Id_Produtos
        JOIN Categorias c ON p.Id_Categoria = c.Id_Categoria
        -- FILTRO ESSENCIAL: Contar apenas pedidos vinculados a uma Compra
        WHERE pd.Id_Compra IS NOT NULL
        GROUP BY p.Nome_Produto, c.Nome_Categoria
        ORDER BY Total_Vendas ASC
        LIMIT 3";
        
    $resultado_menos = $conn->query($sql_menos);
    $menosVendidos = [];
    if ($resultado_menos) {
        while ($row = $resultado_menos->fetch_assoc()) {
            $menosVendidos[] = $row; 
        }
    }
    
    echo json_encode(["maisVendidos" => $maisVendidos, "menosVendidos" => $menosVendidos]);

} catch (Exception $e) {
    http_response_code(500); 
    echo json_encode(["message" => "Erro interno do servidor ao buscar histórico: " . $e->getMessage()]);
}
$conn->close();
?>