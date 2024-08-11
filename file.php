<?php
// session_start(); // Certifique-se de iniciar a sessão

$permission = $_SESSION["permission_type"] ?? '';

if (!isset($_SESSION["nome"]) || !isset($_SESSION["email"])) {
    header("Location: index.php");
    exit();
}

require "connection.php";
$conn = conectarAoBanco();

$searchAchiveName = $_GET['search_achive_name'] ?? '';
$searchIdExterno = $_GET['search_id_externo'] ?? '';

if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);

    $stmt = $conn->prepare("UPDATE tbachive SET deleted = 0 WHERE achive_id = :achive_id");
    $stmt->bindParam(':achive_id', $deleteId, PDO::PARAM_INT);

    try {
        $stmt->execute();
        reload();
    } catch (Exception $err) {
        echo "Erro: " . htmlspecialchars($err->getMessage());
    }
}

$sql = $permission === "adm" ? "SELECT * FROM tbachive WHERE 1=1" : "SELECT * FROM tbachive WHERE deleted = 1";

if (!empty($searchAchiveName)) {
    $sql .= " AND achive_name LIKE :achive_name";
}
if (!empty($searchIdExterno)) {
    $sql .= " AND id_externo = :id_externo";
}

if (!isset($_GET['include_deleted'])) {
    $sql .= " AND tbachive.deleted = 1";
}

$stmt = $conn->prepare($sql);

if (!empty($searchAchiveName)) {
    $stmt->bindValue(':achive_name', '%' . $searchAchiveName . '%');
}
if (!empty($searchIdExterno)) {
    $stmt->bindValue(':id_externo', $searchIdExterno, PDO::PARAM_INT);
}

$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

function montaform()
{
    $conn = conectarAoBanco();
    $stmt = $conn->prepare("SELECT * FROM tbpermissiontype;");
    $stmt->execute();
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $textOptions = "<option default>Selecione um cargo</option>";

    foreach ($permissions as $permission) {
        $permissionName = htmlspecialchars($permission['permission_name']);
        $textOptions .= "<option value=\"$permissionName\">$permissionName</option>";
    }

    return "<select class='form-control m-1' name='user_permission' id='exampleFormControlSelect1'>" . $textOptions . "</select>";
}


function reload() {
    echo '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=home.php?files=true">';
}

