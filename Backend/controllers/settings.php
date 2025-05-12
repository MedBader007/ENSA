<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Vérification du rôle administrateur
requireRole('admin');

// Mise à jour des paramètres généraux
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_general') {
    try {
        $stmt = $pdo->prepare("
            UPDATE settings 
            SET school_name = ?,
                school_email = ?,
                school_phone = ?,
                school_website = ?,
                year_start = ?,
                year_end = ?
            WHERE id = 1
        ");
        
        $stmt->execute([
            cleanInput($_POST['school_name']),
            cleanInput($_POST['school_email']),
            cleanInput($_POST['school_phone']),
            cleanInput($_POST['school_website']),
            cleanInput($_POST['year_start']),
            cleanInput($_POST['year_end'])
        ]);
        
        $response = ['success' => true, 'message' => 'Paramètres généraux mis à jour avec succès'];
    } catch(PDOException $e) {
        $response = ['success' => false, 'message' => 'Erreur lors de la mise à jour des paramètres'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Mise à jour des paramètres de sécurité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_security') {
    try {
        $stmt = $pdo->prepare("
            UPDATE security_settings 
            SET two_factor_required = ?,
                login_attempts_limit = ?,
                session_timeout = ?,
                default_teacher_access = ?
            WHERE id = 1
        ");
        
        $stmt->execute([
            isset($_POST['two_factor_required']) ? 1 : 0,
            cleanInput($_POST['login_attempts_limit']),
            cleanInput($_POST['session_timeout']),
            cleanInput($_POST['default_teacher_access'])
        ]);
        
        $response = ['success' => true, 'message' => 'Paramètres de sécurité mis à jour avec succès'];
    } catch(PDOException $e) {
        $response = ['success' => false, 'message' => 'Erreur lors de la mise à jour des paramètres de sécurité'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Récupération des paramètres
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Paramètres généraux
        $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
        $generalSettings = $stmt->fetch();
        
        // Paramètres de sécurité
        $stmt = $pdo->query("SELECT * FROM security_settings WHERE id = 1");
        $securitySettings = $stmt->fetch();
        
        $response = [
            'success' => true,
            'settings' => [
                'general' => $generalSettings,
                'security' => $securitySettings
            ]
        ];
    } catch(PDOException $e) {
        $response = ['success' => false, 'message' => 'Erreur lors de la récupération des paramètres'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>