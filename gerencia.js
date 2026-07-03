// =======================================================
// gerencia.js - Painel de Gerência Integrado (Final)
// =======================================================

// === 1. CONFIGURAÇÃO E ESTADO DA APLICAÇÃO ===

// **IMPORTANTE: AJUSTE ESTAS DUAS URLS!**
const API_BASE_URL = 'http://localhost/Restaurante%20EM/api'; 
const IMAGE_FOLDER_PATH = '/Restaurante%20EM/Cardápio/'; 

const FALLBACK_IMAGE = 'https://via.placeholder.com/600x600?text=Sem+Foto';

let categorias = []; 
let cardapio = [];   
let categoriaAtual = ''; 


// === FUNÇÃO DE UTILIADE PARA IMAGEM (AGORA MANTÉM ACENTOS E USA HÍFEN) ===

function gerarUrlImagem(nome) {
    if (!nome) return FALLBACK_IMAGE;

    let nomeFinal = nome.toLowerCase();
    nomeFinal = nomeFinal.replace(/ /g, '-'); 
    
    const urlBase = window.location.origin;
    return `${urlBase}${IMAGE_FOLDER_PATH}${nomeFinal}.png`;
}


// === NOVO: FUNÇÃO DE UTILIDADE PARA FORMATAR PREÇO (CORREÇÃO DE EXIBIÇÃO) ===
function formatarPrecoBR(valor) {
    const valorNumerico = parseFloat(valor);
    if (isNaN(valorNumerico)) {
        return 'R$ 0,00';
    }
    return valorNumerico.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });
}


// === 2. FUNÇÕES DE COMUNICAÇÃO COM A API (Ponte para o PHP) ===

async function buscarCategorias() {
    try {
        const response = await fetch(`${API_BASE_URL}/categorias_adm.php`); 
        if (!response.ok) throw new Error('Erro ao buscar categorias do backend');
        
        const data = await response.json();
        return data.map(cat => cat.Nome_Categoria);
    } catch (error) {
        console.error('Erro na API ao buscar categorias:', error);
        return [];
    }
}

async function buscarCardapio() {
    try {
        const response = await fetch(`${API_BASE_URL}/produtos_adm.php`);
        if (!response.ok) throw new Error('Erro ao buscar produtos do backend');

        const data = await response.json();
        
        return data.map(item => ({
            id: item.Id_Produtos, 
            nome: item.Nome_Produto,
            preco: parseFloat(item.Valor_produto).toFixed(2), 
            vendas: parseInt(item.Total_Vendas) || 0, 
            imagemUrl: gerarUrlImagem(item.Nome_Produto),
            descricaoTexto: item.Descricao, 
            categoria: item.Nome_Categoria 
        }));
    } catch (error) {
        console.error('Erro na API ao buscar cardápio:', error);
        return [];
    }
}

async function buscarHistoricoVendas() {
    try {
        const response = await fetch(`${API_BASE_URL}/vendas_adm.php`);
        if (!response.ok) throw new Error('Erro ao buscar histórico de vendas do backend');
        
        const data = await response.json();
        return data; 
    } catch (error) {
        console.error('Erro na API ao buscar histórico:', error);
        return { maisVendidos: [], menosVendidos: [] };
    }
}


// === 3. FUNÇÕES DE RENDERIZAÇÃO E UTILS ===

function popularSelectsCategoria() {
    const selects = document.querySelectorAll('select#selectCategoria, select#filtroCategoria, select#selectCategoriaAdicionar');
    
    selects.forEach(select => {
        if (!select) return;
        select.innerHTML = '';
        categorias.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat;
            option.textContent = cat;
            select.appendChild(option);
        });
    });
}

function renderizarSelectDeItens() {
    const select = document.getElementById('selectItemAlterar');
    const categoriaEl = document.getElementById('selectCategoria');
    
    if (!select || !categoriaEl) return;
    
    const categoriaSelecionada = categoriaEl.value;
    select.innerHTML = '<option value="">Selecione um item...</option>';
    
    const itensFiltrados = cardapio.filter(item => item.categoria === categoriaSelecionada);

    itensFiltrados.forEach(item => {
        const option = document.createElement('option');
        option.value = item.nome; 
        option.textContent = item.nome;
        select.appendChild(option);
    });

    const inputPreco = document.getElementById('inputAlterarPreco');
    const inputDesc = document.getElementById('inputAlterarDescricao');
    if (inputPreco) inputPreco.value = '';
    if (inputDesc) inputDesc.value = '';
}

