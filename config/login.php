<?php 
    session_start();
    require_once 'config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $db_user = $stmt->fetch();

        if ($db_user && password_verify($password, $db_user['password'])) {
            $_SESSION['user_id'] = $db_user['id'];
            header("Location: ../");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid login data!";
            header("Location: ../");
            exit;
        }
    }
?>