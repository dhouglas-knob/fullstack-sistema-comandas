/* esse arquivo roda em toda pagina de dentro do sistema
   pergunta pro sessao.php quem ta logado, se n tiver ninguem é chute pro login */
fetch('../php/sessao.php')
    .then(resposta => resposta.json().then(dados => ({ resposta, dados })))
    .then(({ resposta, dados }) => {
        if (!resposta.ok) {
            alert("Você não está logado. Faça login para acessar esta página.");
            window.location.href = "../index.html";
        } else {
            /* nome e cargo na barra de cima */
            const msg = document.querySelector("#usuario-logado");
            msg.textContent = `${dados.nome} . ${dados.funcao === 'garcom' ? 'Garçom' : 'Gerente'}`;
            /* guardo o id no body pras outras telas saberem quem ta logado (tipo n deixar se excluir) */
            document.body.dataset.usuarioId = dados.id;
            /* o css usa isso pra mostrar as coisas .so-gerente so pro gerente */
            document.body.dataset.funcao = dados.funcao;

            if (dados.funcao === 'gerente') {
                /* libera os links do menu q sao so do gerente */
                const Liberacao = document.querySelectorAll("[data-perfil='gerente']");
                Liberacao.forEach(element => {
                    element.hidden = false;
                });
            } else {
                /* garçom querendo entrar em pagina de gerente (as q tem data-acesso="gerente" no body), aq n */
                const body = document.body;
                if (body.dataset.acesso === 'gerente') {
                    alert("Você não tem permissão para acessar esta página.");
                    window.location.href = "../pages/mesas.html";
                }

            }
        }
    });

/* botao Sair: o logout.php mata a sessao e volta pro login */
const botaoSair = document.querySelector("#botao-sair");

botaoSair.addEventListener("click", () => {
    fetch('../php/logout.php', {
        method: "POST",
        headers: { "Content-Type": "application/json" }
    })
    .then(resposta => resposta.json().then(dados => ({ resposta, dados })))
    .then(({ resposta, dados }) => {
        if (resposta.ok) {
            window.location.href = "../index.html";
        } else {
            alert(dados.erro || "Não foi possível sair. Tente novamente.");
        }
    });
});
