const form = document.querySelector("#form-usuario");

form.addEventListener("submit", async evento => {
    evento.preventDefault();
    const dados = {
        ativo: true,
        funcao: "garcom",
        nome: form.nome.value,
        email: form.email.value,
        senha: form.senha.value
    };
    
    const mensagem = document.getElementById("mensagem");
    mensagem.classList.remove("mensagem--sucesso", "mensagem--erro");

    try {
        const resposta = await fetch("../php/user.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(dados)
        });

        const resultado = await resposta.json();

        if (resposta.ok) {
            mensagem.textContent = resultado.mensagem;
            mensagem.classList.add("mensagem--sucesso");
        } else {
            mensagem.textContent = resultado.erro || "Não foi possível cadastrar.";
            mensagem.classList.add("mensagem--erro");
        }
    } catch (erro) {
        mensagem.textContent = "Não foi possível cadastrar. Tente novamente.";
        mensagem.classList.add("mensagem--erro");
    }
});