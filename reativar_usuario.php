<?php
require 'connection.php';

$conn = conectarAoBanco();

if (isset($_GET['reativar'])) {
    $userId = intval($_GET['reativar']);
    
    $stmt = $conn->prepare("UPDATE tbuser SET ative = 1 WHERE id = ?");
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Falha ao reativar o usuário.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido.']);
}
