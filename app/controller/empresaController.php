<?php
header('Content-Type: application/json'); // Retorna JSON

// Conexão com o banco de dados (substitua com seus dados)
$host = "localhost";
$db   = "senac5";
$user = "empresa";
$pass = "senac";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

// Função para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Captura os dados enviados pelo fetch
$action = $_POST['action'] ?? '';

switch($action) {

    case 'list':
        $stmt = $pdo->query("SELECT id, nome, cpf_cnpj, email FROM fornecedor"); // Não retorna senha
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($fornecedor);
        break;

    case 'create':
        $nome = trim($_POST['nome'] ?? '');
        $cpf_cnpj = trim($_POST['cpf_cnpj'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        // Validação simples
        if (!$nome || !$cpf_cnpj || !$email || !$senha) {
            echo json_encode(["error" => "Todos os campos são obrigatórios"]);
            exit;
        }
        if (!validarEmail($email)) {
            echo json_encode(["error" => "Email inválido"]);
            exit;
        }

        // Hash da senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO empresa (nome, cpf_cnpj, email, senha) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $cpf_cnpj, $email, $senhaHash]);
        echo json_encode(["success" => true]);
        break;

    case 'update':
        $id = $_POST['id'] ?? '';
        $nome = trim($_POST['nome'] ?? '');
        $cpf_cnpj = trim($_POST['cpf_cnpj'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (!$id || !$nome || !$cpfcnpj || !$email) {
            echo json_encode(["error" => "ID, nome, CPF_CNPJ e email são obrigatórios"]);
            exit;
        }
        if (!validarEmail($email)) {
            echo json_encode(["error" => "Email inválido"]);
            exit;
        }

        if ($senha) {
            // Atualiza a senha somente se foi preenchida
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE empresa SET nome=?, cpf_cnpj=?, email=?, senha=? WHERE id=?");
            $stmt->execute([$nome, $cpf_cnpj, $email, $senhaHash, $id]);
        } else {
            // Não altera a senha
            $stmt = $pdo->prepare("UPDATE empresa SET nome=?, cpfcnpj=?, email=? WHERE id=?");
            $stmt->execute([$nome, $cpf_cnpj, $email, $id]);
        }

        echo json_encode(["success" => true]);
        break;

    case 'delete':
        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(["error" => "ID é obrigatório"]);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM empresa WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(["success" => true]);
        break;

    default:
        echo json_encode(["error" => "Ação inválida"]);
        break;
}
