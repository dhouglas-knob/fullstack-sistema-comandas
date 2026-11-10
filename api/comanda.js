/* tela da comanda: na esquerda fica a comanda e na direita oq da pra fazer com ela */

/* pega o id q veio na url, tipo comanda-detalhe.html?id=5 */
const recebido = window.location.search;
const parametro = new URLSearchParams(recebido);
const id = parametro.get('id');

if (!id) {
    alert("Sem id Válido!");
    window.location.href = "mesas.html";
}

const mensagem = document.querySelector("#mensagem");
const formItem = document.querySelector("#form-item");
const formTirar = document.querySelector("#form-tirar");
const formFechar = document.querySelector("#form-fechar");
const botaoExcluir = document.querySelector("#botao-excluir");

/* deixa o valor com cara de dinheiro, 12.5 vira R$ 12,50 */
function formatarValor(valor) {
    return Number(valor).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

/* o banco manda a data toda feia "2026-11-09 19:30:00", aq ela vira "09/11/2026, 19:30" */
function formatarData(texto) {
    const data = new Date(texto.replace(" ", "T"));
    return data.toLocaleString("pt-BR", { dateStyle: "short", timeStyle: "short" });
}

/* pra n ficar copiando as msm 3 linhas de mensagem em todo fetch */
function mostrarMensagem(texto, tipo) {
    mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");
    mensagem.textContent = texto;
    mensagem.classList.add(tipo);
}

/* puxa os dados da comanda (mesa, quem abriu, total, status) e joga na esquerda
   dhouglin, o GET com id volta uma lista, por isso o dados[0] */
function carregarComanda() {
    fetch(`../php/comanda.php?id=${id}`)
        .then(resposta => resposta.json())
        .then(dados => {
            if (dados.length === 0) {
                alert("Comanda Inválida!");
                window.location.href = "mesas.html";
                return;
            }

            const comanda = dados[0];

            document.querySelector("#titulo-comanda").textContent = `Mesa ${String(comanda.mesa).padStart(2, "0")}`;
            document.querySelector("#comanda-usuario").textContent = comanda.nome_usuario;
            document.querySelector("#comanda-abertura").textContent = formatarData(comanda.abertura_comanda);
            document.querySelector("#comanda-total").textContent = formatarValor(comanda.valor_total);

            if (comanda.status === 'aberta') {
                document.querySelector("#comanda-status").textContent = "Aberta";
            } else {
                /* fechou its over, n mexe mais. some as ações da direita
                   e mostra quando fechou e como pagaram */
                document.querySelector("#comanda-status").textContent = "Fechada";
                document.querySelector("#rotulo-usuario").textContent = "Fechada por";
                document.querySelector("#comanda-fechamento").textContent = formatarData(comanda.fechamento_comanda);
                document.querySelector("#comanda-pagamento").textContent = comanda.forma_pagamento;
                document.querySelector("#linha-fechamento").hidden = false;
                document.querySelector("#linha-pagamento").hidden = false;
                document.querySelector("#acoes-aberta").hidden = true;
            }
        })
        .catch(() => {
            mostrarMensagem("Não foi possível Puxar os dados da Comanda. Tente novamente.", "mensagem-erro");
        });
}

/* puxa os itens da comanda, o php ja manda nome e valor do produto junto
   os msm itens vao pra 2 lugares: a tabela da esquerda e o select de "Tirar item" */
function carregarItens() {
    fetch(`../php/produto_comanda.php?comanda_id=${id}`)
        .then(resposta => resposta.json())
        .then(itens => {
            const tbody = document.querySelector("#lista-itens");
            const selectItem = document.querySelector("#item");

            tbody.innerHTML = "";
            selectItem.innerHTML = `<option value="" selected disabled>Selecione...</option>`;

            document.querySelector("#sem-itens").hidden = itens.length > 0;

            itens.forEach(item => {
                const subtotal = item.valor * item.quantidade_produto;

                const linha = document.createElement("tr");
                linha.innerHTML = `
                    <td>${item.nome}</td>
                    <td>${item.quantidade_produto}</td>
                    <td>${formatarValor(item.valor)}</td>
                    <td>${formatarValor(subtotal)}</td>
                `;
                tbody.appendChild(linha);

                /* o value é o id do item (tabela produto_comanda), n o id do produto */
                const opcao = document.createElement("option");
                opcao.value = item.id;
                opcao.textContent = `${item.quantidade_produto}x ${item.nome}`;
                selectItem.appendChild(opcao);
            });
        })
        .catch(() => {
            mostrarMensagem("Não foi possível Puxar os itens da Comanda. Tente novamente.", "mensagem-erro");
        });
}

/* enche o select de produtos, so entra os q tao disponiveis (ativo = 1)
   o q ta indisponivel nem aparece, nem tenta */
function carregarProdutos() {
    fetch("../php/produto.php")
        .then(resposta => resposta.json())
        .then(produtos => {
            const selectProduto = document.querySelector("#produto");

            produtos.forEach(produto => {
                if (produto.ativo != 1) {
                    return;
                }

                const opcao = document.createElement("option");
                opcao.value = produto.id;
                opcao.textContent = `${produto.nome} - ${formatarValor(produto.valor)}`;
                selectProduto.appendChild(opcao);
            });
        });
}

/* abriu a pagina, carrega as 3 coisas de uma vez */
carregarComanda();
carregarItens();
carregarProdutos();

/* LANÇAR ITEM */
formItem.addEventListener("submit", evento => {
    evento.preventDefault();
    const dados = {
        comanda_id: Number(id),
        produto_id: Number(formItem.produto.value),
        quantidade_produto: Number(formItem.quantidade.value)
    };

    fetch("../php/produto_comanda.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dados)
    })
    .then(resposta => resposta.json()
    .then(resultado => ({ resposta, resultado })))
    .then(({ resposta, resultado }) => {
        if (resposta.ok) {
            mostrarMensagem(resultado.mensagem, "mensagem-sucesso");
            formItem.reset();
            /* puxa d novo pra esquerda aparecer o item novo e o total certinho */
            carregarComanda();
            carregarItens();
        } else {
            mostrarMensagem(resultado.erro || "Não foi possível lançar o item.", "mensagem-erro");
        }
    })
    .catch(() => {
        mostrarMensagem("Não foi possível lançar o item. Tente novamente.", "mensagem-erro");
    });
});

