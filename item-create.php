<?php 
    require_once 'config/check-login.php';
    require_once 'config/config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['add_item'])) {
            $maxSize = 5 * pow(10, 6);
            $imagePath = '';
            try {
                $sql = 'insert into items(name, description, price, subcategory_id, image) values(:name, :description, :price, :subcategory_id, :image)';
                $stmt = $pdo->prepare($sql);
                $name = $_POST['item_name'];
                $description = $_POST['item_description'];
                $price = $_POST['item_price'];
                $sub_id = $_POST['subcategory_select'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    if ($_FILES['image']['size'] === 0) {
                        $error = "Empty file. Please try again.";
                    } elseif ($_FILES['image']['size'] > $maxSize) {
                        $error = "File size too large. Please choose a smaller image.";
                    } else {
                        $allowedTypes = ['jped', 'png', 'jpg', 'gif', 'avif'];
                        $imageInfo = pathinfo($_FILES['image']['name']);
                        $extension = strtolower($imageInfo['extension'] ?? '');
                        if (in_array($extension, $allowedTypes)) {
                            $uploadDir = 'images/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0700, true);
                            }
                            $imageName = time() . '_' . basename($_FILES['image']['name']);
                            $imagePath = $uploadDir . $imageName;

                            move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
                        }
                    }
                }
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $price,
                    ':subcategory_id' => $sub_id,
                    ':image' => $imagePath]);
            } catch (PDOException $e) {
                die($e);
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    try {
        $stmt = $pdo->query('select * from items');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $items = [];
        while ($group = $stmt->fetch()) {
            $items[] = [
                'subcategory_id' => $group['subcategory_id'],
                'name' => $group['name'],
                'description' => $group['description'],
                'price' => $group['price'],
                'image' => $group['image'],
                'id' => $group['id'],
                'is_visible' => $group['is_visible']                 
            ];
        }
    } catch (PDOException $e) {
        die($e);
    }
    try { 
        $catStmt = $pdo->query('select id, name from categories');
        $catStmt->setFetchMode(PDO::FETCH_ASSOC);
        $categories = [];
        while ($cat = $catStmt->fetch()) {
            $categories[] = [
                'id' => $cat['id'],
                'name' => $cat['name']
            ];
        }
        $subStmt = $pdo->query('select id, name, category_id from sub_categories');
        $subStmt->setFetchMode(PDO::FETCH_ASSOC);
        $subs = [];
        while ($sub = $subStmt->fetch()) {
            $subs[] = [
                'id' => $sub['id'],
                'name' => $sub['name'],
                'category_id' => $sub['category_id']
            ];
        }
    } catch (PDOExecption $e) {
        die($e);
    }
?>
<!-- ------------------------------------------------------------------------------------------------------------------ 
#######################################################################################################################
------------------------------------------------------------------------------------------------------------------- -->
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
                <a href="item-edit.php">Items</a>
                <a href="config/register.php">New Users</a>
            </nav>
            <a href="config/logout.php" title="Logout" class="btn-logout">X</a>
        </div>
    </header>
    <main>
        <h1>Menu Items</h1>
        <button onclick="toggleForm('form-new')" class="btn btn-add-cat">Add Menu Item</button>
        <div id="form-new" class="d-none">
            <form method="post" action="" enctype="multipart/form-data" class="form-category">
                <input type="text" name="item_name" placeholder="item name" required>
                <input type="text" name="item_description" placeholder="description" required>
                <input type="number" name="item_price" placeholder="price in €" required>
                <label for="image">Item image: .jpeg, .jpg, .png, .gif, .avif</label>
                <input type="file" name="image" id="image" accept=".jpeg, .jpg, .png, .gif, .avif" required>

                <select name="category_select" id="category_select" onchange="loadSubs(this.value)" required>
                    <option value="">-- Please Choose --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach ?>
                </select>

                <select name="subcategory_select" id="subcategory_select" disabled required>
                    <option value="">-- Please choose --</option>
                    <?php foreach ($subs as $sub): ?>
                        <option value="<?= $sub['id'] ?>" data-category="<?= $sub['category_id'] ?>" class="d-none">
                            <?= htmlspecialchars($sub['name']) ?>
                        </option>
                    <?php endforeach ?>
                </select>

                <button type="submit" name="add_item" class="btn btn-save">OK</button>
                <button type="button" onclick="toggleForm('form-new')" class="btn btn-cancel">Cancel</button>
            </form>
        </div>
        <div class="item-group">
            <?php foreach ($items as $item): ?>
                <div id="item-<?= $item['id'] ?>">
                    <div class="item">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <span>€<?= htmlspecialchars($item['price']) ?></span>
                        <p class="item-desc"><?= htmlspecialchars($item['description']) ?></p>
                    </div>
                    <div class="item-categories edit-group">
                        <div class="edit-categories">
                            <?php foreach ($categories as $cat): ?>
                                <?php foreach ($subs as $sub): ?>
                                    <?php if ($sub['id'] == $item['subcategory_id'] && $cat['id'] == $sub['category_id']): ?>
                                        <p>Category: <span><?= htmlspecialchars($cat['name']) ?></span></p>
                                        <p>Sub-Category: <span><?= htmlspecialchars($sub['name']) ?></span></p>
                                    <?php endif ?>
                                <?php endforeach ?>
                            <?php endforeach ?>
                        </div>
                        <div>
                            <button type="button" value="item-<?= $item['id'] ?>" onclick="toggleEdit(this.value)" class="btn btn-add-sub">Edit</button>
                            <form action="item-delete.php" method="post">
                                <button type="submit" name="delete_item" class="btn btn-cancel">X</button>
                            </form>
                        </div>

                    </div>
                </div>
                <div id="edit-item-<?= $item['id'] ?>" class="d-none">
                    <form action="item-edit.php" method="post" class="item-edit">
                        <button class="edit-img" onclick="editImg('edit_image')"><img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"></button>
                        <input type="hidden" name="edit_id" value="<?= $item['id'] ?>">
                        <input type="text" name="edit_name" value="<?= htmlspecialchars($item['name']) ?>" class="edit_item_name" required>
                        <input type="number" name="edit_price" value="<?= htmlspecialchars($item['price']) ?>" class="edit_item_price" required>
                        <textarea type="text" name="edit_description" class="edit_item_description" value="<?= htmlspecialchars($item['description']) ?>"><?= htmlspecialchars($item['description']) ?></textarea>
                        
                        <input type="file" name="image" id="edit_image" accept=".jpeg, .jpg, .png, .gif, .avif" class="d-none">
                        <div class="edit-select">
                            <?php foreach ($categories as $cat): ?>
                                <?php foreach ($subs as $sub): ?>
                                    <?php if ($sub['id'] == $item['subcategory_id'] && $cat['id'] == $sub['category_id']): ?>
                                        <select name="category_edit" id="category_edit" onchange="editCats(this.value, '<?= $item['id'] ?>')">
                                            <option value="<?= $cat['id'] ?>">--<?= htmlspecialchars($cat['name']) ?>--</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                            <?php endforeach ?>
                                        </select>

                                        <select name="subcategory_edit" id="subcategory_edit_<?= $item['id'] ?>" required>
                                            <option value="<?= $sub['id'] ?>">--<?= htmlspecialchars($sub['name']) ?>--</option>
                                            <?php foreach ($subs as $sub): ?>
                                                <option value="<?= $sub['id'] ?>" data-category="<?= $sub['category_id'] ?>" class="d-none">
                                                    <?= htmlspecialchars($sub['name']) ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    <?php endif ?>
                                <?php endforeach ?>
                            <?php endforeach ?>
                            <button type="button" onclick="toggleEdit('item-<?= $item['id'] ?>')" id="edit_off" class="btn btn-cancel">Cancel</button>
                            <button type="submit" name="edit_item" class="btn btn-save">OK</button>
                        </div>
                    </form>
                    
                </div>
                                
            <?php endforeach ?>
        </div>
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
        function loadSubs(categoryId) {
            const subSelect = document.getElementById('subcategory_select');
            const options = subSelect.querySelectorAll('option[data-category]');

            subSelect.value = "";
            subSelect.disabled = (categoryId === "");

            options.forEach(opt => {
                if (opt.getAttribute('data-category') === categoryId) {
                    opt.classList.remove('d-none');
                }
            });
        }
        function toggleEdit(id) {
            const item = document.getElementById(id);
            const editItem = document.getElementById('edit-' + id);

            
                if (item.classList.contains('d-none')) {
                    item.classList.remove('d-none');
                    editItem.classList.add('d-none');
                } else {
                    item.classList.add('d-none');
                    editItem.classList.remove('d-none');
                }
            
            
        }
        function editCats(editCategoryId, itemId) {
            console.log('editCategoryId', editCategoryId);
            const editSelect = document.getElementById('subcategory_edit_' + itemId);
            const editOptions = editSelect.querySelectorAll('option[data-category]');

            editSelect.value = "";

            editOptions.forEach(editOpt => {
                if (editOpt.getAttribute('data-category') === editCategoryId) {
                    editOpt.classList.remove('d-none');
                } else {
                    editOpt.classList.add('d-none');
                }
            });
        }
        function editImg(id) {
            const btn = document.getElementById(id);
            if (btn) {
                btn.click();
            }
        }

    </script>
    </body>
</html>