<?php

namespace App\Controller;

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$codigo = $data['codigo'] ?? '';
$senha = $data['senha'] ?? '';

if (!$codigo || !$senha) {
    echo json_encode(['success' => false, 'message' => 'Código ou senha não informados']);
    exit;
}

// Aqui você validaria o código
$emailFiles = glob("codigo_*.txt");
$valid = false;
foreach ($emailFiles as $file) {
    if (trim(file_get_contents($file)) === $codigo) {
        $valid = true;
        unlink($file); // remove o código após validação
        break;
    }
}

if ($valid) {
    // Aqui você atualizaria a senha no banco
    echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Código inválido']);
}
