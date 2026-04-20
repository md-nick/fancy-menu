<?php

    $host = 'db';
    $db = 'db';
    $user = 'db';
    $pwd = 'db';

    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pwd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Could not connect to database." . $e->getMessage());
    }

?>