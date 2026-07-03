<?php
// Arquivo: login.php (LOGIN COM EMAIL)
require_once 'conexao.php'; // Inclui a conexão e inicia a sessão

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    echo json_encode(["success" => false, "message" => "Método não permitido."]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'] ?? '';
$senha = $data['senha'] ?? '';

if (empty($email) || empty($senha)) {
    echo json_encode(["success" => false, "message" => "Preencha e-mail e senha."]);
    exit;
}

try {
    // Busca o usuário usando a coluna 'email' da tabela
    $sql = "SELECT Id_User, email, senha, tipo FROM Usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($usuario = $resultado->fetch_assoc()) {
        
        if (password_verify($senha, $usuario['senha'])) {
            // Login Bem-sucedido!
            
            $_SESSION['user_id'] = $usuario['Id_User'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_tipo'] = $usuario['tipo'];

            // Define o redirecionamento. Renomeie restaurante.html para .php para segurança.
            $redirect = ($usuario['tipo'] === 'Admin') ? 'gerencia.html' : 'restaurante.php'; // ALTERADO AQUI
            
            echo json_encode([
                "success" => true, 
                "message" => "Login bem-sucedido!", 
                "redirect" => $redirect
            ]);
            
        } else {
            echo json_encode(["success" => false, "message" => "E-mail ou senha incorretos."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "E-mail ou senha incorretos."]);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erro de servidor: " . $e->getMessage()]);
}

$conn->close();
?>