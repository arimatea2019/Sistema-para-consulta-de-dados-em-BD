<!DOCTYPE html>
<html lang="PT-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa no Banco de Dados</title>
    <link rel="stylesheet" href="stylepesquisa.css">
</head>
<body>

<?php
$usuario = '*';
$senha = '*';
$host = '*';
$nome_banco = '*';

$conn = new mysqli($host, $usuario, $senha, $nome_banco);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
$resultado = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $termo = $_POST['termo'];

    $termoCelular = preg_replace('/[\(\)-]|\s/', '', $termo);
    $termoCpf = preg_replace('/[\.\-]/', '', $termo);
    if (strlen($termoCpf) < 11) {
        $termoCpf = str_pad($termoCpf, 11, '0', STR_PAD_LEFT);
    }

    $sql = "SELECT * FROM (
        SELECT nome, cpf, agencia, conta, celular, lista,data,
               ROW_NUMBER() OVER (PARTITION BY nome ORDER BY id DESC) AS rn
        FROM consulta
        WHERE nome LIKE '%$termo%' OR celular = '$termoCelular' OR cpf = '$termoCpf'
    ) AS results
    WHERE rn = 1";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $resultado = "";
        while ($row = $result->fetch_assoc()) {
            $resultado .= "<div class='resultado-item'>";
            $resultado .= "<p>Nome: " . $row['nome'] . "</p>";
            $resultado .= "<p>CPF: " . $row['cpf'] . "</p>";
            $resultado .= "<p>Agência: " . $row['agencia'] . "</p>";
            $resultado .= "<p>Conta: " . $row['conta'] . "</p>";
            $resultado .= "<p>Número: " . $row['celular'] . "</p>";
            $resultado .= "<p>Lista: " . $row['lista'] . "</p>";
            $resultado .= "<p>Data: " . $row['data'] . "</p>";

            $resultado .= "</div>";
        }
    } else {
        $resultado = "Nenhum resultado encontrado.";
    }
}

$conn->close();
?>

<div id="main-content">
    <div id="search-box">
        <h1>Pesquisa no Banco de Dados</h1>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="termo">Digite o CPF ou Numero de celular ou Nome do cliente:</label>
            <input type="text" name="termo" id="termo" required>

            <input type="submit" value="Pesquisar">
        </form>
    </div>

    <div id="resultado">
        <?php echo $resultado; ?>
    </div>
</div>

<footer>
    <button onclick="window.location.href='home.php';" id="linkHome">Voltar para Home</button>
    <button onclick="window.location.href='logout.php';" id="linkSair" class="logout">Sair</button>
</footer>

</body>
</html>
