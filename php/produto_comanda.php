<?php

session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);

    echo json_encode([
        "erro" => "Usuário não autenticado."
    ]);

    exit;
} else {
    if ($_SESSION['usuario_funcao'] !== 'gerente') {
        http_response_code(403);

        echo json_encode([
            "erro" => "Você Não tem permissão para Acessar esta parte."
        ]);

        exit;
    }
}

require_once "conexao.php";

?>