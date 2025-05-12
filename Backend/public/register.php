```php
<?php
require_once '../includes/session.php';

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - ENSA Kénitra</title>
</head>
<body>
    <div id="register-form"></div>
    <script>
        // Récupérer le formulaire HTML existant selon le rôle
        const role = new URLSearchParams(window.location.search).get('role') || 'etudiant';
        const formPath = `/FrontEnd/${role.charAt(0).toUpperCase() + role.slice(1)}/register${role.charAt(0).toUpperCase() + role.slice(1)}.html`;
        
        fetch(formPath)
            .then(response => response.text())
            .then(html => {
                document.getElementById('register-form').innerHTML = html;
                
                // Modifier le comportement du formulaire
                document.querySelector('form').addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const formData = new FormData(e.target);
                    formData.append('action', 'register');
                    formData.append('role', role);
                    
                    try {
                        const response = await fetch('/Backend/controllers/auth.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.href = '/login.php';
                        } else {
                            // Afficher l'erreur avec la fonction showNotification existante
                            showNotification('error', data.message);
                        }
                    } catch (error) {
                        showNotification('error', 'Erreur de connexion au serveur');
                    }
                });
            });
    </script>
</body>
</html>
```