<?php
session_start();

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nome = :usuario");
    $stmt->execute(['usuario' => $usuario]);
    $user = $stmt->fetch();

    if ($user && $senha == $user['senha']) {

        $_SESSION['sessao'] = true;
        $_SESSION['usuario'] = $usuario;

        // Redirecionar para o dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Senha o nome de Usuario inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login - Salão de Beleza</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div id="content">
    <div class="topo">
        <h2 style="text-align: center;">Salão da TaYaRa</h2>
    </div>
    <div class="login-container">
        <h3>Login</h3>
        <?php if(isset($error)) { echo "<p class='error-message'>$error</p>"; } ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <label for="usuario">Usuário:</label>
            <input type="text" id="usuario" name="usuario" required><br>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required><br>
            <input type="submit" value="Login">
        </form>
    </div>
</div>
</body>
</html>
