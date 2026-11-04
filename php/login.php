<?php

require_once "conexao.php";

header("Content-Type: application/json");

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo == "POST") 
{
    $dados = json_decode(file_get_contents("php://input"), true);

    $email = $dados['email'];
    $senha = $dados['senha'];

    $sql = "SELECT email, senha FROM user WHERE email = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param('s', $email);

    $stmt->execute();

    $resultado = $stmt->get_result();

    return $resultado;
}

else 
{
    http_response_code(405);

    echo json_encode([
        "erro" => "Método não permitido."
    ]);
}

?>