```php
<?php
require_once '../includes/session.php';

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: /admin/dashboard.php');
            break;
        case 'enseignant':
            header('Location: /enseignant/dashboard.php');
            break;
        case 'etudiant':
            header('Location: /etudiant/dashboard.php');
            break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ENSA Kénitra</title>
</head>
<body>
    <div id="login-form"></div>
    <script>
        // Récupérer le formulaire HTML existant
        fetch('/FrontEnd/Etudiant/loginEtudiant.html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('login-form').innerHTML = html;
                
                // Modifier le comportement du formulaire
                document.querySelector('form').addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const formData = new FormData(e.target);
                    formData.append('action', 'login');
                    
                    try {
                        const response = await fetch('/Backend/controllers/auth.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.href = data.redirect;
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