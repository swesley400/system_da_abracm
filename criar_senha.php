<?php
require 'connection.php';

$conn = conectarAoBanco();

function gerarSenha($tamanho = 6) {
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $senha = '';
    for ($i = 0; $i < $tamanho; $i++) {
        $senha .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }
    return $senha;
}

if (isset($_GET['userId'])) {
    $userId = intval($_GET['userId']);

    $novaSenha = gerarSenha();
    $passwordHash = $novaSenha;

    $stmt = $conn->prepare("UPDATE tbuser SET password_hash = ? WHERE id = ?");
    $stmt->execute([$passwordHash, $userId]);

    if ($stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'password' => $novaSenha]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Falha ao atualizar a senha.']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido.']);
}
