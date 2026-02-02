<?php
// admin/factures.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filtres
$filters = [];
$params = [];

if (isset($_GET['statut']) && $_GET['statut'] !== '') {
    $filters[] = "f.statut = :statut";
    $params[':statut'] = $_GET['statut'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $filters[] = "(f.numero_facture LIKE :search OR c.client_nom LIKE :search OR c.client_email LIKE :search)";
    $params[':search'] = $search;
}

if (isset($_GET['date_debut']) && !empty($_GET['date_debut'])) {
    $filters[] = "DATE(f.date_facture) >= :date_debut";
    $params[':date_debut'] = $_GET['date_debut'];
}

if (isset($_GET['date_fin']) && !empty($_GET['date_fin'])) {
    $filters[] = "DATE(f.date_facture) <= :date_fin";
    $params[':date_fin'] = $_GET['date_fin'];
}

// Construire la requête
$sql = "SELECT f.*, 
               c.client_nom, c.client_email, c.client_telephone,
               cmd.numero_commande
        FROM factures f
        LEFT JOIN commandes c ON f.commande_id = c.id
        LEFT JOIN commandes cmd ON f.commande_id = cmd.id";

if (!empty($filters)) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}

$sql .= " ORDER BY f.date_facture DESC LIMIT :offset, :perPage";

// Compter le total
$countSql = "SELECT COUNT(*) as total FROM factures f";
if (!empty($filters)) {
    $countSql .= " WHERE " . implode(" AND ", $filters);
}

$countStmt = $db->prepare($countSql);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalItems = $countStmt->fetchColumn();
$totalPages = ceil($totalItems / $perPage);

// Exécuter la requête principale
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$stmt->execute();
$factures = $stmt->fetchAll();

// Statistiques
$statsSql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN statut = 'payee' THEN 1 ELSE 0 END) as payees,
    SUM(CASE WHEN statut = 'envoyee' THEN 1 ELSE 0 END) as envoyees,
    SUM(CASE WHEN statut = 'en_retard' THEN 1 ELSE 0 END) as en_retard,
    SUM(montant_total_fcfa) as total_fcfa,
    SUM(montant_total_euro) as total_euro
    FROM factures";

if (!empty($filters)) {
    $statsSql .= " WHERE " . str_replace('f.', '', implode(" AND ", $filters));
}

$statsStmt = $db->prepare($statsSql);
foreach ($params as $key => $value) {
    $statsStmt->bindValue(str_replace('f.', '', $key), $value);
}
$statsStmt->execute();
$stats = $statsStmt->fetch();

