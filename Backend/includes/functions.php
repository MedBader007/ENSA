<?php
require_once __DIR__ . '/../config/config.php';

// Fonction pour l'authentification
function authenticateUser($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    } catch(PDOException $e) {
        error_log("Erreur d'authentification : " . $e->getMessage());
        return false;
    }
}

// Fonction pour l'inscription
function registerUser($email, $password, $firstname, $lastname, $role, $additionalData = []) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Insertion dans la table users
        $stmt = $pdo->prepare("INSERT INTO users (email, password, firstname, lastname, role) VALUES (?, ?, ?, ?, ?)");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$email, $hashedPassword, $firstname, $lastname, $role]);
        $userId = $pdo->lastInsertId();
        
        // Insertion dans la table spécifique selon le rôle
        if ($role === 'etudiant' && isset($additionalData['apogee']) && isset($additionalData['filiere'])) {
            $stmt = $pdo->prepare("INSERT INTO students (user_id, apogee_number, filiere, niveau) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $additionalData['apogee'], $additionalData['filiere'], $additionalData['niveau']]);
        } elseif ($role === 'enseignant' && isset($additionalData['department'])) {
            $stmt = $pdo->prepare("INSERT INTO teachers (user_id, department, speciality) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $additionalData['department'], $additionalData['speciality'] ?? null]);
        }
        
        $pdo->commit();
        return true;
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log("Erreur d'inscription : " . $e->getMessage());
        return false;
    }
}

// Fonction pour la gestion des fichiers
function uploadFile($file, $projectId) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    
    // Vérification de l'extension
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return false;
    }
    
    // Vérification de la taille
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Génération d'un nom unique
    $newFilename = uniqid() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . '/' . $newFilename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        try {
            global $pdo;
            $stmt = $pdo->prepare("INSERT INTO files (project_id, filename, original_name, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$projectId, $newFilename, $file['name'], $file['type'], $file['size']]);
            return true;
        } catch(PDOException $e) {
            error_log("Erreur d'upload : " . $e->getMessage());
            unlink($uploadPath);
            return false;
        }
    }
    return false;
}

// Fonction pour la création d'un projet
function createProject($title, $description, $type, $module, $studentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO projects (title, description, type, module, student_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $type, $module, $studentId]);
        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        error_log("Erreur de création de projet : " . $e->getMessage());
        return false;
    }
}

// Fonction pour l'évaluation d'un projet
function evaluateProject($projectId, $teacherId, $note, $comment) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO evaluations (project_id, teacher_id, note, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$projectId, $teacherId, $note, $comment]);
        
        // Mise à jour du statut du projet
        $stmt = $pdo->prepare("UPDATE projects SET status = 'validated' WHERE id = ?");
        $stmt->execute([$projectId]);
        
        return true;
    } catch(PDOException $e) {
        error_log("Erreur d'évaluation : " . $e->getMessage());
        return false;
    }
}
?>