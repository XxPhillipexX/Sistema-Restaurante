<?php
// Arquivo: produtos_adm.php
require_once '../conexao.php'; 

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type"); 

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $sql = "SELECT p.Id_Produtos, p.Nome_Produto, p.Valor_produto, p.Descricao, c.Nome_Categoria,
                COALESCE(SUM(pd.Quantidade), 0) AS Total_Vendas
                FROM Produtos p
                JOIN Categorias c ON p.Id_Categoria = c.Id_Categoria
                LEFT JOIN Pedidos pd ON p.Id_Produtos = pd.Id_Produtos AND pd.Id_Compra IS NOT NULL 
                GROUP BY p.Id_Produtos, p.Nome_Produto, p.Valor_produto, p.Descricao, c.Nome_Categoria
                ORDER BY p.Id_Produtos ASC";
        
        $resultado = $conn->query($sql);
        $produtos = [];
        while ($row = $resultado->fetch_assoc()) {
            $row['Valor_produto'] = (float)$row['Valor_produto'];
            $produtos[] = $row; 
        }
        echo json_encode($produtos);
    } 
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $nome = $data['Nome_Produto'] ?? null;
        $valor = $data['Valor_produto'] ?? null; 
        $descricao = $data['Descricao'] ?? null;
        $nomeCategoria = $data['Nome_Categoria'] ?? null;

        if (!$nome || !$valor || !$nomeCategoria) {
            http_response_code(400);
            echo json_encode(["message" => "Dados obrigatórios faltando."]);
            exit;
        }

        $valorFloat = floatval(str_replace(',', '.', $valor));

        $stmt_cat = $conn->prepare("SELECT Id_Categoria FROM Categorias WHERE Nome_Categoria = ?");
        $stmt_cat->bind_param("s", $nomeCategoria);
        $stmt_cat->execute();
        $res_cat = $stmt_cat->get_result();
        $id_categoria = ($row = $res_cat->fetch_assoc()) ? $row['Id_Categoria'] : null;

        if (!$id_categoria) {
            http_response_code(400);
            echo json_encode(["message" => "Categoria não encontrada."]);
            exit;
        }

        $sql_insert = "INSERT INTO Produtos (Valor_produto, Nome_Produto, Descricao, Id_Categoria) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("dssi", $valorFloat, $nome, $descricao, $id_categoria); 

        if ($stmt_insert->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Produto adicionado com sucesso!", "id" => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erro ao adicionar: " . $stmt_insert->error]);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => $e->getMessage()]);
}
$conn->close();
?>