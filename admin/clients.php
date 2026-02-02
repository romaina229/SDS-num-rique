<?php
// admin/clients.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

// Fonction pour obtenir les initiales
//function getInitials($name) {
  //  if (empty($name)) return '??';
  //  $parts = explode(' ', trim($name));
  //  $initials = '';
   // foreach ($parts as $part) {
   //     if (!empty($part)) {
   //         $initials .= strtoupper(substr($part, 0, 1));
     //   }
   // }
 //   return substr($initials, 0, 2);
//}

// Fonction pour formater le prix
//function formatPrice($amount) {
 //   if (empty($amount)) return '0 FCFA';
 //   return number_format($amount, 0, ',', ' ') . ' FCFA';
//}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filtres
$filters = [];
$params = [];

// Recherche
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $filters[] = "(client_nom LIKE :search OR client_email LIKE :search OR client_telephone LIKE :search OR client_entreprise LIKE :search)";
    $params[':search'] = $search;
}

// Date de début
if (isset($_GET['date_debut']) && !empty($_GET['date_debut'])) {
    $filters[] = "DATE(date_commande) >= :date_debut";
    $params[':date_debut'] = $_GET['date_debut'];
}

// Date de fin
if (isset($_GET['date_fin']) && !empty($_GET['date_fin'])) {
    $filters[] = "DATE(date_commande) <= :date_fin";
    $params[':date_fin'] = $_GET['date_fin'];
}

// Construire la requête pour les clients
$sql = "SELECT SQL_CALC_FOUND_ROWS 
            client_email,
            client_nom,
            client_telephone,
            client_entreprise,
            COUNT(*) as total_orders,
            SUM(montant_fcfa) as total_spent,
            MIN(date_commande) as first_order_date,
            MAX(date_commande) as last_order_date
        FROM commandes
        WHERE client_email != '' AND client_email IS NOT NULL";
        
if (!empty($filters)) {
    $sql .= " AND " . implode(" AND ", $filters);
}

$sql .= " GROUP BY client_email 
          ORDER BY last_order_date DESC 
          LIMIT :offset, :perPage";

// Préparer et exécuter
$stmt = $db->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

$stmt->execute();
$clients = $stmt->fetchAll();

// Récupérer le total
$totalStmt = $db->query("SELECT FOUND_ROWS()");
$totalItems = $totalStmt->fetchColumn();
$totalPages = ceil($totalItems / $perPage);

// Statistiques
$statsSql = "SELECT 
                COUNT(DISTINCT client_email) as total_clients,
                AVG(total_orders) as avg_orders_per_client,
                AVG(total_spent) as avg_spent_per_client
            FROM (
                SELECT 
                    client_email,
                    COUNT(*) as total_orders,
                    SUM(montant_fcfa) as total_spent
                FROM commandes
                WHERE client_email != '' AND client_email IS NOT NULL
                GROUP BY client_email
            ) as client_stats";
$statsStmt = $db->query($statsSql);
$stats = $statsStmt->fetch();

// Top clients
$topClientsSql = "SELECT 
                    client_nom,
                    client_email,
                    COUNT(*) as order_count,
                    SUM(montant_fcfa) as total_spent
                  FROM commandes
                  WHERE client_email != '' AND client_email IS NOT NULL
                  GROUP BY client_email
                  ORDER BY total_spent DESC
                  LIMIT 5";
$topClientsStmt = $db->query($topClientsSql);
$topClients = $topClientsStmt->fetchAll();

