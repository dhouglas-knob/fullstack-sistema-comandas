const form = document.querySelector("#form-login");

form.addEventListener("submit", evento => {
    /* segura o envio normal do form, quem manda é o fetch */
    evento.preventDefault();
    const dados = {
        email: form.email.value.trim(),
        senha: form.pass.value
    };

    const mensagem = document.getElementById("mensagem");
    mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");

    fetch("php/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dados)
    })
        .then(resposta => resposta.json().then(resultado => ({ resposta, resultado })))
        .then(({ resposta, resultado }) => {
            if (resposta.ok) {
                /* o login.php ja criou a sessao, é so entrar */
                window.location.href = "pages/mesas.html";
            } else {
                mensagem.textContent = resultado.erro || "Não foi possível entrar.";
                mensagem.classList.add("mensagem-erro");
            }
        })
        .catch(() => {
            mensagem.textContent = "Não foi possível entrar. Tente novamente.";
            mensagem.classList.add("mensagem-erro");
        });
});
