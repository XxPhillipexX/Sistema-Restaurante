<?php
// Arquivo: cardapio.php (AGORA COM PHP NO TOPO)

// IMPORTANTE: ESTE É O PRIMEIRO CÓDIGO DO ARQUIVO.
// SEM ESPAÇOS OU LINHAS EM BRANCO ANTES DESTE BLOCO.

// Inclui a conexão (que inicia a sessão de forma segura)
require_once 'conexao.php'; 

// 1. Proteção de Login: Redireciona se o usuário não estiver logado
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
    header("Location: login.html");
    exit;
}

// 2. Lógica de Busca de Produtos (Agora antes do HTML)
// Query: Busca produtos
// O $conn vem do conexao.php


?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio | Essência Mineira</title>
    <link href="https://fonts.googleapis.com/css2?family=Cabin:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8f8f8;
            background-image: url('Cardápio/fundo.jpeg'); /* Verifique se essa imagem está na pasta 'imagens' */
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            font-family: "Cabin", sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            background-image: url('Imagens Início/header.jpg');
            color: white;
            text-align: center;
            padding: 20px 0;
            position: relative; /* Essencial para posicionar os botões */
            z-index: 1000; /* Garante que o header fique em uma camada abaixo do carrinho aberto */
        }

        h1 {
            margin: 0;
        }

        /* Estilo para o novo botão "Voltar" */
        .back-to-home-button {
            position: absolute;
            top: 20px; /* Alinha com o padding do header */
            left: 20px; /* Afasta da borda esquerda */
            background-color: transparent;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            z-index: 1001; /* Fica acima da maioria dos elementos */
            transition: background-color 0.3s ease-in-out;
        }

        .back-to-home-button:hover {
            background-color: #ff8c00; /* Azul mais escuro no hover */
            color: #250e00;
        }

        .menu-categorias {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin: 20px;
        }

        /* Estilo para os botões de categoria */
        .menu-categorias button {
            background-color: #a0522d;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .menu-categorias button:hover {
            background-color: #ff7b00; /* Cor um pouco mais escura no hover */
            color: #250e00;
        }

        .itens {
            display: none; /* Inicia escondido, JavaScript vai mostrar */
            max-width: 1200px;
            margin: auto;
            display: grid; /* Usado para organizar os itens dentro da categoria */
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .item {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            display: flex; /* Para organizar o conteúdo e o botão "Adicionar" */
            flex-direction: column;
            justify-content: space-between; /* Empurra o botão para o final do item */
        }

        .item img {
            width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .item h3 {
            margin: 10px 0 5px;
        }

        .item p {
            color: #555;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Estilo para o texto pequeno adicionado */
        .item small {
            display: block; /* Garante que ocupe sua própria linha */
            color: #777; /* Uma cor um pouco mais clara */
            font-size: 0.9em; /* Tamanho ligeiramente menor */
            margin-bottom: 10px; /* Espaçamento abaixo do texto */
        }

        /* Botão "Adicionar ao Carrinho" em cada item */
        .add-to-cart-button {
            background-color: #28a745; /* Verde */
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: auto; /* Garante que o botão fique na parte inferior do item */
        }

        .add-to-cart-button:hover {
            background-color: #218838;
        }

        /* Botão do Carrinho no Header (Superior Direita) */
        .cart-toggle-button {
            position: absolute;
            top: 20px; /* Alinha com o padding do header */
            right: 20px; /* Afasta da borda direita */
            background-color: #4CAF50; /* Verde */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            z-index: 1001; /* Fica acima da maioria dos elementos, mas abaixo do carrinho aberto */
            transition: opacity 0.3s ease-in-out; /* Transição para suavizar o desaparecimento/aparecimento */
        }

        .cart-toggle-button:hover {
            background-color: #45a049;
        }

        /* Estilo para esconder o botão quando o carrinho está aberto */
        .cart-toggle-button.hidden-on-cart-open {
            opacity: 0; /* Torna invisível */
            pointer-events: none; /* Impede cliques quando invisível */
        }

        /* Carrinho Lateral */
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -300px; /* Esconde o carrinho inicialmente fora da tela */
            width: 300px;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.98); /* Fundo branco semi-transparente */
            box-shadow: -2px 0 10px rgba(0,0,0,0.2); /* Sombra na lateral */
            padding: 20px;
            box-sizing: border-box;
            transition: right 0.3s ease-in-out; /* Transição suave para abrir/fechar */
            z-index: 1002; /* Garante que sobreponha o botão do carrinho e outros elementos */
            overflow-y: auto; /* Permite rolagem se muitos itens */
        }

        .cart-sidebar.open {
            right: 0; /* Move o carrinho para a tela */
        }

        .cart-sidebar h2 {
            margin-top: 0;
            color: #ff8c00;
            border-bottom: 2px solid #ff8c00;
            padding-bottom: 10px;
        }

        .cart-item-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .cart-item-list li {
            display: flex;
            justify-content: space-between;
            align-items: center; /* Alinha verticalmente nome, preço e botão remover */
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
        }

        .cart-item-list li button { /* Este seletor aponta para o botão de remover dentro de cada item do carrinho */
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 3px 6px;
            cursor: pointer;
            margin-left: 10px;
        }

        .cart-item-list li:last-child {
            border-bottom: none; /* Remove a borda do último item */
        }

        .cart-item-name {
            flex-grow: 1; /* Ocupa o espaço disponível */
            margin-right: 10px;
        }

        .cart-item-price {
            font-weight: bold;
            color: #555;
            white-space: nowrap; /* Evita que o preço quebre a linha */
        }

        .cart-total {
            font-size: 1.2em;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #ff8c00;
            color: #ff8c00;
        }

        /* Botão para fechar o carrinho (o 'X') */
        .close-cart-button {
            background: none;
            border: none;
            font-size: 1.5em;
            position: absolute;
            top: 10px;
            right: 15px;
            cursor: pointer;
            color: #ff8c00;
        }

        /* Estilos do Modal de ML */
        .modal {
            display: none; /* Esconde o modal por padrão */
            position: fixed; /* Fica fixo na tela */
            z-index: 2000; /* Acima de tudo, incluindo o carrinho */
            left: 0;
            top: 0;
            width: 100%; /* Largura total */
            height: 100%; /* Altura total */
            overflow: auto; /* Adiciona rolagem se o conteúdo for muito grande */
            background-color: rgba(0,0,0,0.7); /* Fundo escurecido */
            justify-content: center; /* Centraliza horizontalmente */
            align-items: center; /* Centraliza verticalmente */
            backdrop-filter: blur(5px); /* Efeito de desfoque no fundo */
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto; /* Centraliza */
            padding: 30px;
            border-radius: 10px;
            width: 80%; /* Largura do conteúdo do modal */
            max-width: 500px; /* Limita a largura máxima */
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative; /* Para posicionar o botão de fechar */
            animation: fadeIn 0.3s ease-out; /* Animação de entrada */
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-content h2 {
            color: #ff8c00;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 15px;
            cursor: pointer;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Estilos para as opções de ML dentro do modal */
        .ml-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .ml-option.selected {
            background-color: #ffc4d8; /* Cor de destaque quando selecionado */
            color: white;
            border-color: #ff8c00;
            font-weight: bold;
            transform: scale(1.02);
        }

        .ml-option.selected .ml-price {
            color: white; /* Garante que o preço também fique branco */
        }

        .ml-size {
            font-size: 1.1em;
        }

        .ml-price {
            font-weight: bold;
            color: #ff8c00;
        }

        /* Botão "Escolher ML" nos itens (substituto do "Adicionar ao Carrinho" para cervejas/refrigerantes) */
        .select-ml-button {
            background-color: #007bff; /* Azul para diferenciar */
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: auto; /* Empurra para baixo como o outro botão */
        }

        .select-ml-button:hover {
            background-color: #0056b3;
        }

        /* Botão "Adicionar ao Carrinho" dentro do modal */
        .add-to-cart-modal-button {
            background-color: #28a745; /* Verde */
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            margin-top: 20px;
            width: 100%;
            transition: background-color 0.3s;
        }

        .add-to-cart-modal-button:hover {
            background-color: #218838;
        }
        /* --- NOVOS ESTILOS PARA FINALIZAR PEDIDO --- */
        .finalizar-pedido-button {
            background-color: #ff8c00;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            margin-top: 20px;
            width: 100%;
            transition: background-color 0.3s;
        }

        .finalizar-pedido-button:hover {
            background-color: #cc7000;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-group input[type="text"],
        .form-group input[type="tel"],
        .form-group textarea {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 1em;
        }

        .form-group input[type="radio"] {
            margin-right: 5px;
            vertical-align: middle;
        }

        .form-group label[for="pix"],
        .form-group label[for="cartaoCredito"],
        .form-group label[for="cartaoDebito"],
        .form-group label[for="dinheiro"] {
            display: inline-block;
            margin-right: 15px;
            font-weight: normal;
        }
        .finalizar-pedido-button {
            background-color: #ff8c00; /* Cor laranja */
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            margin-top: 20px; /* Espaçamento acima do botão */
            width: 100%;
            transition: background-color 0.3s;
        }

        .finalizar-pedido-button:hover {
            background-color: #cc7000; /* Laranja mais escuro ao passar o mouse */
        }

        /* Estilos para o formulário dentro do modal de finalizar pedido */
        .form-group {
            margin-bottom: 15px; /* Espaçamento entre os grupos de formulário */
        }

        .form-group label {
            display: block; /* Garante que o label fique em sua própria linha */
            margin-bottom: 5px; /* Espaçamento entre o label e o input/textarea */
            font-weight: bold;
            color: #555;
        }

        .form-group input[type="text"],
        .form-group input[type="tel"],
        .form-group textarea {
            width: calc(100% - 20px); /* Ocupa a largura total menos o padding */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* Garante que padding não aumente a largura total */
            font-size: 1em;
        }

        .form-group input[type="radio"] {
            margin-right: 5px; /* Espaçamento à direita do radio button */
            vertical-align: middle; /* Alinha o radio button com o texto */
        }

        /* Estilos específicos para os labels dos radio buttons para que fiquem na mesma linha */
        .form-group label[for="pix"],
        .form-group label[for="cartaoCredito"],
        .form-group label[for="cartaoDebito"],
        .form-group label[for="dinheiro"] {
            display: inline-block; /* Faz com que os labels fiquem lado a lado */
            margin-right: 15px; /* Espaçamento entre as opções de pagamento */
            font-weight: normal; /* Sobrescreve o bold do label geral */
        }

 /* ========================================= */
/* ESTILOS ESPECÍFICOS PARA O CARRINHO LATERAL */
/* ========================================= */

.cart-sidebar {
    /* ... (Mantenha o seu estilo original do cart-sidebar) ... */
    position: fixed;
    top: 0;
    right: 0;
    width: 300px; /* Ajuste conforme seu layout */
    height: 100%;
    background: #1c140d; /* Cor de fundo escura */
    color: #f5f2e9;
    box-shadow: -5px 0 15px rgba(0, 0, 0, 0.5);
    transform: translateX(100%);
    transition: transform 0.3s ease-in-out;
    z-index: 2000;
    padding: 20px;
    display: flex;
    flex-direction: column;
}

.cart-sidebar.open {
    transform: translateX(0);
}

.cart-item-list {
    list-style: none;
    padding: 0;
    margin: 20px 0;
    overflow-y: auto; /* Permite scroll se houver muitos itens */
}

.cart-item-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #443621;
    padding: 10px 0;
}

.item-details {
    flex-grow: 1;
}

.item-name {
    display: block;
    font-weight: bold;
}

.item-price {
    color: #c9a44b; /* Dourado */
    font-size: 0.9em;
}

.item-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Estilo para os botões de controle (+, -, X) */
.item-controls button {
    background: #c9a44b; /* Cor dourada */
    color: #2d1f0c; /* Texto escuro */
    border: none;
    border-radius: 4px;
    width: 24px;
    height: 24px;
    line-height: 24px;
    text-align: center;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
    transition: background 0.2s;
}

.item-controls button:hover {
    background: #e5c97c; /* Dourado mais claro no hover */
}

.item-quantity {
    min-width: 15px;
    text-align: center;
    font-size: 0.9em;
}

/* Estilo específico para o botão de remover (X) */
.item-controls .remove-button {
    background: #d9534f; /* Vermelho para destaque de remoção */
    color: white;
}

.item-controls .remove-button:hover {
    background: #c9302c;
}

.cart-total {
    margin-top: auto; /* Empurra o total e o botão para o final */
    padding-top: 15px;
    border-top: 2px solid #443621;
    font-size: 1.2em;
    font-weight: bold;
    color: #c9a44b;
}

/* Estilo do botão Finalizar Pedido no carrinho */
.finalizar-pedido-button {
    width: 100%;
    padding: 12px;
    background: #4bb883; /* Verde para finalização */
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 10px;
    transition: background 0.2s;
}

.finalizar-pedido-button:hover:enabled {
    background: #3c9d72;
}

.finalizar-pedido-button:disabled {
    background: #666;
    cursor: not-allowed;
}

.close-cart-button {
    background: none;
    color: #f5f2e9;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
    align-self: flex-end;
}
    </style>
</head>
<body>
    <header>
        <button class="back-to-home-button" onclick="window.open('restaurante.php', '_self')">
            ⇦
        </button>

        <h1>Cardápio</h1>
         <button class="cart-toggle-button" onclick="toggleCart()">
            Carrinho 
            <span id="cart-item-count" class="cart-badge">0</span> 
        </button>
    </header>

    <div class="menu-categorias">
        <button onclick="mostrarCategoria('Massas')">Massas e pratos</button>
        <button onclick="mostrarCategoria('Lanches')">Lanches e porções</button>
        <button onclick="mostrarCategoria('Caldos')">Caldos</button>
        <button onclick="mostrarCategoria('Doces')">Doces</button>
        <button onclick="mostrarCategoria('Cervejas')">Cervejas</button>
        <button onclick="mostrarCategoria('Vinhos')">Vinhos</button>
        <button onclick="mostrarCategoria('Refrigerantes')">Refrigerantes</button>
        <button onclick="mostrarCategoria('Sucos')">Sucos</button>
    </div>
    
    <div class="itens" id="Massas">
        <?php
        $sql = "SELECT Id_Produtos, Nome_Produto, Descricao, Valor_produto FROM Produtos wHERE id_categoria = 1"; 
        $resultado = $conn->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($produto = $resultado->fetch_assoc()) {
                $valorReais = number_format($produto['Valor_produto'] , 2, ',', '.');
                $id = $produto['Id_Produtos'];
                $nome = htmlspecialchars($produto['Nome_Produto']);
                $descricao = htmlspecialchars($produto['Descricao']);
        ?>
            <div class="item">
                <img src="Cardápio/<?php echo strtolower(str_replace(' ', '-', $nome)) . '.png'; ?>"> 
                <h3><?php echo $nome; ?></h3>
                <small><?php echo $descricao; ?></small>
                <p>R$ <?php echo $valorReais; ?></p>
                <button class="add-to-cart-button" 
                        onclick="adicionarAoBanco(<?php echo $id; ?>, 1)">
                    Adicionar ao Carrinho
                </button>
            </div>
        <?php
            } 
        } else {
            echo "<p>Nenhum item cadastrado nesta categoria no momento.</p>";
        }
        ?>
    </div>

    <div class="itens" id="Lanches">
        <?php
        $sql = "SELECT Id_Produtos, Nome_Produto, Descricao, Valor_produto FROM Produtos wHERE id_categoria = 2"; 
        $resultado = $conn->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($produto = $resultado->fetch_assoc()) {
                $valorReais = number_format($produto['Valor_produto'] , 2, ',', '.');
                $id = $produto['Id_Produtos'];
                $nome = htmlspecialchars($produto['Nome_Produto']);
                $descricao = htmlspecialchars($produto['Descricao']);
        ?>
            <div class="item">
                <img src="Cardápio/<?php echo strtolower(str_replace(' ', '-', $nome)) . '.png'; ?>"> 
                <h3><?php echo $nome; ?></h3>
                <small><?php echo $descricao; ?></small>
                <p>R$ <?php echo $valorReais; ?></p>
                <button class="add-to-cart-button" 
                        onclick="adicionarAoBanco(<?php echo $id; ?>, 1)">
                    Adicionar ao Carrinho
                </button>
            </div>
        <?php
            } 
        } else {
            echo "<p>Nenhum item cadastrado nesta categoria no momento.</p>";
        }
        ?>
    </div>

    <div class="itens" id="Caldos">
        <?php
        $sql = "SELECT Id_Produtos, Nome_Produto, Descricao, Valor_produto FROM Produtos wHERE id_categoria = 3"; 
        $resultado = $conn->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($produto = $resultado->fetch_assoc()) {
                $valorReais = number_format($produto['Valor_produto'] , 2, ',', '.');
                $id = $produto['Id_Produtos'];
                $nome = htmlspecialchars($produto['Nome_Produto']);
                $descricao = htmlspecialchars($produto['Descricao']);
        ?>
            <div class="item">
                <img src="Cardápio/<?php echo strtolower(str_replace(' ', '-', $nome)) . '.png'; ?>"> 
                <h3><?php echo $nome; ?></h3>
                <small><?php echo $descricao; ?></small>
                <p>R$ <?php echo $valorReais; ?></p>
                <button class="add-to-cart-button" 
                        onclick="adicionarAoBanco(<?php echo $id; ?>, 1)">
                    Adicionar ao Carrinho
                </button>
            </div>
        <?php
            } 
        } else {
            echo "<p>Nenhum item cadastrado nesta categoria no momento.</p>";
        }
        ?>
    </div>
    
    <div class="itens" id="Doces">
        <?php
        $sql = "SELECT Id_Produtos, Nome_Produto, Descricao, Valor_produto FROM Produtos wHERE id_categoria = 4"; 
        $resultado = $conn->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($produto = $resultado->fetch_assoc()) {
                $valorReais = number_format($produto['Valor_produto'] , 2, ',', '.');
                $id = $produto['Id_Produtos'];
                $nome = htmlspecialchars($produto['Nome_Produto']);
                $descricao = htmlspecialchars($produto['Descricao']);
        ?>
            <div class="item">
                <img src="Cardápio/<?php echo strtolower(str_replace(' ', '-', $nome)) . '.png'; ?>"> 
                <h3><?php echo $nome; ?></h3>
                <small><?php echo $descricao; ?></small>
                <p>R$ <?php echo $valorReais; ?></p>
                <button class="add-to-cart-button" 
                        onclick="adicionarAoBanco(<?php echo $id; ?>, 1)">
                    Adicionar ao Carrinho
                </button>
            </div>
        <?php
            } 
        } else {
            echo "<p>Nenhum item cadastrado nesta categoria no momento.</p>";
        }
        ?>
    </div>

    <div class="itens" id="Cervejas">
        <?php
        $sql = "SELECT Id_Produtos, Nome_Produto, Descricao, Valor_produto FROM Produtos wHERE id_categoria = 5"; 
        $resultado = $conn->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($produto = $resultado->fetch_assoc()) {
                $valorReais = number_format($produto['Valor_produto'] , 2, ',', '.');
                $id = $produto['Id_Produtos'];
                $nome = htmlspecialchars($produto['Nome_Produto']);
                $descricao = htmlspecialchars($produto['Descricao']);
        ?>
            <div class="item">
                <img src="Cardápio/<?php echo strtolower(str_replace(' ', '-', $nome)) . '.png'; ?>"> 
                <h3><?php echo $nome; ?></h3>
                <small><?php echo $descricao; ?></small>
                <p>R$ <?php echo $valorReais; ?></p>
                <button class="add-to-cart-button" 
                        onclick="adicionarAoBanco(<?php echo $id; ?>, 1)">
                    Adicionar ao Carrinho
                </button>
            </div>
        <?php
            } 
        } else {
            echo "<p>Nenhum item cadastrado nesta categoria no momento.</p>";
        }
        ?>
    </div>

    <div class="itens" id="Vinhos">
        <?php
        $sql = "SELECT Id_Produtos, Nome_Produto, Descricao, Valor_produto FROM Produtos wHERE id_categoria = 6"; 
        $resultado = $conn->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($produto = $resultado->fetch_assoc()) {
                $valorReais = number_format($produto['Valor_produto'] , 2, ',', '.');
                $id = $produto['Id_Produtos'];
                $nome = htmlspecialchars($produto['Nome_Produto']);
                $descricao = htmlspecialchars($produto['Descricao']);
        ?>
            <div class="item">
                <img src="Cardápio/<?php echo strtolower(str_replace(' ', '-', $nome)) . '.png'; ?>"> 
                <h3><?php echo $nome; ?></h3>
                <small><?php echo $descricao; ?></small>
                <p>R$ <?php echo $valorReais; ?></p>
                <button class="add-to-cart-button" 
                        onclick="adicionarAoBanco(<?php echo $id; ?>, 1)">
                    Adicionar ao Carrinho
                </button>
            </div>
        <?php
            } 
        } else {
            echo "<p>Nenhum item cadastrado nesta categoria no momento.</p>";
        }
        ?>
    </div>

    <div class="itens" id="Refrigerantes">
        <?php
        $sql = "SELECT Id_Produtos, Nome_Produto, Descricao, Valor_produto FROM Produtos wHERE id_categoria = 7"; 
        $resultado = $conn->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($produto = $resultado->fetch_assoc()) {
                $valorReais = number_format($produto['Valor_produto'] , 2, ',', '.');
                $id = $produto['Id_Produtos'];
                $nome = htmlspecialchars($produto['Nome_Produto']);
                $descricao = htmlspecialchars($produto['Descricao']);
        ?>
            <div class="item">
                <img src="Cardápio/<?php echo strtolower(str_replace(' ', '-', $nome)) . '.png'; ?>"> 
                <h3><?php echo $nome; ?></h3>
                <small><?php echo $descricao; ?></small>
                <p>R$ <?php echo $valorReais; ?></p>
                <button class="add-to-cart-button" 
                        onclick="adicionarAoBanco(<?php echo $id; ?>, 1)">
                    Adicionar ao Carrinho
                </button>
            </div>
        <?php
            } 
        } else {
            echo "<p>Nenhum item cadastrado nesta categoria no momento.</p>";
        }
        ?>
    </div>

    <div class="itens" id="Sucos">
        <?php
        $sql = "SELECT Id_Produtos, Nome_Produto, Descricao, Valor_produto FROM Produtos wHERE id_categoria = 8"; 
        $resultado = $conn->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($produto = $resultado->fetch_assoc()) {
                $valorReais = number_format($produto['Valor_produto'] , 2, ',', '.');
                $id = $produto['Id_Produtos'];
                $nome = htmlspecialchars($produto['Nome_Produto']);
                $descricao = htmlspecialchars($produto['Descricao']);
        ?>
            <div class="item">
                <img src="Cardápio/<?php echo strtolower(str_replace(' ', '-', $nome)) . '.png'; ?>"> 
                <h3><?php echo $nome; ?></h3>
                <small><?php echo $descricao; ?></small>
                <p>R$ <?php echo $valorReais; ?></p>
                <button class="add-to-cart-button" 
                        onclick="adicionarAoBanco(<?php echo $id; ?>, 1)">
                    Adicionar ao Carrinho
                </button>
            </div>
        <?php
            } 
        } else {
            echo "<p>Nenhum item cadastrado nesta categoria no momento.</p>";
        }
        ?>
    </div>

    <div id="mlSelectionModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeMlSelection()">&times;</span>
            <h2 id="modalProductName"></h2>
            <div id="mlOptionsContainer">
                </div>
            <button class="add-to-cart-modal-button" id="addToCartModalButton" onclick="addSelectedMlToCart()">Adicionar ao Carrinho</button>
        </div>
    </div>
    <div id="dadosClienteModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeDadosClienteModal()">&times;</span>
            <h2>Finalizar Pedido</h2>
            <form id="formFinalizarPedido">
                <div class="form-group">
                    <label for="nome">Nome Completo:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="form-group">
                    <label for="endereco">Endereço (Rua, Número, Bairro, Cidade):</label>
                    <textarea id="endereco" name="endereco" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone (DDD + Número) - Cliente:</label>
                    <input type="tel" id="telefone" name="telefone" pattern="[0-9]{10,11}" placeholder="Ex: 31991234567" required>
                </div>

                <div class="form-group">
                    <label>Forma de Pagamento:</label><br>
                    <input type="radio" id="pix" name="tipoPagamento" value="Pix" required>
                    <label for="pix">Pix</label><br>
                    <input type="radio" id="cartaoCredito" name="tipoPagamento" value="Cartão de Crédito">
                    <label for="cartaoCredito">Cartão de Crédito</label><br>
                    <input type="radio" id="cartaoDebito" name="tipoPagamento" value="Cartão de Débito">
                    <label for="cartaoDebito">Cartão de Débito</label><br>
                    <input type="radio" id="dinheiro" name="tipoPagamento" value="Dinheiro">
                    <label for="dinheiro">Dinheiro</label><br>
                </div>

                <button type="submit" id="confirmarFinalizarBtn" class="add-to-cart-modal-button">Confirmar Pedido</button>
            </form>
        </div>
    </div>
    <div class="cart-sidebar" id="cart-sidebar">
        <button class="close-cart-button" onclick="toggleCart()">X</button>
        <h2>Seu Carrinho</h2>
        <ul class="cart-item-list" id="cart-items">
            </ul>
        <div class="cart-total" id="cart-total">
            Total: R$ 0,00
        </div>
        
        <button id="finalizarPedidoBtn" class="finalizar-pedido-button">Finalizar Pedido</button>
    </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>