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
    
    const resposta = await fetch("../php/user.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dados)
    });

    const resultado = await resposta.json();
    console.log(resposta.status, resultado);
    
});