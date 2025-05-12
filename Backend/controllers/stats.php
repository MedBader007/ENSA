<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Vérification du rôle administrateur
requireRole('admin');

// Récupération des statistiques globales
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['type']) && $_GET['type'] === 'global') {
    try {
        // Statistiques des projets
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_projects,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_projects,
                SUM(CASE WHEN status = 'validated' THEN 1 ELSE 0 END) as validated_projects,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_projects
            FROM projects
        ");
        $projectStats = $stmt->fetch();
        
        // Statistiques des utilisateurs
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN role = 'etudiant' THEN 1 ELSE 0 END) as student_count,
                SUM(CASE WHEN role = 'enseignant' THEN 1 ELSE 0 END) as teacher_count,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count
            FROM users
        ");
        $userStats = $stmt->fetch();
        
        // Statistiques par filière
        $stmt = $pdo->query("
            SELECT filiere, COUNT(*) as count
            FROM students
            GROUP BY filiere
        ");
        $filiereStats = $stmt->fetchAll();
        
        $response = [
            'success' => true,
            'stats' => [
                'projects' => $projectStats,
                'users' => $userStats,
                'filieres' => $filiereStats
            ]
        ];
    } catch(PDOException $e) {
        $response = ['success' => false, 'message' => 'Erreur lors de la récupération des statistiques'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Statistiques par période
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['type']) && $_GET['type'] === 'period') {
    $startDate = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-1 month'));
    $endDate = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
    
    try {
        // Projets par jour
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
            FROM projects
            WHERE created_at BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        $projectsByDay = $stmt->fetchAll();
        
        // Évaluations par jour
        $stmt = $pdo->prepare("
            SELECT 
                DATE(evaluated_at) as date,
                COUNT(*) as count,
                AVG(note) as average_note
            FROM evaluations
            WHERE evaluated_at BETWEEN ? AND ?
            GROUP BY DATE(evaluated_at)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        $evaluationsByDay = $stmt->fetchAll();
        
        $response = [
            'success' => true,
            'stats' => [
                'projects' => $projectsByDay,
                'evaluations' => $evaluationsByDay
            ]
        ];
    } catch(PDOException $e) {
        $response = ['success' => false, 'message' => 'Erreur lors de la récupération des statistiques'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>