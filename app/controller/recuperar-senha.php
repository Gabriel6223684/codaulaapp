<?php

namespace App\Controller;

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email não informado']);
    exit;
}

// Simula envio de código
$codigo = rand(100000, 999999);
file_put_contents("codigo_$email.txt", $codigo); // guarda o código temporariamente

// Aqui você integraria com mail() ou SMTP para enviar o código
// mail($email, "Seu código de recuperação", "Código: $codigo");

echo json_encode(['success' => true, 'message' => "Código enviado para $email"]);