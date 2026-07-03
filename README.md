# 🍲 Essência Mineira - Sistema de Restaurante & Delivery

O **Essência Mineira** é uma aplicação web completa para gerenciamento de pedidos e delivery de um restaurante especializado em culinária tradicional. Desenvolvido utilizando arquitetura monolítica com PHP e MySQL, o sistema simula toda a jornada do cliente, desde o cadastro e navegação no cardápio até o gerenciamento dinâmico do carrinho e a finalização da compra.

---

## 🚀 Funcionalidades Principais

* **Autenticação Segura:** Sistema de cadastro e login de usuários com senhas criptografadas via `password_hash` do PHP e controle de acesso baseado em sessões.
* **Cardápio Interativo:** Listagem dinâmica de produtos integrada diretamente com o banco de dados.
* **Carrinho de Compras Dinâmico (AJAX):** Permite adicionar, atualizar quantidades e remover itens do carrinho em tempo real sem atualizar a página.
* **Finalização de Pedido Inteligente:** Checkout integrado que coleta dados de entrega e aceita múltiplas formas de pagamento (Pix, Cartões e Dinheiro).
* **Painel Administrativo (Gerência):** Interface estilizada para administração do restaurante, histórico de pedidos e controle de estoque/cardápio.
* **Robustez no Banco de Dados:** Uso de transações SQL (`begin_transaction`, `commit`, `rollback`) para garantir a integridade dos dados na hora da compra e *Stored Procedures* para o cálculo automatizado de valores.

---

## 🛠️ Tecnologias Utilizadas

* **Frontend:** HTML5, CSS3 (com design moderno, efeitos de blur e responsividade) e JavaScript (Fetch API para requisições assíncronas).
* **Backend:** PHP (Orientado a Objetos e Prepared Statements para prevenção de SQL Injection).
* **Banco de Dados:** MySQL / MariaDB.

---

## 📊 Estrutura do Banco de Dados

O ecossistema do banco de dados está preparado para lidar com relacionamentos complexos, estruturado da seguinte forma:
* `Usuarios`: Armazena dados de clientes e administradores (Nome, CPF, Telefone, E-mail, Senha e Endereço).
* `Produtos`: Registro do cardápio do restaurante.
* `Pedidos`: Itens temporários adicionados ao carrinho de um usuário específico.
* `Carrinho`: Tabela utilitária para consolidação de valores ativos.
* `Compra`: Registro definitivo e histórico dos pedidos finalizados.

> 💡 **Diferencial Técnico:** O projeto utiliza a procedure `RecalcularCarrinho(id_user)` diretamente no banco de dados para garantir que os totais estejam sempre sincronizados com as ações do usuário na aplicação.

---

## 🔧 Como Executar o Projeto Localmente

### Pré-requisitos
* Servidor local Apache com suporte a PHP 7.4 ou superior (XAMPP, WAMP, Laragon).
* SGBD MySQL.

### Passo a Passo
1. **Clonar o repositório:**
   ```bash
   git clone [https://github.com/SEU_USUARIO/NOME_DO_REPOSITORIO.git](https://github.com/SEU_USUARIO/NOME_DO_REPOSITORIO.git)
