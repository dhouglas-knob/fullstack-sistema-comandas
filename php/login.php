<?php

require_once "conexao.php";

header("Content-Type: application/json");

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo == "POST") 
{
    $dados = json_decode(file_get_contents("php://input"), true);

    $email = $dados['email'] ?? '';
    $senha = $dados['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Email ou senha não preenchidos."
        ]);

        exit;
    }

    $sql = "SELECT * FROM user WHERE email = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param('s', $email);

    $stmt->execute();

    $resultado = $stmt->get_result();

    $user = $resultado->fetch_assoc();

    if ($email != $user['email'] || !password_verify($senha, $user['senha'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Email ou senha incorretos."
            ]);

        exit;
    }

    if (!$user['ativo']) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Usuário não ativo."
        ]);

        exit;
    }

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Login efetuado com sucesso!"
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