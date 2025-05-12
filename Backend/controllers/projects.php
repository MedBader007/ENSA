<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Vérification de l'authentification
requireAuth();

// Soumission d'un nouveau projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit') {
    $title = cleanInput($_POST['title']);
    $description = cleanInput($_POST['description']);
    $type = cleanInput($_POST['type']);
    $module = cleanInput($_POST['module']);
    
    $projectId = createProject($title, $description, $type, $module, $_SESSION['user_id']);
    
    if ($projectId) {
        // Traitement des fichiers
        $uploadSuccess = true;
        if (isset($_FILES['files'])) {
            foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                $file = [
                    'name' => $_FILES['files']['name'][$key],
                    'type' => $_FILES['files']['type'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['files']['error'][$key],
                    'size' => $_FILES['files']['size'][$key]
                ];
                
                if (!uploadFile($file, $projectId)) {
                    $uploadSuccess = false;
                    break;
                }
            }
        }
        
        $response = [
            'success' => true,
            'message' => $uploadSuccess ? 'Projet soumis avec succès' : 'Projet créé mais certains fichiers n\'ont pas pu être uploadés',
            'projectId' => $projectId
        ];
    } else {
        $response = ['success' => false, 'message' => 'Erreur lors de la création du projet'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Récupération des projets
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.firstname, u.lastname, f.filename
            FROM projects p
            JOIN users u ON p.student_id = u.id
            LEFT JOIN files f ON p.id = f.project_id
            WHERE p.student_id = ?
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