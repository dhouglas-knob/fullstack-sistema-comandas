<?php

session_start();
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpar todas as variáveis de sessão
    $_SESSION = array();

    // Destruir a sessão
    session_destroy();

    http_response_code(200);
    echo json_encode([
        "mensagem" => "Logout realizado com sucesso."
    ]);
} else {
    http_response_code(405);
    echo json_encode([
        "erro" => "Método não permitido."
    ]);
}