$page_title = "Gestion des Clients - Admin Shalom Digital Solutions";
$page_description = "Gérez vos clients et leurs commandes";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <style>
        .clients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .client-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .client-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .client-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--secondary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
        }
        
        .client-info h3 {
            margin: 0 0 5px 0;
            color: var(--primary);
        }
        
        .client-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .client-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 15px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
            display: block;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }
        
        .client-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .client-details {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
            color: #666;
        }
        
        .client-details p {
            margin: 5px 0;
        }
        
        .top-clients {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .top-client-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .top-client-item:last-child {
            border-bottom: none;
        }
        
        .client-rank {
            width: 30px;
            height: 30px;
            background-color: var(--secondary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .client-rank.rank-1 { background-color: #FFD700; }
        .client-rank.rank-2 { background-color: #C0C0C0; }
        .client-rank.rank-3 { background-color: #CD7F32; }
        
        .client-spent {
            font-weight: bold;
            color: var(--primary);
        }
        
        .export-options {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .client-tags {
            display: flex;
            gap: 5px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .client-tag {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 12px;
            background-color: #e9ecef;
            color: #495057;
        }
        
        .client-tag.vip { background-color: #fff3cd; color: #856404; }
        .client-tag.new { background-color: #d1ecf1; color: #0c5460; }
        .client-tag.regular { background-color: #d4edda; color: #155724; }
        
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
        
        /* Filtres */
        .filters-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
            padding: 20px 0;
        }
        
        .page-link {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            background: white;
            transition: all 0.3s;
        }
        
        .page-link:hover,
        .page-link.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        /* Modal */
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
            max-width: 600px;
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
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-users"></i> Gestion des Clients</h1>
                <div class="header-actions">
                    <button class="btn btn-success" onclick="exportClients()">
                        <i class="fas fa-file-export"></i> Exporter
                    </button>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_clients'] ?? 0; ?></h3>
                        <p>Clients total</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['avg_orders_per_client'] ?? 0, 1); ?></h3>
                        <p>Commandes moyennes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo formatPrice($stats['avg_spent_per_client'] ?? 0); ?></h3>
                        <p>Dépense moyenne</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalItems; ?></h3>
                        <p>Clients actifs</p>
                    </div>
                </div>
            </div>
            
            <!-- Filtres -->
            <div class="filters-container">
                <form method="GET" class="filters-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Recherche</label>
                            <input type="text" name="search" placeholder="Nom, email, téléphone, entreprise..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label>Date de début</label>
                            <input type="date" name="date_debut" 
                                   value="<?php echo isset($_GET['date_debut']) ? htmlspecialchars($_GET['date_debut']) : ''; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label>Date de fin</label>
                            <input type="date" name="date_fin" 
                                   value="<?php echo isset($_GET['date_fin']) ? htmlspecialchars($_GET['date_fin']) : ''; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filtrer
                            </button>
                            <a href="clients.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Liste des clients -->
            <div class="table-container">
                <?php if (empty($clients)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucun client trouvé.
                </div>
                <?php else: ?>
                
                <div class="clients-grid">
                    <?php foreach ($clients as $client): 
                        $initials = getInitials($client['client_nom']);
                        $orderCount = $client['total_orders'];
                        $totalSpent = $client['total_spent'];
                        $firstOrderDate = !empty($client['first_order_date']) ? date('d/m/Y', strtotime($client['first_order_date'])) : 'N/A';
                        $lastOrderDate = !empty($client['last_order_date']) ? date('d/m/Y', strtotime($client['last_order_date'])) : 'N/A';
                        
                        // Déterminer les tags
                        $tags = [];
                        if ($orderCount >= 5) $tags[] = ['class' => 'vip', 'text' => 'VIP'];
                        if ($orderCount == 1) $tags[] = ['class' => 'new', 'text' => 'Nouveau'];
                        if ($orderCount >= 3) $tags[] = ['class' => 'regular', 'text' => 'Régulier'];
                    ?>
                    <div class="client-card">
                        <div class="client-header">
                            <div class="client-avatar">
                                <?php echo $initials; ?>
                            </div>
                            <div class="client-info">
                                <h3><?php echo htmlspecialchars($client['client_nom'] ?: 'Non renseigné'); ?></h3>
                                <p><?php echo htmlspecialchars($client['client_email'] ?: 'Non renseigné'); ?></p>
                                <?php if ($client['client_telephone']): ?>
                                <p><?php echo htmlspecialchars($client['client_telephone']); ?></p>
                                <?php endif; ?>
                                <?php if ($client['client_entreprise']): ?>
                                <p><small><?php echo htmlspecialchars($client['client_entreprise']); ?></small></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($tags)): ?>
                        <div class="client-tags">
                            <?php foreach ($tags as $tag): ?>
                            <span class="client-tag <?php echo $tag['class']; ?>"><?php echo $tag['text']; ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="client-stats">
                            <div class="stat-item">
                                <span class="stat-value"><?php echo $orderCount; ?></span>
                                <span class="stat-label">Commandes</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value"><?php echo formatPrice($totalSpent); ?></span>
                                <span class="stat-label">Total dépensé</span>
                            </div>
                        </div>
                        
                        <div class="client-details">
                            <p><strong>Première commande :</strong> <?php echo $firstOrderDate; ?></p>
                            <p><strong>Dernière commande :</strong> <?php echo $lastOrderDate; ?></p>
                        </div>
                        
                        <div class="client-actions">
                            <a href="commandes.php?search=<?php echo urlencode($client['client_email']); ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-shopping-cart"></i> Voir commandes
                            </a>
                            <?php if ($client['client_email'] && $client['client_email'] != 'Non renseigné'): ?>
                            <a href="mailto:<?php echo htmlspecialchars($client['client_email']); ?>" 
                               class="btn btn-sm btn-secondary">
                                <i class="fas fa-envelope"></i> Email
                            </a>
                            <?php endif; ?>
                            <button onclick="viewClientDetails('<?php echo htmlspecialchars(addslashes($client['client_email'])); ?>', '<?php echo htmlspecialchars(addslashes($client['client_nom'])); ?>')" 
                                    class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> Détails
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i> Précédent
                    </a>
                    <?php endif; ?>
                    
                    <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                       class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-link">
                        Suivant <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
            
            <!-- Top clients -->
            <?php if (!empty($topClients)): ?>
            <div class="top-clients">
                <h3><i class="fas fa-trophy"></i> Top 5 des clients</h3>
                <?php foreach ($topClients as $index => $topClient): ?>
                <div class="top-client-item">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div class="client-rank rank-<?php echo $index + 1; ?>">
                            <?php echo $index + 1; ?>
                        </div>
                        <div>
                            <strong><?php echo htmlspecialchars($topClient['client_nom'] ?: 'Non renseigné'); ?></strong>
                            <p style="margin: 2px 0 0 0; font-size: 0.9rem; color: #666;">
                                <?php echo htmlspecialchars($topClient['client_email'] ?: 'Non renseigné'); ?>
                            </p>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div class="client-spent"><?php echo formatPrice($topClient['total_spent']); ?></div>
                        <small><?php echo $topClient['order_count']; ?> commandes</small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Options d'export -->
            <div class="export-options">
                <button class="btn btn-secondary" onclick="exportData('csv')">
                    <i class="fas fa-file-csv"></i> Exporter en CSV
                </button>
                <button class="btn btn-secondary" onclick="exportData('excel')">
                    <i class="fas fa-file-excel"></i> Exporter en Excel
                </button>
                <button class="btn btn-secondary" onclick="exportData('pdf')">
                    <i class="fas fa-file-pdf"></i> Exporter en PDF
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal détails client -->
    <div id="client-details-modal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2><i class="fas fa-user"></i> Détails du client</h2>
                <button class="close-modal" onclick="closeModal('client-details-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="client-details-content">
                    <!-- Les détails seront chargés dynamiquement -->
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Fonction pour obtenir les initiales
    function getInitials(name) {
        if (!name || name.trim() === '') return '??';
        const parts = name.trim().split(' ');
        let initials = '';
        for (let part of parts) {
            if (part) {
                initials += part.charAt(0).toUpperCase();
            }
        }
        return initials.substring(0, 2);
    }
    
    // Voir les détails d'un client
    function viewClientDetails(email, name) {
        const content = `
            <div style="padding: 20px;">
                <h3 style="color: var(--primary); margin-bottom: 20px;">Détails du client</h3>
                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background-color: var(--secondary); 
                                color: white; display: flex; align-items: center; justify-content: center; 
                                font-size: 32px; font-weight: bold;">
                        ${getInitials(name)}
                    </div>
                    <div>
                        <h4 style="margin: 0 0 5px 0;">${name}</h4>
                        <p style="margin: 0; color: #666;"><i class="fas fa-envelope"></i> ${email}</p>
                    </div>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h4 style="margin-top: 0;"><i class="fas fa-info-circle"></i> Informations</h4>
                    <p>Pour voir toutes les commandes de ce client, cliquez sur "Voir commandes" dans la carte du client.</p>
                    <p>Vous pouvez également envoyer un email directement en cliquant sur le bouton "Email".</p>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="commandes.php?search=${encodeURIComponent(email)}" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i> Voir toutes les commandes
                    </a>
                    ${email && email !== 'Non renseigné' ? `
                    <a href="mailto:${email}" class="btn btn-secondary" style="margin-left: 10px;">
                        <i class="fas fa-envelope"></i> Envoyer un email
                    </a>
                    ` : ''}
                </div>
            </div>
        `;
        document.getElementById('client-details-content').innerHTML = content;
        openModal('client-details-modal');
    }
    
    // Exporter les données
    function exportData(format) {
        alert(`Fonctionnalité d'export ${format.toUpperCase()} en cours de développement.\nLes filtres actuels seront pris en compte.`);
        // Implémentation future :
        // const filters = new URLSearchParams(window.location.search);
        // filters.append('format', format);
        // window.location.href = 'export-clients.php?' + filters.toString();
    }
    
    // Export général
    function exportClients() {
        alert('Sélectionnez un format d\'export dans les options ci-dessous.');
    }
    
    // Gestion des modales
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
    
    // Fermer la modal avec ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('client-details-modal');
        }
    });
    
    // Fermer en cliquant en dehors de la modal
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
    
    // Améliorer l'expérience de filtrage
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.form.submit();
                }
            });
        }
    });
    </script>
</body>
</html>