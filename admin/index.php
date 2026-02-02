<?php
// admin/index.php
session_start();
require_once '../config/database.php';

// Vérifier si admin est connecté
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer les statistiques
$query_stats = "SELECT 
    COUNT(*) as total_commandes,
    SUM(montant_fcfa) as total_revenus,
    COUNT(DISTINCT client_email) as total_clients,
    (SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente') as en_attente,
    (SELECT COUNT(*) FROM commandes WHERE DATE(date_commande) = CURDATE()) as commandes_aujourdhui,
    (SELECT SUM(montant_fcfa) FROM commandes WHERE DATE(date_commande) = CURDATE()) as revenus_aujourdhui
    FROM commandes";
$stmt_stats = $db->prepare($query_stats);
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Récupérer les commandes récentes
$query_commandes = "SELECT c.*, s.categorie, s.nom as service_nom 
                   FROM commandes c 
                   LEFT JOIN services s ON c.service_id = s.id 
                   ORDER BY c.date_commande DESC 
                   LIMIT 8";
$stmt_commandes = $db->prepare($query_commandes);
$stmt_commandes->execute();
$commandes = $stmt_commandes->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les statistiques mensuelles
$query_monthly = "SELECT 
    DATE_FORMAT(date_commande, '%Y-%m') as mois,
    COUNT(*) as nb_commandes,
    SUM(montant_fcfa) as total_mois
    FROM commandes 
    WHERE date_commande >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(date_commande, '%Y-%m')
    ORDER BY mois DESC";
$stmt_monthly = $db->prepare($query_monthly);
$stmt_monthly->execute();
$monthly_stats = $stmt_monthly->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Tableau de bord - Admin Shalom Digital Solutions";
include 'includes/header.php';
include 'sidebar.php';
?>

<div class="main-content">
    <!-- En-tête de la page -->
    <div class="page-header">
        <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord</h1>
        <p class="page-subtitle">Aperçu global de votre activité</p>
    </div>
    
    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['total_commandes']); ?></h3>
                <p>Commandes totales</p>
                <small style="color: #10b981; font-weight: 500;">
                    <i class="fas fa-arrow-up"></i> 
                    +<?php echo $stats['commandes_aujourdhui']; ?> aujourd'hui
                </small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['total_revenus'], 0, ',', ' '); ?> FCFA</h3>
                <p>Revenus totaux</p>
                <small style="color: #10b981; font-weight: 500;">
                    <i class="fas fa-arrow-up"></i> 
                    +<?php echo number_format($stats['revenus_aujourdhui'] ?? 0, 0, ',', ' '); ?> FCFA aujourd'hui
                </small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['total_clients']); ?></h3>
                <p>Clients</p>
                <small>Clients actifs</small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['en_attente']); ?></h3>
                <p>En attente</p>
                <small>Nécessitent attention</small>
            </div>
        </div>
    </div>
    
    <!-- Actions rapides -->
    <div class="quick-actions">
        <a href="services.php" class="action-btn">
            <i class="fas fa-plus-circle"></i>
            <span>Nouveau Service</span>
        </a>
        <a href="commandes.php" class="action-btn">
            <i class="fas fa-plus-circle"></i>
            <span>Nouvelle Commande</span>
        </a>
        <a href="generer-facture.php" class="action-btn">
            <i class="fas fa-file-invoice"></i>
            <span>Générer Facture</span>
        </a>
        <a href="statistiques.php" class="action-btn">
            <i class="fas fa-chart-line"></i>
            <span>Voir Rapports</span>
        </a>
    </div>
    
    <!-- Charts Section -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3>Commandes par mois</h3>
            <div class="monthly-stats">
                <?php if (!empty($monthly_stats)): ?>
                    <?php foreach ($monthly_stats as $month): 
                        $month_name = DateTime::createFromFormat('Y-m', $month['mois'])->format('F Y');
                    ?>
                    <div class="month-item">
                        <span class="month-name"><?php echo $month_name; ?></span>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span class="month-count"><?php echo $month['nb_commandes']; ?> cmd</span>
                            <span class="month-amount"><?php echo number_format($month['total_mois'], 0, ',', ' '); ?> FCFA</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #64748b; text-align: center; padding: 20px;">Aucune donnée disponible</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="chart-card">
            <h3>Statistiques rapides</h3>
            <div style="padding: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                    <span>Taux de conversion</span>
                    <span style="font-weight: 600; color: var(--success);"><?php echo $stats['total_commandes'] > 0 ? round(($stats['total_commandes'] / $stats['total_clients']) * 100, 1) : 0; ?>%</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                    <span>Panier moyen</span>
                    <span style="font-weight: 600; color: var(--primary);">
                        <?php echo $stats['total_commandes'] > 0 ? number_format($stats['total_revenus'] / $stats['total_commandes'], 0, ',', ' ') : 0; ?> FCFA
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                    <span>Commandes aujourd'hui</span>
                    <span style="font-weight: 600; color: var(--warning);"><?php echo $stats['commandes_aujourdhui']; ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Revenus aujourd'hui</span>
                    <span style="font-weight: 600; color: var(--success);"><?php echo number_format($stats['revenus_aujourdhui'] ?? 0, 0, ',', ' '); ?> FCFA</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Commandes récentes -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-history"></i> Commandes récentes</h2>
            <a href="commandes.php">
                Voir toutes <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($commandes)): ?>
                        <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary);">#<?php echo htmlspecialchars($commande['numero_commande']); ?></strong>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($commande['client_nom']); ?></div>
                                <small style="color: #64748b;"><?php echo htmlspecialchars($commande['client_email']); ?></small>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($commande['service_nom']); ?></div>
                                <small style="color: #64748b;"><?php echo $commande['categorie']; ?></small>
                            </td>
                            <td>
                                <strong style="color: var(--success);"><?php echo number_format($commande['montant_fcfa'], 0, ',', ' '); ?> FCFA</strong>
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($commande['date_commande'])); ?>
                                <br>
                                <small style="color: #64748b;"><?php echo date('H:i', strtotime($commande['date_commande'])); ?></small>
                            </td>
                            <td>
                                <span class="status <?php echo $commande['statut']; ?>">
                                    <?php 
                                    $statuts = [
                                        'en_attente' => 'En attente',
                                        'confirmee' => 'Confirmée',
                                        'annulee' => 'Annulée',
                                        'terminee' => 'Terminée'
                                    ];
                                    echo $statuts[$commande['statut']] ?? $commande['statut'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn btn-primary btn-sm" 
                                            onclick="window.location.href='commande_detail.php?id=<?php echo $commande['id']; ?>'">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if($commande['statut'] == 'en_attente'): ?>
                                    <button class="btn btn-success btn-sm" 
                                            onclick="updateStatus(<?php echo $commande['id']; ?>, 'confirmee')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">
                                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block; color: #cbd5e1;"></i>
                                Aucune commande récente
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function updateStatus(commandeId, newStatus) {
    if (confirm('Êtes-vous sûr de vouloir modifier le statut de cette commande ?')) {
        fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + commandeId + '&statut=' + newStatus
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Statut mis à jour avec succès', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Erreur lors de la mise à jour', 'error');
            }
        })
        .catch(error => {
            showNotification('Erreur réseau', 'error');
        });
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease;
    `;
    
    if (type === 'success') {
        notification.style.background = '#10b981';
    } else {
        notification.style.background = '#ef4444';
    }
    
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Ajouter les styles d'animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>
</body>
</html>