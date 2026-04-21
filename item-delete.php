<?php 
    require_once 'config/check-login.php';
    require_once 'config/config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $deleteId = $_POST['delete_id'];
        try {
            $stmt = $pdo->prepare('select * from items where id = ?');
            $stmt->execute([$deleteId]);

            if (isset($_POST['delete_item'])) {
                $sql = 'delete from items where id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $deleteId]);
            }

            if (isset($_POST['hide_item'])) {
                $sql = 'update items set 
                is_visible = 0 
                where id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $deleteId]);
            }
        } catch (PDOException $e) {
            die($e);
        }
        header("Location: item-create.php");
        exit();     
    }
?>