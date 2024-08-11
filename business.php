<?php
require "connection.php";

$conn = conectarAoBanco();

function verificaStatus($status)
{
    return $status === '1' ? '<span class="text-success">Ativo</span>' : '<span class="text-danger">Deletado</span>';
}

// Exclusão de empresa
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("UPDATE tb_empresa SET ative = 0 WHERE empresa_id = :empresa_id");
    $stmt->bindParam(':empresa_id', $_GET['delete']);

    try {
        $stmt->execute();
        reload();
    } catch (Exception $err) {
        echo $err->getMessage();
    }
}

// Tratamento de busca
if (isset($_GET['query'])) {
    $query = $_GET['query'];
    $stmt = $conn->prepare("SELECT * FROM tb_empresa WHERE ative = 1 AND (NomeEmpresa LIKE :query OR SiteEmpresa LIKE :query)");
    $query = "%$query%";
    $stmt->bindParam(':query', $query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->prepare("SELECT * FROM tb_empresa WHERE ative = 1");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Adicionar ou atualizar empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST["nome_empresa"]) && !empty($_POST["site_empresa"])) {
        if (isset($_POST["empresa_id"]) && !empty($_POST["empresa_id"])) {
            atualizaEmpresa($_POST["empresa_id"], $_POST["numero_ans"], $_POST["nome_empresa"], $_POST["site_empresa"], $_POST["cidade"], $_POST["estado"]);
        } else {
            salvaEmpresa($_POST["numero_ans"], $_POST["nome_empresa"], $_POST["site_empresa"], $_POST["cidade"], $_POST["estado"]);
        }
    }
}

function salvaEmpresa($numero_ans, $nome_empresa, $site_empresa, $cidade, $estado)
{
    $conn = conectarAoBanco();

    $stmt = $conn->prepare("INSERT INTO tb_empresa (DataAtualizacao, NumeroANS, NomeEmpresa, SiteEmpresa, Cidade, Estado) VALUES (CURDATE(), :numero_ans, :nome_empresa, :site_empresa, :cidade, :estado)");

    $stmt->bindParam(':numero_ans', $numero_ans);
    $stmt->bindParam(':nome_empresa', $nome_empresa);
    $stmt->bindParam(':site_empresa', $site_empresa);
    $stmt->bindParam(':cidade', $cidade);
    $stmt->bindParam(':estado', $estado);

    try {
        $stmt->execute();
        reload();
    } catch (Exception $err) {
        echo $err->getMessage();
    }
}


function reload() {
    echo '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=home.php?empresas=true">';
}

function atualizaEmpresa($empresa_id, $numero_ans, $nome_empresa, $site_empresa, $cidade, $estado)
{
    $conn = conectarAoBanco();

    $stmt = $conn->prepare("UPDATE tb_empresa SET DataAtualizacao = CURDATE(), NumeroANS = :numero_ans, NomeEmpresa = :nome_empresa, SiteEmpresa = :site_empresa, Cidade = :cidade, Estado = :estado WHERE empresa_id = :empresa_id");

    $stmt->bindParam(':empresa_id', $empresa_id);
    $stmt->bindParam(':numero_ans', $numero_ans);
    $stmt->bindParam(':nome_empresa', $nome_empresa);
    $stmt->bindParam(':site_empresa', $site_empresa);
    $stmt->bindParam(':cidade', $cidade);
    $stmt->bindParam(':estado', $estado);

    try {
        $stmt->execute();
        header("Location: home.php?empresas=true");
        exit();
    } catch (Exception $err) {
        echo $err->getMessage();
    }
}
?>

