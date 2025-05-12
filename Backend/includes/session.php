<?php
// Démarrage sécurisé de la session
function secureSessionStart() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1);
    session_start();
    session_regenerate_id(true);
}

// Vérification de l'authentification
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit();
    }
}

// Vérification du rôle
function requireRole($role) {
    requireAuth();
    if ($_SESSION['role'] !== $role) {
        header('Location: /unauthorized.php');
        exit();
    }
}

// Déconnexion sécurisée
function logout() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
}
?>