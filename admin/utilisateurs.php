<?php
// admin/utilisateurs.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

$message = '';
$message_type = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_user':
            // Ajouter un utilisateur
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            
            // Validation du mot de passe
            if (strlen($password) < 8) {
                $message = 'Le mot de passe doit contenir au moins 8 caractères';
                $message_type = 'error';
                break;
            }
            
            // Vérifier si l'utilisateur existe déjà
            $checkSql = "SELECT COUNT(*) FROM admins WHERE username = :username OR email = :email";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute([':username' => $username, ':email' => $email]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $message = 'Un utilisateur avec ce nom ou cet email existe déjà';
                $message_type = 'error';
            } else {
                $sql = "INSERT INTO admins (username, password, email, role, active) 
                        VALUES (:username, :password, :email, :role, 1)";
                
                $stmt = $db->prepare($sql);
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                if ($stmt->execute([
                    ':username' => $username,
                    ':password' => $hashedPassword,
                    ':email' => $email,
                    ':role' => $role
                ])) {
                    $message = 'Utilisateur ajouté avec succès';
                    $message_type = 'success';
                } else {
                    $message = 'Erreur lors de l\'ajout de l\'utilisateur';
                    $message_type = 'error';
                }
            }
            break;
            
        case 'update_user':
            // Modifier un utilisateur
            $id = $_POST['id'];
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $active = isset($_POST['active']) ? 1 : 0;
            
            // Validation du mot de passe s'il est fourni
            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) < 8) {
                    $message = 'Le mot de passe doit contenir au moins 8 caractères';
                    $message_type = 'error';
                    break;
                }
            }
            
            // Construire la requête dynamiquement
            $sql = "UPDATE admins SET username = :username, email = :email, role = :role, active = :active";
            $params = [
                ':id' => $id,
                ':username' => $username,
                ':email' => $email,
                ':role' => $role,
                ':active' => $active
            ];
            
            // Ajouter le mot de passe s'il est fourni
            if (!empty($_POST['password'])) {
                $sql .= ", password = :password";
                $params[':password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            if ($stmt->execute($params)) {
                $message = 'Utilisateur modifié avec succès';
                $message_type = 'success';
            } else {
                $message = 'Erreur lors de la modification de l\'utilisateur';
                $message_type = 'error';
            }
            break;
            
        case 'delete_user':
            // Supprimer un utilisateur
           $id = $_POST['id'];
            
            // Ne pas permettre de se supprimer soi-même
            if ($id == $_SESSION['admin_id']) {
                $message = 'Vous ne pouvez pas supprimer votre propre compte';
                $message_type = 'error';
            } else {
                $sql = "DELETE FROM admins WHERE id = :id";
                $stmt = $db->prepare($sql);
                
               if ($stmt->execute([':id' => $id])) {
                    $message = 'Utilisateur supprimé avec succès';
                    $message_type = 'success';
                } else {
                    $message = 'Erreur lors de la suppression de l\'utilisateur';
                    $message_type = 'error';
                }
            }
            break;
    }
}

// Récupérer tous les utilisateurs
$sql = "SELECT id, username, email, role, active, created_at, last_login 
        FROM admins 
        ORDER BY created_at DESC";
$stmt = $db->query($sql);
$users = $stmt->fetchAll();

// Rôles disponibles
$roles = [
    'admin' => 'Administrateur',
    'editor' => 'Éditeur',
    'viewer' => 'Observateur'
];

// Fonction pour obtenir les initiales
//function getInitials($name) {
   // if (empty($name)) return '??';
   // $parts = explode(' ', trim($name));
   // $initials = '';
   // foreach ($parts as $part) {
     //   if (!empty($part)) {
     //       $initials .= strtoupper(substr($part, 0, 1));
     //   }
   // }
   // return substr($initials, 0, 2);
//}

