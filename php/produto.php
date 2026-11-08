<?php

session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);

    echo json_encode([
        "erro" => "Usuário não autenticado."
    ]);

    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];

// Qualquer usuário logado pode ver os produtos (GET);
// só o gerente pode cadastrar, editar ou excluir.
if ($metodo !== "GET" && $_SESSION['usuario_funcao'] !== 'gerente') {
    http_response_code(403);

    echo json_encode([
        "erro" => "Você não tem permissão para alterar produtos."
    ]);

    exit;
}

require_once "conexao.php";

if ($metodo == "GET")
{
    $id = $_GET['id'] ?? null;

    if ($id) {
        $sql = "SELECT * FROM produto WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $resultado = $stmt->get_result();

        $produto = [];

        while ($linha = $resultado->fetch_assoc()) {
            $produto[] = $linha;
        }

        echo json_encode($produto);
    }

    else {
        $sql = "SELECT * FROM produto";

        $resultado = $conn->query($sql);

        $produtos = [];

        while ($linha = $resultado->fetch_assoc())
        {
            $produtos[] = $linha;
        }

        echo json_encode($produtos);
    }
}

elseif ($metodo == "POST")
{
    $dados = json_decode(file_get_contents("php://input"), true);

    $ativo = $dados['ativo'];
    $nome = $dados['nome'];
    $categoria = $dados['categoria'];
    $valor = $dados['valor'];
    $obsPedido = $dados['descricao'];

    if (!isset($dados['ativo'], $dados['nome'], $dados['categoria'], $dados['valor'], $dados['descricao'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campos obrigatórios ausentes."
        ]);

        exit;
    }

    $sql = "INSERT INTO produto (ativo, nome, categoria, valor, descricao)
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
    $obsPedido = $dados['descricao'];

    if (!isset($dados['ativo'], $dados['nome'], $dados['categoria'], $dados['valor'], $dados['descricao'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campos obrigatórios ausentes."
        ]);

        exit;
    }

    $sql = "UPDATE produto
            SET ativo = ?, nome = ?, categoria = ?, valor = ?, descricao = ?
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

    if (!isset($dados['id'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campo obrigatório ausente."
        ]);

        exit;
    }

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