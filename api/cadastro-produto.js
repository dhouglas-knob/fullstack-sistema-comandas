const form = document.querySelector("#form-produto");

form.addEventListener("submit", evento => {
    evento.preventDefault();
    const dados = {
        ativo: 1,
        nome: form.nome.value,
        categoria: form.categoria.value,
        valor: Number(form.valor.value),
        descricao: form.descricao.value
    };

    const mensagem = document.getElementById("mensagem");
    mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");

    fetch("../php/produto.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dados)
    })
        .then(resposta => resposta.json()
        .then(resultado => ({ resposta, resultado })))
        .then(({ resposta, resultado }) => {
            if (resposta.ok) {
                mensagem.textContent = resultado.mensagem;
                mensagem.classList.add("mensagem-sucesso");
                form.reset();
            } else {
                mensagem.textContent = resultado.erro || "Não foi possível cadastrar.";
                mensagem.classList.add("mensagem-erro");
            }
        })
        .catch(() => {
            mensagem.textContent = "Não foi possível cadastrar. Tente novamente.";
            mensagem.classList.add("mensagem-erro");
        });
});
