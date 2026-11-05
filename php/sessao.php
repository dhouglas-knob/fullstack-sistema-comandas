<?php
session_start();
header("Content-Type: application/json");

if(!isset($_SESSION['usuario_id'], $_SESSION['usuario_nome'], $_SESSION['usuario_email'], $_SESSION['usuario_funcao'], $_SESSION['usuario_ativo'])) {
    http_response_code(401);

    echo json_encode([
        "erro" => "Usuário não autenticado."
    ]);

    exit;
}

http_response_code(200);

echo json_encode([
    "id" => $_SESSION['usuario_id'],
    "nome" => $_SESSION['usuario_nome'],
    "email" => $_SESSION['usuario_email'],
    "funcao" => $_SESSION['usuario_funcao'],
    "ativo" => $_SESSION['usuario_ativo']
]);