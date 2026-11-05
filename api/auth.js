fetch('../php/sessao.php')
    .then(resposta => resposta.json().then(dados => ({ resposta, dados })))
    .then(({ resposta, dados }) => {
        if (!resposta.ok) {
            alert("Você não está logado. Faça login para acessar esta página.");
            window.location.href = "../index.html";
        } else {
            const msg = document.querySelector("#usuario-logado");
            msg.textContent = `${dados.nome} . ${dados.funcao === 'garcom' ? 'Garçom' : 'Gerente'}`;

            if (dados.funcao === 'gerente') {
                const Liberacao = document.querySelectorAll("[data-perfil='gerente']");
                Liberacao.forEach(element => {
                    element.hidden = false;
                });
            } else {
                const body = document.body;
                if (body.dataset.acesso === 'gerente') {
                    alert("Você não tem permissão para acessar esta página.");
                    window.location.href = "../pages/mesas.html";
                }

            }
        }
    });

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