/* TIRAR ITEM
   so o gerente ve esse form, e se o garçom tentar dar uma de esperto o php barra */
formTirar.addEventListener("submit", evento => {
    evento.preventDefault();

    const confirmacao = confirm("Tem certeza que deseja tirar esse item da comanda?");

    if (!confirmacao) {
        return;
    }

    fetch("../php/produto_comanda.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: Number(formTirar.item.value) })
    })
    .then(resposta => resposta.json()
    .then(resultado => ({ resposta, resultado })))
    .then(({ resposta, resultado }) => {
        if (resposta.ok) {
            mostrarMensagem(resultado.mensagem, "mensagem-sucesso");
            carregarComanda();
            carregarItens();
        } else {
            mostrarMensagem(resultado.erro || "Não foi possível tirar o item.", "mensagem-erro");
        }
    })
    .catch(() => {
        mostrarMensagem("Não foi possível tirar o item. Tente novamente.", "mensagem-erro");
    });
});

/* FECHAR CONTA
   o status vira 'concluida' e o php salva a hora, o total e quem fechou */
formFechar.addEventListener("submit", evento => {
    evento.preventDefault();

    const confirmacao = confirm("Fechar a conta dessa mesa? Depois de fechada a comanda não muda mais.");

    if (!confirmacao) {
        return;
    }

    const dados = {
        id: Number(id),
        status: "concluida",
        forma_pagamento: formFechar.pagamento.value
    };

    fetch("../php/comanda.php", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dados)
    })
    .then(resposta => resposta.json()
    .then(resultado => ({ resposta, resultado })))
    .then(({ resposta, resultado }) => {
        if (resposta.ok) {
            mostrarMensagem("Conta fechada! A mesa ficou livre de novo.", "mensagem-sucesso");
            /* carrega d novo, ai ja aparece fechada e as ações somem sozinhas */
            carregarComanda();
        } else {
            mostrarMensagem(resultado.erro || "Não foi possível fechar a conta.", "mensagem-erro");
        }
    })
    .catch(() => {
        mostrarMensagem("Não foi possível fechar a conta. Tente novamente.", "mensagem-erro");
    });
});

/* EXCLUIR COMANDA
   so rola com a comanda aberta e vazia, quem confere isso é o php */
botaoExcluir.addEventListener("click", () => {
    const confirmacao = confirm("Tem certeza que deseja excluir essa comanda?");

    if (!confirmacao) {
        return;
    }

    fetch("../php/comanda.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: Number(id) })
    })
    .then(resposta => resposta.json()
    .then(resultado => ({ resposta, resultado })))
    .then(({ resposta, resultado }) => {
        if (resposta.ok) {
            /* a comanda ja era, volta pras mesas */
            window.location.href = "mesas.html";
        } else {
            mostrarMensagem(resultado.erro || "Não foi possível excluir a comanda.", "mensagem-erro");
        }
    })
    .catch(() => {
        mostrarMensagem("Não foi possível excluir a comanda. Tente novamente.", "mensagem-erro");
    });
});
