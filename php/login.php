<?php
session_start();

header("Content-Type: application/json");

require_once "conexao.php";

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

    if (!$user || !password_verify($senha, $user['senha'])) {
        http_response_code(401);

        echo json_encode([
            "erro" => "Email ou senha incorretos."
            ]);

        exit;
    }

    if (!$user['ativo']) {
        http_response_code(403);

        echo json_encode([
            "erro" => "Usuário não ativo."
        ]);

        exit;
    }

    session_regenerate_id(true);

    $_SESSION['usuario_id']     = $user['id'];
    $_SESSION['usuario_nome']   = $user['nome'];
    $_SESSION['usuario_email']  = $user['email'];
    $_SESSION['usuario_funcao'] = $user['funcao'];
    $_SESSION['usuario_ativo']  = $user['ativo'];

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Login efetuado com sucesso!",
        "id"       => $user['id'],
        "nome"     => $user['nome'],
        "funcao"   => $user['funcao']
    ]);
}

else 
{
    http_response_code(405);

    echo json_encode([
        "erro" => "Método não permitido."
    ]);
}