function preencherCamposAlteracao() {
    const selectItem = document.getElementById('selectItemAlterar');
    if (!selectItem) return;

    const nomeSelecionado = selectItem.value;
    const item = cardapio.find(i => i.nome === nomeSelecionado);

    const inputPreco = document.getElementById('inputAlterarPreco');
    const inputDesc = document.getElementById('inputAlterarDescricao');

    if (item) {
        if (inputPreco) inputPreco.value = item.preco.replace('.', ','); 
        if (inputDesc) inputDesc.value = item.descricaoTexto; 
    } else {
        if (inputPreco) inputPreco.value = '';
        if (inputDesc) inputDesc.value = '';
    }
}

async function renderizarHistorico() {
    const historico = await buscarHistoricoVendas(); 
    const maisVendidos = historico.maisVendidos;
    const menosVendidos = historico.menosVendidos;

    const formatarLista = (arr) => 
        arr.map(item => 
            `<li>${item.Nome_Produto} (${item.Nome_Categoria}) - **${item.Total_Vendas} vendas**</li>`
        ).join('');

    const elMais = document.getElementById('maisVendidos');
    const elMenos = document.getElementById('menosVendidos');
    if (elMais) elMais.innerHTML = formatarLista(maisVendidos);
    if (elMenos) elMenos.innerHTML = formatarLista(menosVendidos);
}

function renderizarCardapio() {
    const tabelaCorpo = document.getElementById('tabelaCorpo');
    const filtroEl = document.getElementById('filtroCategoria');
    if (!tabelaCorpo || !filtroEl) return;

    const filtro = filtroEl.value;
    tabelaCorpo.innerHTML = ''; 

    const dadosFiltrados = filtro === 'Todos' 
        ? cardapio 
        : cardapio.filter(item => item.categoria === filtro);

    dadosFiltrados.forEach(item => {
        const row = tabelaCorpo.insertRow();
        row.insertCell(0).textContent = item.categoria;
        row.insertCell(1).textContent = item.nome;
        row.insertCell(2).textContent = formatarPrecoBR(item.preco); 
        row.insertCell(3).textContent = item.vendas;
        
        const cellImg = row.insertCell(4);
        const img = document.createElement('img');
        img.src = item.imagemUrl;
        img.alt = item.nome;
        img.classList.add('tabela-imagem');
        cellImg.appendChild(img);
    });
}


// === 4. FUNÇÕES DE AÇÃO (CRUD VIA API) ===

async function handleAdicionarItem(event) {
    event.preventDefault(); 
    const form = document.getElementById('formAdicionarItem');
    if (!form) return;

    const nome = form.querySelector('#inputAdicionarNome').value.trim();
    const precoStr = form.querySelector('#inputAdicionarPreco').value.replace(',', '.'); 
    const descricao = form.querySelector('#inputAdicionarDescricao').value.trim(); 
    const categoria = form.querySelector('#selectCategoriaAdicionar').value;

    const preco = parseFloat(precoStr);

    if (!nome || isNaN(preco) || preco <= 0 || !categoria) {
        alert("Por favor, preencha o Nome, Preço (válido) e selecione a Categoria.");
        return;
    }

    const novoItem = { 
        Nome_Produto: nome, 
        Valor_produto: preco, 
        Descricao: descricao, 
        Nome_Categoria: categoria 
    };

    try {
        const response = await fetch(`${API_BASE_URL}/produtos_adm.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(novoItem)
        });

        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Erro ao adicionar.');
        
        await recarregarDados(); 
        form.reset();
        alert(`"${nome}" adicionado com sucesso!`);
    } catch (error) {
        alert(`Erro: ${error.message}`);
    }
}

async function handleAlterarItem(event) {
    event.preventDefault();
    
    const categoriaEl = document.getElementById('selectCategoria');
    const itemSelectEl = document.getElementById('selectItemAlterar');
    
    if (!categoriaEl || !itemSelectEl) return;

    const categoriaSelecionada = categoriaEl.value;
    const nomeSelecionado = itemSelectEl.value;

    if (!nomeSelecionado) {
        alert("Selecione um item para alterar.");
        return;
    }

    const item = cardapio.find(i => i.nome === nomeSelecionado && i.categoria === categoriaSelecionada);
    if (!item || !item.id) {
         alert("Item não encontrado.");
         return;
    }

    const inputPreco = document.getElementById('inputAlterarPreco');
    const inputDesc = document.getElementById('inputAlterarDescricao');

    if (!inputPreco || !inputDesc) return;

    const novoPrecoRaw = inputPreco.value;
    const novaDescricao = inputDesc.value.trim(); 
    
    const precoFormatado = novoPrecoRaw.replace(',', '.');
    const dadosParaAtualizar = {};
    let alteracaoFeita = false;

    if (precoFormatado !== item.preco) {
        dadosParaAtualizar.Valor_produto = precoFormatado;
        alteracaoFeita = true;
    }

    if (novaDescricao !== item.descricaoTexto) {
        dadosParaAtualizar.Descricao = novaDescricao; 
        alteracaoFeita = true;
    }

    if (!alteracaoFeita) {
        alert("Nenhuma alteração detectada.");
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL}/produto_unico_adm.php?id=${item.id}`, {
            method: 'PUT', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dadosParaAtualizar)
        });
        
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Erro ao salvar.');
        
        await recarregarDados();
        alert(`Alterações salvas para "${item.nome}".`);
    } catch (error) {
        alert(`Erro: ${error.message}`);
    }
}

