const tbody = document.querySelector("#lista-comandas");
const mensagem = document.querySelector("#mensagem");

/* deixa o valor com cara de dinheiro, 12.5 vira R$ 12,50 */
function formatarValor(valor) {
    return Number(valor).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

/* o banco manda a data toda feia "2026-11-09 19:30:00", aq ela vira "09/11/2026, 19:30" */
function formatarData(texto) {
    const data = new Date(texto.replace(" ", "T"));
    return data.toLocaleString("pt-BR", { dateStyle: "short", timeStyle: "short" });
}

/* dhouglin, é o msm comanda.php da tela de mesas, so muda o filtro
   la eu peço as abertas e aq as concluidas (q é como fica o status dps de fechar) */
fetch("../php/comanda.php?status=concluida")
    .then(resposta => resposta.json())
    .then(dados => {
        tbody.innerHTML = "";

        if (dados.length === 0) {
            document.querySelector("#lista-vazia").hidden = false;
            return;
        }

        dados.forEach(comanda => {
            const linha = document.createElement("tr");
            /* o "Ver" abre a msm tela da comanda, so q la ela aparece fechada e sem as ações */
            linha.innerHTML = `
                <td>${String(comanda.mesa).padStart(2, "0")}</td>
                <td>${comanda.nome_usuario}</td>
                <td class="talao">${formatarData(comanda.abertura_comanda)}</td>
                <td class="talao">${formatarData(comanda.fechamento_comanda)}</td>
                <td>${comanda.forma_pagamento}</td>
                <td class="talao">${formatarValor(comanda.valor_total)}</td>
                <td class="tabela-acoes">
                    <a class="botao botao-contorno" href="comanda-detalhe.html?id=${comanda.id}">Ver</a>
                </td>
            `;
            tbody.appendChild(linha);
        });
    })
    .catch(() => {
        mensagem.textContent = "Não foi possível carregar o histórico. Tente novamente.";
        mensagem.classList.add("mensagem-erro");
    });
