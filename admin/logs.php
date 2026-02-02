<?php
// admin/logs.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

// Filtres
$type = $_GET['type'] ?? 'all';
$date = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

// Construire la requête
$sql = "SELECT * FROM system_logs WHERE 1=1";
$params = [];

if ($type !== 'all') {
    $sql .= " AND type = :type";
    $params[':type'] = $type;
}

if ($date) {
    $sql .= " AND DATE(created_at) = :date";
    $params[':date'] = $date;
}

if ($search) {
    $sql .= " AND (message LIKE :search OR user_ip LIKE :search OR user_agent LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$sql .= " ORDER BY created_at DESC LIMIT 1000";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Types de logs disponibles
$logTypes = [
    'all' => 'Tous les types',
    'login' => 'Connexions',
    'action' => 'Actions',
    'error' => 'Erreurs',
    'security' => 'Sécurité',
    'system' => 'Système'
];

$page_title = "Journaux système - Admin Shalom Digital Solutions";
$page_description = "Consultez les journaux d'activité du système";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <style>
        .logs-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .log-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        
        .log-item:hover {
            background-color: #f8f9fa;
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .log-type {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .type-login { background-color: #d1ecf1; color: #0c5460; }
        .type-action { background-color: #d4edda; color: #155724; }
        .type-error { background-color: #f8d7da; color: #721c24; }
        .type-security { background-color: #fff3cd; color: #856404; }
        .type-system { background-color: #e2e3e5; color: #383d41; }
        
        .log-date {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .log-message {
            margin: 5px 0;
            word-break: break-word;
        }
        
        .log-meta {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 8px;
        }
        
        .log-actions {
            margin-top: 10px;
        }
        
        .log-actions button {
            background: none;
            border: none;
            color: var(--secondary);
            cursor: pointer;
            font-size: 0.85rem;
            padding: 2px 5px;
        }
        
        .log-actions button:hover {
            text-decoration: underline;
        }
        
        .empty-logs {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
        
        .empty-logs i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-clipboard-list"></i> Journaux système</h1>
                <div class="header-actions">
                    <button class="btn btn-danger" onclick="clearLogs()">
                        <i class="fas fa-trash"></i> Vider les journaux
                    </button>
                    <button class="btn btn-secondary" onclick="exportLogs()">
                        <i class="fas fa-download"></i> Exporter
                    </button>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($logs); ?></h3>
                        <p>Entrées totales</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($logs, function($log) { return $log['type'] == 'login'; })); ?></h3>
                        <p>Connexions</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($logs, function($log) { return $log['type'] == 'error'; })); ?></h3>
                        <p>Erreurs</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($logs, function($log) { return $log['type'] == 'security'; })); ?></h3>
                        <p>Alertes sécurité</p>
                    </div>
                </div>
            </div>
            
            <!-- Filtres -->
            <div class="filters-container">
                <form method="GET" class="filters-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Type de log</label>
                            <select name="type">
                                <?php foreach ($logTypes as $value => $label): ?>
                                <option value="<?php echo $value; ?>" 
                                        <?php echo $type == $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Date</label>
                            <input type="date" name="date" 
                                   value="<?php echo htmlspecialchars($date); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label>Recherche</label>
                            <input type="text" name="search" placeholder="Message, IP, navigateur..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filtrer
                            </button>
                            <a href="logs.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Liste des logs -->
            <div class="logs-container">
                <?php if (empty($logs)): ?>
                <div class="empty-logs">
                    <i class="fas fa-clipboard"></i>
                    <h3>Aucun log trouvé</h3>
                    <p>Aucune entrée ne correspond à vos critères de recherche.</p>
                </div>
                <?php else: ?>
                
                <div class="log-list">
                    <?php foreach ($logs as $log): ?>
                    <div class="log-item">
                        <div class="log-header">
                            <span class="log-type type-<?php echo $log['type']; ?>">
                                <?php echo $logTypes[$log['type']] ?? $log['type']; ?>
                            </span>
                            <span class="log-date">
                                <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                            </span>
                        </div>
                        
                        <div class="log-message">
                            <?php echo htmlspecialchars($log['message']); ?>
                        </div>
                        
                        <div class="log-meta">
                            <?php if ($log['user_id']): ?>
                            <span>User ID: <?php echo $log['user_id']; ?></span>
                            <?php endif; ?>
                            
                            <?php if ($log['user_ip']): ?>
                            <span>IP: <?php echo htmlspecialchars($log['user_ip']); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($log['user_agent']): ?>
                            <span>Navigateur: <?php echo substr(htmlspecialchars($log['user_agent']), 0, 50); ?>...</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="log-actions">
                            <button onclick="viewLogDetails(<?php echo $log['id']; ?>)">
                                <i class="fas fa-search"></i> Détails
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal détails log -->
    <div id="log-details-modal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-search"></i> Détails du log</h2>
                <button class="close-modal" onclick="closeModal('log-details-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="log-details-content">
                    <!-- Les détails seront chargés ici -->
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Voir les détails d'un log
    function viewLogDetails(logId) {
        fetch('ajax/get_log_details.php?id=' + logId)
            .then(response => response.text())
            .then(html => {
                document.getElementById('log-details-content').innerHTML = html;
                openModal('log-details-modal');
            })
            .catch(error => {
                alert('Erreur lors du chargement des détails: ' + error.message);
            });
    }
    
    // Exporter les logs
    function exportLogs() {
        const filters = new URLSearchParams(window.location.search);
        window.location.href = 'ajax/export_logs.php?' + filters.toString();
    }
    
    // Vider les logs
    function clearLogs() {
        if (confirm('Êtes-vous sûr de vouloir vider tous les journaux ? Cette action est irréversible.')) {
            fetch('ajax/clear_logs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erreur réseau: ' + error.message);
            });
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
    
    // Fermer avec ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('log-details-modal');
        }
    });
    </script>
</body>
</html>