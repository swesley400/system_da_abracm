<?php

if (!isset($_SESSION["nome"]) && !isset($_SESSION["email"])) {
    
    header("Location: index.php");
    exit();
}

require "connection.php";

$conn = conectarAoBanco();

$stmt = $conn->prepare("SELECT * FROM tbUser INNER JOIN tbpermissiontype ON tbUser.permission_id = tbpermissiontype.permission_id");

$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['delete'])) {
    $conn = conectarAoBanco();
    $stmt = $conn->prepare("Update tbUser set ative = 0 where id=:usuario_id");
    $stmt->bindParam(':usuario_id', $_GET['delete']);

    try {
        $stmt->execute();
        header("Location: home.php?users=true");
        exit();
    } catch (Exception $err) {
        echo $err->getMessage();
    } 
}

if(!empty($_POST["email"]) && !empty($_POST["password"])) {
    
    $perm_value = $_POST["user_permission"] === "adm" ? 1 : 2;  

    salvaUsuario( $_POST["nome"], $_POST["email"], $_POST["password"], $perm_value);
}

function salvaUsuario($nome, $email, $senha, $permission_id) {
    $conn = conectarAoBanco();

    $stmt = $conn->prepare("INSERT INTO tbUser (nome, email, password_hash, permission_id) values (:nome, :email, :password, :permission_id)");

    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $senha);
    $stmt->bindParam(':permission_id', $permission_id);
    
    try {
        $stmt->execute();
        header("Location: home.php?users=true");
        exit();
    } catch (Exception $err) {
        echo $err->getMessage();
    } 
   
};

function montaform()
{
    $conn = conectarAoBanco();
    $stmt = $conn->prepare("SELECT * FROM tbpermissiontype;");

    $stmt->execute();

    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $textOptions = "<option default >Selecione um cargo</option>";

    foreach ($permissions as $permission) {
        $permissionName = $permission['permission_name'];
        $textOptions .= "<option value=\"$permissionName\">$permissionName</option>";
    }

    return "<select class='form-control m-1' name='user_permission' id='exampleFormControlSelect1'>" . $textOptions . "</select>";

};

?>

<div class="m-1 shadow p-3 mb-5 bg-white rounded w-auto">
    <?php
        function verificaStatus($status) {
            $textOptions = "";

            if($status === '1') {
                return $textOptions = '<span class="text-success">Ativo</span>';
            } else {
                return $textOptions = '<span class="text-danger">Deletado</span>';
            }
        }

        if (!empty($results)) {

            echo "
            <div>
                <h2 class='m-10'> Usuários </h2>
            </div>";

            echo '
            <div class="d-flex justify-content-between"> 
                <div  class="d-flex items-center">
                    <div>
                        <input type="search" class="form-control rounded" placeholder="Search" aria-label="Search" aria-describedby="search-addon" />
                    </div>
                    <div>
                    <span class="input-group-text border-0" id="search-addon">
                    <i class="bi bi-search"></i>
                        </span>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn btn-dark" data-toggle="modal" data-target="#exampleModal" id="novoUser">Novo Usuário</button>
                </div>
            </div>';

            echo '<div calss="border rounded-5">';

            echo '
            <table class="table align-middle mb-0 bg-white">
            <thead class="bg-light">
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nome</th>
                    <th scope="col">email</th>
                    <th scope="col">Status</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Actions</th>
                    </tr>
            </thead>';
        
            $users = array();
        
            foreach ($results as $user) {
                $userId = $user['id'];
        
        
                if (!isset($users[$userId])) {
                    $users[$userId] = $user;
                    $users[$userId]['roles'] = array();
                }
        
                $users[$userId]['roles'][] = $user['permission_name'];
            }
        
            foreach ($users as $user) {
                echo '
                    <tr>
                        <th class="fw-normal mb-1" scope="row">' . $user['id'] . '</th>
                        <th class="fw-normal mb-1"  scope="row">' . $user['nome'] . '</th>
                        <th class="fw-normal mb-1"  scope="row">' . $user['email'] . '</th>
                        <th class="fw-normal mb-1"  scope="row">' . verificaStatus($user['ative']) . '</th>
                        <th  class="fw-normal mb-1"  scope="row">' . implode(', ', $user['roles']) . '</th>
                        <th>
                            <div class="d-flex item-center">
                                <div class="p-1  cursor-pointer">
                                    <a class="btn btn-outline-primary" href="user.php?viewUser='. $user['id']. '"><i class="bi bi-eye"></i></a>
                                </div>
                                <div class="p-1 text-danger cursor-pointer">
                                    <a class="btn btn-outline-danger" href="user.php?delete='. $user['id'] .'"><i class="bi bi-trash"></i></a>
                                </div>
                            </div>
                        </th>
                    </tr>
                ';
            }
        
            echo '</table>';
            echo '</div>';

            echo '
            <div class="modal fade" id="exampleModal" tabindex="-1" role="document" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                <form action="user.php" method="POST" name="submit"> 
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Castra  um novo usuario</h5>
                            <button type="button" class="btn-close" aria-label="Close" id="close"></button>
                        </button>
                    </div>
                    <div class="modal-body form-group">
                            <input type="text" id="email" class="form-control m-1" name="nome" placeholder="Digite o nome do usuario">
                            <input type="text" id="email" class="form-control m-1" name="email" placeholder="Email">
                            <input type="text" id="password" class="form-control m-1" name="password" placeholder="Senha">
                            ' 
                .
                montaform()
                .
                '       
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal" id="cancel">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar dados</button>
                    </div>
                    </form>
                </div>
            </div>
            ';
        } else {
            echo '<span> Nao ha nada a ser exibido</span>';
        }
        ?>
    
    >

</div>

