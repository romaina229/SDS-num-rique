<?php
// admin/maintenance.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

$message = '';
$message_type = '';

// Vérifier si le mode maintenance est activé
$maintenance_file = __DIR__ . '/../.maintenance';
$is_maintenance = file_exists($maintenance_file);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'toggle_maintenance':
            $enable = isset($_POST['enable_maintenance']) ? (int)$_POST['enable_maintenance'] : 0;
            $message_text = $_POST['maintenance_message'] ?? '';
            
            if ($enable) {
                // Activer le mode maintenance
                $content = "<?php\n";
                $content .= "// Mode maintenance activé le " . date('Y-m-d H:i:s') . "\n";
                $content .= "// Par: " . $_SESSION['admin_username'] . "\n";
                $content .= "// Message: " . $message_text . "\n";
                $content .= "header('HTTP/1.1 503 Service Temporarily Unavailable');\n";
                $content .= "header('Retry-After: 3600');\n";
                $content .= "?>\n";
                $content .= "<!DOCTYPE html>\n";
                $content .= "<html>\n";
                $content .= "<head>\n";
                $content .= "    <title>Maintenance - Shalom Digital Solutions</title>\n";
                $content .= "    <style>\n";
                $content .= "        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #f5f5f5; }\n";
                $content .= "        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
                $content .= "        h1 { color: #e74c3c; }\n";
                $content .= "        .icon { font-size: 60px; color: #f39c12; margin-bottom: 20px; }\n";
                $content .= "    </style>\n";
                $content .= "    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css\">\n";
                $content .= "</head>\n";
                $content .= "<body>\n";
                $content .= "    <div class=\"container\">\n";
                $content .= "        <div class=\"icon\">\n";
                $content .= "            <i class=\"fas fa-tools\"></i>\n";
                $content .= "        </div>\n";
                $content .= "        <h1>Maintenance en cours</h1>\n";
                $content .= "        <p>" . htmlspecialchars($message_text) . "</p>\n";
                $content .= "        <p>Nous effectuons actuellement des travaux de maintenance. Le site sera de retour très bientôt.</p>\n";
                $content .= "        <p>Merci de votre compréhension.</p>\n";
                $content .= "        <p><small>L'équipe Shalom Digital Solutions</small></p>\n";
                $content .= "    </div>\n";
                $content .= "</body>\n";
                $content .= "</html>\n";
                
                if (file_put_contents($maintenance_file, $content)) {
                    $message = 'Mode maintenance activé';
                    $message_type = 'success';
                    $is_maintenance = true;
                } else {
                    $message = 'Erreur lors de l\'activation du mode maintenance';
                    $message_type = 'error';
                }
            } else {
                // Désactiver le mode maintenance
                if (file_exists($maintenance_file) && unlink($maintenance_file)) {
                    $message = 'Mode maintenance désactivé';
                    $message_type = 'success';
                    $is_maintenance = false;
                } else {
                    $message = 'Erreur lors de la désactivation du mode maintenance';
                    $message_type = 'error';
                }
            }
            break;
            
        case 'clear_cache':
            // Nettoyer le cache
            $cache_dir = __DIR__ . '/../cache/';
            $deleted = 0;
            
            if (is_dir($cache_dir)) {
                $files = scandir($cache_dir);
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        $file_path = $cache_dir . $file;
                        if (is_file($file_path) && unlink($file_path)) {
                            $deleted++;
                        }
                    }
                }
            }
            
            $message = "Cache nettoyé : $deleted fichier(s) supprimé(s)";
            $message_type = 'success';
            break;
            
        case 'optimize_db':
            // Optimiser la base de données
            try {
                $stmt = $db->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($tables as $table) {
                    $db->exec("OPTIMIZE TABLE `$table`");
                }
                
                $message = 'Base de données optimisée avec succès';
                $message_type = 'success';
            } catch (Exception $e) {
                $message = 'Erreur lors de l\'optimisation : ' . $e->getMessage();
                $message_type = 'error';
            }
            break;
            
        case 'run_diagnostics':
            // Exécuter les diagnostics
            $diagnostics = [];
            
            // Vérifier les permissions des dossiers
            $folders = [
                'uploads' => '../uploads/',
                'backups' => '../backups/',
                'cache' => '../cache/',
                'logs' => '../logs/'
            ];
            
            foreach ($folders as $name => $path) {
                $full_path = __DIR__ . '/' . $path;
                if (!is_dir($full_path)) {
                    $diagnostics[] = [
                        'name' => "Dossier $name",
                        'status' => 'error',
                        'message' => 'Dossier manquant'
                    ];
                } elseif (!is_writable($full_path)) {
                    $diagnostics[] = [
                        'name' => "Dossier $name",
                        'status' => 'warning',
                        'message' => 'Permissions en écriture manquantes'
                    ];
                } else {
                    $diagnostics[] = [
                        'name' => "Dossier $name",
                        'status' => 'success',
                        'message' => 'OK'
                    ];
                }
            }
            
            // Vérifier la connexion à la base de données
            try {
                $db->query("SELECT 1");
                $diagnostics[] = [
                    'name' => 'Base de données',
                    'status' => 'success',
                    'message' => 'Connexion établie'
                ];
            } catch (Exception $e) {
                $diagnostics[] = [
                    'name' => 'Base de données',
                    'status' => 'error',
                    'message' => 'Erreur de connexion'
                ];
            }
            
            // Vérifier la version PHP
            $php_version = phpversion();
            $diagnostics[] = [
                'name' => 'Version PHP',
                'status' => version_compare($php_version, '7.4', '>=') ? 'success' : 'warning',
                'message' => $php_version
            ];
            
            // Vérifier les extensions PHP
            $required_extensions = ['pdo_mysql', 'mbstring', 'json', 'session'];
            foreach ($required_extensions as $ext) {
                $diagnostics[] = [
                    'name' => "Extension $ext",
                    'status' => extension_loaded($ext) ? 'success' : 'error',
                    'message' => extension_loaded($ext) ? 'Chargée' : 'Manquante'
                ];
            }
            
            $_SESSION['diagnostics'] = $diagnostics;
            $message = 'Diagnostics exécutés avec succès';
            $message_type = 'success';
            break;
    }
}

