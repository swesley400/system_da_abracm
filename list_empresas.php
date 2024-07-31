<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <title>Tabela Incorporada</title>
    <style>
        td {
            padding: 5px;
            border: 1px solid black;
            margin: 0;
        }
        th {
            font-size: 12px;
            text-align: center;
        }
        a {
            color: blue;
            text-decoration: underline;
        }
        .updated-time {
            text-align: right;
        }
    </style>
</head>

<body>
    <?php
    $servername = "";
    $dbname = "";
    $username = "";
    $password = "";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verificar última atualização
        $sqlLastUpdate = "SELECT MAX(DataAtualizacao) AS LastUpdate FROM tb_empresa";
        $stmtLastUpdate = $conn->prepare($sqlLastUpdate);
        $stmtLastUpdate->execute();
        $rowLastUpdate = $stmtLastUpdate->fetch(PDO::FETCH_ASSOC);

        if ($rowLastUpdate) {
            echo "<p class='updated-time'>Atualizada em: " . htmlspecialchars($rowLastUpdate['LastUpdate']) . "</p>";
        } else {
            echo "<p>Nenhuma atualização encontrada.</p>";
        }

        // Consultar dados da tabela
        $sql = "SELECT * FROM tb_empresa WHERE ative = 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "<p>Erro de conexão: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Número ANS</th>
                <th>Nome da Empresa</th>
                <th>Site da Empresa</th>
                <th>Cidade</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result) {
                foreach ($result as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["NumeroANS"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["NomeEmpresa"]) . "</td>";
                    echo "<td><a href='" . htmlspecialchars($row["SiteEmpresa"]) . "' target='_blank'>" . htmlspecialchars($row["SiteEmpresa"]) . "</a></td>";
                    echo "<td>" . htmlspecialchars($row["Cidade"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["Estado"]) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Nenhum resultado encontrado.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>

</html>
