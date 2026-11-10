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

/* ver os produtos qualquer um logado pode
   agora cadastrar, editar e excluir é so o gerente */
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

    /* confere os campos antes de ler, senao o php vomita um aviso no meio do json */
    if (!isset($dados['ativo'], $dados['nome'], $dados['categoria'], $dados['valor'])
        || trim($dados['nome']) === '' || $dados['valor'] <= 0) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campos obrigatórios ausentes."
        ]);

        exit;
    }

    $ativo = $dados['ativo'] ? 1 : 0;
    $nome = trim($dados['nome']);
    $categoria = $dados['categoria'];
    $valor = $dados['valor'];
    $descricao = $dados['descricao'] ?? ''; /* a descricao é opcional */

    $sql = "INSERT INTO produto (ativo, nome, categoria, valor, descricao)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("issds", $ativo, $nome, $categoria, $valor, $descricao);

    $stmt->execute();

    http_response_code(201);

    echo json_encode([
        "mensagem" => "Produto cadastrado com sucesso!"
    ]);
}

elseif ($metodo == "PUT") 
{
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id'], $dados['ativo'], $dados['nome'], $dados['categoria'], $dados['valor'])
        || trim($dados['nome']) === '' || $dados['valor'] <= 0) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campos obrigatórios ausentes."
        ]);

        exit;
    }

    $id = $dados['id'];
    $ativo = $dados['ativo'] ? 1 : 0;
    $nome = trim($dados['nome']);
    $categoria = $dados['categoria'];
    $valor = $dados['valor'];
    $descricao = $dados['descricao'] ?? '';

    $sql = "UPDATE produto
            SET ativo = ?, nome = ?, categoria = ?, valor = ?, descricao = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("issdsi", $ativo, $nome, $categoria, $valor, $descricao, $id);

    $stmt->execute();

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Produto atualizado com sucesso!"
    ]);
}

elseif ($metodo == "DELETE")
{
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campo obrigatório ausente."
        ]);

        exit;
    }

    $id = $dados['id'];

    $sql = "DELETE FROM produto
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        /* 1451 = a chave estrangeira n deixa apagar, o produto ja ta em alguma comanda */
        if ($e->getCode() == 1451) {
            http_response_code(409);
            echo json_encode(["erro" => "Esse produto já foi lançado em comanda. Deixe ele indisponível em Editar."]);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao excluir produto."]);
        }
        exit;
    }

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