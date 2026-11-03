<?php

require_once "conexao.php";

header("Content-Type: application/json");

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo == "GET")
{
    $sql = "SELECT * FROM produto";

    $resultado = $conn->query($sql);

    $produtos = [];

    while ($linha = $resultado->fetch_assoc())
    {
        $produtos[] = $linha;
    }

    echo json_encode($produtos);
}

elseif ($metodo == "POST")
{
    $dados = json_decode(file_get_contents("php://input"), true);

    $ativo = $dados['ativo'];
    $nome = $dados['nome'];
    $categoria = $dados['categoria'];
    $valor = $dados['valor'];
    $obsPedido = $dados['obs_pedido'];

    $sql = "INSERT INTO produto (ativo, nome, categoria, valor, obs_pedido)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("issds", $ativo, $nome, $categoria, $valor, $obsPedido);

    $stmt->execute();

    http_response_code(201);

    echo json_encode([
        "mensagem" => "Produto cadastrado com sucesso!"
    ]);
}

elseif ($metodo == "PUT") 
{
    $dados = json_decode(file_get_contents("php://input"), true);

    $id = $dados['id'];
    $ativo = $dados['ativo'];
    $nome = $dados['nome'];
    $categoria = $dados['categoria'];
    $valor = $dados['valor'];
    $obsPedido = $dados['obs_pedido'];

    $sql = "UPDATE produto
            SET ativo = ?, nome = ?, categoria = ?, valor = ?, obs_pedido = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("issdsi", $ativo, $nome, $categoria, $valor, $obsPedido, $id);

    $stmt->execute();

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Produto atualizado com sucesso!"
    ]);
}

elseif ($metodo == "DELETE")
{
    $dados = json_decode(file_get_contents("php://input"), true);

    $id = $dados['id'];

    $sql = "DELETE FROM produto
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    $stmt->execute();

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Produto excluído com sucesso!"
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