if (isset($_GET['downloadFile'])) {
    $fileName = urldecode($_GET['downloadFile']);
    $filePath = __DIR__ . '/public/' . $fileName;

    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "Arquivo não encontrado.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $documentNumber = $_POST["documentNumber"] ?? '';

    $uploadDirectory = "./public/";
    $uploadedFile = $_FILES["customFile"]["tmp_name"] ?? '';
    $originalFileName = $_FILES["customFile"]["name"] ?? '';
    $newFileName = uniqid() . '_' . $originalFileName;
    $destination = $uploadDirectory . $newFileName;
    $id = $_SESSION["id_user"] ?? 0;

    if (move_uploaded_file($uploadedFile, $destination)) {
        try {
            $stmt = $conn->prepare("INSERT INTO tbAchive (achive_name, id_externo, id_user) VALUES (:newFileName, :id_externo, :id_user)");
            $stmt->bindParam(':newFileName', $newFileName);
            $stmt->bindParam(':id_externo', $documentNumber);
            $stmt->bindParam(':id_user', $id, PDO::PARAM_INT);

            $stmt->execute();

            reload();
        } catch (PDOException $e) {
            echo "Erro ao salvar no banco de dados: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "Erro ao fazer o upload do arquivo.";
    }

    $conn = null;
}
?>

<div class="m-1 shadow p-3 mb-5 bg-white rounded w-auto">
    <?php
    function verificaStatus($status)
    {
        return $status === '1' ? '<span class="text-success">Ativo</span>' : '<span class="text-danger">Deletado</span>';
    }

    echo "
    <div>
        <h2 class='m-10'>Arquivos</h2>
    </div>";

    echo '
    <div class="d-flex justify-content-between"> 
        <div class="d-flex items-center">
            <!-- Formulário de Pesquisa -->
            <form method="GET" action="home.php" class="form-inline d-flex">
                <input type="hidden" name="files" value="true">
                <input type="text" name="search_achive_name" class="form-control m-1" placeholder="Nome do Arquivo" value="' . htmlspecialchars($searchAchiveName) . '">
                <input type="text" name="search_id_externo" class="form-control m-1" placeholder="Nome/Indentificao" value="' . htmlspecialchars($searchIdExterno) . '">';
    if ($_SESSION["permission_type"] === "adm") {
        echo '
                <div class="form-check m-1 d-flex align-items-center">
                    <input type="checkbox" class="form-check-input" name="include_deleted" id="include_deleted" ' . (isset($_GET['include_deleted']) ? 'checked' : '') . '>
                    <label class="form-check-label m-1" for="include_deleted">Deletados</label>
                </div>';
    }
    echo '
                <button type="submit" class="btn btn-primary m-1">Pesquisar</button>
            </form>
    </div>';
    if ($_SESSION["permission_type"] === "adm") {
        echo '<div>
            <button type="button" class="btn btn-dark" data-toggle="modal" data-target="#achiveModal" id="novoArquivo">Novo Arquivo</button>
        </div>';
    }

    echo '</div>';

    if (!empty($results)) {
        echo '<div class="table-responsive">';
        echo '
        <table class="table align-middle mb-0 bg-white">
        <thead class="bg-light">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nome ou Indentificação</th>
                <th scope="col">Nome do arquivo</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>';

        $files = [];

        foreach ($results as $file) {
            $fileId = $file['achive_id'];

            if (!isset($files[$fileId])) {
                $files[$fileId] = $file;
                $files[$fileId]['roles'] = [];
            }

            $files[$fileId]['roles'][] = $file['achive_name'];
        }

        foreach ($files as $file) {
            echo '
                <tr>
                    <th class="fw-normal mb-1" scope="row">' . htmlspecialchars($file['achive_id']) . '</th>
                    <th class="fw-normal mb-1" scope="row">' . htmlspecialchars($file['id_externo']) . '</th>
                    <th class="fw-normal mb-1" scope="row">' . htmlspecialchars($file['achive_name']) . '</th>
                    <th class="fw-normal mb-1" scope="row">' . verificaStatus($file['deleted']) . '</th>
                    <th>
                        <div class="d-flex item-center">
                            <div class="p-1 cursor-pointer">
                                <a class="btn btn-outline-primary" href="./public/' . htmlspecialchars($file['achive_name']) . '" target="_blank"><i class="bi bi-eye"></i></a>
                            </div>';

            if ($_SESSION["permission_type"] === "adm") {
                echo '<div class="p-1 text-danger cursor-pointer">
                                <a class="btn btn-outline-danger" href="home.php?files=true&delete=' . htmlspecialchars($file['achive_id']) . '"><i class="bi bi-trash"></i></a>
                            </div>';
                echo '<div class="p-1 text-primary cursor-pointer">
                                <a class="btn btn-outline-primary" href="public/' . urlencode($file['achive_name']) . '" download>
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>';
            }

            echo '</div></th></tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '
            <div class="modal fade" id="achiveModal" tabindex="-1" role="document" aria-labelledby="achiveModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                <form action="" method="POST" name="submit" enctype="multipart/form-data"> 
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cadastrar um novo arquivo</h5>
                    </div>
                    <div class="modal-body">
                        <label for="documentNumber">Nome ou Indentifição:</label>
                        <input type="text" class="form-control" name="documentNumber" id="documentNumber" required>
                        <label for="customFile">Escolha o arquivo:</label>
                        <input type="file" class="form-control" id="customFile" name="customFile" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="close-modal-novo-arquivo">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                    </div>
                </form>
                </div>
            </div>';
    } else {
        echo '
            <div class="modal fade" id="achiveModal" tabindex="-1" role="document" aria-labelledby="achiveModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                <form action="" method="POST" name="submit" enctype="multipart/form-data"> 
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cadastrar um novo arquivo</h5>
                    </div>
                    <div class="modal-body">
                        <label for="documentNumber">Nome ou Indentifição:</label>
                        <input type="text" class="form-control" name="documentNumber" id="documentNumber" required>
                        <label for="customFile">Escolha o arquivo:</label>
                        <input type="file" class="form-control" id="customFile" name="customFile" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="close-modal-novo-arquivo-2">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                    </div>
                </form>
                </div>
            </div>';
        echo "<p>Nenhum arquivo encontrado.</p>";
    }
    ?>
</div>