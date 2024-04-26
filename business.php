<?php

// if (!isset($_SESSION["nome"]) && !isset($_SESSION["email"])) {
//     header("Location: index.php");
//     exit();
// }

require "connection.php";

$conn = conectarAoBanco();

$stmt = $conn->prepare("SELECT * FROM tb_empresa");

$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['delete'])) {
    $conn = conectarAoBanco();
    $stmt = $conn->prepare("UPDATE tb_empresa SET ative = 0 WHERE empresa_id=:empresa_id");
    $stmt->bindParam(':empresa_id', $_GET['delete']);

    try {
        $stmt->execute();
        header("Location: home.php?empresas=true");
        exit();
    } catch (Exception $err) {
        echo $err->getMessage();
    }
}

if (!empty($_POST["nome_empresa"]) && !empty($_POST["site_empresa"])) {
    salvaEmpresa($_POST["numero_ans"], $_POST["nome_empresa"], $_POST["site_empresa"], $_POST["cidade"], $_POST["estado"]);
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
        header("Location: home.php?empresas=true");
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

    echo "
    <div>
        <h2 class='m-10'> Empresas Associadas </h2>
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
            <button type="button" class="btn btn btn-dark" data-toggle="modal" data-target="#novaEmpresaModal" id="novoUser">Nova Empresa</button>
        </div>
    </div>';

    echo '
            <div class="modal fade" id="novaEmpresaModal" tabindex="-1" role="document" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form action="business.php" method="POST" name="submit"> 
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Cadastrar uma nova empresa</h5>
                                <button type="button" class="btn-close" aria-label="Close" id="close"></button>
                            </div>
                            <div class="modal-body form-group">
                                <input type="text" class="form-control m-1" name="numero_ans" placeholder="Nº ANS">
                                <input type="text" class="form-control m-1" name="nome_empresa" placeholder="Nome da Empresa">
                                <input type="text" class="form-control m-1" name="site_empresa" placeholder="Site da Empresa">
                                <input type="text" class="form-control m-1" name="cidade" placeholder="Cidade da Empresa">
                                <input type="text" class="form-control m-1" name="estado" placeholder="Estado da Empresa">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal" id="cancel">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar dados</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        ';

    if (!empty($results)) {

        echo '<div calss="border rounded-5">';

        echo '
            <table class="table align-middle mb-0 bg-white">
            <thead class="bg-light">
                    <tr>
                    <th scope="col">Numero ANS</th>
                    <th scope="col">Nome</th>
                    <th scope="col">Site da empresa</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                    </tr>
            </thead>';

        $empresas = array();

        foreach ($results as $empresa) {
            $empresaId = $empresa['NumeroANS']; 
            if (!isset($empresas[$empresaId])) {
                $empresas[$empresaId] = $empresa;
                $empresas[$empresaId]['roles'] = array();
            }
        
            $empresas[$empresaId]['roles'][] = $empresa['NumeroANS']; 
        }
        
        foreach ($empresas as $empresa) {
            echo '
                <tr>
                    <th class="fw-normal mb-1" scope="row">' . $empresa['NumeroANS'] . '</th>
                    <th class="fw-normal mb-1"  scope="row">' . $empresa['NomeEmpresa'] . '</th>
                    <th class="fw-normal mb-1"  scope="row">' . $empresa['SiteEmpresa'] . '</th>
                    <th class="fw-normal mb-1"  scope="row">' . verificaStatus($empresa['ative']) . '</th>  
                    <th>
                        <div class="d-flex item-center"> 
                            <div class="p-1 text-danger cursor-pointer">
                                <a class="btn btn-outline-danger" href="business.php?delete=' . $empresa['empresa_id'] . '"><i class="bi bi-trash"></i></a> 
                            </div>
                        </div>
                    </th>
                </tr>
            ';
        }

        echo '</table>';
        echo '</div>';

        echo '
            <div class="modal fade" id="novaEmpresaModal" tabindex="-1" role="document" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form action="business.php" method="POST" name="submit"> 
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Cadastrar uma nova empresa</h5>
                                <button type="button" class="btn-close" aria-label="Close" id="close"></button>
                            </div>
                            <div class="modal-body form-group">
                                <input type="text" class="form-control m-1" name="numero_ans" placeholder="Nº ANS">
                                <input type="text" class="form-control m-1" name="nome_empresa" placeholder="Nome da Empresa">
                                <input type="text" class="form-control m-1" name="site_empresa" placeholder="Site da Empresa">
                                <input type="text" class="form-control m-1" name="cidade" placeholder="Cidade da Empresa">
                                <input type="text" class="form-control m-1" name="estado" placeholder="Estado da Empresa">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal" id="cancel">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar dados</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        ';
    } else {
        echo '<span> Nao ha nada a ser exibido</span>';
    }
    ?>
</div>