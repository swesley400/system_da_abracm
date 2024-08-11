<?php
// session_start();

if (!isset($_SESSION["nome"]) && !isset($_SESSION["email"])) {
    header("Location: index.php");
    exit();
}

require "connection.php";

$conn = conectarAoBanco();

// Inicializa a consulta SQL básica
$sql = "SELECT * FROM tbUser INNER JOIN tbpermissiontype ON tbUser.permission_id = tbpermissiontype.permission_id ";



if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("UPDATE tbUser SET ative = 0 WHERE id = :usuario_id");
    $stmt->bindParam(':usuario_id', $_GET['delete']);

    try {
        $stmt->execute();
        reload();
    } catch (Exception $err) {
        echo $err->getMessage();
    }
}

if (!empty($_GET['search_name'])) {
    $searchName = $_GET['search_name'];
    $sql .= " WHERE tbUser.nome LIKE :search_name";
}

if (!isset($_GET['include_deleted'])) {
    $sql .= " AND tbUser.ative = 1";
}

$stmt = $conn->prepare($sql);

// Se houver um valor de pesquisa, vincula o parâmetro
if (!empty($_GET['search_name'])) {
    $stmt->bindValue(':search_name', '%' . $searchName . '%', PDO::PARAM_STR);
}

$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($_POST["email"]) && !empty($_POST["password"])) {
    $perm_value = $_POST["user_permission"];
    salvaUsuario($_POST["nome"], $_POST["email"], $_POST["password"], $perm_value);
}

if (!empty($_POST["edit_user_id"])) {
    $userId = $_POST["edit_user_id"];
    $userName = $_POST["edit_nome"];
    $userEmail = $_POST["edit_email"];
    $perm_value = $_POST["user_permission"];
    editaUsuario($userId, $userName, $userEmail, $perm_value);
}

// Função para gerar uma senha aleatória de 6 caracteres maiúsculos
function gerarSenha($tamanho = 6)
{
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $senha = '';
    for ($i = 0; $i < $tamanho; $i++) {
        $senha .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }
    return $senha;
}

function reload()
{
    echo '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=home.php?users=true">';
}

function salvaUsuario($nome, $email, $senha, $permission_id)
{
    $conn = conectarAoBanco();
    $stmt = $conn->prepare("INSERT INTO tbUser (nome, email, password_hash, permission_id) VALUES (:nome, :email, :password, :permission_id)");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $senha);
    $stmt->bindParam(':permission_id', $permission_id);

    try {
        $stmt->execute();
        reload();
    } catch (Exception $err) {
        echo $err->getMessage();
    }
}

function editaUsuario($userId, $userName, $userEmail, $userPermission)
{
    echo $userPermission;
    $conn = conectarAoBanco();
    $stmt = $conn->prepare("UPDATE tbUser SET nome = :nome, email = :email, permission_id = :permission_id WHERE id = :id");
    $stmt->bindParam(':id', $userId);
    $stmt->bindParam(':nome', $userName);
    $stmt->bindParam(':email', $userEmail);
    $stmt->bindParam(':permission_id', $userPermission);

    try {
        $stmt->execute();
        reload();
    } catch (Exception $err) {
        echo $err->getMessage();
    }
}

function montaform($selectedPermission = null)
{
    $conn = conectarAoBanco();
    $stmt = $conn->prepare("SELECT * FROM tbpermissiontype;");
    $stmt->execute();
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $textOptions = "<option default>Selecione um cargo</option>";
    foreach ($permissions as $permission) {
        $permissionId = $permission['permission_id'];
        $permissionName = $permission['permission_name'];
        $selected = ($permissionId == $selectedPermission) ? "selected" : "";
        $textOptions .= "<option value=\"$permissionId\" $selected>$permissionName</option>";
    }
    return "<select class='form-control m-1' name='user_permission' id='exampleFormControlSelect1'>" . $textOptions . "</select>";
}

function verificaStatus($status)
{
    if ($status === '1') {
        return '<span class="text-success">Ativo</span>';
    } else {
        return '<span class="text-danger">Deletado</span>';
    }
}
?>


