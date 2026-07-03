CREATE DATABASE IF NOT EXISTS Restaurante;
USE Restaurante;

CREATE TABLE IF NOT EXISTS Usuarios (
    Id_User INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    endereco VARCHAR(500) NOT NULL,
    CPF VARCHAR(11) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    tipo ENUM('Admin','Cliente') DEFAULT 'Cliente'
);

CREATE TABLE IF NOT EXISTS Categorias (
    Id_Categoria INT AUTO_INCREMENT PRIMARY KEY,
    Nome_Categoria VARCHAR(30) NOT NULL
);

-- CORRIGIDO: Valor_Total agora é DECIMAL
CREATE TABLE IF NOT EXISTS Compra (
    Id_Compra INT AUTO_INCREMENT PRIMARY KEY,
    Data_Compra DATE NOT NULL,
    Valor_Total DECIMAL(10, 2) NOT NULL, -- <--- CORREÇÃO
    Id_User INT NOT NULL,
    Nome_Cliente VARCHAR(100) NOT NULL,
    Endereco_Cliente VARCHAR(500) NOT NULL,
    Telefone_Cliente VARCHAR(15) NOT NULL,
    CPF_Cliente VARCHAR(11) NULL,
    Forma_Pagamento VARCHAR(50) NOT NULL,
    FOREIGN KEY (Id_User) REFERENCES Usuarios(Id_User)
);

-- CORRIGIDO: Valor_produto agora é DECIMAL
CREATE TABLE IF NOT EXISTS Produtos (
    Id_Produtos INT AUTO_INCREMENT PRIMARY KEY,
    Valor_produto DECIMAL(10, 2) NOT NULL, -- <--- CORREÇÃO
    Nome_Produto VARCHAR(50) NOT NULL,
    Descricao VARCHAR(200) NOT NULL,
    Id_Categoria INT NOT NULL,
    FOREIGN KEY (Id_Categoria) REFERENCES Categorias(Id_Categoria)
);

-- CORRIGIDO: Valor_Pedido agora é DECIMAL
CREATE TABLE IF NOT EXISTS Pedidos(
    Id_Pedidos INT AUTO_INCREMENT PRIMARY KEY,
    Quantidade INT NOT NULL,
    Valor_Pedido DECIMAL(10, 2) NOT NULL, -- <--- CORREÇÃO
    Data_pedido DATE NOT NULL,
    Id_Produtos INT NOT NULL,
    Id_User INT NOT NULL,
    Id_Compra INT NULL,
    FOREIGN KEY (Id_User) REFERENCES Usuarios(Id_User),
    FOREIGN KEY (Id_Compra) REFERENCES Compra(Id_Compra),
    FOREIGN KEY (Id_Produtos) REFERENCES Produtos(Id_Produtos)
);

-- CORRIGIDO: Valor_Carrinho agora é DECIMAL
CREATE TABLE IF NOT EXISTS Carrinho (
    Id_Carrinho INT AUTO_INCREMENT PRIMARY KEY,
    Valor_Carrinho DECIMAL(10, 2) DEFAULT 0, -- <--- CORREÇÃO
    Id_User INT NOT NULL,
    Data_Carrinho DATE NOT NULL,
    FOREIGN KEY (Id_User) REFERENCES Usuarios(Id_User)
);

-- #################### TRIGGERS ####################

DELIMITER $$

-- Corrigido: DECLARE agora usa DECIMAL
CREATE TRIGGER calcula_valor_pedido
BEFORE INSERT ON Pedidos
FOR EACH ROW
BEGIN
    DECLARE preco DECIMAL(10,2); -- <--- CORREÇÃO

    SELECT Valor_produto INTO preco
    FROM Produtos
    WHERE Id_Produtos = NEW.Id_Produtos;

    SET NEW.Valor_Pedido = NEW.Quantidade * preco;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER criar_atualizar_carrinho
