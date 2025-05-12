```php
<?php
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Vérification du rôle administrateur
requireRole('admin');

// Récupération des informations de l'administrateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin'");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Administrateur - ENSA Kénitra</title>
</head>
<body>
    <div id="dashboard"></div>
    <script>
        // Charger le template HTML existant
        fetch('/FrontEnd/Administrateur/admin.html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('dashboard').innerHTML = html;
                
                // Mettre à jour les informations de l'administrateur
                const userInfo = document.querySelector('.user-info span');
                userInfo.textContent = '<?php echo $admin['firstname'] . ' ' . $admin['lastname']; ?>';
                
                // Fonction pour charger les statistiques
                async function loadStats() {
                    try {
                        const response = await fetch('/Backend/controllers/admin.php?action=stats');
                        const data = await response.json();
                        
                        if (data.success) {
                            // Mettre à jour les statistiques dans le dashboard
                            const projectStats = data.stats.projects;
                            const userStats = data.stats.users;
                            
                            // Mettre à jour le graphique des projets
                            updateProjectChart(projectStats);
                            
                            // Mettre à jour les statistiques utilisateurs
                            updateUserStats(userStats);
                        }
                    } catch (error) {
                        showNotification('error', 'Erreur lors du chargement des statistiques');
                    }
                }
                
                // Fonction pour charger la liste des utilisateurs
                async function loadUsers() {
                    try {
                        const response = await fetch('/Backend/controllers/admin.php?action=users');
                        const data = await response.json();
                        
                        if (data.success) {
                            const tbody = document.querySelector('.projects-table tbody');
                            tbody.innerHTML = '';
                            
                            data.users.forEach(user => {
                                tbody.innerHTML += `
                                    <tr>
                                        <td>${user.lastname} ${user.firstname}</td>
                                        <td>${user.email}</td>
                                        <td>${user.role}</td>
                                        <td>
                                            <button class="btn btn-edit" onclick="editUser(${user.id})">
                                                <i class="fas fa-edit"></i>
                                                <span>Éditer</span>
                                            </button>
                                            <button class="btn btn-delete" onclick="deleteUser(${user.id})">
                                                <i class="fas fa-trash"></i>
                                                <span>Supprimer</span>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                        }
                    } catch (error) {
                        showNotification('error', 'Erreur lors du chargement des utilisateurs');
                    }
                }
                
                // Fonction pour supprimer un utilisateur
                window.deleteUser = async function(userId) {
                    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                        try {
                            const formData = new FormData();
                            formData.append('action', 'delete_user');
                            formData.append('user_id', userId);
                            
                            const response = await fetch('/Backend/controllers/admin.php', {
                                method: 'POST',
                                body: formData
                            });
                            
                            const data = await response.json();
                            
                            if (data.success) {
                                showNotification('success', data.message);
                                loadUsers(); // Recharger la liste
                            } else {
                                showNotification('error', data.message);
                            }
                        } catch (error) {
                            showNotification('error', 'Erreur lors de la suppression');
                        }
                    }
                };
                
                // Fonction pour éditer un utilisateur
                window.editUser = function(userId) {
                    // Implémenter la logique d'édition
                };
                
                // Fonction pour mettre à jour le graphique des projets
                function updateProjectChart(stats) {
                    const validatedCount = stats.find(s => s.status === 'validated')?.count || 0;
                    const pendingCount = stats.find(s => s.status === 'pending')?.count || 0;
                    const rejectedCount = stats.find(s => s.status === 'rejected')?.count || 0;
                    
                    document.querySelector('.bar.bar1').style.height = `${(validatedCount / (validatedCount + pendingCount + rejectedCount)) * 100}%`;
                    document.querySelector('.bar.bar2').style.height = `${(pendingCount / (validatedCount + pendingCount + rejectedCount)) * 100}%`;
                    document.querySelector('.bar.bar3').style.height = `${(rejectedCount / (validatedCount + pendingCount + rejectedCount)) * 100}%`;
                }
                
                // Fonction pour mettre à jour les statistiques utilisateurs
                function updateUserStats(stats) {
                    const adminCount = stats.find(s => s.role === 'admin')?.count || 0;
                    const teacherCount = stats.find(s => s.role === 'enseignant')?.count || 0;
                    const studentCount = stats.find(s => s.role === 'etudiant')?.count || 0;
                    
                    // Mettre à jour le graphique en camembert
                    const total = adminCount + teacherCount + studentCount;
                    const pieChart = document.querySelector('.pie-chart');
                    pieChart.style.background = `conic-gradient(
                        var(--secondary) 0% ${(adminCount/total)*100}%,
                        var(--accent) ${(adminCount/total)*100}% ${((adminCount+teacherCount)/total)*100}%,
                        #E67E22 ${((adminCount+teacherCount)/total)*100}% 100%
                    )`;
                }
                
                // Charger les données initiales
                loadStats();
                loadUsers();
                
                // Gérer les exports
                document.querySelectorAll('.btn-export').forEach(btn => {
                    btn.addEventListener('click', function() {
                        // Implémenter la logique d'export
                        showNotification('success', 'Export en cours de préparation...');
                    });
                });
            });
    </script>
</body>
</html>
```