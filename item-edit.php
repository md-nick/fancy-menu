<?php
    require_once 'config/check-login.php';
    require_once 'config/config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_item'])) {   
        $maxSize = 5 * pow(10, 6);
        $imagePath = '';
        $id = $_POST['edit_id'];
        try {
            $stmt = $pdo->prepare('select * from items where id = ?');
            $stmt->execute([$id]);
            $currentItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $name = $_POST['edit_name'];
            $description = $_POST['edit_description'];
            $price = $_POST['edit_price'];
            $sub_id = $_POST['subcategory_edit'];
            $is_visible = ($currentItem['is_visible'] == 0) ? 1 : $currentItem['is_visible'];
            $imagePath = $currentItem['image'];
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                if ($_FILES['image']['size'] === 0) {
                    $error = "Empty file. Please try again.";
                } elseif ($_FILES['image']['size'] > $maxSize) {
                    $error = "File size too large. Please choose a smaller image.";
                } else {
                    $allowedTypes = ['jpeg', 'png', 'jpg', 'gif', 'avif'];
                    $imageInfo = pathinfo($_FILES['image']['name']);
                    $extension = strtolower($imageInfo['extension'] ?? '');
                    if (in_array($extension, $allowedTypes)) {
                        $uploadDir = 'images/';
                        $imageName = time() . '_' . basename($_FILES['image']['name']);
                        $targetPath = $uploadDir . $imageName;

                        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                            if (!empty($currentItem['image']) && file_exists($currentItem['image'])) {
                                unlink($currentItem['image']);
                            }
                            $imagePath = $targetPath;
                        }
                    }
                }
            }
            $sql = 'update items set 
                name = :name, 
                description = :description, 
                price = :price, image = :image, 
                subcategory_id = :subcategory_id, 
                is_visible = :is_visible 
                where id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':image' => $imagePath,
                ':subcategory_id' => $sub_id,
                ':is_visible' => $is_visible,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            die($e);
        }
        
        header("Location: item-create.php");
        exit();
    }
    
?>
