<?php
// Arquivo: restaurante.php (ADICIONAR NO TOPO)
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: login.html");
    exit;
}

// O ID do usuário logado está disponível via $_SESSION['user_id']
$idUserLogado = $_SESSION['user_id'];
?>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="restaurante.css">
        <title>Início | Essência Mineira</title>
    </head>
    <body>
        <header>
            <div class="logo-nome">
            <img class="logo" src="Imagens Início/Logo EM.png" alt="logo">
                <div class="nome">
                <h1>Essência Mineira</h1>
                <p>Restaurante Mineiro de Verdade</p>
                </div>
            </div>
            <div class="nav">
            <nav>
                <div>
                <a href="#contato">Contato</a></li>
                <a href="#local">Localização</a>
                <a href="#promoções">Promoções</a>
                <a href="#horario">Funcionamento</a>
                </div>
                <div>
                <a href="cardapio.php">Cardápio</a>
                <a href="historia.html">Nossa História</a></li>
                <a href="equipe.html">Nossa Equipe</a>
                <a href="login.html">Sair</a>
                </div>
            </nav>
            </div>
        </header>
        <main>
            <section id="entrada" class="entrada">
                <div>
                <h2>Nosso restaurante espera por você!</h2>
                </div>
            </section>
            <section id="fotos">
                <h2>Pratos Principais</h2>
                <div class="pratos">
                    <div class="pratos3">
                        <div>
                        <img src="Imagens Início/feijoada-2.png" alt="prato1">
                        <h3>Feijoada Mineira</h3>
                        </div>
                        <div>
                        <img src="Imagens Início/tropeiro.png" alt="prato1">
                        <h3>Tropeiro</h3>
                        </div>
                        <div>
                        <img src="Imagens Início/vacaatolada.png" alt="prato1">
                        <h3>Vaca Atolada</h3>
                        </div>
                    </div>
                    <div class="pratos2">
                        <div>
                        <img src="Imagens Início/frango.png" alt="prato1">
                        <h3>Frango com Quiabo</h3>
                        </div>
                        <div>
                        <img src="Imagens Início/mandioca.png" alt="prato1">
                        <h3>Porção de Mandioca com Carne de Sol</h3>
                        </div>
                    </div>
                </div>
            </section>
            <section id="promoções">
                <h2>Promoções do Dia</h2>
                <p>Válidas somente para o dia 11/06/2025</p>
                <br>
                <div class="promoções">
                    <div class="promo">
                        <img src="Imagens Início/feijoada.png" alt="feijoada">
                        <h3>Feijoada Mineira</h3>
                        <p>de R$70,00 por R$69,69</p>
                        <p class="obs">Serve Três Pessoas</p>
                    </div>
                    <div class="promo">
                        <img src="Imagens Início/fritas.png" alt="fritas">
                        <h3>Porção de Fritas com Queijo e Bacon</h3>
                        <p>de R$30,00 por R$29,99</p>
                    </div>
                    <div class="promo">
                        <img src="Imagens Início/torresmo.png" alt="torresmo">
                        <h3>Porção de Torresmo</h3>
                        <p>de R$20,00 por R$19,99</p>
                    </div>
                </div>
            </section>
            <section id="horario">
                <h2>Horário de Funcionamento</h2>
                <div class="horarios">
                <p>Segunda à Sexta: 11:00 - 15:00 / 18:00 - 22:30</p>
                </div>
                <br>
                <div class="horarios">
                <p>Sábado: 10:00 - 15:30 / 18:00 - 22:30</p>
                </div>
                <br>
                <div class="horarios">
                <p>Domingo: 10:00 - 16:00</p>
                </div>
                <br>
                <div class="horarios">
                <p>Feriados: Contate-nos para informações</p>
                </div>
                <br>
                <h2>Delivery</h2>
                <div class="horarios">
                <p>Segunda à Sexta: 12:00 - 14:30 / 19:00 - 22:00</p>
                </div>
                <br>
                <div class="horarios">
                <p>Sábado: 11:00 - 15:00 / 19:00 - 22:00</p>
                </div>
                <br>
                <div class="horarios">
                <p>Domingo: 11:00 - 15:30</p>
                </div>
                <br>
                <div class="horarios">
                <p>Feriados: Contate-nos para informações</p>
                </div>
                <br>
            </section>
            <section id="contato">
                <h2>Contato</h2>
                <p class="destaque">Para dúvidas ligue:</p>
                <p><strong>(31)94002-8922</strong></p>
                <p class="destaque">Ou envie uma mensagem em:</p>
                <p><strong>essenciamineira@gmail.com</strong></p>
            </section>
            <section id="local">
                <h2>Localização</h2>
                <p class="destaque">Venha nos encontrar em:</p>
                <p><strong>Rua: Maria Bernardina, 192, Bairro: Lagoa, Barão de Cocais - MG </strong></p>
                <p class="destaque">Estamos esperando por você!</p>
            </section>
        </main>
        <footer>
            <p>© 2025 Essência Mineira. Todos os direitos reservados.</p>
        </footer>
    </body>
</html>