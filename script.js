// script.js
// O estado do carrinho é agora totalmente gerenciado pelo banco de dados (DB).

// =================================================================
// Variáveis e Constantes
// =================================================================

const WHATSAPP_EMPRESA_NUMERO = "5531994937236"; 
let carrinhoData = { itens: [], total: 0.00, total_quantidade: 0 }; 

// =================================================================
// FUNÇÕES DE COMUNICAÇÃO COM O SERVIDOR (PHP) - CARRINHO
// =================================================================

/**
 * Envia o produto selecionado para o arquivo PHP, que verifica se deve 
 * inserir ou atualizar a quantidade (Acúmulo de Produtos).
 */
async function adicionarAoBanco(produtoId, quantidade) {
    try {
        const response = await fetch('adicionar_ao_carrinho.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                produtoId: produtoId,
                quantidade: quantidade 
            })
        });

        const data = await response.json();

        if (data.success) {
            buscarECarregarCarrinho();
            console.log(data.message); 
        } else {
            alert('Erro ao adicionar ao carrinho: ' + data.message);
        }

    } catch (error) {
        console.error('Erro de rede ao adicionar item:', error);
        alert('Falha na comunicação com o servidor ao adicionar item.');
    }
}

/**
 * Envia uma requisição para manipular o item (adicionar 1, remover 1, ou remover totalmente).
 */
async function manipularCarrinho(idPedido, acao) {
    // acao pode ser 'aumentar', 'diminuir', ou 'remover'
    
    // Validação que previne o erro "Dados inválidos" se o ID não for passado
    if (!idPedido) { 
        console.error("ID do Pedido inválido ou ausente.");
        return;
    }
    
    try {
        const response = await fetch('manipular_carrinho.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                idPedido: idPedido, // Chave enviada para o PHP
                acao: acao
            })
        });

        const data = await response.json();

        if (data.success) {
            buscarECarregarCarrinho();
            console.log(data.message);
        } else {
            alert('Erro ao manipular item: ' + data.message);
        }
    } catch (error) {
        console.error('Erro de rede ao manipular item:', error);
        alert('Falha na comunicação com o servidor.');
    }
}


/**
 * Busca os dados atuais do carrinho no DB via PHP e atualiza a interface.
 */
async function buscarECarregarCarrinho() {
    try {
        const response = await fetch('buscar_carrinho.php');
        const data = await response.json(); 

        const listaItens = document.getElementById('cart-items');
        const totalDiv = document.getElementById('cart-total');
        const contadorCart = document.getElementById('cart-item-count'); 

        if (data.success === false || !data.carrinho) {
             console.error('Erro ao buscar carrinho:', data.message || 'Estrutura de dados inválida.');
             if(listaItens) listaItens.innerHTML = '<li class="empty-cart-message">Erro ao carregar o carrinho.</li>';
             if(totalDiv) totalDiv.textContent = 'Total: R$ 0,00';
             if (contadorCart) contadorCart.textContent = '0';
             carrinhoData = { itens: [], total: 0.00, total_quantidade: 0 }; 
             return;
        }

        carrinhoData = data.carrinho; 
        const carrinho = carrinhoData;
        
        // 1. Limpa e Popula a Lista de Itens
        if(listaItens) listaItens.innerHTML = '';
        if (listaItens && carrinho.itens.length === 0) { 
            listaItens.innerHTML = '<li class="empty-cart-message">Seu carrinho está vazio.</li>';
        } else if (listaItens) {
            carrinho.itens.forEach(item => {
                const li = document.createElement('li');
                
                // HTML CORRIGIDO: USANDO item.idPedido (camelCase)
                li.innerHTML = `
                    <div class="item-details">
                        <span class="item-name">${item.nomeProduto}</span>
                        <span class="item-price">R$ ${item.valorTotal.toFixed(2).replace('.', ',')}</span>
                    </div>
                    <div class="item-controls">
                        <button onclick="manipularCarrinho(${item.idPedido}, 'diminuir')">-</button>
                        <span class="item-quantity">${item.quantidade}</span>
                        <button onclick="manipularCarrinho(${item.idPedido}, 'aumentar')">+</button>
                        <button class="remove-button" onclick="manipularCarrinho(${item.idPedido}, 'remover')">X</button>
                    </div>
                `;
                listaItens.appendChild(li);
            });
        }

        // 2. Atualiza o Total e o Contador
        if(totalDiv) totalDiv.textContent = `Total: R$ ${carrinho.total.toFixed(2).replace('.', ',')}`; 
        
        if (contadorCart) {
            contadorCart.textContent = carrinho.total_quantidade > 9 ? '9+' : carrinho.total_quantidade;
            contadorCart.style.display = carrinho.total_quantidade > 0 ? 'inline-block' : 'none';
        }

        // 3. Habilita/Desabilita o botão Finalizar
        const finalizarBtn = document.getElementById('finalizarPedidoBtn'); 
        if (finalizarBtn) {
            finalizarBtn.disabled = carrinho.total_quantidade === 0;
            finalizarBtn.textContent = carrinho.total_quantidade === 0 ? 'Carrinho Vazio' : 'Finalizar Pedido';
        }

    } catch (error) {
        console.error('Erro de rede ao carregar carrinho:', error);
    }
}


