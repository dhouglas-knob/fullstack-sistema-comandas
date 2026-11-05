console.log("users.js carregado");

fetch("../php/user.php")
    .then(resposta => resposta.json())
    .then(dados => {
        const tabela = document.querySelector("#lista-usuarios");
        tabela.innerHTML = "";
        dados.forEach(usuario => {
            const linha = document.createElement("tr");
            linha.innerHTML = `
                <td>${usuario.nome}</td>
                <td>${usuario.email}</td>
                <td>${usuario.perfil}</td>
            `;
            tabela.appendChild(linha);
        });
    });