AFTER INSERT ON Pedidos
FOR EACH ROW
BEGIN
    DECLARE carrinho_id INT;

    SELECT Id_Carrinho INTO carrinho_id
    FROM Carrinho
    WHERE Id_User = NEW.Id_User
    LIMIT 1;

    IF carrinho_id IS NULL THEN
        -- O Valor_Pedido já está correto (DECIMAL)
        INSERT INTO Carrinho (Id_User, Data_Carrinho, Valor_Carrinho)
        VALUES (NEW.Id_User, NEW.Data_pedido, NEW.Valor_Pedido);
    ELSE
        -- O Valor_Carrinho e Valor_Pedido são DECIMAL, a soma será correta
        UPDATE Carrinho
        SET Valor_Carrinho = Valor_Carrinho + NEW.Valor_Pedido
        WHERE Id_Carrinho = carrinho_id;
    END IF;
END$$

DELIMITER ;

DELIMITER $$

-- Corrigido: DECLARE agora usa DECIMAL
CREATE TRIGGER recalcula_valor_pedido_update
BEFORE UPDATE ON Pedidos
FOR EACH ROW
BEGIN
    DECLARE preco DECIMAL(10,2); -- <--- CORREÇÃO
    
    IF NEW.Quantidade <> OLD.Quantidade THEN
        
        SELECT Valor_produto INTO preco
        FROM Produtos
        WHERE Id_Produtos = NEW.Id_Produtos;
        
        SET NEW.Valor_Pedido = NEW.Quantidade * preco;
    END IF;
END$$

DELIMITER ;


-- #################### STORED PROCEDURES ####################

DELIMITER $$

-- SP RecalcularCarrinho: FILTRO Id_Compra IS NULL e DECLARE DECIMAL
DROP PROCEDURE IF EXISTS RecalcularCarrinho$$

CREATE PROCEDURE RecalcularCarrinho(IN p_id_user INT)
BEGIN
    DECLARE novo_valor DECIMAL(10,2) DEFAULT 0; -- <--- CORREÇÃO

    -- Calcula a soma total dos pedidos *ativos* (Id_Compra IS NULL)
    SELECT COALESCE(SUM(p.Valor_Pedido), 0) INTO novo_valor
    FROM Pedidos p
    WHERE p.Id_User = p_id_user AND p.Id_Compra IS NULL; 

    -- Atualiza a tabela Carrinho
    UPDATE Carrinho
    SET Valor_Carrinho = novo_valor,
        Data_Carrinho = CURDATE()
    WHERE Id_User = p_id_user;
    
    -- Insere o carrinho se ele não existir
    IF ROW_COUNT() = 0 THEN
         INSERT INTO Carrinho (Id_User, Valor_Carrinho, Data_Carrinho)
         VALUES (p_id_user, novo_valor, CURDATE());
    END IF;
END$$

DELIMITER ;


-- #################### DADOS DE INSERÇÃO ####################

INSERT INTO Categorias (Id_Categoria, Nome_Categoria) VALUES

(1, 'Massas e Pratos'),
(2, 'Lanches e Porções'),
(3, 'Caldos'),
(4, 'Doces'),
(5, 'Cervejas'),
(6, 'Vinhos'),
(7, 'Refrigerantes'),
(8, 'Sucos');

-- Os valores de inserção estão em formato DECIMAL (correto)
INSERT INTO produtos (Valor_produto, Nome_Produto, Descricao, Id_Categoria) VALUES

-- MASSAS E PRATOS PRINCIPAIS
(56.00, 'Carbonara', 'Serve 3 Pessoas\nDe R$ 70,00 por R$ 56,00', 1),
(56.00, 'Macarrão a Bolonhesa', 'Serve 3 Pessoas\nDe R$ 70,00 por R$ 56,00', 1),
(48.00, 'Macarronada', 'Serve 3 Pessoas\nDe R$ 60,00 por R$ 48,00', 1),
(60.00, 'Feijoada Mineira', 'Serve 3 Pessoas\nDe R$ 70,00 por $60,00', 1),
(45.00, 'Galinhada', 'Serve 3 Pessoas\nDe R$ 55,00 por R$ 45,00', 1),
(45.00, 'Frango com Quiabo', 'Serve 3 Pessoas\nDe R$ 55,00 por R$ 45,00', 1),
(45.00, 'Feijão Tropeiro', 'Serve 3 Pessoas\nDe R$ 55,00 por R$ 45,00', 1),
(40.00, 'Arroz com Pequi', 'Serve 3 Pessoas\nDe R$ 50,00 por R$ 40,00', 1),
(40.00, 'Tutu de Feijão', 'Serve 3 Pessoas\nDe R$ 50,00 por R$ 40,00', 1),
(45.00, 'Vaca Atolada', 'Serve 3 Pessoas\nDe R$ 55,00 por R$ 45,00', 1),
(40.00, 'Frango ao Molho Pardo', 'Serve 3 Pessoas\nDe R$ 50,00 por $ 40,00', 1),

