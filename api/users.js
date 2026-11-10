const tabelaDesativados = document.querySelector("#lista-usuarios-desativados");
const secaoDesativados = document.querySelector("#secao-desativados");

/* puxa todos os usuarios e cria uma linha pra cada um
   os ativos vao pra tabela de cima e os desativados pra de baixo */
fetch("../php/user.php")
    .then(resposta => resposta.json())
    .then(dados => {
        const tabela = document.querySelector("#lista-usuarios");
        tabela.innerHTML = "";
        tabelaDesativados.innerHTML = "";
        dados.forEach(usuario => {
            const linha = document.createElement("tr");
            linha.innerHTML = `
                <td>${usuario.nome}</td>
                <td>${usuario.email}</td>
                <td>${usuario.funcao === 'garcom' ? 'Garçom' : 'Gerente'}</td>
                <td class="tabela-acoes">
                    <a class="botao botao-contorno" href="editar.html?id=${usuario.id}" data-acao="editar">Editar</a>
                    <button type="button" class="botao botao-v" data-acao="excluir" data-nome="${usuario.nome}" data-id="${usuario.id}">Excluir</button>
                </td>
            `;
            if (usuario.ativo == 1) {
                tabela.appendChild(linha);
            } else {
                tabelaDesativados.appendChild(linha);
            }
        });

        /* a tabela de desativados so aparece se tiver algum */
        secaoDesativados.hidden = tabelaDesativados.children.length === 0;
    });

/* os botoes de excluir nascem dps pelo js, entao escuto o clique no painel inteiro
   (pega as duas tabelas) e vejo pelo data-acao se foi num botao de excluir */
document.querySelector(".painel").addEventListener("click", evento => {
    if (evento.target.dataset.acao !== "excluir") {
        return;
    }
    const mensagem = document.querySelector("#mensagem");
    const user_id = evento.target.dataset.id;
    const user_name = evento.target.dataset.nome;
    /* o id de quem ta logado o auth.js deixou no body */
    if (user_id === document.body.dataset.usuarioId) {
        mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");
        mensagem.textContent = "Você não pode se Excluir.";
        mensagem.classList.add("mensagem-erro");
        return;
    }
    const confirmacao = confirm(`Tem Certeza que deseja Excluir a Conta de: ${user_name}?`);

    if (!confirmacao) {
        return;
    } else {

        fetch("../php/user.php", {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({id: Number(user_id)})
        })
        .then(resposta => resposta.json()
        .then(resultado => ({resposta, resultado})))
        .then(({resposta, resultado}) => {
            mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");

            if (resposta.ok) {
                const linha = evento.target.closest('tr');
                if (linha) linha.remove();
                secaoDesativados.hidden = tabelaDesativados.children.length === 0;

                mensagem.textContent = resultado.mensagem;
                mensagem.classList.add("mensagem-sucesso");
            } else {
                mensagem.textContent = resultado.erro || "Não foi possível excluir.";
                mensagem.classList.add("mensagem-erro");
            }
        })
        .catch(() => {
            mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");
            mensagem.textContent = "Não foi possível excluir. Tente novamente.";
            mensagem.classList.add("mensagem-erro");
        });
    }

});