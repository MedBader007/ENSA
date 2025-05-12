<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Vérification du rôle administrateur
requireRole('admin');

// Gestion des utilisateurs
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'users') {
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, 
                   CASE 
                       WHEN s.user_id IS NOT NULL THEN 'etudiant'
                       WHEN t.user_id IS NOT NULL THEN 'enseignant'
                       ELSE 'admin'
                   END as user_type
            FROM users u
            LEFT JOIN students s ON u.id = s.user_id
            LEFT JOIN teachers t ON u.id = t.user_id
            ORDER BY u.created_at DESC
        ");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        $response = ['success' => true, 'users' => $users];
    } catch(PDOException $e) {
        $response = ['success' => false, 'message' => 'Erreur lors de la récupération des utilisateurs'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Statistiques
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'stats') {
    try {
        // Nombre total de projets par statut
        $stmt = $pdo->query("
            SELECT status, COUNT(*) as count
            FROM projects
            GROUP BY status
        ");
        $projectStats = $stmt->fetchAll();
        
        // Nombre d'utilisateurs par rôle
        $stmt = $pdo->query("
            SELECT role, COUNT(*) as count
            FROM users
            GROUP BY role
        ");
        $userStats = $stmt->fetchAll();
        
        $response = [
            'success' => true,
            'stats' => [
                'projects' => $projectStats,
                'users' => $userStats
            ]
        ];
    } catch(PDOException $e) {
        $response = ['success' => false, 'message' => 'Erreur lors de la récupération des statistiques'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Suppression d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $userId = cleanInput($_POST['user_id']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        $response = ['success' => true, 'message' => 'Utilisateur supprimé avec succès'];
    } catch(PDOException $e) {
        $response = ['success' => false, 'message' => 'Erreur lors de la suppression de l\'utilisateur'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>