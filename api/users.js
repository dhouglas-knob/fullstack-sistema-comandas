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
                <td>${usuario.funcao === 'garcom' ? 'Garçom' : 'Gerente'}</td>
                <td class="tabela-acoes">
                    <a class="botao botao-contorno" href="cadastro.html?id=${usuario.id}" data-acao="editar">Editar</a>
                    <button type="button" class="botao botao-v" data-acao="excluir" data-id="${usuario.id}">Excluir</button>
                </td>
            `;
            tabela.appendChild(linha);
        });
    });