<?php
// Arquivo: buscar_dados_usuario.php
require_once 'conexao.php'; // Assume conexao.php inicia a sessão e fornece $conn

header('Content-Type: application/json');

// 1. VERIFICAÇÃO DE SESSÃO
// A maioria dos seus arquivos usa um ID de teste ($idUserTeste), mas cardapio.php usa a SESSION.
// Vamos usar o ID da SESSION, que é o correto para usuários logados.
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Usuário não logado."]);
    exit;
}

$idUser = $_SESSION['user_id']; // ID do usuário logado

try {
    // 2. BUSCA OS DADOS DO USUÁRIO
    // Colunas 'nome', 'telefone' e 'endereco' estão na sua tabela Usuarios
    $sql = "SELECT nome, telefone, endereco FROM Usuarios WHERE Id_User = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idUser);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($usuario = $resultado->fetch_assoc()) {
        echo json_encode([
            "success" => true,
            "data" => [
                "nome" => $usuario['nome'],
                "telefone" => (string)$usuario['telefone'], 
                "endereco" => $usuario['endereco']
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Dados do usuário não encontrados."]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro de servidor: " . $e->getMessage()]);
}

$conn->close();
?>