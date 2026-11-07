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

    if ($id) {
        $sql = "SELECT * FROM produto_comanda WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $resultado = $stmt->get_result();

        $produto_comanda = [];

        while ($linha = $resultado->fetch_assoc()) {
            $produto_comanda[] = $linha;
        }

        echo json_encode($produto_comanda);
    }

    else {
        $sql = "SELECT * FROM produto_comanda";

        $resultado = $conn->query($sql);

        $produtos_comanda = [];

        while ($linha = $resultado->fetch_assoc())
        {
            $produtos_comanda[] = $linha;
        }

        echo json_encode($produtos_comanda);
    }
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

    http_response_code(201);

    echo json_encode([
        "mensagem" => "Produto registrado com sucesso na comanda!"
    ]);
}

elseif ($metodo == "PUT")
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

    http_response_code(200);

    echo json_encode([  
        "mensagem" => "Produtos atualizada na comanda com sucesso!"
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

    $sql = "DELETE FROM produto_comanda
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    $stmt->execute();

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
    //impede que seja registado um valor negativo ou 0
    if ($quantidade < 1) {
        http_response_code(400);

        echo json_encode([
            "erro" => "A quantidade deve ser no minimo 1."
        ]);

        exit;
    }

    //verifica se a comanda esta disponivel
    $sqlConsultaComanda = "SELECT status FROM comanda WHERE id = ?";

    $stmtConsultaComanda = $conn->prepare($sqlConsultaComanda);

    $stmtConsultaComanda->bind_param('i', $comandaId);

    $stmtConsultaComanda->execute();

    $pegarValorComanda = $stmtConsultaComanda->get_result(); 

    $linha = $pegarValorComanda->fetch_assoc();

    $valorComanda = $linha['status']; 

    if ($valorComanda != "aberta") {
        http_response_code(400);

        echo json_encode([
            "erro" => "Não é possível atribuir um produto em uma comanda que não está aberta."
        ]);

        exit;
    }

    //verifica se o produto esta disponivel
    $sqlConsultaProduto = "SELECT ativo FROM produto WHERE id = ?";

    $stmtConsultaProduto = $conn->prepare($sqlConsultaProduto);

    $stmtConsultaProduto->bind_param('i', $produtoId);

    $stmtConsultaProduto->execute();

    $pegarValorProduto = $stmtConsultaProduto->get_result(); 

    $linha = $pegarValorProduto->fetch_assoc();

    $ativo = $linha['ativo']; 

    if ($ativo == false) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Produto está inativo."
        ]);

        exit;
    }
}

?>