<?php
require_once __DIR__ . '/../config/database.php';

// Création de la base de données
$sql = file_get_contents(__DIR__ . '/../../supabase/migrations/20250512155413_curly_snowflake.sql');

try {
    $pdo->exec($sql);
    echo "Base de données initialisée avec succès!\n";
} catch(PDOException $e) {
    die("Erreur d'initialisation : " . $e->getMessage());
}