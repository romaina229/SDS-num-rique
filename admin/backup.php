<?php
// admin/backup.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

$message = '';
$message_type = '';

// Dossier de sauvegarde
$backup_dir = __DIR__ . '/../backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_backup':
            // Créer une sauvegarde
            $backup_name = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backup_path = $backup_dir . $backup_name;
            
            try {
                // Récupérer toutes les tables
                $tables = [];
                $stmt = $db->query("SHOW TABLES");
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $tables[] = $row[0];
                }
                
                $output = "-- Backup de la base de données Shalom Digital Solutions\n";
                $output .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
                $output .= "-- Généré par: " . $_SESSION['admin_username'] . "\n\n";
                
                foreach ($tables as $table) {
                    // Structure de la table
                    $output .= "--\n-- Structure de la table `$table`\n--\n\n";
                    $output .= "DROP TABLE IF EXISTS `$table`;\n";
                    
                    $stmt = $db->query("SHOW CREATE TABLE `$table`");
                    $row = $stmt->fetch(PDO::FETCH_NUM);
                    $output .= $row[1] . ";\n\n";
                    
                    // Données de la table
                    $output .= "--\n-- Données de la table `$table`\n--\n\n";
                    
                    $stmt = $db->query("SELECT * FROM `$table`");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $output .= "INSERT INTO `$table` VALUES(";
                        $values = [];
                        foreach ($row as $value) {
                            $values[] = is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                        }
                        $output .= implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
                
                // Écrire dans le fichier
                if (file_put_contents($backup_path, $output)) {
                    // Compresser le fichier
                    if (class_exists('ZipArchive')) {
                        $zip = new ZipArchive();
                        $zip_path = str_replace('.sql', '.zip', $backup_path);
                        
                        if ($zip->open($zip_path, ZipArchive::CREATE) === TRUE) {
                            $zip->addFile($backup_path, basename($backup_path));
                            $zip->close();
                            unlink($backup_path); // Supprimer le fichier SQL original
                            $backup_name = str_replace('.sql', '.zip', $backup_name);
                        }
                    }
                    
                    $message = 'Sauvegarde créée avec succès: ' . $backup_name;
                    $message_type = 'success';
                } else {
                    $message = 'Erreur lors de la création de la sauvegarde';
                    $message_type = 'error';
                }
                
            } catch (Exception $e) {
                $message = 'Erreur: ' . $e->getMessage();
                $message_type = 'error';
            }
            break;
            
        case 'restore_backup':
            // Restaurer une sauvegarde
            $backup_file = $_POST['backup_file'] ?? '';
            
            if (empty($backup_file)) {
                $message = 'Veuillez sélectionner un fichier de sauvegarde';
                $message_type = 'error';
            } else {
                $backup_path = $backup_dir . $backup_file;
                
                if (file_exists($backup_path)) {
                    // Désactiver les contraintes de clé étrangère
                    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
                    
                    // Exécuter le script SQL
                    $sql = file_get_contents($backup_path);
                    $db->exec($sql);
                    
                    // Réactiver les contraintes
                    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
                    
                    $message = 'Base de données restaurée avec succès';
                    $message_type = 'success';
                } else {
                    $message = 'Fichier de sauvegarde non trouvé';
                    $message_type = 'error';
                }
            }
            break;
            
        case 'delete_backup':
            // Supprimer une sauvegarde
            $backup_file = $_POST['backup_file'] ?? '';
            
            if (empty($backup_file)) {
                $message = 'Veuillez sélectionner un fichier à supprimer';
                $message_type = 'error';
            } else {
                $backup_path = $backup_dir . $backup_file;
                
                if (file_exists($backup_path) && unlink($backup_path)) {
                    $message = 'Sauvegarde supprimée avec succès';
                    $message_type = 'success';
                } else {
                    $message = 'Erreur lors de la suppression du fichier';
                    $message_type = 'error';
                }
            }
            break;
    }
}

// Lister les sauvegardes existantes
$backups = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && (strpos($file, 'backup_') === 0)) {
            $file_path = $backup_dir . $file;
            $backups[] = [
                'name' => $file,
                'size' => filesize($file_path),
                'date' => date('d/m/Y H:i', filemtime($file_path)),
                'path' => $file_path
            ];
        }
    }
}