// =================================================================
// FUNÇÕES DE INTERFACE (MODAL/CARRINHO E CATEGORIAS)
// =================================================================

function toggleCart(forceState = null) {
    const sidebar = document.getElementById('cart-sidebar');
    if (sidebar) {
        if (forceState !== null) {
            sidebar.classList.toggle('open', forceState);
        } else {
            sidebar.classList.toggle('open');
        }
    }
}

/**
 * Mostra a div de itens da categoria selecionada e esconde as outras.
 */
function mostrarCategoria(categoria) {
    // 1. Pega todas as divs de itens (todas as divs com a classe 'itens')
    const todasAsCategorias = document.querySelectorAll('.itens');

    // 2. Itera sobre elas
    todasAsCategorias.forEach(div => {
        // Pega o ID da div (que é o nome da categoria)
        const categoriaId = div.id; 

        // 3. Verifica se o ID da div corresponde à categoria clicada
        if (categoriaId === categoria) {
            // Se for a categoria correta, define o display para 'grid' (conforme seu CSS)
            div.style.display = 'grid'; 
        } else {
            // Se não for, esconde a div
            div.style.display = 'none';
        }
    });
}


/**
 * Busca os dados do usuário logado e preenche o formulário do modal.
 */
async function buscarEPreencherDadosUsuario() {
    try {
        // Você precisará criar o arquivo buscar_dados_usuario.php
        const response = await fetch('buscar_dados_usuario.php');
        const data = await response.json();

        if (data.success) {
            const userData = data.data;
            const form = document.getElementById('formFinalizarPedido');
            
            // Preenche os campos do modal
            if (form.querySelector('#nome')) form.querySelector('#nome').value = userData.nome || '';
            if (form.querySelector('#telefone')) form.querySelector('#telefone').value = userData.telefone || '';
            if (form.querySelector('#endereco')) form.querySelector('#endereco').value = userData.endereco || '';
        } else {
            console.error('Falha ao buscar dados do usuário:', data.message);
        }
    } catch (error) {
        console.error('Erro de rede ao buscar dados do usuário:', error);
    }
}


/**
 * Abre o modal de dados do cliente.
 */
function openDadosClienteModal() {
    toggleCart(false); // Esconde a sidebar
    const modal = document.getElementById('dadosClienteModal');
    if (modal) {
        if (carrinhoData.total_quantidade === 0) {
            alert("Seu carrinho está vazio. Adicione produtos antes de finalizar a compra.");
            return;
        }
        
        buscarEPreencherDadosUsuario(); 
        
        modal.style.display = 'flex';
    }
}

function closeDadosClienteModal() {
    const modal = document.getElementById('dadosClienteModal');
    if (modal) {
        modal.style.display = 'none';
        document.getElementById('formFinalizarPedido')?.reset(); 
    }
}


// =================================================================
// FUNÇÕES DE FINALIZAÇÃO DE COMPRA
// =================================================================

async function handleFinalizarPedido(event) {
    event.preventDefault(); 

    const form = document.getElementById('formFinalizarPedido'); 
    const confirmBtn = document.getElementById('confirmarFinalizarBtn');
    
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processando...';

    // 1. Coleta dos dados do formulário
    const nome = form.querySelector('#nome')?.value || '';
    const endereco = form.querySelector('#endereco')?.value || '';
    const telefone = form.querySelector('#telefone')?.value || '';
    const cpf = form.querySelector('#CPF')?.value || ''; // Adicionei o CPF (se estiver no seu formulário)
    const pagamento = form.querySelector('input[name="tipoPagamento"]:checked')?.value;
    
    // Validação básica
    if (!nome || !endereco || !telefone || !pagamento) {
        alert("Por favor, preencha todos os dados e escolha uma forma de pagamento.");
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirmar Pedido';
        return;
    }
    
    const carrinho = carrinhoData; 

    if (!carrinho || carrinho.itens.length === 0) {
        alert("O carrinho está vazio. Impossível finalizar.");
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirmar Pedido';
        return;
    }

    try {
        // 2. Chama o PHP para registrar a compra e LIMPAR o DB 
        // Você precisará de um arquivo finalizar_compra.php
        const responseDB = await fetch('finalizar_compra.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                nome: nome,
                endereco: endereco,
                telefone: telefone,
                cpf: cpf,
                pagamento: pagamento
            }) 
        });

        const dataDB = await responseDB.json();
        
        if (dataDB.success) {
            
            // 3. Monta a mensagem do WhatsApp 
            let mensagem = `*NOVO PEDIDO - ESSÊNCIA MINEIRA*\n\n`;
            mensagem += `*Dados do Cliente:*\n`;
            mensagem += `Nome: ${nome}\n`;
            mensagem += `Telefone: ${telefone}\n`;
            mensagem += `Endereço: ${endereco}\n`;
            mensagem += `Forma de Pagamento: ${pagamento}\n\n`;
            mensagem += `*Itens do Pedido:*\n`;
            
            carrinho.itens.forEach(item => {
                mensagem += `• ${item.quantidade}x ${item.nomeProduto} (R$ ${item.valorTotal.toFixed(2).replace('.', ',')})\n`;
            });
            
            mensagem += `\n*TOTAL GERAL:* R$ ${carrinho.total.toFixed(2).replace('.', ',')}\n\n`;
            mensagem += `Aguardando confirmação.`;
            
            const url = `https://api.whatsapp.com/send?phone=${WHATSAPP_EMPRESA_NUMERO}&text=${encodeURIComponent(mensagem)}`;
            
            // 4. Redireciona
            alert("Pedido confirmado no sistema! Você será redirecionado para o WhatsApp para envio.");
            window.open(url, '_blank');
            
            // 5. Limpa a interface após o envio
            closeDadosClienteModal();
            form.reset(); 
            buscarECarregarCarrinho(); 
            
        } else {
            alert('Erro ao processar a compra no servidor: ' + dataDB.message);
        }

    } catch (error) {
        console.error('Erro de rede ao finalizar compra:', error);
        alert('Falha na comunicação com o servidor ao finalizar a compra.');
    } finally {
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirmar Pedido';
    }
}