// Récupérer les diagnostics s'ils existent
$diagnostics = $_SESSION['diagnostics'] ?? [];
unset($_SESSION['diagnostics']);

$page_title = "Maintenance - Admin Shalom Digital Solutions";
$page_description = "Outils de maintenance et diagnostics système";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <style>
        .maintenance-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .maintenance-status {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .status-active {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }
        
        .status-inactive {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .maintenance-icon {
            font-size: 2rem;
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .tool-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 2px solid #f8f9fa;
            transition: all 0.3s;
        }
        
        .tool-card:hover {
            border-color: var(--secondary);
            transform: translateY(-5px);
        }
        
        .tool-icon {
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 15px;
        }
        
        .tool-card h3 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .tool-card p {
            color: #6c757d;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .diagnostics-list {
            margin-top: 20px;
        }
        
        .diagnostic-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .diagnostic-item:last-child {
            border-bottom: none;
        }
        
        .diagnostic-name {
            font-weight: 600;
            color: var(--primary);
        }
        
        .diagnostic-status {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-success { background-color: #d4edda; color: #155724; }
        .status-warning { background-color: #fff3cd; color: #856404; }
        .status-error { background-color: #f8d7da; color: #721c24; }
        
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .warning-box i {
            color: #f39c12;
            margin-right: 10px;
        }
        
        .modal-wide {
            max-width: 700px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary);
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-tools"></i> Maintenance système</h1>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Attention :</strong> Ces outils sont réservés aux administrateurs expérimentés. 
                Une mauvaise utilisation peut entraîner des dysfonctionnements du site.
            </div>
            
            <!-- Statut du mode maintenance -->
            <div class="maintenance-status status-<?php echo $is_maintenance ? 'active' : 'inactive'; ?>">
                <div class="maintenance-icon">
                    <i class="fas fa-<?php echo $is_maintenance ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 5px 0;">
                        Mode maintenance : <?php echo $is_maintenance ? 'ACTIVÉ' : 'DÉSACTIVÉ'; ?>
                    </h3>
                    <p style="margin: 0;">
                        <?php echo $is_maintenance 
                            ? 'Le site public est inaccessible. Seuls les administrateurs peuvent y accéder.' 
                            : 'Le site public est accessible à tous les visiteurs.'; ?>
                    </p>
                </div>
                <div style="margin-left: auto;">
                    <button class="btn btn-<?php echo $is_maintenance ? 'success' : 'warning'; ?>" 
                            onclick="openModal('maintenance-modal')">
                        <i class="fas fa-cog"></i> 
                        <?php echo $is_maintenance ? 'Désactiver' : 'Activer'; ?>
                    </button>
                </div>
            </div>
            
            <!-- Outils de maintenance -->
            <div class="tools-grid">
                <!-- Nettoyage du cache -->
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-broom"></i>
                    </div>
                    <h3>Nettoyage du cache</h3>
                    <p>Supprime les fichiers temporaires pour libérer de l'espace disque et résoudre d'éventuels problèmes d'affichage.</p>
                    <form method="POST" action="" onsubmit="return confirm('Êtes-vous sûr de vouloir nettoyer le cache ?')">
                        <input type="hidden" name="action" value="clear_cache">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play"></i> Exécuter
                        </button>
                    </form>
                </div>
                
                <!-- Optimisation de la base de données -->
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3>Optimisation BDD</h3>
                    <p>Optimise les tables de la base de données pour améliorer les performances et récupérer de l'espace.</p>
                    <form method="POST" action="" onsubmit="return confirm('Êtes-vous sûr de vouloir optimiser la base de données ?')">
                        <input type="hidden" name="action" value="optimize_db">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play"></i> Exécuter
                        </button>
                    </form>
                </div>
                
                <!-- Diagnostics système -->
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h3>Diagnostics</h3>
                    <p>Vérifie l'état du système, les permissions des dossiers, les extensions PHP et autres paramètres critiques.</p>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="run_diagnostics">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play"></i> Exécuter
                        </button>
                    </form>
                </div>
                
                <!-- Logs système -->
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>Journaux système</h3>
                    <p>Consultez les journaux d'erreurs et d'activité pour diagnostiquer les problèmes et surveiller le système.</p>
                    <a href="logs.php" class="btn btn-secondary">
                        <i class="fas fa-external-link-alt"></i> Voir les logs
                    </a>
                </div>
                
                <!-- Sauvegarde -->
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-save"></i>
                    </div>
                    <h3>Sauvegarde</h3>
                    <p>Créez une sauvegarde complète de la base de données avant d'effectuer des modifications importantes.</p>
                    <a href="backup.php" class="btn btn-secondary">
                        <i class="fas fa-external-link-alt"></i> Gérer les sauvegardes
                    </a>
                </div>
                
                <!-- Informations système -->
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3>Informations système</h3>
                    <p>Affiche les informations détaillées sur le serveur, la configuration PHP et les ressources disponibles.</p>
                    <button class="btn btn-secondary" onclick="showSystemInfo()">
                        <i class="fas fa-eye"></i> Afficher
                    </button>
                </div>
            </div>
            
            <!-- Résultats des diagnostics -->
            <?php if (!empty($diagnostics)): ?>
            <div class="maintenance-container">
                <h2>Résultats des diagnostics</h2>
                <div class="diagnostics-list">
                    <?php foreach ($diagnostics as $diag): ?>
                    <div class="diagnostic-item">
                        <span class="diagnostic-name"><?php echo htmlspecialchars($diag['name']); ?></span>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span><?php echo htmlspecialchars($diag['message']); ?></span>
                            <span class="diagnostic-status status-<?php echo $diag['status']; ?>">
                                <?php echo ucfirst($diag['status']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal mode maintenance -->
    <div id="maintenance-modal" class="modal" style="display: none;">
        <div class="modal-content modal-wide">
            <div class="modal-header">
                <h2><i class="fas fa-cog"></i> Configuration du mode maintenance</h2>
                <button class="close-modal" onclick="closeModal('maintenance-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <form method="POST" action="" id="maintenance-form">
                    <input type="hidden" name="action" value="toggle_maintenance">
                    
                    <div class="form-group">
                        <label>Statut</label>
                        <div>
                            <label style="display: inline-block; margin-right: 20px;">
                                <input type="radio" name="enable_maintenance" value="1" 
                                       <?php echo $is_maintenance ? 'checked' : ''; ?>>
                                Activer le mode maintenance
                            </label>
                            <label style="display: inline-block;">
                                <input type="radio" name="enable_maintenance" value="0" 
                                       <?php echo !$is_maintenance ? 'checked' : ''; ?>>
                                Désactiver le mode maintenance
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="maintenance_message">Message de maintenance</label>
                        <textarea name="maintenance_message" id="maintenance_message" class="form-control" 
                                  placeholder="Message affiché aux visiteurs pendant la maintenance">
                            <?php echo $is_maintenance && file_exists($maintenance_file) 
                                ? htmlspecialchars(file_get_contents($maintenance_file)) 
                                : 'Le site est actuellement en maintenance. Nous serons de retour très bientôt !'; ?>
                        </textarea>
                        <small class="text-muted">Ce message sera affiché aux visiteurs du site public.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="notify_admins" value="1" checked>
                            Notifier les autres administrateurs
                        </label>
                        <small class="text-muted">Envoie un email aux autres administrateurs lors de l'activation/désactivation.</small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important :</strong> En mode maintenance, seuls les administrateurs connectés pourront accéder au site. 
                        Tous les autres visiteurs verront le message de maintenance.
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('maintenance-modal')">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal informations système -->
    <div id="system-info-modal" class="modal" style="display: none;">
        <div class="modal-content modal-wide">
            <div class="modal-header">
                <h2><i class="fas fa-info-circle"></i> Informations système</h2>
                <button class="close-modal" onclick="closeModal('system-info-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <div id="system-info-content">
                    <!-- Les informations seront chargées ici -->
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Gestion des modales
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // Afficher les informations système
    function showSystemInfo() {
        fetch('ajax/get_system_info.php')
            .then(response => response.text())
            .then(html => {
                document.getElementById('system-info-content').innerHTML = html;
                openModal('system-info-modal');
            })
            .catch(error => {
                alert('Erreur lors du chargement des informations: ' + error.message);
            });
    }
    
    // Validation du formulaire de maintenance
    document.getElementById('maintenance-form')?.addEventListener('submit', function(e) {
        const enable = document.querySelector('input[name="enable_maintenance"]:checked').value;
        
        if (enable === '1') {
            const message = document.getElementById('maintenance_message').value.trim();
            if (!message) {
                e.preventDefault();
                alert('Veuillez saisir un message de maintenance');
                return;
            }
            
            if (!confirm('Activer le mode maintenance ? Le site sera inaccessible aux visiteurs.')) {
                e.preventDefault();
                return;
            }
        } else {
            if (!confirm('Désactiver le mode maintenance ? Le site sera à nouveau accessible.')) {
                e.preventDefault();
                return;
            }
        }
    });
    
    // Fermer avec ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('maintenance-modal');
            closeModal('system-info-modal');
        }
    });
    
    // Mise à jour dynamique de l'interface selon le mode maintenance
    document.querySelectorAll('input[name="enable_maintenance"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const messageField = document.getElementById('maintenance_message');
            if (this.value === '1') {
                messageField.disabled = false;
                messageField.style.opacity = '1';
            } else {
                messageField.disabled = true;
                messageField.style.opacity = '0.6';
            }
        });
    });
    </script>
</body>
</html>