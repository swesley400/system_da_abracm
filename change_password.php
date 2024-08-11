<?php
session_start();
require "connection.php";
$conn = conectarAoBanco();

$response = ['success' => false, 'message' => ''];
$ID_USER = $_SESSION["id_user"] ?? 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmNewPassword = $_POST['confirmNewPassword'] ?? '';

    if ($ID_USER == 0) {
        $response['message'] = 'Usuário não autenticado.';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    if ($newPassword !== $confirmNewPassword) {
        $response['message'] = 'A nova senha e a confirmação da nova senha não coincidem.';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    try {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT password_hash FROM tbuser WHERE id = :id");
        $stmt->bindParam(':id', $ID_USER, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || $currentPassword !== $result['password_hash']) {
            $response['message'] = 'Senha atual incorreta.';
            $response['query'] = "SELECT password_hash FROM tbuser WHERE id = :id";
            $response['userData'] = $result;
            $response['currentPassword'] = $currentPassword;
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        $stmt = $conn->prepare("UPDATE tbuser SET password_hash = :password WHERE id = :id");
        $stmt->bindParam(':password', $newPassword);
        $stmt->bindParam(':id', $ID_USER, PDO::PARAM_INT);
        $stmt->execute();

        $response['success'] = true;
        $response['message'] = 'Senha alterada com sucesso.';
    } catch (PDOException $e) {
        $response['message'] = 'Erro ao acessar o banco de dados: ' . $e->getMessage();
    }

    $conn = null;
} else {
    $response['message'] = 'Método de solicitação inválido.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
