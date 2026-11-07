const recebido = window.location.search;
const parametro = new URLSearchParams(recebido);
const id = parametro.get('id');

console.log(id);

if (!id) {
    alert("Sem id Válido!");
    window.location.href = "usuarios.html";
}

fetch(`../php/user.php?id=${id}`)
    .then(resposta => resposta.json())
    .then(dados => {
        if (dados.length === 0) {
            alert("Usuário Inválido!");
            window.location.href = "usuarios.html";
            return;
        }

        const nome = dados[0].nome;
        const email = dados[0].email;
        const cargo = dados[0].funcao;
        const ativo = dados[0].ativo;

        const input_nome = document.querySelector('#nome');
        const input_email = document.querySelector('#email');
        const input_cargo = document.querySelector('#perfil');
        const input_ativo = document.querySelector('#ativo');

        input_nome.value = nome;
        input_email.value = email;
        input_cargo.value = cargo;
        input_ativo.value = ativo;

    })
    .catch(() => {
        const mensagem = document.getElementById("mensagem");
        mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");
        mensagem.textContent = "Não foi possível Puxar os dados do Usuário. Tente novamente.";
        mensagem.classList.add("mensagem-erro");
    });

const form = document.querySelector('#form-editar');

form.addEventListener('submit', evento => {
    evento.preventDefault();
    const dados = {
        id: id,
        email: form.email.value,
        nome: form.nome.value,
        funcao: form.perfil.value,
        ativo: form.ativo.value
    };

    const senha = form.senha.value;
    if (senha) dados.senha = senha;

    const mensagem = document.getElementById("mensagem");
    mensagem.classList.remove("mensagem-sucesso", "mensagem-erro");

    fetch('../php/user.php', {
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
            form.senha.value = '';
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