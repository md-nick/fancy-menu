<?php 
    require_once 'config/check-login.php';
    require_once 'config/config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['add_category'])) {    
            try {
                $sql = 'insert into categories(name, description) values(:name, :description)';
                $stmt = $pdo->prepare($sql);
                $name = $_POST['category_name'];
                $description = $_POST['cat_description'];
                $stmt->execute([':name' => $name, ':description' => $description]);
            } catch (PDOException $e) {
                die($e);
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        if (isset($_POST['add_subcategory'])) {
            try {
                $sql = 'insert into sub_categories(category_id, name) values(:cat_id, :name)';
                $stmt = $pdo->prepare($sql);
                $name = $_POST['subcategory_name'];
                $cat_id = $_POST['category_id'];
                $stmt->execute([':name' => $name, ':cat_id' => $cat_id]);
            } catch (PDOException $e) {
                die($e);
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    try {
        $stmt = $pdo->query('select * from categories');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $categories = [];
        while ($row = $stmt->fetch()) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description']                
            ];
        }
    } catch (PDOExecption $e) {
        die($e);
    }
    try {
        $subStmt = $pdo->query('select * from sub_categories');
        $subStmt->setFetchMode(PDO::FETCH_ASSOC);
        $subcategories = [];
        while ($line = $subStmt->fetch()) {
            $subcategories[] = [
                'name' => $line['name'],
                'category_id' => $line['category_id']
            ];
        }
    } catch (PDOExecption $e) {
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
            <nav>
                <a href="categories.php">Categories</a>
                <a href="item-create.php">Items</a>
                <a href="config/register.php">New Users</a>
            </nav>
            <a href="config/logout.php" title="Logout" class="btn-logout">X</a>
        </div>
    </header>
    <main>
        <h1>Categories</h1>
        <button onclick="toggleForm('form-new')" class="btn btn-add-cat">Add Category</button>
        <div id="form-new" class="d-none">
            <form method="post" action="" class="form-category">
                <input type="text" name="category_name" placeholder="category name" required>
                <input type="text" name="cat_description" placeholder="subtitle" required>
                <button type="submit" name="add_category" class="btn btn-save">OK</button>
                <button type="button" onclick="toggleForm('form-new')" class="btn btn-cancel">Cancel</button>
            </form>
        </div>
        <?php foreach ($categories as $cat): ?>
            <ul class="category-list">
                <li>
                    <h2><?= htmlspecialchars($cat['name']) ?></h2>
                    <h3><?= htmlspecialchars($cat['description']) ?></h3>
                    <?php if (isset($subcategories)): ?>
                    <ul class="sub-list">
                        <?php foreach ($subcategories as $sub): ?>
                            <?php if($cat['id'] == $sub['category_id']): ?>
                                <li><?= htmlspecialchars($sub['name']) ?></li>
                            <?php endif ?>
                        <?php endforeach ?>
                    </ul>
                    <?php endif ?>
                    <button onclick="toggleForm('form-sub-<?= $cat['id'] ?>')" class="btn btn-add-sub">Add Sub-Category</button>
                    <div id="form-sub-<?= $cat['id'] ?>" class="d-none">
                        <form method="post" class="form-category">
                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                            <input type="text" name="subcategory_name" placeholder="Sub-Category Name" required>
                            <button type="submit" name="add_subcategory" class="btn btn-save">OK</button>
                            <button type="button" class="btn btn-cancel" onclick="toggleForm('form-sub-<?= $cat['id'] ?>')">Cancel</button>
                        </form>
                    </div>
                </li>
            </ul>
        <?php endforeach ?>
    </main>
    <script>
        function toggleForm(id) {
            const form = document.getElementById(id);
            if (form.classList.contains('d-none')) {
                form.classList.remove('d-none');
            } else {
                form.classList.add('d-none');
            }
        }
    </script>
    </body>
</html>