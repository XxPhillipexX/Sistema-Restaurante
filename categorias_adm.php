<?php
// Arquivo: categorias_adm.php
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
    $sql = "SELECT Nome_Categoria FROM Categorias ORDER BY Nome_Categoria";
    $resultado = $conn->query($sql);
    
    $categorias = [];
    if ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $categorias[] = $row; // Retorna apenas o nome da categoria
        }
    }
    
    echo json_encode($categorias);

} catch (Exception $e) {
    http_response_code(500); 
    echo json_encode(["message" => "Erro na consulta de categorias: " . $e->getMessage()]);
}
$conn->close();
?>