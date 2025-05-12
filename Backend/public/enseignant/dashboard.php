```php
<?php
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Vérification du rôle enseignant
requireRole('enseignant');

// Récupération des informations de l'enseignant
$stmt = $pdo->prepare("
    SELECT u.*, t.department, t.speciality
    FROM users u
    JOIN teachers t ON u.id = t.user_id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$teacher = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Enseignant - ENSA Kénitra</title>
</head>
<body>
    <div id="dashboard"></div>
    <script>
        // Charger le template HTML existant
        fetch('/FrontEnd/Enseignant/prof.html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('dashboard').innerHTML = html;
                
                // Mettre à jour les informations de l'enseignant
                const userInfo = document.querySelector('.user-info span');
                userInfo.textContent = '<?php echo $teacher['firstname'] . ' ' . $teacher['lastname']; ?>';
                
                // Modifier le comportement du formulaire d'évaluation
                document.querySelector('.evaluation form').addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const formData = new FormData(e.target);
                    
                    try {
                        const response = await fetch('/Backend/controllers/evaluations.php', {
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
                        showNotification('error', 'Erreur lors de l\'évaluation du projet');
                    }
                });
                
                // Fonction pour charger les projets à évaluer
                async function loadProjects() {
                    try {
                        const response = await fetch('/Backend/controllers/evaluations.php');
                        const data = await response.json();
                        
                        if (data.success) {
                            const projectsContainer = document.querySelector('.projects .card');
                            projectsContainer.innerHTML = '';
                            
                            data.projects.forEach(project => {
                                projectsContainer.innerHTML += `
                                    <div class="project-card">
                                        <h3>${project.title}</h3>
                                        <p><strong>Étudiant:</strong> ${project.firstname} ${project.lastname}</p>
                                        <p><strong>Type:</strong> ${project.type}</p>
                                        <p><strong>Module:</strong> ${project.module || 'N/A'}</p>
                                        <p><strong>Statut:</strong> <span class="status status-${project.status}">${project.status}</span></p>
                                        <div class="actions">
                                            <button class="btn" onclick="viewProject(${project.id})">
                                                <i class="fas fa-eye"></i> Voir détails
                                            </button>
                                            ${project.status === 'pending' ? `
                                                <button class="btn" onclick="evaluateProject(${project.id})">
                                                    <i class="fas fa-check"></i> Évaluer
                                                </button>
                                            ` : ''}
                                        </div>
                                    </div>
                                `;
                            });
                        }
                    } catch (error) {
                        showNotification('error', 'Erreur lors du chargement des projets');
                    }
                }
                
                // Fonction pour évaluer un projet
                window.evaluateProject = function(projectId) {
                    const evaluationForm = document.querySelector('.evaluation');
                    evaluationForm.style.display = 'block';
                    evaluationForm.querySelector('form').dataset.projectId = projectId;
                };
                
                // Fonction pour voir les détails d'un projet
                window.viewProject = function(projectId) {
                    // Implémenter la logique d'affichage des détails
                };
                
                // Charger les projets au chargement de la page
                loadProjects();
            });
    </script>
</body>
</html>
```