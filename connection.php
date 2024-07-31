<?php
function conectarAoBanco() {
    $host = "";
    $dbname = ""; 
    $username = "";
    $password = "";
    
    try {
        $con = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $con->exec("SET NAMES 'utf8'");
        return $con;

    } catch (PDOException $e) {
        header("Location: index.php?erro=1");
        exit();
    }
}