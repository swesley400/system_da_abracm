<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        session_start();
        session_destroy();
        // Retorna uma resposta de sucesso
        echo json_encode(["success" => true]);

        // Redireciona para a página de login (ou qualquer outra página desejada)
        header("Location: index.php");
        exit();
    }
?>