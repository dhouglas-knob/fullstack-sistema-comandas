const tbody = document.querySelector("#lista-produtos");
const tbodyIndisponiveis = document.querySelector("#lista-produtos-indisponiveis");
const secaoIndisponiveis = document.querySelector("#secao-indisponiveis");
const mensagem = document.querySelector("#mensagem");

/* deixa o valor com cara de dinheiro, 12.5 vira R$ 12,50 */
function formatarValor(valor) {
    return Number(valor).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

/* garçom tbm ve essa lista, mas a coluna de ações é so-gerente e fica escondida pra ele
   os disponiveis (ativo = 1) vao pra tabela de cima e os indisponiveis pra de baixo */
fetch("../php/produto.php")
    .then(resposta => resposta.json())
    .then(dados => {
        tbody.innerHTML = "";
        tbodyIndisponiveis.innerHTML = "";

        if (dados.length === 0) {
            document.querySelector("#lista-vazia").hidden = false;
            return;
        }

        dados.forEach(produto => {
            const linha = document.createElement("tr");
            linha.innerHTML = `
                <td>${produto.nome}</td>
                <td>${produto.categoria}</td>
                <td class="talao">${formatarValor(produto.valor)}</td>
                <td class="tabela-acoes so-gerente">
                    <a class="botao botao-contorno" href="editar-produto.html?id=${produto.id}" data-acao="editar">Editar</a>
                    <button type="button" class="botao botao-v" data-acao="excluir" data-nome="${produto.nome}" data-id="${produto.id}">Excluir</button>
                </td>
            `;
            if (produto.ativo == 1) {
                tbody.appendChild(linha);
            } else {
                tbodyIndisponiveis.appendChild(linha);
            }
        });

        /* a tabela de indisponiveis so aparece se tiver algum */
        secaoIndisponiveis.hidden = tbodyIndisponiveis.children.length === 0;
    })
    .catch(() => {
        mensagem.textContent = "Não foi possível carregar os produtos. Tente novamente.";
        mensagem.classList.add("mensagem-erro");
    });

/* escuto o clique no painel inteiro pq agora o botao de excluir pode estar em qualquer uma das duas tabelas */
document.querySelector(".painel").addEventListener("click", evento => {
    if (evento.target.dataset.acao !== "excluir") {
        return;
    }

    const produto_id = evento.target.dataset.id;
    const produto_nome = evento.target.dataset.nome;

    const confirmacao = confirm(`Tem certeza que deseja excluir o produto: ${produto_nome}?`);

    if (!confirmacao) {
        return;
    }

    mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");

    fetch("../php/produto.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: Number(produto_id) })
    })
    .then(resposta => resposta.json()
    .then(resultado => ({ resposta, resultado })))
    .then(({ resposta, resultado }) => {
        if (resposta.ok) {
            const linha = evento.target.closest("tr");
            if (linha) linha.remove();
            secaoIndisponiveis.hidden = tbodyIndisponiveis.children.length === 0;

            mensagem.textContent = resultado.mensagem;
            mensagem.classList.add("mensagem-sucesso");
        } else {
            mensagem.textContent = resultado.erro || "Não foi possível excluir.";
            mensagem.classList.add("mensagem-erro");
        }
    })
    .catch(() => {
        mensagem.textContent = "Não foi possível excluir. Tente novamente.";
        mensagem.classList.add("mensagem-erro");
    });
});
