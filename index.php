<?php 
    session_start();
    require_once 'config/config.php';
    $error = isset($_SESSION['login_error']);

    try {
        $catStmt = $pdo->query('select * from categories');
        $catStmt->setFetchMode(PDO::FETCH_ASSOC);
        $categories = [];
        while ($row = $catStmt->fetch()) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description']
            ];
        }
        $subStmt = $pdo->query('select * from sub_categories');
        $subStmt->setFetchMode(PDO::FETCH_ASSOC);
        $subs = [];
        while ($line = $subStmt->fetch()) {
            $subs[] = [
                'id' => $line['id'],
                'name' => $line['name'],
                'category_id' => $line['category_id']
            ];
        }
        $itemStmt = $pdo->query('select * from items');
        $itemStmt->setFetchMode(PDO::FETCH_ASSOC);
        $items = [];
        while($group = $itemStmt->fetch()) {
            $items[] = [
                'id' => $group['id'],
                'name' => $group['name'],
                'description' => $group['description'],
                'subcategory_id' => $group['subcategory_id'],
                'price' => $group['price'],
                'image' => $group['image'],
                'is_visible' => $group['is_visible']
            ];
        }
    } catch (PDOException $e) {
        die($e);
    }
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <nav>
                    <a href="categories.php">Categories</a>
                    <a href="item-create.php">Items</a>
                    <a href="config/register.php">New Users</a>
                </nav>
                <a href="config/logout.php" title="Logout" class="btn-logout">X</a>
            <?php else: ?>
                <button id="login-toggle" name="login-toggle" class="btn-login <?php echo $error ? 'd-none' : 'd-block' ?>">Login</button>
                <form id="login-form" method="post" action="config/login.php" class=" <?php echo $error ? 'd-block' : 'd-none' ?>">
                    <input type="text" name="username" placeholder="username" required>
                    <input type="password" name="password" placeholder="password" required>
                    <button name="login" class="btn btn-login">Login</button>
                </form>
            <?php endif ?>
        </div>
    </header>
    <main>
        <?php if ($error): ?>
            <p class="error err-login"><?php echo $_SESSION['login_error']; ?></p>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif ?>
        <?php foreach ($categories as $cat): ?>
            <section class="categories">
                <h2><?= htmlspecialchars($cat['name']) ?></h2>
                <p><?= htmlspecialchars($cat['description']) ?></p>
                <?php foreach ($subs as $sub): ?>
                    <?php if($cat['id'] == $sub['category_id']): ?>
                        <div class="sub-categories">
                            <h3><?= htmlspecialchars($sub['name']) ?></h3>
                            <div class="item-group">
                                <?php foreach ($items as $item): ?>
                                    <?php if($sub['id'] == $item['subcategory_id'] && $item['is_visible'] = true): ?>
                                        <div class="item">
                                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                                            <span>€<?= htmlspecialchars($item['price']) ?></span>
                                            <p class="item-desc"><?= htmlspecialchars($item['description']) ?></p>
                                        </div>
                                    <?php endif ?>
                                <?php endforeach ?>
                            </div>
                        </div>
                    <?php endif ?>
                <?php endforeach ?>
            </section>
        <?php endforeach ?>
    </main>
    <script>
        const loginToggle = document.getElementById('login-toggle');
        const loginForm = document.getElementById('login-form');
        if (loginToggle) {
            loginToggle.addEventListener('click', function() {
                loginForm.classList.replace('d-none', 'd-block');
                loginToggle.classList.replace('d-block', 'd-none');
            });
        }
    </script>
</body>
</html>
