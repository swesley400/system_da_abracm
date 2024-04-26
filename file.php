<?php
$permission = $_SESSION["permission_type"];

if (!isset($_SESSION["nome"]) && !isset($_SESSION["email"])) {

    header("Location: index.php");
    exit();
}

require "connection.php";

$conn = conectarAoBanco();

if($permission = $_SESSION["permission_type"] === "adm") {
    $stmt = $conn->prepare("SELECT * FROM tbachive ;");
} else {
    $stmt = $conn->prepare("SELECT * FROM tbachive where deleted = 1;");
}


$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['delete'])) {
    $conn = conectarAoBanco();
    $stmt = $conn->prepare("update tbachive set deleted=0 where achive_id=:achive_id");
    $stmt->bindParam(':achive_id', $_GET['delete']);

    try {
        $stmt->execute();
        header("Location: home.php?files=true");
        exit();
    } catch (Exception $err) {
        echo $err->getMessage();
    }
}



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
}


if (isset($_GET['downloadFile'])) {
    $filename = $_GET['downloadFile'];

    // Verifique a existência do arquivo
    if (file_exists($filename)) {
        // Configurar cabeçalhos para download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filename));

        // Ler e enviar o conteúdo do arquivo
        readfile($filename);

        exit;
    } else {
        echo "O arquivo não existe.";
    }
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conexão com o banco de dados
    $conn = conectarAoBanco();

    // Recupera os dados do formulário
    $documentNumber = $_POST["documentNumber"];

    // Configurações do upload
    $uploadDirectory = "./public/";
    $uploadedFile = $_FILES["customFile"]["tmp_name"];
    $originalFileName = $_FILES["customFile"]["name"];
    $newFileName = uniqid() . '_' . $originalFileName;
    $destination = $uploadDirectory . $newFileName;
    $id = $_SESSION["id_user"];



    // Move o arquivo para a pasta de destino
    if (move_uploaded_file($uploadedFile, $destination)) {
        try {
            // Prepara a consulta SQL usando um prepared statement
            $stmt = $conn->prepare("INSERT INTO tbAchive (achive_name, id_externo,id_user) VALUES (:newFileName,:id_externo, :id_user)");
            $stmt->bindParam(':newFileName', $newFileName);
            $stmt->bindParam(':id_externo', $documentNumber);
            $stmt->bindParam(':id_user', $id);

            // Executa a consulta
            $stmt->execute();

            header("Location: home.php?files=true");
            exit();
        } catch (PDOException $e) {
            echo "Erro ao salvar no banco de dados: " . $e->getMessage();
        }
    } else {
        echo "Erro ao fazer o upload do arquivo.";
    }

    // Fecha a conexão com o banco de dados
    $conn = null;
}

?>



<div class="m-1 shadow p-3 mb-5 bg-white rounded w-auto">
    <?php

    function verificaStatus($status)
    {
        $textOptions = "";

        if ($status === '1') {
            return $textOptions = '<span class="text-success">Ativo</span>';
        } else {
            return $textOptions = '<span class="text-danger">Deletado</span>';
        }
    }

    if (!empty($results)) {

        echo "
        <div>
            <h2 class='m-10'> Arquivos </h2>
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
            </div>';
        
        if ($_SESSION["permission_type"] === "adm") {
            echo '<div>
                <button type="button" class="btn btn btn-dark" data-toggle="modal" data-target="#achiveModal" id="novoArquivo">Novo Arquivo</button>
            </div>';
        }
    
        echo '</div>';

        echo '<div calss="border rounded-5">';

        echo '
        <table class="table align-middle mb-0 bg-white">
        <thead class="bg-light">
                <tr>
                <th scope="col">#</th>
                <th scope="col">N° do documento</th>
                <th scope="col">Nome do documento</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
                </tr>
        </thead>';

        $files = array();

        foreach ($results as $file) {
            $fileId = $file['achive_id'];


            if (!isset($files[$fileId])) {
                $files[$fileId] = $file;
                $files[$fileId]['roles'] = array();
            }

            $files[$fileId]['roles'][] = $file['achive_name'];
        }
        foreach ($files as $file) {
            echo '
                <tr>
                    <th class="fw-normal mb-1" scope="row">' . $file['achive_id'] . '</th>
                    <th class="fw-normal mb-1" scope="row">' . $file['id_externo'] . '</th>
                    <th class="fw-normal mb-1" scope="row">' . $file['achive_name'] . '</th>
                    <th class="fw-normal mb-1" scope="row">' . verificaStatus($file['deleted']) . '</th>
                    <th>
                        <div class="d-flex item-center">
                            <div class="p-1  cursor-pointer">
                                <a class="btn btn-outline-primary" href="./public/' . $file['achive_name'] . ' " target="_blank"><i class="bi bi-eye"></i></a>
                            </div>';

            if ($_SESSION["permission_type"] === "adm") {
                echo '<div class="p-1 text-danger cursor-pointer">
                                <a class="btn btn-outline-danger" href="file.php?delete=' . $file['achive_id'] . '"><i class="bi bi-trash"></i></a>
                            </div>';
            }
            if ($_SESSION["permission_type"] === "adm") {
                echo '
                            <div>
                                <a class="btn btn-outline-success download-file-link m-1" download href="' . $file['achive_name'] . '">
                                    <i class="bi bi-cloud-download"></i>
                                </a>
                            </div>';
            }

            echo '
                        </div>
                    </th>
                </tr>
            ';
        }

        echo '
            <div class="modal fade" id="achiveModal" tabindex="-1" role="document" aria-labelledby="achiveModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                <form action="" method="POST" name="submit" enctype="multipart/form-data"> 
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cadastrar um novo arquivo</h5>
                            <button type="button" class="btn-close" aria-label="Close" id="closeModalAchives"></button>
                        </button>
                    </div>
                    <div class="modal-body form-group">
                            <label class="form-label m-1" for="customFile">Selecione um arquivo</label>
                            <input type="file" class="form-control m-1" id="customFile" name="customFile" /> <!-- Adicione o atributo name="customFile" -->
                            <label class="form-label m-1" for="documentNumber">N* do documento</label>
                            <input type="text" id="documentNumber" name="documentNumber" class="form-control m-1" placeholder="Numero do documento">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal" id="modalCancelAchives">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar dados</button>
                    </div>
                    </form>
                </div>
            </div>
            ';
    }
    ?>
</div>