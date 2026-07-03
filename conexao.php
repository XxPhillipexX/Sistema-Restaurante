<?php
// Arquivo: conexao.php (COM SUPORTE A SESSÃO)

// Tenta iniciar a sessão APENAS SE ELA JÁ NÃO ESTIVER ATIVA.
// É essencial que não haja NENHUMA linha ou espaço antes de '<?php'
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ATENÇÃO: Substitua as credenciais abaixo pelas suas
$servidor = "localhost"; 
$usuario = "root";     
$senha = "";           
$banco = "Restaurante";

// Pega o ID do usuário da sessão, ou usa 0 se não estiver logado.
$idUserLogado = $_SESSION['user_id'] ?? 0; 
$idUserTeste = $idUserLogado; 

// Tenta estabelecer a conexão
$conn = new mysqli($servidor, $usuario, $senha, $banco);

// Verifica se a conexão falhou
if ($conn->connect_error) {
    // Retorna um erro JSON se a requisição for AJAX
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
         die(json_encode(["success" => false, "message" => "Erro de Conexão com o DB: " . $conn->connect_error]));
    } else {
         die("Erro de Conexão com o DB: " . $conn->connect_error);
    }
}

$conn->set_charset("utf8");

// IMPORTANTE: Deixe SEM o ponto de interrogação final (?)
