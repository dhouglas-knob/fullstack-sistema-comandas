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
    $id = $_GET['id'] ?? null;
    $status = $_GET['status'] ?? null;

    /* o JOIN ja traz o nome do usuario junto com a comanda, ai o front so mostra */
    $sqlBase = "SELECT c.*, u.nome AS nome_usuario
                FROM comanda c
                JOIN user u ON u.id = c.user_id";

    /* comanda.php?id=5 traz so aquela comanda ali */
    if ($id) {
        $sql = $sqlBase . " WHERE c.id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $resultado = $stmt->get_result();
    }

    /* comanda.php?status=aberta (tela de mesas) ou ?status=concluida (historico) */
    elseif ($status) {
        $sql = $sqlBase . " WHERE c.status = ? ORDER BY c.id DESC";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $status);

        $stmt->execute();

        $resultado = $stmt->get_result();
    }

    else {
        $sql = $sqlBase . " ORDER BY c.id DESC";

        $resultado = $conn->query($sql);
    }

    $comandas = [];

    while ($linha = $resultado->fetch_assoc())
    {
        $comandas[] = $linha;
    }

    echo json_encode($comandas);
}

elseif ($metodo == "POST")
{
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['mesa'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campo obrigatório ausente."
        ]);

        exit;
    }

    $userId = $_SESSION['usuario_id'];
    $mesa = $dados['mesa'];
    $aberturaComanda = date('Y-m-d H:i:s'); /* pega a hora exata q a comanda nasceu */

    if ($mesa < 1 || $mesa > 12) {
        http_response_code(400);

        echo json_encode([
            "erro" => "O restaurante só tem 12 mesas."
        ]);

        exit;
    }

    /* ve se ja tem outra comanda aberta nessa mesa, pra n abrir 2 */
    $sqlVerificarMesa = "SELECT COUNT(mesa) as contadorMesa FROM comanda WHERE mesa = ? AND status = 'aberta'";

    $stmtVerificarMesa = $conn->prepare($sqlVerificarMesa);

    $stmtVerificarMesa->bind_param("i", $mesa);

    $stmtVerificarMesa->execute();

    $resultadoMesa = $stmtVerificarMesa->get_result();

    $contador = $resultadoMesa->fetch_assoc();

    if ($contador['contadorMesa'] > 0) {
        http_response_code(400);

        echo json_encode([
            "erro" => "A mesa {$mesa} ja está em uso com uma comanda aberta."
        ]);

        exit;
    }

    $sql = "INSERT INTO comanda (user_id, mesa, status, valor_total, forma_pagamento, abertura_comanda, fechamento_comanda)
            VALUES (?, ?, 'aberta', 0, NULL, ?, NULL)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("iis", $userId, $mesa, $aberturaComanda);

    $stmt->execute();

    http_response_code(201);

    /* devolve o id da comanda nova pro front ja abrir a tela dela */
    echo json_encode([
        "mensagem" => "Comanda registrada com sucesso!",
        "id" => $conn->insert_id
    ]);
}

elseif ($metodo == "PUT")
{
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id'], $dados['status'])) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campos obrigatórios ausentes."
        ]);

        exit;
    }

    $id = $dados['id'];
    $userId = $_SESSION['usuario_id']; /* fica salvo quem fechou, sem desculpinha dps */
    $status = $dados['status'];
    $formaPagamento = $dados['forma_pagamento'] ?? null;

    if ($status != 'aberta' && $status != 'concluida') {
        http_response_code(400);

        echo json_encode([
            "erro" => "Status inválido."
        ]);

        exit;
    }

    /* pra fechar a conta tem q falar como pagaram */
    if ($status == 'concluida' && !$formaPagamento) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Informe a forma de pagamento para fechar a conta."
        ]);

        exit;
    }

    /* comanda fechada its over, n muda mais */
    verificarComandaAberta($conn, $id);

    $sqlValorProduto = "SELECT SUM(p.valor * pc.quantidade_produto) AS valor_total
                        FROM produto_comanda pc
                        JOIN produto p
                        ON p.id = pc.produto_id
                        JOIN comanda c
                        ON c.id = pc.comanda_id
                        WHERE c.id = ? AND status = 'aberta'";

    $stmtValorProduto = $conn->prepare($sqlValorProduto);

    $stmtValorProduto->bind_param('i', $id);

    $stmtValorProduto->execute();

    $pegarValorProduto = $stmtValorProduto->get_result();

    $linha = $pegarValorProduto->fetch_assoc();

    $valorTotal = $linha['valor_total'] ?? 0;

    if ($status == 'concluida') {
        $fechamentoComanda = date('Y-m-d H:i:s');
    } else {
        $fechamentoComanda = NULL;
    }

    if (!$formaPagamento) {
        $formaPagamento = NULL;
    }

    $sql = "UPDATE comanda
            SET status = ?, forma_pagamento = ?, valor_total = ?, user_id = ?, fechamento_comanda = ?
            WHERE id = ? AND status = 'aberta'";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ssdssi", $status, $formaPagamento, $valorTotal, $userId, $fechamentoComanda, $id);

    $stmt->execute();

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Comanda atualizada com sucesso!"
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

    /* so da pra excluir comanda aberta */
    verificarComandaAberta($conn, $id);

    /* e q ta vazia, sem nenhum item lancado */
    $sqlItens = "SELECT COUNT(id) AS contadorItens FROM produto_comanda WHERE comanda_id = ?";

    $stmtItens = $conn->prepare($sqlItens);

    $stmtItens->bind_param("i", $id);

    $stmtItens->execute();

    $contador = $stmtItens->get_result()->fetch_assoc();

    if ($contador['contadorItens'] > 0) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Não é possível excluir uma comanda que já tem itens."
        ]);

        exit;
    }

    $sql = "DELETE FROM comanda
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    $stmt->execute();

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Comanda deletada com sucesso!"
    ]);
}

else
{
    http_response_code(405);

    echo json_encode([
        "erro" => "Método não permitido."
    ]);
}

/* para tudo e da erro se a comanda n existe ou ja fechou */
function verificarComandaAberta(mysqli $conn, int $id)
{
    $sql = "SELECT status FROM comanda WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $comanda = $stmt->get_result()->fetch_assoc();

    if (!$comanda) {
        http_response_code(404);

        echo json_encode([
            "erro" => "Comanda não encontrada."
        ]);

        exit;
    }

    if ($comanda['status'] != 'aberta') {
        http_response_code(400);

        echo json_encode([
            "erro" => "Essa comanda já foi fechada e não pode mais ser alterada."
        ]);

        exit;
    }
}

?>
