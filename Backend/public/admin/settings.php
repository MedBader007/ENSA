<?php
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Vérification du rôle administrateur
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Administration ENSA Kénitra</title>
</head>
<body>
    <div id="settings"></div>
    <script>
        // Charger le template HTML existant
        fetch('/FrontEnd/Administrateur/Parametres.html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('settings').innerHTML = html;
                
                // Charger les paramètres actuels
                loadSettings();
                
                // Gérer la soumission des formulaires
                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        
                        const formData = new FormData(e.target);
                        formData.append('action', e.target.id === 'general-form' ? 'update_general' : 'update_security');
                        
                        try {
                            const response = await fetch('/Backend/controllers/settings.php', {
                                method: 'POST',
                                body: formData
                            });
                            
                            const data = await response.json();
                            
                            if (data.success) {
                                showNotification('success', data.message);
                            } else {
                                showNotification('error', data.message);
                            }
                        } catch (error) {
                            showNotification('error', 'Erreur lors de la mise à jour des paramètres');
                        }
                    });
                });
                
                // Fonction pour charger les paramètres
                async function loadSettings() {
                    try {
                        const response = await fetch('/Backend/controllers/settings.php');
                        const data = await response.json();
                        
                        if (data.success) {
                            // Remplir les champs avec les valeurs actuelles
                            const settings = data.settings;
                            
                            // Paramètres généraux
                            Object.keys(settings.general).forEach(key => {
                                const input = document.getElementById(key);
                                if (input) input.value = settings.general[key];
                            });
                            
                            // Paramètres de sécurité
                            Object.keys(settings.security).forEach(key => {
                                const input = document.getElementById(key);
                                if (input) {
                                    if (input.type === 'checkbox') {
                                        input.checked = settings.security[key] === 1;
                                    } else {
                                        input.value = settings.security[key];
                                    }
                                }
                            });
                        }
                    } catch (error) {
                        showNotification('error', 'Erreur lors du chargement des paramètres');
                    }
                }
            });
    </script>
</body>
</html>