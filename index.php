<!DOCTYPE html>
<html lang="en">

<?php
session_start();
if (isset($_SESSION["nome"]) && isset($_SESSION["email"])) {
    header("Location: home.php?files=true");
    exit();
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous">
    </script>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/style.css?v=1.5">
    <title>Login</title>
</head>

<body>

    <div class="wrapper fadeInDown">
        <div id="formContent">

            <div class="fadeIn first" style="height: 150px; display: flex; align-items: center; justify-content: center;">
                <img src="public/logo.png" alt="" width="150" height="150">
            </div>

            <div class="p-10">
                <div id="error" style="display: none;">
                    <p class="text-danger">Usuário ou senha incorretos</p>
                </div>
                <form action="processa_form.php" method="POST" name="submit">
                    <input type="text" id="login" class="fadeIn second" name="login" placeholder="Usuário">
                    <input id="password" class="fadeIn third" type="password" name="password" placeholder="Senha">
                    <div class="d-flex flex-row align-items-center justify-content-center">
                        <div class="m-3">
                            <button type="submit" class="btn btn-secondary btn-lg btn-block" value="Entrar">Entrar</button>
                        </div>
                        <div>
                            <a class="underlineHover password" href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Esqueceu senha?</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Esqueceu Senha -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Recuperação de Senha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Por favor, entre em contato com o administrador do sistema para recuperar sua senha.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const error = <?php echo json_encode(isset($_GET['erro']) && $_GET['erro'] == 1); ?>;
            if (error) {
                const err = document.getElementById('error');
                err.style.display = 'block';
            }
        });
    </script>
</body>

</html>
