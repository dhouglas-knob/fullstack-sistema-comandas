<?php

$host = "localhost";
$user = "root";
$password = "dhouglasKnob";
$database = "restaurantedb";

header("Content-Type: application/json");

try {
    $conn = new mysqli($host, $user, $password, $database);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao conectar no banco de dados."]);
    exit;
}