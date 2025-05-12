<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Traitement de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    
    if (authenticateUser($email, $password)) {
        $response = ['success' => true, 'message' => 'Connexion réussie'];
        switch ($_SESSION['role']) {
            case 'admin':
                $response['redirect'] = '/admin/dashboard.php';
                break;
            case 'enseignant':
                $response['redirect'] = '/enseignant/dashboard.php';
                break;
            case 'etudiant':
                $response['redirect'] = '/etudiant/dashboard.php';
                break;
        }
    } else {
        $response = ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Traitement de l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $firstname = cleanInput($_POST['firstname']);
    $lastname = cleanInput($_POST['lastname']);
    $role = cleanInput($_POST['role']);
    
    $additionalData = [];
    
    if ($role === 'etudiant') {
        $additionalData = [
            'apogee' => cleanInput($_POST['apogee']),
            'filiere' => cleanInput($_POST['filiere']),
            'niveau' => cleanInput($_POST['niveau'])
        ];
    } elseif ($role === 'enseignant') {
        $additionalData = [
            'department' => cleanInput($_POST['department']),
            'speciality' => cleanInput($_POST['speciality'] ?? null)
        ];
    }
    
    if (registerUser($email, $password, $firstname, $lastname, $role, $additionalData)) {
        $response = ['success' => true, 'message' => 'Inscription réussie'];
    } else {
        $response = ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>