-- LANCHES E PORÇÕES
(15.00, 'Pão de Queijo com Linguiça', 'R$ 15,00', 2),
(15.00, 'Pão com Linguiça', 'R$ 15,00', 2),
(15.00, 'Pão com Carne de Boi Desfiada', 'R$ 15,00', 2),
(15.00, 'Pão com carne de frango desfiada', 'R$ 15,00', 2),

(20.00, 'Torresmo', 'Serve 3 Pessoas\nDe R$ 30,00 por R$ 20,00', 2),
(30.00, 'Porção de Mandioca Frita com Carne', 'Serve 3 Pessoas\nDe R$ 40,00 por R$ 30,00', 2),
(30.00, 'Porção de Batata com Queijo e Bacon', 'Serve 3 Pessoas\nDe R$ 40,00 por R$ 30,00', 2),
(40.00, 'Porção de Tilápia Frita', 'Serve 3 Pessoas\nDe R$ 50,00 por R$ 40,00', 2),
(65.00, 'Porção de Picanha', 'Serve 3 Pessoas\nDe R$ 85,00 por R$ 65,00', 2),

-- CALDOS
(12.00, 'Caldo de Mandioca', 'R$ 12,00', 3),
(12.00, 'Caldo de Feijão', 'R$ 12,00', 3),
(12.00, 'Caldo de Abóbora', 'R$ 12,00', 3),
(15.00, 'Canjiquinha', 'R$ 15,00', 3),
(15.00, 'Caldo de Mocotó', 'R$ 15,00', 3),

-- DOCES
(12.00, 'Doce de Abóbora', 'R$ 12,00', 4),
(14.00, 'Goiabada Cremosa', 'R$ 14,00', 4),
(14.00, 'Goiabada Cascão', 'R$ 14,00', 4),
(10.00, 'Pé de Moleque', 'R$ 10,00', 4),
(16.00, 'Rocambole', 'R$ 16,00', 4),
(15.00, 'Canudinho de Doce de Leite', 'R$ 15,00', 4),
(25.00, 'Petit Gateau', 'R$ 25,00', 4),

-- BEBIDAS — CERVEJAS COM ML (NOVAS)
-- Tamanhos usados: 269ml=6,00 | 350ml=8,00 | 330ml=10,00 | 600ml=14,00

-- HEINEKEN
(6.00, 'Heineken 269ml', 'Cerveja Heineken 269 ml', 5),
(8.00, 'Heineken 350ml', 'Cerveja Heineken 350 ml', 5),
(10.00, 'Heineken Long Neck 330ml', 'Cerveja Heineken 330 ml Long Neck', 5),
(14.00, 'Heineken 600ml', 'Cerveja Heineken 600 ml', 5),

-- ORIGINAL
(6.00, 'Original 269ml', 'Cerveja Original 269 ml', 5),
(8.00, 'Original 350ml', 'Cerveja Original 350 ml', 5),
(10.00, 'Original Long Neck 330ml', 'Cerveja Original 330 ml Long Neck', 5),

-- BOHEMIA
(6.00, 'Bohemia 269ml', 'Cerveja Bohemia 269 ml', 5),
(8.00, 'Bohemia 350ml', 'Cerveja Bohemia 350 ml', 5),
(10.00, 'Bohemia Long Neck 330ml', 'Cerveja Bohemia 330 ml Long Neck', 5),
(14.00, 'Bohemia 600ml', 'Cerveja Bohemia 600 ml', 5),

