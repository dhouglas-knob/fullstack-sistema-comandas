const form = document.querySelector("#form-login");

form.addEventListener("submit", evento => {
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