<div class="m-1 shadow p-3 mb-5 bg-white rounded w-auto">
    <div>
        <h2 class='m-10'> Usuários </h2>
    </div>

    <div class="d-flex justify-content-between ">
        <div class="d-flex items-center">
            <form method="GET" action="home.php" class="form-inline">
                <div class="d-flex align-items-center">
                    <input type="hidden" name="users" value="true">
                    <input type="text" name="search_name" class="form-control m-1" placeholder="Nome do Usuário"
                        value="<?php echo htmlspecialchars($_GET['search_name'] ?? ''); ?>">
                    <?php if ($_SESSION["permission_type"] === "adm"): ?>
                        <div class="form-check m-1 d-flex align-items-center">
                            <input type="checkbox" class="form-check-input" name="include_deleted" id="include_deleted"
                                <?php echo isset($_GET['include_deleted']) ? 'checked' : ''; ?>>
                            <label class="form-check-label m-1" for="include_deleted">Deletados</label>
                        </div>
                    <?php endif; ?>
                    <div>
                        <button type="submit" class="btn btn-primary m-1">Pesquisar</button>
                    </div>
                </div>

            </form>
        </div>
        <div>
            <button type="button" class="btn btn-dark" data-toggle="modal" data-target="#exampleModal"
                id="novoUser">Novo Usuário</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 bg-white">
            <thead class="bg-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nome</th>
                    <th scope="col">Email</th>
                    <th scope="col">Status</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($results)) {
                    $users = array();

                    foreach ($results as $user) {
                        $userId = $user['id'];

                        if (!isset($users[$userId])) {
                            $users[$userId] = $user;
                            $users[$userId]['roles'] = array();
                        }

                        $users[$userId]['roles'][] = $user['permission_id'];
                    }

                    foreach ($users as $user) {
                        echo '
                            <tr>
                                <th class="fw-normal mb-1" scope="row">' . $user['id'] . '</th>
                                <th class="fw-normal mb-1" scope="row">' . $user['nome'] . '</th>
                                <th class="fw-normal mb-1" scope="row">' . $user['email'] . '</th>
                                <th class="fw-normal mb-1" scope="row">' . verificaStatus($user['ative']) . '</th>
                                <th class="fw-normal mb-1" scope="row">' . $user['permission_name'] . '</th>
                                <th>
                                    <div class="d-flex item-center">
                                        <div class="p-1 cursor-pointer">
                                            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#editUserModal" data-id="' . $user['id'] . '" data-nome="' . $user['nome'] . '" data-email="' . $user['email'] . '" data-permission="' . implode(', ', $user['roles']) . '"><i class="bi bi-pencil"></i></button>
                                        </div>
                                        <div class="p-1 text-danger cursor-pointer">
                                            <a class="btn btn-outline-danger" href="home.php?users=true&delete=' . $user['id'] . '"><i class="bi bi-trash"></i></a>
                                        </div>
                                        <div class="p-1 cursor-pointer">
                                            <button class="btn btn-outline-secondary" title="Gerar nova senha" onclick="gerarSenha(' . $user['id'] . ')"><i class="bi bi-key"></i></button>
                                        </div>';

                        if ($user['ative'] == 0) { // Se o usuário estiver desativado, mostre o ícone de reativar
                            echo '
                                        <div class="p-1 cursor-pointer">
                                            <button class="btn btn-outline-success" title="Reativar usuário" onclick="reativarUsuario(' . $user['id'] . ')"><i class="bi bi-arrow-clockwise"></i></button>
                                        </div>';
                        }

                        echo '
                                    </div>
                                </th>
                            </tr>
                        ';
                    }
                } else {
                    echo '
                        <tr>
                            <td colspan="6" class="text-center">Nenhum usuário encontrado</td>
                        </tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para mostrar a nova senha -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">Nova Senha</h5>
                </div>
                <div class="modal-body">
                    <p>Senha gerada:</p>
                    <div class="input-group">
                        <input type="text" class="form-control" id="new_password" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="copyPasswordButton">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        id="close-modal-senha">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Cadastrar Usuário -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="document" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Cadastro de Usuário</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
                <div class="modal-body">
                    <form method="post" action="home.php?users=true">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Nome</label>
                            <input type="text" class="form-control m-1" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Email</label>
                            <input type="email" class="form-control m-1" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Senha</label>
                            <input type="password" class="form-control m-1" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="exampleFormControlSelect1">Permissão</label>
                            <?php echo montaform(); ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary " data-dismiss="modal" aria-label="Close"
                                id="close">Fechar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Usuário -->
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Editar Usuário</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="home.php?users=true">
                        <input type="hidden" name="edit_user_id" id="edit_user_id">
                        <div class="form-group">
                            <label for="edit_nome">Nome</label>
                            <input type="text" class="form-control m-1" name="edit_nome" id="edit_nome" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" class="form-control m-1" name="edit_email" id="edit_email" required>
                        </div>
                        <div class="form-group">
                            <label for="exampleFormControlSelect1">Permissão</label>
                            <?php echo montaform(1); ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                id="close-modal-senha">Fechar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script>
    $('#editUserModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var userId = button.data('id');
        var userName = button.data('nome');
        var userEmail = button.data('email');
        var userPermission = button.data('permission');

        var modal = $(this);
        modal.find('#edit_user_id').val(userId);
        modal.find('#edit_nome').val(userName);
        modal.find('#edit_email').val(userEmail);
        modal.find('#edit_user_permission').val(userPermission);

    });

    // Close modals on clicking the close button
    $('.close').on('click', function () {
        $('#exampleModal').modal('hide');
        $('#editUserModal').modal('hide');
    });



    $(document).ready(function () {
        const modal = document.getElementById("close-modal-senha");
        if (modal) {
            modal.addEventListener('click', function (e) {
                $('#passwordModal').modal('hide');
            });
        }

    });


    function gerarSenha(userId) {
        fetch('criar_senha.php?userId=' + userId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta da rede: ' + response.statusText);
                }
                return response.text(); // Leia como texto primeiro
            })
            .then(text => {
                try {
                    const data = JSON.parse(text); // Tente converter para JSON
                    if (data.success) {
                        document.getElementById('new_password').value = data.password;
                        $('#passwordModal').modal('show');
                    } else {
                        alert('Erro ao gerar nova senha: ' + data.message);
                    }
                } catch (e) {
                    alert('Erro ao processar a resposta JSON: ' + e.message);
                }
            })
            .catch(error => {
                alert('Erro na solicitação: ' + error.message);
            });
    }

    function reativarUsuario(userId) {
        fetch('reativar_usuario.php?reativar=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Usuário reativado com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao reativar usuário: ' + data.message);
                }
            });
    }

    document.getElementById('copyPasswordButton').addEventListener('click', function () {
        const passwordInput = document.getElementById('new_password');
        passwordInput.select();
        passwordInput.setSelectionRange(0, 99999); // Para dispositivos móveis

        try {
            document.execCommand('copy');
            alert('Senha copiada para a área de transferência!');
        } catch (err) {
            alert('Falha ao copiar a senha.');
        }
    });
</script>