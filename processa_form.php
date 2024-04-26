<?php
require "connection.php";

$conn = conectarAoBanco();

if (!empty($_POST["login"]) && !empty($_POST["password"])) {
    $stmt = $conn->prepare("SELECT * FROM tbUser inner join tbpermissiontype on tbpermissiontype.permission_id = tbuser.permission_id  WHERE email = :login and password_hash = :password");
    $stmt->bindParam(':login', $_POST["login"]);
    $stmt->bindParam(':password', $_POST["password"]);

    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['ative']) {

        session_start();

        $_SESSION['nome'] = $result['nome'];

        $_SESSION["email"] = $result['email'];
        $_SESSION["id_user"] = $result['id'];

        $_SESSION["permission_type"] = $result['permission_name'];

        $_SESSION["newsession"] = "logged";


        echo  $_SESSION["permission_type"] ;

        echo $_SESSION["permission_type"];
        echo $result['permission_name'];
       
        header("Location: home.php?files=true");
        exit();


    } else {
        header("Location: index.php?erro=1");
        exit();
    }
}
?>

