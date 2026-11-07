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

    console.log(dados);
});