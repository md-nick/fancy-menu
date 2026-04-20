<?php
    require_once 'config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare('select id from users where username = ?');
            $stmt->execute(['username']);

            if ($stmt->fetch()) {
                $_SESSION['register_error'] = "Username taken. Please choose a different one.";
            } else {
                $stmt = $pdo->prepare('insert into users (username, password) values (:username, :password)');
                if ($stmt->execute([':username' => $username, 'password' => $password])) {
                    $_SESSION['register_success'] = "Registering complete!";
                } else {
                    $_SESSION['register_error'] = "Something went wrong.";
                }
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            die($e);
        }
    }
    try {
        $userStmt = $pdo->query('select * from users');
        $userStmt->setFetchMode(PDO::FETCH_ASSOC);
        $users = [];
        while ($row = $userStmt->fetch()) {
            $users[] = [
                'id' => $row['id'],
                'username' => $row['username']               
            ];
        }
    } catch (PDOExecption $e) {
        die($e);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles.css">
    </head>
    <body>
        <header>
        <a href="/" class="logo" title="home">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-utensils-crossed w-16 h-16 mx-auto mb-6 stroke-1" data-fg-d3bl8="0.8:2.15909:/src/app/App.tsx:384:13:14488:63:e:UtensilsCrossed::::::ITI" data-fgid-d3bl8=":r7:">
                <path d="m16 2-2.3 2.3a3 3 0 0 0 0 4.2l1.8 1.8a3 3 0 0 0 4.2 0L22 8"></path>
                <path d="M15 15 3.3 3.3a4.2 4.2 0 0 0 0 6l7.3 7.3c.7.7 2 .7 2.8 0L15 15Zm0 0 7 7"></path>
                <path d="m2.1 21.8 6.4-6.3"></path><path d="m19 5-7 7"></path>
            </svg>
        </a>
        <div class="nav-stuff">
            <nav>
                <a href="../categories.php">Categories</a>
                <a href="../item-create.php">Items</a>
                <a href="register.php">New Users</a>
            </nav>
            <a href="config/logout.php" title="Logout" class="btn-logout">X</a>
        </div>
    </header>
    <main>
        <h1>Add New Users</h1>
        <div>
            <form method="post" action="" class="form-category">
                <input type="text" name="username" placeholder="username" required>
                <input type="password" name="password" placeholder="password" required>
                <button type="submit" name="register" class="btn btn-save">OK</button>
            </form>
        </div>
        <div>
            <h2>Users</h2>
            <hr>
            <ul>
                <?php foreach ($users as $user): ?>
                    <li><?= htmlspecialchars($user['username']) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    </main>
    </body>
</html>