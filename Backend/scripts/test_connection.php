<?php
require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->query("SELECT 1");
    echo "Connexion à la base de données réussie!\n";
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}