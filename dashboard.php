<?php
session_start();

if (!isset($_SESSION['sessao']) || $_SESSION['sessao'] !== true) {
    header("Location: index.php");
    exit;
}

// Conexão com o banco de dados
$host = 'localhost';
$dbname = 'slb';
$usuario = 'root';
$senha = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Cadastro de Clientes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_cliente'])) {
    $nome = $_POST['nome_cliente'];
    $telefone = $_POST['telefone_cliente'];
    $morada = $_POST['morada_cliente'];

    // Preparar a consulta SQL
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, telefone, morada) VALUES (:nome, :telefone, :morada)");

    // Executar a consulta
    $stmt->execute(['nome' => $nome, 'telefone' => $telefone, 'morada' => $morada]);
}

// Cadastrar de Penteados
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_penteado'])) {
    $tipo = $_POST['tipo_penteado'];
    $preco = $_POST['preco_penteado'];

    // Preparar a consulta SQL
    $stmt = $pdo->prepare("INSERT INTO penteados (tipo, preco) VALUES (:tipo, :preco)");

    // Executar a consulta
    $stmt->execute(['tipo' => $tipo, 'preco' => $preco]);
}

// Excluir Cliente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_cliente'])) {
    $id_cliente = $_POST['excluir_cliente'];

    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = :id");

    $stmt->execute(['id' => $id_cliente]);
}

// Excluir Penteado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_penteado'])) {
    $id_penteado = $_POST['excluir_penteado'];

    $stmt = $pdo->prepare("DELETE FROM penteados WHERE id = :id");

    $stmt->execute(['id' => $id_penteado]);
}

// Consulta de Clientes
$stmt_clientes = $pdo->query("SELECT * FROM clientes");

// Consulta de Penteados
$stmt_penteados = $pdo->query("SELECT * FROM penteados");

// Consulta de Penteados Feitos por Clientes
$stmt_penteado_cliente = $pdo->query("SELECT cliente_id, penteado_id FROM penteado_cliente");

// Agrupar os penteados por cliente
$clientes_com_penteados = array();
while ($row = $stmt_penteado_cliente->fetch(PDO::FETCH_ASSOC)) {
    $cliente_id = $row['cliente_id'];
    $penteado_id = $row['penteado_id'];

    // Verificar se o cliente já está no array, se não, criar uma entrada para ele
    if (!isset($clientes_com_penteados[$cliente_id])) {
        $clientes_com_penteados[$cliente_id] = array();
    }

    // Adicionar o penteado ao array do cliente
    $clientes_com_penteados[$cliente_id][] = $penteado_id;
}

// Processar o envio do formulário de cadastro de penteados feitos por clientes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_penteado_cliente'])) {
    $cliente_id = $_POST['cliente_id'];
    $penteado_id = $_POST['penteado_id'];

    // Preparar a consulta SQL
    $stmt = $pdo->prepare("INSERT INTO penteado_cliente (cliente_id, penteado_id) VALUES (:cliente_id, :penteado_id)");

    // Executar a consulta
    $stmt->execute(['cliente_id' => $cliente_id, 'penteado_id' => $penteado_id]);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Salão de Beleza</title>
    <link rel="stylesheet" href="style-d.css">
</head>
<body>
<div class="col">
    <h2>Bem-vindo ao Dashboard</h2>
    <div class="sair" style="width: 100%; text-align: center">
        <a href="logout.php">Sair</a>
    </div>
