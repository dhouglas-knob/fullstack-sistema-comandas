/* msm esquema do editar.js dos usuarios, so q com produto */
const recebido = window.location.search;
const parametro = new URLSearchParams(recebido);
const id = parametro.get('id');

if (!id) {
    alert("Sem id Válido!");
    window.location.href = "produtos.html";
}

fetch(`../php/produto.php?id=${id}`)
    .then(resposta => resposta.json())
    .then(dados => {
        if (dados.length === 0) {
            alert("Produto Inválido!");
            window.location.href = "produtos.html";
            return;
        }

        const input_nome = document.querySelector('#nome');
        const input_categoria = document.querySelector('#categoria');
        const input_valor = document.querySelector('#valor');
        const input_ativo = document.querySelector('#ativo');
        const input_descricao = document.querySelector('#descricao');

        input_nome.value = dados[0].nome;
        input_categoria.value = dados[0].categoria;
        input_descricao.value = dados[0].descricao ?? '';
        input_valor.value = dados[0].valor;
        input_ativo.value = dados[0].ativo;
    })
    .catch(() => {
        const mensagem = document.getElementById("mensagem");
        mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");
        mensagem.textContent = "Não foi possível Puxar os dados do Produto. Tente novamente.";
        mensagem.classList.add("mensagem-erro");
    });

const form = document.querySelector('#form-editar-produto');

form.addEventListener('submit', evento => {
    evento.preventDefault();
    const dados = {
        id: Number(id),
        nome: form.nome.value,
        categoria: form.categoria.value,
        valor: Number(form.valor.value),
        ativo: Number(form.ativo.value),
        descricao: form.descricao.value
    };

    const mensagem = document.getElementById("mensagem");
    mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");

    fetch('../php/produto.php', {
        method: 'PUT',
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(dados)
    })
    .then(resposta => resposta.json()
    .then(resultado => ({resposta, resultado})))
    .then(({resposta, resultado}) => {
        if (resposta.ok) {
            mensagem.textContent = resultado.mensagem;
            mensagem.classList.add("mensagem-sucesso");
        } else {
            mensagem.textContent = resultado.erro || "Não foi possível Editar.";
            mensagem.classList.add("mensagem-erro");
        }
    })
    .catch(() => {
        mensagem.textContent = "Não foi possível Editar. Tente novamente.";
        mensagem.classList.add("mensagem-erro");
    });
});
