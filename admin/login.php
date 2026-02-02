<?php
// admin/login.php
session_start();

// Rediriger si déjà connecté
if (isset($_SESSION['admin']) && $_SESSION['admin']) {
    header('Location: index.php');
    exit();
}

// Traitement du formulaire
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Validation
    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        // Inclure les fonctions

// includes/functions.php
require_once '../includes/functions.php';
        
        // Rechercher l'utilisateur
        $db = new PDO('mysql:host=localhost;dbname=lifero_pro', 'root','');
        $sql = "SELECT * FROM admins WHERE username = :username AND active = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Vérifier le mot de passe (en production, utiliser password_verify)
            if (md5($password) === $admin['password']) {
                // Connexion réussie
                $_SESSION['admin'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_last_login'] = date('Y-m-d H:i:s');
                
                // Mettre à jour la dernière connexion
                $update_sql = "UPDATE admins SET last_login = NOW() WHERE id = :id";
                $update_stmt = $db->prepare($update_sql);
                $update_stmt->execute([':id' => $admin['id']]);
                
                // Rediriger vers le dashboard
                header('Location: index.php');
                exit();
            } else {
                $error = 'Mot de passe incorrect';
            }
        } else {
            $error = 'Utilisateur non trouvé';
        }
    }
}

// Générer un token CSRF
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Logo du site-->
    <link rel="icon" href="../assets/images/Faviconsds.png" type="image/png">
    <title>Connexion Admin - Shalom Digital Solutions</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <a href="../index.php">
                Shalom Digital <span>Solutions</span>
            </a>
        </div>
        
        <h1>Connexion Administrateur</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" class="form-control" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       placeholder="Entrez votre nom d'utilisateur">
            </div>
            
            <div class="form-group password-toggle">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required 
                       placeholder="Entrez votre mot de passe">
                <button type="button" class="toggle-btn" id="toggle-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </div>
            
            <div class="form-group" style="text-align: center;">
                <label>
                    <input type="checkbox" name="remember" id="remember" value="1">
                    Se souvenir de moi
                </label>
            </div>
        </form>
        
        <div class="login-footer">
            <p>
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i> Retour au site
                </a>
                | 
                <a href="forgot-password.php">
                    <i class="fas fa-key"></i> Mot de passe oublié ?
                </a>
            </p>
            <p style="margin-top: 10px; font-size: 0.8rem;">
                Version 1.0.0 | © <?php echo date('Y'); ?> Shalom DigitalSolutions
            </p>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const toggleBtn = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            
            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            });
            
            // Form validation
            const form = document.getElementById('login-form');
            form.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value.trim();
                
                if (!username || !password) {
                    e.preventDefault();
                    alert('Veuillez remplir tous les champs');
                    return false;
                }
                
                // Show loading
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion...';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            });
            
            // Auto-focus on username field
            document.getElementById('username').focus();
            
            // Check for saved credentials
            if (localStorage.getItem('remember_username')) {
                document.getElementById('username').value = localStorage.getItem('remember_username');
                document.getElementById('remember').checked = true;
            }
            
            // Save credentials if remember is checked
            document.getElementById('remember').addEventListener('change', function() {
                const username = document.getElementById('username').value;
                if (this.checked && username) {
                    localStorage.setItem('remember_username', username);
                } else {
                    localStorage.removeItem('remember_username');
                }
            });
        });
    </script>
</body>
</html>