// =================================================================
// FUNÇÕES DE LOGIN E CADASTRO
// =================================================================

document.getElementById('cadastroForm')?.addEventListener('submit', function(event) {
    event.preventDefault(); 

    const msgDiv = document.getElementById('msg');
    
    // CAMPOS NOVOS E CORRIGIDOS
    const nome = document.getElementById('nome').value; 
    const telefone = document.getElementById('telefone').value; 
    
    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;
    const endereco = document.getElementById('endereco').value;
    const cpf = document.getElementById('cpf').value; 

    // Validação de telefone (apenas números, 10-11 dígitos)
    if (telefone && !/^\d{10,11}$/.test(telefone)) {
        msgDiv.textContent = "Telefone deve conter 10 ou 11 números (DDD incluso).";
        msgDiv.style.color = "red";
        return;
    }
    
    if (senha.length < 6) {
        msgDiv.textContent = "A senha deve ter no mínimo 6 caracteres.";
        msgDiv.style.color = "red";
        return;
    }

    msgDiv.textContent = "Processando...";
    msgDiv.style.color = "orange";

    fetch('cadastro.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            nome: nome,        
            telefone: telefone, 
            email: email, 
            senha: senha,
            endereco: endereco,
            cpf: cpf
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            msgDiv.textContent = data.message;
            msgDiv.style.color = "lightgreen";
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000); 
        } else {
            msgDiv.textContent = data.message;
            msgDiv.style.color = "red";
        }
    })
    .catch(error => {
        console.error('Erro de rede:', error);
        msgDiv.textContent = "Falha na comunicação com o servidor.";
        msgDiv.style.color = "red";
    });
});


document.getElementById('loginForm')?.addEventListener('submit', function(event) {
    event.preventDefault(); 

    const msgDiv = document.getElementById('msg_login'); 
    const email = document.getElementById('email_login').value; 
    const senha = document.getElementById('senha_login').value; 

    msgDiv.textContent = "Verificando credenciais...";
    msgDiv.style.color = "orange";

    fetch('login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            email: email, 
            senha: senha 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            msgDiv.textContent = data.message;
            msgDiv.style.color = "lightgreen";
            window.location.href = data.redirect;
        } else {
            msgDiv.textContent = data.message;
            msgDiv.style.color = "red";
        }
    })
    .catch(error => {
        console.error('Erro de rede:', error);
        msgDiv.textContent = "Falha na comunicação com o servidor.";
        msgDiv.style.color = "red";
    });
});

// =================================================================
// INICIALIZAÇÃO 
// =================================================================

document.addEventListener('DOMContentLoaded', () => {
    // Carrega o estado inicial do carrinho (se o elemento existir)
    if (document.getElementById('cart-sidebar')) {
        buscarECarregarCarrinho(); 
    }
    
    // Conecta o botão "Finalizar Pedido" do carrinho para abrir o modal
    const finalizarBtnCarrinho = document.getElementById('finalizarPedidoBtn'); 
    if (finalizarBtnCarrinho) {
        finalizarBtnCarrinho.addEventListener('click', openDadosClienteModal);
    }

    // Conecta o formulário do MODAL de finalização à função de envio
    const formFinalizar = document.getElementById('formFinalizarPedido');
    if (formFinalizar) {
        formFinalizar.addEventListener('submit', handleFinalizarPedido); 
    } 
    
    // Lógica para fechar o modal ao clicar fora dele
    const modal = document.getElementById('dadosClienteModal');
    if (modal) {
        window.onclick = function(event) {
            if (event.target === modal) {
                closeDadosClienteModal();
            }
        };
    }
    
    // NOVO: Exibe a primeira categoria por padrão
    // Isso garante que Massas esteja visível ao carregar o cardápio.php
    if (document.getElementById('Massas')) {
        mostrarCategoria('Massas'); 
    }
});