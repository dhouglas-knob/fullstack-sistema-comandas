/* dhouglin, se liga como funciona essa tela:
   n existe tabela de mesa no banco, as mesas sao so os numeros de 1 ate 12
   quem fala se a mesa ta livre ou ocupada é a comanda: se tem comanda ABERTA
   com o numero daquela mesa ela ta ocupada, se n tem ela ta livre, simples assim */
const TOTAL_MESAS = 12;

const grade = document.querySelector("#grade-mesas");
const mensagem = document.querySelector("#mensagem");

/* deixa o valor com cara de dinheiro, 12.5 vira R$ 12,50 */
function formatarValor(valor) {
    return Number(valor).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

/* deixa o numero sempre com 2 digitos, 3 vira "03" */
function doisDigitos(numero) {
    return String(numero).padStart(2, "0");
}

/* aq é onde as mesas nascem. recebe a lista de comandas abertas q veio do php
   e passa mesa por mesa (1 ate 12) vendo se alguma comanda é dela */
function desenharMesas(comandas) {
    grade.innerHTML = "";

    let ocupadas = 0;
    let totalAberto = 0;

    for (let numero = 1; numero <= TOTAL_MESAS; numero++) {
        /* procura na lista uma comanda q seja dessa mesa
           se n achar o find devolve undefined e a mesa ta livre */
        const comanda = comandas.find(c => c.mesa == numero);

        if (comanda) {
            /* mesa ocupada: vira um link q ja leva pra comanda dela */
            ocupadas++;
            totalAberto += Number(comanda.valor_total);

            grade.innerHTML += `
                <a class="mesa mesa-ocupada" href="comanda-detalhe.html?id=${comanda.id}">
                    <span class="mesa-numero">${doisDigitos(numero)}</span>
                    <span class="mesa-status">ocupada · ${comanda.nome_usuario}</span>
                    <span class="mesa-total">${formatarValor(comanda.valor_total)}</span>
                </a>
            `;
        } else {
            /* mesa livre: vira um botao, o numero fica guardado no data-mesa
               pra na hora do clique eu saber qual mesa abrir */
            grade.innerHTML += `
                <button type="button" class="mesa mesa-livre" data-mesa="${numero}">
                    <span class="mesa-numero">${doisDigitos(numero)}</span>
                    <span class="mesa-status">livre</span>
                </button>
            `;
        }
    }

    /* resumo da lateral */
    document.querySelector("#total-livres").textContent = TOTAL_MESAS - ocupadas;
    document.querySelector("#total-ocupadas").textContent = ocupadas;
    document.querySelector("#total-aberto").textContent = formatarValor(totalAberto);
}

/* a listinha "Abertas agora" da lateral, uma linha pra cada comanda */
function desenharAbertas(comandas) {
    const lista = document.querySelector("#lista-abertas");
    lista.innerHTML = "";

    /* o aviso de "nenhuma comanda" so aparece se a lista veio vazia */
    document.querySelector("#nenhuma-aberta").hidden = comandas.length > 0;

    comandas.forEach(comanda => {
        const item = document.createElement("li");
        item.innerHTML = `
            <a href="comanda-detalhe.html?id=${comanda.id}">Mesa ${doisDigitos(comanda.mesa)}</a>
            · ${comanda.nome_usuario} · ${formatarValor(comanda.valor_total)}
        `;
        lista.appendChild(item);
    });
}

/* puxa so as comandas abertas (as fechadas ficam no historico) e manda desenhar */
fetch("../php/comanda.php?status=aberta")
    .then(resposta => resposta.json()
    .then(resultado => ({ resposta, resultado })))
    .then(({ resposta, resultado }) => {
        if (!resposta.ok) {
            mensagem.textContent = resultado.erro || "Não foi possível carregar as mesas.";
            mensagem.classList.add("mensagem-erro");
            return;
        }

        desenharMesas(resultado);
        desenharAbertas(resultado);
    })
    .catch(() => {
        mensagem.textContent = "Não foi possível carregar as mesas. Tente novamente.";
        mensagem.classList.add("mensagem-erro");
    });

/* clique na grade. as mesas nascem dps pelo js, entao escuto o clique
   na grade inteira e descubro em qual mesa foi (igual o excluir do users.js)
   mesa ocupada é <a>, o navegador ja abre sozinho, aq so interessa a livre */
grade.addEventListener("click", evento => {
    /* o clique pode cair no <span> de dentro, o closest sobe ate achar o botao da mesa */
    const botao = evento.target.closest("[data-mesa]");

    if (!botao) {
        return;
    }

    const mesa = Number(botao.dataset.mesa);

    const confirmacao = confirm(`Abrir uma comanda na mesa ${doisDigitos(mesa)}?`);

    if (!confirmacao) {
        return;
    }

    mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");

    /* o garçom da comanda o php pega da sessao, entao so precisa mandar a mesa */
    fetch("../php/comanda.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ mesa: mesa })
    })
    .then(resposta => resposta.json()
    .then(resultado => ({ resposta, resultado })))
    .then(({ resposta, resultado }) => {
        if (resposta.ok) {
            /* o php devolve o id da comanda q acabou de nascer, ai ja abre ela */
            window.location.href = `comanda-detalhe.html?id=${resultado.id}`;
        } else {
            /* tipo outro garçom q foi mais rapido e abriu essa mesa antes */
            mensagem.textContent = resultado.erro || "Não foi possível abrir a comanda.";
            mensagem.classList.add("mensagem-erro");
        }
    })
    .catch(() => {
        mensagem.textContent = "Não foi possível abrir a comanda. Tente novamente.";
        mensagem.classList.add("mensagem-erro");
    });
});
