<?php

session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);

    echo json_encode([
        "erro" => "Usuário não autenticado."
    ]);

    exit;
} else {
    if ($_SESSION['usuario_funcao'] !== 'gerente') {
        http_response_code(403);

        echo json_encode([
            "erro" => "Você Não tem permissão para Acessar esta parte."
        ]);

        exit;
    }
}

require_once "conexao.php";

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo == "GET")
{
    $id = $_GET['id'] ?? null;

    if ($id) {
        $sql = "SELECT id, ativo, funcao, nome, email FROM user WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $resultado = $stmt->get_result();

        $user = [];

        while ($linha = $resultado->fetch_assoc()) {
            $user[] = $linha;
        }

        echo json_encode($user);
    }

    else {
        $sql = "SELECT id, ativo, funcao, nome, email FROM user";

        $resultado = $conn->query($sql);

        $usuarios = [];

        while ($linha = $resultado->fetch_assoc()) {
            $usuarios[] = $linha;
        }

        echo json_encode($usuarios);   
    }
}

elseif ($metodo == "POST") 
{
    $dados = json_decode(file_get_contents("php://input"), true);

    /* confere os campos antes de ler, senao o php vomita um aviso no meio do json */
    if (!isset($dados['ativo'], $dados['funcao'], $dados['nome'], $dados['email'], $dados['senha'])
        || trim($dados['nome']) === '' || trim($dados['email']) === '' || $dados['senha'] === '') {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campos obrigatórios ausentes."
        ]);

        exit;
    }

    $ativo = $dados['ativo'] ? 1 : 0;
    $funcao = $dados['funcao'];
    $nome = trim($dados['nome']);
    $email = trim($dados['email']);
    $senha = password_hash($dados['senha'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (ativo, funcao, nome, email, senha)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("issss", $ativo, $funcao, $nome, $email, $senha);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        /* 1062 = email repetido, a coluna é UNIQUE entao n rola */
        if ($e->getCode() == 1062) {
            http_response_code(409);
            echo json_encode(["erro" => "Este e-mail já está cadastrado."]);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao cadastrar usuário."]);
        }
        exit;
    }

    http_response_code(201);

    echo json_encode([
        "mensagem" => "Usuário cadastrado com sucesso!"
    ]);
}

elseif ($metodo == "PUT") 
{
    $dados = json_decode(file_get_contents("php://input"), true);

    /* a senha n entra aq pq na edicao ela é opcional */
    if (!isset($dados['id'], $dados['ativo'], $dados['funcao'], $dados['nome'], $dados['email'])
        || trim($dados['nome']) === '' || trim($dados['email']) === '') {
        http_response_code(400);

        echo json_encode([
            "erro" => "Campos obrigatórios ausentes."
        ]);

        exit;
    }

    $id = $dados['id'];
    $ativo = $dados['ativo'] ? 1 : 0;
    $funcao = $dados['funcao'];
    $nome = trim($dados['nome']);
    $email = trim($dados['email']);

    /* so troca a senha se veio alguma coisa, veio vazia fica a antiga msm */
    if (isset($dados['senha']) && $dados['senha'] !== '') {
        $senha = password_hash($dados['senha'], PASSWORD_DEFAULT);

        $sql = "UPDATE user
                SET ativo = ?, funcao = ?, nome = ?, email = ?, senha = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("issssi", $ativo, $funcao, $nome, $email, $senha, $id);
    }

    else {
        $sql = "UPDATE user
                SET ativo = ?, funcao = ?, nome = ?, email = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("isssi", $ativo, $funcao, $nome, $email, $id);
    }

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            http_response_code(409);
            echo json_encode(["erro" => "Este e-mail já está cadastrado."]);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar usuário."]);
        }
        exit;
    }

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Usuário atualizado com sucesso!"
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

    /* o gerente n pode se apagar, o front ja barra mas aq é pra garantir */
    if ($id == $_SESSION['usuario_id']) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Você não pode excluir a própria conta."
        ]);

        exit;
    }

    $sql = "DELETE FROM user 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        /* 1451 = a chave estrangeira n deixa apagar, o cara tem comanda no nome dele */
        if ($e->getCode() == 1451) {
            http_response_code(409);
            echo json_encode(["erro" => "Esse usuário já tem comandas no sistema. Desative ele em Editar."]);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao excluir usuário."]);
        }
        exit;
    }

    http_response_code(200);

    echo json_encode([
        "mensagem" => "Usuário excluído com sucesso!"              
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