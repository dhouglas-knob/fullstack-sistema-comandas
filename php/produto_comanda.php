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

require_once "conexao.php";

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo == "GET")
{
    $comandaId = $_GET['comanda_id'] ?? null;

    /* o JOIN ja traz nome e valor do produto junto com cada item */
    $sqlBase = "SELECT pc.id, pc.comanda_id, pc.produto_id, pc.quantidade_produto, p.nome, p.valor
                FROM produto_comanda pc
                JOIN produto p ON p.id = pc.produto_id";

    /* produto_comanda.php?comanda_id=5 traz so os itens daquela comanda */
    if ($comandaId) {
        $sql = $sqlBase . " WHERE pc.comanda_id = ? ORDER BY pc.id";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $comandaId);

        $stmt->execute();

        $resultado = $stmt->get_result();
    }

    else {
        $sql = $sqlBase . " ORDER BY pc.id";

        $resultado = $conn->query($sql);
    }

    $produtos_comanda = [];

    while ($linha = $resultado->fetch_assoc())
    {
        $produtos_comanda[] = $linha;
    }

    echo json_encode($produtos_comanda);
}

elseif ($metodo == "POST")
{
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['comanda_id'], $dados['produto_id'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campos obrigatórios ausentes."
        ]);

        exit;
    }

    $comandaId = $dados['comanda_id'];
    $produtoId = $dados['produto_id'];
    $quantidade = $dados['quantidade_produto'] ?? 1;

    verificarProdutoComanda($conn, $comandaId, $produtoId, $quantidade);

    $sql = "INSERT INTO produto_comanda (comanda_id, produto_id, quantidade_produto)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param('iii', $comandaId, $produtoId, $quantidade);

    $stmt->execute();

    atualizarTotalComanda($conn, $comandaId);

    http_response_code(201);

    echo json_encode([
        "mensagem" => "Produto registrado com sucesso na comanda!"
    ]);
}

elseif ($metodo == "PUT")
{
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id'], $dados['comanda_id'], $dados['produto_id'], $dados['quantidade_produto'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campos obrigatórios ausentes."
        ]);

        exit;
    }

    $id = $dados['id'];
    $comandaId = $dados['comanda_id'];
    $produtoId = $dados['produto_id'];
    $quantidade = $dados['quantidade_produto'];

    verificarProdutoComanda($conn, $comandaId, $produtoId, $quantidade);

    $sql = "UPDATE produto_comanda
            SET comanda_id = ?, produto_id = ?, quantidade_produto = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("iiii", $comandaId, $produtoId, $quantidade, $id);

    $stmt->execute();

    atualizarTotalComanda($conn, $comandaId);

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Produtos atualizada na comanda com sucesso!"
    ]);
}

elseif ($metodo == "DELETE")
{
    /* tirar item da comanda so o gerente, garçom nem tenta */
    if ($_SESSION['usuario_funcao'] !== 'gerente') {
        http_response_code(403);

        echo json_encode([
            "erro" => "Só o gerente pode tirar um item da comanda."
        ]);

        exit;
    }

    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campo obrigatório ausente."
        ]);

        exit;
    }

    $id = $dados['id'];

    /* descobre de qual comanda é o item e se ela ainda ta aberta */
    $sqlConsultaItem = "SELECT pc.comanda_id, c.status
                        FROM produto_comanda pc
                        JOIN comanda c ON c.id = pc.comanda_id
                        WHERE pc.id = ?";

    $stmtConsultaItem = $conn->prepare($sqlConsultaItem);

    $stmtConsultaItem->bind_param("i", $id);

    $stmtConsultaItem->execute();

    $item = $stmtConsultaItem->get_result()->fetch_assoc();

    if (!$item) {
        http_response_code(404);

        echo json_encode([
            "erro" => "Item não encontrado."
        ]);

        exit;
    }

    if ($item['status'] != "aberta") {
        http_response_code(400);

        echo json_encode([
            "erro" => "Não é possível tirar um item de uma comanda que não está aberta."
        ]);

        exit;
    }

    $sql = "DELETE FROM produto_comanda
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    $stmt->execute();

    atualizarTotalComanda($conn, $item['comanda_id']);

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Item excluído da comanda com sucesso!"
    ]);
}

else
{
    http_response_code(405);

    echo json_encode([
        "erro" => "Método não permitido."
    ]);
}

function verificarProdutoComanda(mysqli $conn, int $comandaId, int $produtoId, int $quantidade)
{
    /* barra quantidade negativa ou 0, ninguem pede -2 cerveja */
    if ($quantidade < 1) {
        http_response_code(400);

        echo json_encode([
            "erro" => "A quantidade deve ser no minimo 1."
        ]);

        exit;
    }

    /* ve se a comanda ta de pé */
    $sqlConsultaComanda = "SELECT status FROM comanda WHERE id = ?";

    $stmtConsultaComanda = $conn->prepare($sqlConsultaComanda);

    $stmtConsultaComanda->bind_param('i', $comandaId);

    $stmtConsultaComanda->execute();

    $pegarValorComanda = $stmtConsultaComanda->get_result();

    $linha = $pegarValorComanda->fetch_assoc();

    /* se n veio linha a comanda nem existe */
    if (!$linha || $linha['status'] != "aberta") {
        http_response_code(400);

        echo json_encode([
            "erro" => "Não é possível atribuir um produto em uma comanda que não está aberta."
        ]);

        exit;
    }

    /* ve se o produto ta disponivel */
    $sqlConsultaProduto = "SELECT ativo FROM produto WHERE id = ?";

    $stmtConsultaProduto = $conn->prepare($sqlConsultaProduto);

    $stmtConsultaProduto->bind_param('i', $produtoId);

    $stmtConsultaProduto->execute();

    $pegarValorProduto = $stmtConsultaProduto->get_result();

    $linha = $pegarValorProduto->fetch_assoc();

    if (!$linha || $linha['ativo'] == false) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Produto está inativo."
        ]);

        exit;
    }
}

/* toda vez q mexe nos itens soma tudo d novo e salva o total na comanda */
function atualizarTotalComanda(mysqli $conn, int $comandaId)
{
    $sql = "UPDATE comanda
            SET valor_total = (SELECT COALESCE(SUM(p.valor * pc.quantidade_produto), 0)
                               FROM produto_comanda pc
                               JOIN produto p ON p.id = pc.produto_id
                               WHERE pc.comanda_id = ?)
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ii", $comandaId, $comandaId);

    $stmt->execute();
}

?>