</div>
<div class="container">
    <div class="box">

        <div class="cadastro">
            <h3>Cadastro de Clientes</h3>
            <form class="cadastro-form" method="post" action="<?php echo $_SERVER["PHP_SELF"];?>">
                <label for="nome_cliente">Nome:</label>
                <input type="text" id="nome_cliente" name="nome_cliente" required><br>
                <label for="telefone_cliente">Telefone:</label>
                <input type="text" id="telefone_cliente" name="telefone_cliente" required><br>
                <label for="morada_cliente">Morada:</label>
                <input type="text" id="morada_cliente" name="morada_cliente" required><br>
                <input type="submit" name="submit_cliente" value="Cadastrar Cliente">
            </form>
        </div>
        <hr>
        <h3>Clientes Cadastrados</h3>
        <table>
            <thead>
            <tr>
                <th>Nome</th>
                <th>Telefone</th>
                <th>Morada</th>
                <th>Ação</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $stmt_clientes->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo $row['nome']; ?></td>
                    <td><?php echo $row['telefone']; ?></td>
                    <td><?php echo $row['morada']; ?></td>
                    <td>
                        <form method="post" action="<?php echo $_SERVER["PHP_SELF"];?>">
                            <input type="hidden" name="excluir_cliente" value="<?php echo $row['id']; ?>">
                            <input type="submit" value="Excluir">
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="box">
        <!-- Formulário de Cadastro de Penteados -->
        <div class="cadastro">
            <h3>Cadastro de Penteados</h3>
            <form class="cadastro-form" method="post" action="<?php echo $_SERVER["PHP_SELF"];?>">
                <label for="tipo_penteado">Tipo:</label>
                <input type="text" id="tipo_penteado" name="tipo_penteado" required><br>
                <label for="preco_penteado">Preço:</label>
                <input type="text" id="preco_penteado" name="preco_penteado" required><br>
                <input type="submit" name="submit_penteado" value="Cadastrar Penteado">
            </form>
        </div>

        <!-- Tabela de Penteados Cadastrados -->
        <h3>Penteados Cadastrados</h3>
        <table>
            <thead>
            <tr>
                <th>Tipo</th>
                <th>Preço</th>
                <th>Ação</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $stmt_penteados->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo $row['tipo']; ?></td>
                    <td><?php echo $row['preco']; ?></td>
                    <td>
                        <form method="post" action="<?php echo $_SERVER["PHP_SELF"];?>">
                            <input type="hidden" name="excluir_penteado" value="<?php echo $row['id']; ?>">
                            <input type="submit" value="Excluir">
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<!---->
<!--    <div class="box">-->
<!--        <!-- Formulário de Cadastro de Penteados Feitos por Clientes -->
<!--        <div class="cadastro">-->
<!--            <h3>Cadastro de Penteados Feitos por Clientes</h3>-->
<!--            <form class="cadastro-form" method="post" action="--><?php //echo $_SERVER["PHP_SELF"];?><!--">-->
<!--                <label for="cliente_id">Cliente:</label>-->
<!--                <select id="cliente_id" name="cliente_id" required>-->
<!--                    --><?php //while ($rows = $stmt_clientes->fetch(PDO::FETCH_ASSOC)): ?>
<!--                        <option value="--><?php //echo $rows['id']; ?><!--">--><?php //echo $rows['nome']; ?><!--</option>-->
<!--                    --><?php //endwhile; ?>
<!--                </select><br>-->
<!--                <label for="penteado_id">Penteado:</label>-->
<!--                <select id="penteado_id" name="penteado_id" required>-->
<!--                    --><?php //$stmt_penteados->execute(); ?>
<!--                    --><?php //while ($row = $stmt_penteados->fetch(PDO::FETCH_ASSOC)): ?>
<!--                        <option value="--><?php //echo $row['id']; ?><!--">--><?php //echo $row['tipo']; ?><!--</option>-->
<!--                    --><?php //endwhile; ?>
<!--                </select><br>-->
<!--                <input type="submit" name="submit_penteado_cliente" value="Cadastrar Penteado para Cliente">-->
<!--            </form>-->
<!--        </div>-->
<!---->
<!--        <!-- Tabela de Penteados Feitos por Clientes -->-->
<!--        <h3>Penteados Feitos por Clientes</h3>-->
<!--        <table>-->
<!--            <thead>-->
<!--            <tr>-->
<!--                <th>Cliente</th>-->
<!--                <th>Penteado(s)</th>-->
<!--            </tr>-->
<!--            </thead>-->
<!--            <tbody>-->
<!--            --><?php //while ($row = $stmt_clientes->fetch(PDO::FETCH_ASSOC)): ?>
<!--                <tr>-->
<!--                    <td>--><?php //echo $row['nome']; ?><!--</td>-->
<!--                    <td>-->
<!--                        --><?php //if (isset($clientes_com_penteados[$row['id']])): ?>
<!--                            --><?php //foreach ($clientes_com_penteados[$row['id']] as $penteado_id): ?>
<!--                                --><?php
//                                // Consultar o tipo do penteado com base no ID
//                                $stmt_tipo_penteado = $pdo->prepare("SELECT tipo FROM penteados WHERE id = :id");
//                                $stmt_tipo_penteado->execute(['id' => $penteado_id]);
//                                $tipo_penteado = $stmt_tipo_penteado->fetchColumn();
//                                ?>
<!--                                --><?php //echo $tipo_penteado; ?><!--<br>-->
<!--                            --><?php //endforeach; ?>
<!--                        --><?php //else: ?>
<!--                            Nenhum penteado registrado.-->
<!--                        --><?php //endif; ?>
<!--                    </td>-->
<!--                </tr>-->
<!--            --><?php //endwhile; ?>
<!--            </tbody>-->
<!--        </table>-->
<!--    </div>-->
</div>
</body>
</html>