<div class="m-1 shadow p-3 mb-5 bg-white rounded w-auto">
    <?php
    echo "<div><h2 class='m-10'> Empresas Associadas </h2></div>";

    echo '
    <div class="d-flex justify-content-between"> 
        <div class="d-flex items-center">
            <form action="home.php" method="GET" class="d-flex items-center">
                <input type="hidden" name="empresas" value="true">
                <input type="search" id="searchEmpresa" name="query" class="form-control rounded m-1" placeholder="Search" aria-label="Search" aria-describedby="search-addon" />
                <button type="submit" class="btn btn-primary m-1">Pesquisar</button>
            </form>
        </div>
        <div>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#novaEmpresaModal" id="novaEmpresaBtn">Nova Empresa</button>
        </div>
    </div>';

    echo '
    <div class="modal fade" id="novaEmpresaModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="business.php" method="POST"> 
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cadastrar uma nova empresa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body form-group">
                        <input type="hidden" name="empresa_id" id="empresa_id">
                        <input type="text" class="form-control m-1" name="numero_ans" id="numero_ans" placeholder="Nº ANS">
                        <input type="text" class="form-control m-1" name="nome_empresa" id="nome_empresa" placeholder="Nome da Empresa">
                        <input type="text" class="form-control m-1" name="site_empresa" id="site_empresa" placeholder="Site da Empresa">
                        <input type="text" class="form-control m-1" name="cidade" id="cidade" placeholder="Cidade da Empresa">
                        <input type="text" class="form-control m-1" name="estado" id="estado" placeholder="Estado da Empresa">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar dados</button>
                    </div>
                </div>
            </form>
        </div>
    </div>';

    if (!empty($results)) {
        echo '<div class="border rounded-5 table-responsive">';
        echo '
        <table class="table align-middle mb-0 bg-white">
            <thead class="bg-light">
                <tr>
                    <th scope="col">Numero ANS</th>
                    <th scope="col">Nome</th>
                    <th scope="col">Site da empresa</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody id="empresasTable">';

        foreach ($results as $empresa) {
            echo '
                <tr>
                    <th class="fw-normal mb-1" scope="row">' . htmlspecialchars($empresa['NumeroANS']) . '</th>
                    <th class="fw-normal mb-1" scope="row">' . htmlspecialchars($empresa['NomeEmpresa']) . '</th>
                    <th class="fw-normal mb-1" scope="row">' . htmlspecialchars($empresa['SiteEmpresa']) . '</th>
                    <th class="fw-normal mb-1" scope="row">' . verificaStatus($empresa['ative']) . '</th>  
                    <th>
                        <div class="d-flex item-center"> 
                            <div class="p-1 text-danger cursor-pointer">
                                <a class="btn btn-outline-danger" href="business.php?delete=' . urlencode($empresa['empresa_id']) . '"><i class="bi bi-trash"></i></a>
                            </div>
                            <div class="p-1 text-warning cursor-pointer">
                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#novaEmpresaModal" data-id="' . htmlspecialchars($empresa['empresa_id']) . '" data-numeroans="' . htmlspecialchars($empresa['NumeroANS']) . '" data-nome="' . htmlspecialchars($empresa['NomeEmpresa']) . '" data-site="' . htmlspecialchars($empresa['SiteEmpresa']) . '" data-cidade="' . htmlspecialchars($empresa['Cidade']) . '" data-estado="' . htmlspecialchars($empresa['Estado']) . '"><i class="bi bi-pencil"></i></button>
                            </div>
                        </div>
                    </th>
                </tr>';
        }

        echo '</tbody></table></div>';
    }
    ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const empresaModal = document.getElementById('novaEmpresaModal');
        empresaModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const empresaId = button.getAttribute('data-id');
            const numeroAns = button.getAttribute('data-numeroans');
            const nomeEmpresa = button.getAttribute('data-nome');
            const siteEmpresa = button.getAttribute('data-site');
            const cidade = button.getAttribute('data-cidade');
            const estado = button.getAttribute('data-estado');

            document.getElementById('empresa_id').value = empresaId || '';
            document.getElementById('numero_ans').value = numeroAns || '';
            document.getElementById('nome_empresa').value = nomeEmpresa || '';
            document.getElementById('site_empresa').value = siteEmpresa || '';
            document.getElementById('cidade').value = cidade || '';
            document.getElementById('estado').value = estado || '';
        });
    });
</script>
