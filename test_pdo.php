<?php
try {
    $pdo = new PDO('pgsql:host=postgres;port=5432;dbname=development_db', 'senac', 'senac');
    echo "Connected successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
