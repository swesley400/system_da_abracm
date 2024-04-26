<?php
function conectarAoBanco() {
    $host = "";
    $dbname = ""; 
    $username = "";
    $password = "";
    
    try {
        $con = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        // Configurar o PDO para mostrar exceções em caso de erro
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $con;
    } catch (PDOException $e) {
        header("Location: index.php?erro=1");
        exit();
    }
}