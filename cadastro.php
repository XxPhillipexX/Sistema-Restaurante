<?php

// Arquivo: cadastro.php (INSERINDO COM NOME E TELEFONE)
require_once 'conexao.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    echo json_encode(["success" => false, "message" => "Método não permitido."]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Novos campos
$nome = $data['nome'] ?? '';
$telefone = $data['telefone'] ?? '';

// Campos existentes
$email = $data['email'] ?? ''; 
$senha = $data['senha'] ?? '';
$endereco = $data['endereco'] ?? ''; 
$cpf = $data['cpf'] ?? ''; 
$tipo = 'Cliente'; 

// Validação (agora com Nome e Telefone)
if (empty($nome) || empty($telefone) || empty($email) || empty($senha) || empty($endereco) || empty($cpf)) {
    echo json_encode(["success" => false, "message" => "Todos os campos são obrigatórios."]);
    exit;
}

$senha_hashed = password_hash($senha, PASSWORD_DEFAULT);

try {
    // Colunas: Nome, Telefone, email, senha, endereco, CPF, tipo
    $sql = "INSERT INTO Usuarios (Nome, Telefone, email, senha, endereco, CPF, tipo) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // bind_param: Nome(s), Telefone(s), email(s), senha(s), endereco(s), CPF(s), tipo(s)
    $stmt->bind_param("sssssss", $nome, $telefone, $email, $senha_hashed, $endereco, $cpf, $tipo); 

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true, 
            "message" => "Cadastro realizado com sucesso! Redirecionando para login.",
            "redirect" => "login.html"
        ]);
    } else {
        // --- TRATAMENTO DE ERRO DE UNICIDADE ---
        // 1062 é o código de erro do MySQL para violação de chave única/primária (Duplicidade)
        if ($stmt->errno == 1062) {
            echo json_encode(["success" => false, "message" => "Este E-mail e/ou CPF já estão cadastrados."]);
        } else {
            // Erro de inserção genérico
            echo json_encode(["success" => false, "message" => "Erro ao cadastrar usuário: " . $stmt->error]);
        }
    }
    $stmt->close();
    
} catch (Exception $e) {
    // Erro de conexão/servidor
    echo json_encode(["success" => false, "message" => "Erro de servidor: " . $e->getMessage()]);
}

$conn->close();
?>