-- KAISER
(6.00, 'Kaiser 269ml', 'Cerveja Kaiser 269 ml', 5),
(8.00, 'Kaiser 350ml', 'Cerveja Kaiser 350 ml', 5),
(14.00, 'Kaiser 600ml', 'Cerveja Kaiser 600 ml', 5),

-- AMSTEL
(6.00, 'Amstel 269ml', 'Cerveja Amstel 269 ml', 5),
(8.00, 'Amstel 350ml', 'Cerveja Amstel 350 ml', 5),
(10.00, 'Amstel Long Neck 330ml', 'Cerveja Amstel 330 ml Long Neck', 5),
(14.00, 'Amstel 600ml', 'Cerveja Amstel 600 ml', 5),

-- CORONA
(6.00, 'Corona 269ml', 'Cerveja Corona 269 ml', 5),
(8.00, 'Corona 350ml', 'Cerveja Corona 350 ml', 5),
(10.00, 'Corona Long Neck 330ml', 'Cerveja Corona 330 ml Long Neck', 5),

-- BRAHMA
(6.00, 'Brahma 269ml', 'Cerveja Brahma 269 ml', 5),
(8.00, 'Brahma 350ml', 'Cerveja Brahma 350 ml', 5),
(10.00, 'Brahma Long Neck 330ml', 'Cerveja Brahma 330 ml Long Neck', 5),
(14.00, 'Brahma 600ml', 'Cerveja Brahma 600 ml', 5),

-- STELLA ARTOIS
(6.00, 'Stella Artois 269ml', 'Cerveja Stella Artois 269 ml', 5),
(8.00, 'Stella Artois 350ml', 'Cerveja Stella Artois 350 ml', 5),
(10.00, 'Stella Artois Long Neck 330ml', 'Cerveja Stella Artois 330 ml Long Neck', 5),
(14.00, 'Stella Artois 600ml', 'Cerveja Stella Artois 600 ml', 5),

-- EISENBAHN
(6.00, 'Eisenbahn 269ml', 'Cerveja Eisenbahn 269 ml', 5),
(10.00, 'Eisenbahn Long Neck 330ml', 'Cerveja Eisenbahn 330 ml Long Neck', 5),
(14.00, 'Eisenbahn 600ml', 'Cerveja Eisenbahn 600 ml', 5),

-- ARTESANAL
(18.00, 'Cerveja Artesanal 600ml', 'Cerveja Artesanal 600 ml', 5),

-- VINHOS
(35.00, 'Gato Negro', 'De R$ 50,00 por R$ 35,00', 6),
(28.00, 'Pergola', 'De R$ 40,00 por R$ 28,00', 6),
(49.00, 'Casillero del Diablo', 'De R$ 70,00 por R$ 49,00', 6),
(42.00, 'Putos', 'De R$ 60,00 por R$ 42,00', 6),
(49.00, 'Miolo', 'De R$ 70,00 por R$ 49,00', 6),
(28.00, 'Country Wine', 'De R$ 40,00 por R$ 28,00', 6),
(56.00, 'Trivento', 'De R$ 80,00 por R$ 56,00', 6),
(42.00, 'Reservado', 'De R$ 60,00 por R$ 42,00', 6),

-- REFRIGERANTES
(6.00, 'Fanta Lata 350ml', 'Refrigerante Fanta 350ml', 7),
(6.00, 'Coca-cola Lata 350ml', 'Refrigerante Coca-cola 350ml', 7),
(6.00, 'Pepsi Lata 350ml', 'Refrigerante Pepsi 350ml', 7),
(6.00, 'Guaraná Antártica Lata 350ml', 'Refrigerante Guaraná Antártica 350ml', 7),
(6.00, 'Sprite Lata 350ml', 'Refrigerante Sprite 350ml', 7),

-- SUCOS
(30.00, 'Suco de Laranja (1L)', 'R$ 30,00', 8),
(30.00, 'Suco de Uva (1L)', 'R$ 30,00', 8),
(30.00, 'Suco de Abacaxi (1L)', 'R$ 30,00', 8),
(30.00, 'Suco de Limão (1L)', 'R$ 30,00', 8);