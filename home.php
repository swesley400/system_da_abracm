<?php
session_start();
$nome = $_SESSION["nome"];
$permission_type = $_SESSION["permission_type"];


if (!isset($_SESSION["nome"]) && !isset($_SESSION["email"])) {
  
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">


    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> -->
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"> </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/home.css?v=1.3">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"> </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">

    <title>Home</title>
</head>

<body id="body-pd">
    <header class="header" id="header">
        <div class="header_toggle"> <i class='bx bx-menu' id="header-toggle"></i> </div>
        <div class="d-flex flex-row items-center justify-content-center">
            <div class="d-flex m-1 items-centerfs-8 flex-column ">
                <div>
                    <span class="font-weight-bold">
                        <?php echo htmlspecialchars($nome); ?>
                    </span>
                </div>
                <div>
                    <span class="text-sm">
                        <?php echo $permission_type === "adm" ? "Administrador" : "Usuario"; ?>
                    </span>
                </div>
            </div>
            <div class="header_img mt-2">
                <img src="https://cdn-icons-png.flaticon.com/512/1864/1864593.png" alt="">
            </div>
        </div>
    </header>
    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div>
                <a href="#" class="nav_logo"> <i class='bx bx-layer nav_logo-icon'></i>
                        <img class="nav_logo-name" src="./public/logo.png" alt="logo" width="150px" height="150px">
                    </span>
                </a>
                <div class="nav_list">
                    <?php
                        if ($permission_type === "adm") {
                            echo
                                '
                                    <a href="?empresas=true" class="nav_link active"><i class="bi bi-building"></i></i>
                                    <span class="nav_name">Empresas</span>
                                    </a>';
                        }
                    ?>
                    <?php
                    if ($permission_type === "adm") {
                        echo
                            '
                                <a href="?users=true" class="nav_link active"> <i class="bx bx-grid-alt nav_icon"></i>
                                <span class="nav_name">Usuarios</span>
                                </a>';
                    }
                    ?>
                    <a href="?files=true" class="nav_link">
                        <i class='bx bx-folder nav_icon'></i> <span class="nav_name">Arquivos</span>
                    </a>
                </div>
            </div>
            <a class="nav_link" id="sign_out"> <i class='bx bx-log-out nav_icon'></i> <span
                    class="nav_name">Sair</span> </a>
        </nav>
    </div>

    <div class="p-1">
        <?php
        if ($permission_type == 'adm') {
            if (isset($_GET['users']) && $_GET['users'] === 'true') {
                include 'user.php';
            }
        }
        
        if ($permission_type == 'adm') {
            if (isset($_GET['empresas']) && $_GET['empresas'] === 'true') {
                include 'business.php';
            }
        }

        if (isset($_GET['files']) && $_GET['files'] === 'true') {
            include 'file.php';
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/sidebar.js?v=2.1"></script>

    <script>

        $(document).ready(function () {
            const signOut = document.getElementById("sign_out");

            signOut.addEventListener('click', function (e) {
                e.preventDefault();

                $.ajax({
                    type: "POST",
                    url: "logout.php",
                    success: function (response) {
                        window.location.href = "index.php";
                    }
                });
            });
        });

    </script>

    <script>

        $(document).ready(function () {
            const modal = document.getElementById("novoUser");
            if(modal){
                modal.addEventListener('click', function (e) {
                console.log("Fui clicado")
                $('#exampleModal').modal('show');
                $('#exampleModal').modal('show').style = '!importante width: 100%;';
                });
            }
            
        });

    </script>


    <script>

        $(document).ready(function () {
            const modalClose = document.getElementById("close");
            
            if(modalClose){
                modalClose.addEventListener('click', function (e) {
                console.log("Fui clicado")
                $('#exampleModal').modal('hide');
                });
            }

        });

    </script>

    <script>

        $(document).ready(function () {
            const modalClose = document.getElementById("cancel");
            if(modalClose){
                modalClose.addEventListener('click', function (e) {
                $('#exampleModal').modal('hide');
                });
            }
        });

    </script>

    <script>

        $(document).ready(function () {
            const modal = document.getElementById("novoArquivo");
            if(modal){
                modal.addEventListener('click', function (e) {
                    $('#achiveModal').modal('show');
                    $('#achiveModal').modal('show').style = '!importante width: 100%;';
                });
            }
         
        });

    </script>

    <script>

        $(document).ready(function () {
            const modalClose = document.getElementById("closeModalAchives");

            if(modalClose) {
                modalClose.addEventListener('click', function (e) {
                    console.log("Fui clicado")
                    $('#achiveModal').modal('hide');
                });
            }
            
        });

    </script>

    <script>

        $(document).ready(function () {
            const modalClose = document.getElementById("modalCancelAchives");
            if(modalClose){
                modalClose.addEventListener('click', function (e) {
                    $('#achiveModal').modal('hide');
                });
            }
          
        });

    </script>

<script>
    $(document).ready(function () {
        const modal = document.getElementById("novoUser");
        if (modal) {
            modal.addEventListener('click', function (e) {
                console.log("Fui clicado");
                $('#novaEmpresaModal').modal('show');
                $('#novaEmpresaModal').modal('show').style = '!important; width: 100%;';
            });
        }
    });
</script>

<script>
    $(document).ready(function () {
        const modalClose = document.getElementById("close");
        if (modalClose) {
            modalClose.addEventListener('click', function (e) {
                console.log("Fui clicado");
                $('#novaEmpresaModal').modal('hide');
            });
        }
    });
</script>

<script>
    $(document).ready(function () {
        const modalClose = document.getElementById("cancel");
        if (modalClose) {
            modalClose.addEventListener('click', function (e) {
                $('#novaEmpresaModal').modal('hide');
            });
        }
    });
</script>

</body>

</html>