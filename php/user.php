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

    // Valida ANTES de ler os campos (senão o PHP solta warnings em HTML e quebra o JSON)
    if (!isset($dados['ativo'], $dados['funcao'], $dados['nome'], $dados['email'], $dados['senha'])
        || trim($dados['nome']) === '' || trim($dados['email']) === '' || $dados['senha'] === '') {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campos obrigatórios ausentes."
        ]);

        exit;
    }

    $ativo = $dados['ativo'] ? 1 : 0;
    $funcao = $dados['funcao'];
    $nome = trim($dados['nome']);
    $email = trim($dados['email']);
    $senha = password_hash($dados['senha'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (ativo, funcao, nome, email, senha)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("issss", $ativo, $funcao, $nome, $email, $senha);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        // 1062 = chave duplicada (email já existe, coluna UNIQUE)
        if ($e->getCode() == 1062) {
            http_response_code(409);
            echo json_encode(["erro" => "Este e-mail já está cadastrado."]);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao cadastrar usuário."]);
        }
        exit;
    }

    http_response_code(201);

    echo json_encode([
        "mensagem" => "Usuário cadastrado com sucesso!"
    ]);
}

elseif ($metodo == "PUT") 
{
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id'], $dados['ativo'], $dados['funcao'], $dados['nome'], $dados['email'], $dados['senha'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campos obrigatórios ausentes."
        ]);

        exit;
    }

    $id = $dados['id'];
    $ativo = $dados['ativo'] ? 1 : 0;
    $funcao = $dados['funcao'];
    $nome = trim($dados['nome']);
    $email = trim($dados['email']);
    $senha = password_hash($dados['senha'], PASSWORD_DEFAULT);

    $sql = "UPDATE user 
            SET ativo = ?, funcao = ?, nome = ?, email = ?, senha = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("issssi", $ativo, $funcao, $nome, $email, $senha, $id);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            http_response_code(409);
            echo json_encode(["erro" => "Este e-mail já está cadastrado."]);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar usuário."]);
        }
        exit;
    }

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Usuário atualizado com sucesso!"
    ]);
}

elseif ($metodo == "DELETE")
{
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campo obrigatório ausente."
        ]);

        exit;
    }

    $id = $dados['id'];

    $sql = "DELETE FROM user 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    $stmt->execute();

    http_response_code(200);

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