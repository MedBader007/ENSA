<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Vérification du rôle enseignant
requireRole('enseignant');

// Soumission d'une évaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = cleanInput($_POST['project_id']);
    $note = floatval($_POST['note']);
    $comment = cleanInput($_POST['comment']);
    
    if ($note < 0 || $note > 20) {
        $response = ['success' => false, 'message' => 'La note doit être comprise entre 0 et 20'];
    } else {
        if (evaluateProject($projectId, $_SESSION['user_id'], $note, $comment)) {
            $response = ['success' => true, 'message' => 'Évaluation enregistrée avec succès'];
        } else {
            $response = ['success' => false, 'message' => 'Erreur lors de l\'enregistrement de l\'évaluation'];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Récupération des projets à évaluer
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.firstname, u.lastname, f.filename
            FROM projects p
            JOIN users u ON p.student_id = u.id
            LEFT JOIN files f ON p.id = f.project_id
            WHERE p.teacher_id = ? OR p.teacher_id IS NULL
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $projects = $stmt->fetchAll();
        
        $response = ['success' => true, 'projects' => $projects];
    } catch(PDOException $e) {
        $response = ['success' => false, 'message' => 'Erreur lors de la récupération des projets'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>