$page_title = "Gestion des Utilisateurs - Admin Shalom Digital Solutions";
$page_description = "Gérez les utilisateurs ayant accès au panel admin";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <style>
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .user-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .user-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary), #2980b9);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .user-info h3 {
            margin: 0 0 5px 0;
            color: var(--primary);
        }
        
        .user-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .user-details {
            margin: 15px 0;
        }
        
        .user-details p {
            margin: 5px 0;
            font-size: 0.9rem;
        }
        
        .user-details strong {
            color: var(--primary);
            display: inline-block;
            width: 100px;
        }
        
        .user-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .role-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .role-admin {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .role-editor {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        
        .role-viewer {
            background-color: #e8f5e8;
            color: #2e7d32;
        }
        
        .user-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .modal-wide {
            max-width: 700px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        /* Styles pour les alertes */
        .alert {
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-user-shield"></i> Gestion des Utilisateurs</h1>
                <button class="btn btn-primary" onclick="openModal('add-user-modal')">
                    <i class="fas fa-plus"></i> Ajouter un utilisateur
                </button>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($users); ?></h3>
                        <p>Utilisateurs total</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($users, function($user) { return $user['active'] == 1; })); ?></h3>
                        <p>Actifs</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($users, function($user) { return $user['active'] == 0; })); ?></h3>
                        <p>Inactifs</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($users, function($user) { return $user['role'] == 'admin'; })); ?></h3>
                        <p>Administrateurs</p>
                    </div>
                </div>
            </div>
            
            <div class="table-container">
                <?php if (empty($users)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucun utilisateur trouvé.
                </div>
                <?php else: ?>
                
                <div class="users-grid">
                    <?php foreach ($users as $user): 
                        $initials = getInitials($user['username']);
                        $lastLogin = $user['last_login'] 
                            ? date('d/m/Y H:i', strtotime($user['last_login'])) 
                            : 'Jamais connecté';
                    ?>
                    <div class="user-card">
                        <span class="user-status status-<?php echo $user['active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $user['active'] ? 'Actif' : 'Inactif'; ?>
                        </span>
                        
                        <div class="user-header">
                            <div class="user-avatar">
                                <?php echo $initials; ?>
                            </div>
                            <div class="user-info">
                                <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                                <p><?php echo htmlspecialchars($user['email']); ?></p>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo $roles[$user['role']] ?? $user['role']; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="user-details">
                            <p><strong>ID :</strong> <?php echo $user['id']; ?></p>
                            <p><strong>Créé le :</strong> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                            <p><strong>Dernière connexion :</strong> <?php echo $lastLogin; ?></p>
                        </div>
                        
                        <div class="user-actions">
                            <button onclick="editUser(<?php echo $user['id']; ?>)" 
                                    class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            
                            <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                            <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['username'])); ?>')" 
                                    class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal d'ajout d'utilisateur -->
    <div id="add-user-modal" class="modal">
        <div class="modal-content modal-wide">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Ajouter un utilisateur</h2>
                <button class="close-modal" onclick="closeModal('add-user-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <form method="POST" action="" id="add-user-form">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Nom d'utilisateur *</label>
                            <input type="text" name="username" id="username" class="form-control" required
                                   placeholder="Ex: admin2">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" name="email" id="email" class="form-control" required
                                   placeholder="exemple@liferopro.com">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Mot de passe *</label>
                            <input type="password" name="password" id="password" class="form-control" required
                                   placeholder="Minimum 8 caractères">
                            <small class="text-muted">Le mot de passe sera crypté avant stockage</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe *</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="role">Rôle *</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="">Sélectionner un rôle</option>
                                <?php foreach ($roles as $value => $label): ?>
                                <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                            
                            <div class="mt-3" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
                                <small>
                                    <strong>Permissions par rôle :</strong><br>
                                    • Administrateur : Toutes les permissions<br>
                                    • Éditeur : Gestion des commandes et clients<br>
                                    • Observateur : Lecture seule
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Créer l'utilisateur
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('add-user-modal')">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal de modification d'utilisateur -->
    <div id="edit-user-modal" class="modal">
        <div class="modal-content modal-wide">
            <div class="modal-header">
                <h2><i class="fas fa-user-edit"></i> Modifier l'utilisateur</h2>
                <button class="close-modal" onclick="closeModal('edit-user-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <form method="POST" action="" id="edit-user-form">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="id" id="edit-user-id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-username">Nom d'utilisateur *</label>
                            <input type="text" name="username" id="edit-username" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit-email">Email *</label>
                            <input type="email" name="email" id="edit-email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-password">Nouveau mot de passe</label>
                            <input type="password" name="password" id="edit-password" class="form-control"
                                   placeholder="Laisser vide pour ne pas changer">
                            <small class="text-muted">Minimum 8 caractères</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit-confirm_password">Confirmer le mot de passe</label>
                            <input type="password" name="confirm_password" id="edit-confirm_password" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-role">Rôle *</label>
                            <select name="role" id="edit-role" class="form-control" required>
                                <option value="">Sélectionner un rôle</option>
                                <?php foreach ($roles as $value => $label): ?>
                                <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="active" id="edit-active" value="1">
                                Compte actif
                            </label>
                            <small class="help-text">Désactiver pour bloquer l'accès</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('edit-user-modal')">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    // Gestion des modales
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        document.body.style.overflow = '';
        // Réinitialiser les formulaires
        const form = document.getElementById(modalId)?.querySelector('form');
        if (form) {
            form.reset();
        }
    }
    
    // Validation du formulaire d'ajout
    const addUserForm = document.getElementById('add-user-form');
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas');
                return false;
            }
            
            return true;
        });
    }
    
    // Validation du formulaire d'édition
    const editUserForm = document.getElementById('edit-user-form');
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            const password = document.getElementById('edit-password').value;
            const confirmPassword = document.getElementById('edit-confirm_password').value;
            
            if (password && password.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères');
                return false;
            }
            
            if (password && password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas');
                return false;
            }
            
            return true;
        });
    }
    
    // Éditer un utilisateur - Version simplifiée sans AJAX
    function editUser(userId) {
        // Récupérer les données de l'utilisateur à partir du DOM
        const userCard = document.querySelector(`.user-card:has([onclick*="${userId}"])`);
        if (!userCard) {
            alert('Utilisateur non trouvé');
            return;
        }
        
        const username = userCard.querySelector('.user-info h3').textContent;
        const email = userCard.querySelector('.user-info p').textContent;
        const role = userCard.querySelector('.role-badge').className.match(/role-(\w+)/)?.[1] || '';
        const active = userCard.querySelector('.user-status').textContent.trim() === 'Actif';
        
        // Remplir le formulaire
        document.getElementById('edit-user-id').value = userId;
        document.getElementById('edit-username').value = username;
        document.getElementById('edit-email').value = email;
        document.getElementById('edit-role').value = role;
        document.getElementById('edit-active').checked = active;
        
        openModal('edit-user-modal');
    }
    
    // Supprimer un utilisateur
    function deleteUser(userId, username) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${username}" ? Cette action est irréversible.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_user';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = userId;
            
            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Fermer avec ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('add-user-modal');
            closeModal('edit-user-modal');
        }
    });
    
    // Fermer en cliquant en dehors de la modal
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
    </script>
</body>
</html>