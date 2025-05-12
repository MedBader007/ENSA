```php
<?php
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Vérification du rôle étudiant
requireRole('etudiant');

// Récupération des informations de l'étudiant
$stmt = $pdo->prepare("
    SELECT u.*, s.apogee_number, s.filiere, s.niveau
    FROM users u
    JOIN students s ON u.id = s.user_id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Étudiant - ENSA Kénitra</title>
</head>
<body>
    <div id="dashboard"></div>
    <script>
        // Charger le template HTML existant
        fetch('/FrontEnd/Etudiant/etud.html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('dashboard').innerHTML = html;
                
                // Mettre à jour les informations de l'étudiant
                const userInfo = document.querySelector('.user-info span');
                userInfo.textContent = '<?php echo $student['firstname'] . ' ' . $student['lastname']; ?>';
                
                // Modifier le comportement du formulaire de soumission
                document.querySelector('.form-container form').addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const formData = new FormData();
                    formData.append('action', 'submit');
                    formData.append('title', document.getElementById('project-title').value);
                    formData.append('type', document.getElementById('project-type').value);
                    formData.append('module', document.getElementById('project-module').value);
                    formData.append('description', document.getElementById('project-description').value);
                    
                    // Ajouter les fichiers
                    const fileInput = document.getElementById('fileInput');
                    for (let i = 0; i < fileInput.files.length; i++) {
                        formData.append('files[]', fileInput.files[i]);
                    }
                    
                    try {
                        const response = await fetch('/Backend/controllers/projects.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            showNotification('success', data.message);
                            // Recharger la liste des projets
                            loadProjects();
                        } else {
                            showNotification('error', data.message);
                        }
                    } catch (error) {
                        showNotification('error', 'Erreur lors de la soumission du projet');
                    }
                });
                
                // Fonction pour charger les projets
                async function loadProjects() {
                    try {
                        const response = await fetch('/Backend/controllers/projects.php');
                        const data = await response.json();
                        
                        if (data.success) {
                            const tbody = document.querySelector('.projects-table tbody');
                            tbody.innerHTML = '';
                            
                            data.projects.forEach(project => {
                                tbody.innerHTML += `
                                    <tr>
                                        <td>${project.title}</td>
                                        <td>${project.type}</td>
                                        <td>${project.module || 'N/A'}</td>
                                        <td>${new Date(project.created_at).toLocaleDateString()}</td>
                                        <td><span class="status status-${project.status}">${project.status}</span></td>
                                        <td>
                                            <button class="action-btn view-btn" onclick="viewProject(${project.id})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            ${project.status === 'pending' ? `
                                                <button class="action-btn edit-btn" onclick="editProject(${project.id})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            ` : ''}
                                        </td>
                                    </tr>
                                `;
                            });
                        }
                    } catch (error) {
                        showNotification('error', 'Erreur lors du chargement des projets');
                    }
                }
                
                // Charger les projets au chargement de la page
                loadProjects();
            });
    </script>
</body>
</html>
```