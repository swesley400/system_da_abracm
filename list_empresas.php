<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <title>Tabela Incorporada</title>
</head>

<body>
    <style>
        td {
            margin-left: 0;
        }
    </style>
    <?php
    $servername = "localhost";
    $dbname = "php_db";
    $username = "root";
    $password = "Ws49650812@";



    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sqlLastUpdate = "SELECT MAX(DataAtualizacao) AS LastUpdate FROM tb_empresa";
    $resultLastUpdate = $conn->query($sqlLastUpdate);
    $rowLastUpdate = $resultLastUpdate->fetch_assoc();

    if ($rowLastUpdate) {
        echo "<p style='text-align:right'>Atualizada em: " . $rowLastUpdate['LastUpdate'] . "</p>";
    } else {
        echo "<p>Nenhuma atualização encontrada.</p>";
    }
    ?>

    <table class="text-md" width="100%">
        <thead>
            <tr align="center">
                <th style="font-size: 12px;text-align:center;" align="center">Número ANS</th>
                <th style="font-size: 12px;text-align:center;" align="center">Nome da Empresa</th>
                <th style="font-size: 12px;text-align:center;" align="center">Site da Empresa</th>
                <th style="font-size: 12px;text-align:center;" align="center">Cidade</th>
                <th style="font-size: 12px;text-align:center;" align="center">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Substitua as informações do banco de dados e a lógica de consulta abaixo com o seu próprio código
            $sql = "SELECT * FROM tb_empresa where ative=1";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr align='center'>";
                    echo "<td style='border:1px solid black; padding: 0; margin: 0;' align='center'><font size='2'>" . $row["NumeroANS"] . "</font></td>";
                    echo "<td style='border: 1px solid black; padding: 0; margin: 0;' align='center'><font size='2'>" . $row["NomeEmpresa"] . "</font></td>";
                    echo "<td style='border: 1px solid black; padding: 0; margin: 0;' align='center'><font size='2' color='blue'><u><a href='" . $row["SiteEmpresa"] . "' target='_blank'>" . $row["SiteEmpresa"] . "</a></u></font></td>";
                    echo "<td style='border: 1px solid black; padding: 0; margin: 0;' align='center'><font size='2'>" . $row["Cidade"] . "</font></td>";
                    echo "<td style='border: 1px solid black; padding: 0; margin: 0;' align='center'><font size='2'>" . $row["Estado"] . "</font></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>Nenhum resultado encontrado.</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>

</body>

</html>