const tbody = document.querySelector("#lista-produtos");
const mensagem = document.querySelector("#mensagem");

// Formata o valor como dinheiro: 12.5 -> R$ 12,50
function formatarValor(valor) {
    return Number(valor).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

fetch("../php/produto.php")
    .then(resposta => resposta.json())
    .then(dados => {
        tbody.innerHTML = "";

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
                <td>${produto.ativo == 1 ? 'Disponível' : 'Indisponível'}</td>
                <td class="tabela-acoes so-gerente">
                    <a class="botao botao-contorno" href="editar-produto.html?id=${produto.id}" data-acao="editar">Editar</a>
                    <button type="button" class="botao botao-v" data-acao="excluir" data-nome="${produto.nome}" data-id="${produto.id}">Excluir</button>
                </td>
            `;
            tbody.appendChild(linha);
        });
    })
    .catch(() => {
        mensagem.textContent = "Não foi possível carregar os produtos. Tente novamente.";
        mensagem.classList.add("mensagem-erro");
    });

tbody.addEventListener("click", evento => {
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

            mensagem.textContent = resultado.mensagem;
            mensagem.classList.add("mensagem-sucesso");
        } else {
            mensagem.textContent = resultado.erro || "Não foi possível excluir.";
            mensagem.classList.add("mensagem-erro");
        }
    })
    .catch(() => {
        mensagem.textContent = "Não foi possível excluir. Se o produto já foi lançado em alguma comanda, deixe-o indisponível em Editar.";
        mensagem.classList.add("mensagem-erro");
    });
});