$page_title = "Gestion des Factures - Admin Shalom Digital Solutions";
$page_description = "Gérez les factures de vos clients";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <style>
        /* Styles similaires à commandes.php mais adaptés pour les factures */
        .invoice-number {
            font-weight: bold;
            color: #3498db;
            text-decoration: none;
        }
        
        .invoice-number:hover {
            text-decoration: underline;
        }
        
        .amount-due {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .amount-paid {
            color: #2ecc71;
            font-weight: bold;
        }
        
        .invoice-actions {
            display: flex;
            gap: 5px;
        }
        
        .btn-pdf {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-email {
            background-color: #3498db;
            color: white;
        }
        
        .status-brouillon { background-color: #f8f9fa; color: #6c757d; }
        .status-envoyee { background-color: #d1ecf1; color: #0c5460; }
        .status-payee { background-color: #d4edda; color: #155724; }
        .status-en_retard { background-color: #f8d7da; color: #721c24; }
        .status-annulee { background-color: #f2f2f2; color: #666; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-file-invoice-dollar"></i> Gestion des Factures</h1>
                <div class="header-actions">
                    <a href="generer-facture.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle facture
                    </a>
                    <a href="exports/export_pdf.php" class="btn btn-success">
                        <i class="fas fa-file-export"></i> Exporter
                    </a>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total'] ?? 0; ?></h3>
                        <p>Factures totales</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['payees'] ?? 0; ?></h3>
                        <p>Factures payées</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['envoyees'] ?? 0; ?></h3>
                        <p>Factures envoyées</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['en_retard'] ?? 0; ?></h3>
                        <p>En retard</p>
                    </div>
                </div>
            </div>
            
            <!-- Montants totaux -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo formatPrice($stats['total_fcfa'] ?? 0); ?></h3>
                        <p>Total FCFA</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon teal">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo formatPrice($stats['total_euro'] ?? 0, '€'); ?></h3>
                        <p>Total €</p>
                    </div>
                </div>
            </div>
            
            <!-- Filtres -->
            <div class="filters-container">
                <form method="GET" class="filters-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Recherche</label>
                            <input type="text" name="search" placeholder="N° facture, client..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label>Statut</label>
                            <select name="statut">
                                <option value="">Tous les statuts</option>
                                <option value="brouillon" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'brouillon') ? 'selected' : ''; ?>>Brouillon</option>
                                <option value="envoyee" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'envoyee') ? 'selected' : ''; ?>>Envoyée</option>
                                <option value="payee" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'payee') ? 'selected' : ''; ?>>Payée</option>
                                <option value="en_retard" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'en_retard') ? 'selected' : ''; ?>>En retard</option>
                                <option value="annulee" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'annulee') ? 'selected' : ''; ?>>Annulée</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Date de début</label>
                            <input type="date" name="date_debut" 
                                   value="<?php echo isset($_GET['date_debut']) ? htmlspecialchars($_GET['date_debut']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Date de fin</label>
                            <input type="date" name="date_fin" 
                                   value="<?php echo isset($_GET['date_fin']) ? htmlspecialchars($_GET['date_fin']) : ''; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label style="visibility: hidden;">Actions</label>
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filtrer
                                </button>
                                <a href="factures.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Réinitialiser
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Tableau des factures -->
            <div class="table-container">
                <?php if (empty($factures)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucune facture trouvée.
                </div>
                <?php else: ?>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>N° Facture</th>
                                <th>Client</th>
                                <th>N° Commande</th>
                                <th>Montant</th>
                                <th>Date</th>
                                <th>Échéance</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($factures as $facture): ?>
                            <?php 
                            // Calculer le montant payé
                            $paiementsSql = "SELECT SUM(montant_fcfa) as total_paye_fcfa, 
                                                    SUM(montant_euro) as total_paye_euro 
                                             FROM paiements 
                                             WHERE facture_id = :facture_id";
                            $paiementsStmt = $db->prepare($paiementsSql);
                            $paiementsStmt->execute([':facture_id' => $facture['id']]);
                            $paiements = $paiementsStmt->fetch();
                            ?>
                            <tr>
                                <td>
                                    <a href="facture-detail.php?id=<?php echo $facture['id']; ?>" class="invoice-number">
                                        <?php echo htmlspecialchars($facture['numero_facture']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="client-info">
                                        <strong><?php echo htmlspecialchars($facture['client_nom']); ?></strong>
                                        <small><?php echo htmlspecialchars($facture['client_email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($facture['numero_commande'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo formatPrice($facture['montant_total_fcfa']); ?></strong>
                                        <small><?php echo formatPrice($facture['montant_total_euro'], '€'); ?></small>
                                    </div>
                                    <?php if ($facture['statut'] !== 'payee' && $paiements['total_paye_fcfa'] > 0): ?>
                                    <div class="amount-paid">
                                        Payé: <?php echo formatPrice($paiements['total_paye_fcfa']); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($facture['date_facture'])); ?>
                                </td>
                                <td>
                                    <?php if ($facture['date_echeance']): ?>
                                    <?php echo date('d/m/Y', strtotime($facture['date_echeance'])); ?>
                                    <?php if (strtotime($facture['date_echeance']) < time() && $facture['statut'] !== 'payee'): ?>
                                    <span class="badge badge-danger">En retard</span>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $facture['statut']; ?>">
                                        <?php 
                                        $statutLabels = [
                                            'brouillon' => 'Brouillon',
                                            'envoyee' => 'Envoyée',
                                            'payee' => 'Payée',
                                            'en_retard' => 'En retard',
                                            'annulee' => 'Annulée'
                                        ];
                                        echo $statutLabels[$facture['statut']] ?? $facture['statut'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="invoice-actions">
                                        <a href="facture-detail.php?id=<?php echo $facture['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="generer-pdf.php?facture_id=<?php echo $facture['id']; ?>" 
                                           class="btn btn-sm btn-pdf" title="PDF" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="envoyer-facture.php?id=<?php echo $facture['id']; ?>" 
                                           class="btn btn-sm btn-email" title="Envoyer par email">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                        <?php if ($facture['statut'] !== 'payee'): ?>
                                        <a href="enregistrer-paiement.php?facture_id=<?php echo $facture['id']; ?>" 
                                           class="btn btn-sm btn-success" title="Enregistrer paiement">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i> Précédent
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
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
        </div>
    </div>
</body>
</html>