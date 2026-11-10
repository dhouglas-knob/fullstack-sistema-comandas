<?php

session_start();
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* limpa tudo q tava guardado na sessao */
    $_SESSION = array();

    /* e mata ela de vez */
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
