<?php

require_once "conexao.php";

header("Content-Type: application/json");

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo == "GET") 
{
    $sql = "SELECT * FROM user";

    $resultado = $conn->query($sql);

    $usuarios = [];

    while ($linha = $resultado->fetch_assoc()) 
    {
        $usuarios[] = $linha;
    }

    echo json_encode($usuarios);
}

elseif ($metodo == "POST") 
{
    $dados = json_decode(file_get_contents("php://input"), true);

    $ativo = $dados['ativo'];
    $funcao = $dados['funcao'];
    $nome = $dados['nome'];
    $email = $dados['email'];
    $senha = password_hash($dados['senha'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (ativo, funcao, nome, email, senha)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("issss", $ativo, $funcao, $nome, $email, $senha);

    $stmt->execute();

    http_response_code(201);

    echo json_encode([
        "mensagem" => "Usuário cadastrado com sucesso!"
    ]);
}

elseif ($metodo == "PUT") 
{
    $dados = json_decode(file_get_contents("php://input"), true);

    $id = $dados['id'];
    $ativo = $dados['ativo'];
    $funcao = $dados['funcao'];
    $nome = $dados['nome'];
    $email = $dados['email'];
    $senha = password_hash($dados['senha'], PASSWORD_DEFAULT);

    $sql = "UPDATE user 
            SET ativo = ?, funcao = ?, nome = ?, email = ?, senha = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("issssi", $ativo, $funcao, $nome, $email, $senha, $id);

    $stmt->execute();

    echo json_encode([
        "mensagem" => "Usuário atualizado com sucesso!"
    ]);
}

elseif ($metodo == "DELETE")
{
    $dados = json_decode(file_get_contents("php://input"), true);

    $id = $dados['id'];

    $sql = "DELETE FROM user WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    $stmt->execute();

    echo json_encode([
        "mensagem" => "Usuário excluído com sucesso!"              
    ]);
}

else 
{
    http_response_code(405);

    echo json_encode([
        "erro" => "Método não permitido."
    ]);
}

?>