// Trier par date (plus récent d'abord)
usort($backups, function($a, $b) {
    return filemtime($b['path']) - filemtime($a['path']);
});

// Taille du dossier de sauvegarde
$total_size = 0;
foreach ($backups as $backup) {
    $total_size += $backup['size'];
}

$page_title = "Sauvegarde - Admin Shalom Digital Solutions";
$page_description = "Gérez les sauvegardes de votre base de données";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <style>
        .backup-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .backup-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .backup-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
        }
        
        .backup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .backup-name {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        .backup-info {
            display: flex;
            gap: 20px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .backup-info span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .backup-actions-btns {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .backup-type {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .type-sql { background-color: #d4edda; color: #155724; }
        .type-zip { background-color: #cce5ff; color: #004085; }
        
        .empty-backups {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
        
        .empty-backups i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        
        .disk-usage {
            background: linear-gradient(135deg, var(--secondary), #2980b9);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .usage-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .usage-bar {
            height: 10px;
            background-color: rgba(255,255,255,0.2);
            border-radius: 5px;
            overflow: hidden;
        }
        
        .usage-fill {
            height: 100%;
            background-color: white;
            border-radius: 5px;
            transition: width 0.3s;
        }
        
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .warning i {
            color: #f39c12;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-database"></i> Sauvegarde de la base de données</h1>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <div class="warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important :</strong> Les sauvegardes sont stockées sur le serveur. Pour plus de sécurité, 
                téléchargez-les régulièrement et stockez-les ailleurs.
            </div>
            
            <!-- Utilisation du disque -->
            <div class="disk-usage">
                <div class="usage-info">
                    <div>
                        <h3 style="color: white; margin: 0;">Espace de sauvegarde</h3>
                        <p style="margin: 5px 0 0 0; opacity: 0.9;">
                            <?php echo count($backups); ?> sauvegarde(s)
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <h3 style="color: white; margin: 0;">
                            <?php echo formatBytes($total_size); ?>
                        </h3>
                        <p style="margin: 5px 0 0 0; opacity: 0.9;">
                            sur 100 MB maximum
                        </p>
                    </div>
                </div>
                
                <div class="usage-bar">
                    <div class="usage-fill" style="width: <?php echo min(($total_size / (100 * 1024 * 1024)) * 100, 100); ?>%;"></div>
                </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="backup-actions">
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="action" value="create_backup">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Créer une nouvelle sauvegarde
                    </button>
                </form>
                
                <button class="btn btn-success" onclick="openModal('restore-modal')">
                    <i class="fas fa-redo"></i> Restaurer une sauvegarde
                </button>
                
                <button class="btn btn-secondary" onclick="downloadAllBackups()">
                    <i class="fas fa-download"></i> Télécharger toutes les sauvegardes
                </button>
            </div>
            
            <!-- Liste des sauvegardes -->
            <div class="backup-container">
                <h2 style="margin-bottom: 20px;">Sauvegardes disponibles</h2>
                
                <?php if (empty($backups)): ?>
                <div class="empty-backups">
                    <i class="fas fa-database"></i>
                    <h3>Aucune sauvegarde disponible</h3>
                    <p>Créez votre première sauvegarde pour protéger vos données.</p>
                </div>
                <?php else: ?>
                
                <?php foreach ($backups as $backup): 
                    $extension = pathinfo($backup['name'], PATHINFO_EXTENSION);
                ?>
                <div class="backup-card">
                    <div class="backup-header">
                        <div>
                            <span class="backup-name">
                                <?php echo htmlspecialchars($backup['name']); ?>
                                <span class="backup-type type-<?php echo $extension; ?>">
                                    .<?php echo strtoupper($extension); ?>
                                </span>
                            </span>
                        </div>
                        
                        <div class="backup-info">
                            <span><i class="fas fa-calendar"></i> <?php echo $backup['date']; ?></span>
                            <span><i class="fas fa-hdd"></i> <?php echo formatBytes($backup['size']); ?></span>
                        </div>
                    </div>
                    
                    <div class="backup-actions-btns">
                        <a href="ajax/download_backup.php?file=<?php echo urlencode($backup['name']); ?>" 
                           class="btn btn-sm btn-success">
                            <i class="fas fa-download"></i> Télécharger
                        </a>
                        
                        <button onclick="restoreBackup('<?php echo htmlspecialchars($backup['name']); ?>')" 
                                class="btn btn-sm btn-warning">
                            <i class="fas fa-redo"></i> Restaurer
                        </button>
                        
                        <button onclick="deleteBackup('<?php echo htmlspecialchars($backup['name']); ?>')" 
                                class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal de restauration -->
    <div id="restore-modal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2><i class="fas fa-redo"></i> Restaurer une sauvegarde</h2>
                <button class="close-modal" onclick="closeModal('restore-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <?php if (empty($backups)): ?>
                <p class="text-muted">Aucune sauvegarde disponible.</p>
                <?php else: ?>
                <form method="POST" action="" id="restore-form">
                    <input type="hidden" name="action" value="restore_backup">
                    
                    <div class="form-group">
                        <label>Sélectionnez une sauvegarde *</label>
                        <select name="backup_file" class="form-control" required>
                            <option value="">Choisir une sauvegarde...</option>
                            <?php foreach ($backups as $backup): ?>
                            <option value="<?php echo htmlspecialchars($backup['name']); ?>">
                                <?php echo htmlspecialchars($backup['name']); ?> 
                                (<?php echo $backup['date']; ?>, <?php echo formatBytes($backup['size']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention :</strong> Cette action va écraser toutes les données actuelles. 
                        Assurez-vous d'avoir une sauvegarde récente avant de continuer.
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="confirm" required>
                            Je comprends que cette action est irréversible
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-redo"></i> Restaurer
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('restore-modal')">
                            Annuler
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    // Formatage des bytes
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Restaurer une sauvegarde
    function restoreBackup(filename) {
        if (confirm(`Êtes-vous sûr de vouloir restaurer la sauvegarde "${filename}" ?\n\nCette action va écraser toutes les données actuelles.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'restore_backup';
            
            const fileInput = document.createElement('input');
            fileInput.type = 'hidden';
            fileInput.name = 'backup_file';
            fileInput.value = filename;
            
            const confirmInput = document.createElement('input');
            confirmInput.type = 'hidden';
            confirmInput.name = 'confirm';
            confirmInput.value = '1';
            
            form.appendChild(actionInput);
            form.appendChild(fileInput);
            form.appendChild(confirmInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Supprimer une sauvegarde
    function deleteBackup(filename) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer la sauvegarde "${filename}" ?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_backup';
            
            const fileInput = document.createElement('input');
            fileInput.type = 'hidden';
            fileInput.name = 'backup_file';
            fileInput.value = filename;
            
            form.appendChild(actionInput);
            form.appendChild(fileInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Télécharger toutes les sauvegardes
    function downloadAllBackups() {
        if (confirm('Télécharger toutes les sauvegardes ? Cela peut prendre du temps.')) {
            window.location.href = 'ajax/download_all_backups.php';
        }
    }
    
    // Gestion des modales
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // Validation du formulaire de restauration
    document.getElementById('restore-form')?.addEventListener('submit', function(e) {
        if (!confirm('ATTENTION : Cette action est irréversible. Voulez-vous vraiment continuer ?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Fermer avec ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('restore-modal');
        }
    });
    
    // Auto-refresh toutes les 30 secondes pour les grosses sauvegardes
    let refreshTimer = null;
    
    function startRefreshTimer() {
        refreshTimer = setTimeout(() => {
            window.location.reload();
        }, 30000);
    }
    
    function stopRefreshTimer() {
        if (refreshTimer) {
            clearTimeout(refreshTimer);
        }
    }
    
    // Démarrer le timer si une opération est en cours
    <?php if (isset($_POST['action']) && $_POST['action'] === 'create_backup'): ?>
    startRefreshTimer();
    <?php endif; ?>
    </script>
</body>
</html>

<!--<?php
// Fonction pour formater les bytes (ajoutez-la à functions.php)
//function formatBytes($bytes) {
  //  if ($bytes == 0) return '0 Bytes';
    
   // $k = 1024;
   // $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
   // $i = floor(log($bytes) / log($k));
    
   // return number_format($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
//}
//?>-->