async function handleExcluirItem() {
    const categoriaEl = document.getElementById('selectCategoria');
    const itemSelectEl = document.getElementById('selectItemAlterar');
    if (!categoriaEl || !itemSelectEl) return;

    const categoriaSelecionada = categoriaEl.value;
    const nomeSelecionado = itemSelectEl.value;
    
    if (!nomeSelecionado) {
        alert("Selecione um item para excluir.");
        return;
    }

    const itemParaExcluir = cardapio.find(i => i.nome === nomeSelecionado && i.categoria === categoriaSelecionada);
    if (!itemParaExcluir || !itemParaExcluir.id) return;

    if (confirm(`Deseja EXCLUIR o item "${itemParaExcluir.nome}"?`)) {
        try {
            const response = await fetch(`${API_BASE_URL}/produto_unico_adm.php?id=${itemParaExcluir.id}`, {
                method: 'DELETE'
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Erro ao excluir.');

            await recarregarDados();
            itemSelectEl.value = '';
            preencherCamposAlteracao();
            alert(`Item "${itemParaExcluir.nome}" excluído.`);
        } catch (error) {
             alert(`Erro: ${error.message}`);
        }
    }
}

async function recarregarDados() {
    cardapio = await buscarCardapio();
    renderizarCardapio();
    renderizarSelectDeItens(); 
    renderizarHistorico(); 
}


// === 5. INICIALIZAÇÃO E LISTENERS ===

async function init() {
    categorias = await buscarCategorias();
    popularSelectsCategoria();
    
    const filtro = document.getElementById('filtroCategoria');
    if (filtro && !filtro.querySelector('[value="Todos"]')) {
        const optionTodos = document.createElement('option');
        optionTodos.value = 'Todos';
        optionTodos.textContent = 'Todos os Produtos';
        filtro.prepend(optionTodos);
    }
    
    const selectCat = document.getElementById('selectCategoria');
    if (selectCat) categoriaAtual = selectCat.value;

    cardapio = await buscarCardapio();
    renderizarSelectDeItens(); 
    renderizarCardapio();
    renderizarHistorico(); 

    // Listeners com verificações de segurança
    const formAdd = document.getElementById('formAdicionarItem');
    if (formAdd) formAdd.addEventListener('submit', handleAdicionarItem);

    const formAlt = document.getElementById('formAlterarItem');
    if (formAlt) formAlt.addEventListener('submit', handleAlterarItem);

    const btnExcluir = document.getElementById('btnExcluirItem');
    if (btnExcluir) btnExcluir.addEventListener('click', handleExcluirItem);

    const btnHist = document.getElementById('btnAtualizarHistorico');
    if (btnHist) btnHist.addEventListener('click', renderizarHistorico); 

    if (selectCat) selectCat.addEventListener('change', renderizarSelectDeItens);

    const selectItemAlt = document.getElementById('selectItemAlterar');
    if (selectItemAlt) selectItemAlt.addEventListener('change', preencherCamposAlteracao);

    if (filtro) filtro.addEventListener('change', renderizarCardapio);
}

init();