<?php
// admin/commandes.php
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

// Statut
if (isset($_GET['statut']) && $_GET['statut'] !== '') {
    $filters[] = "c.statut = :statut";
    $params[':statut'] = $_GET['statut'];
}

// Recherche
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $filters[] = "(c.numero_commande LIKE :search OR c.client_nom LIKE :search OR c.client_email LIKE :search OR c.client_telephone LIKE :search)";
    $params[':search'] = $search;
}

// Date de début
if (isset($_GET['date_debut']) && !empty($_GET['date_debut'])) {
    $filters[] = "DATE(c.date_commande) >= :date_debut";
    $params[':date_debut'] = $_GET['date_debut'];
}

// Date de fin
if (isset($_GET['date_fin']) && !empty($_GET['date_fin'])) {
    $filters[] = "DATE(c.date_commande) <= :date_fin";
    $params[':date_fin'] = $_GET['date_fin'];
}

// Service
if (isset($_GET['service_id']) && !empty($_GET['service_id'])) {
    $filters[] = "c.service_id = :service_id";
    $params[':service_id'] = $_GET['service_id'];
}

// Construire la requête
$sql = "SELECT c.*, s.nom as service_nom 
        FROM commandes c 
        LEFT JOIN services s ON c.service_id = s.id";
        
if (!empty($filters)) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}

$sql .= " ORDER BY c.date_commande DESC LIMIT :offset, :perPage";

// D'abord, compter le total
$countSql = "SELECT COUNT(*) as total FROM commandes c";
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

// Maintenant, exécuter la requête principale
$stmt = $db->prepare($sql);

// Bind parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

$stmt->execute();
$commandes = $stmt->fetchAll();

// Fonction pour récupérer tous les services
function getAllServices() {
    $db = getDBConnection();
    $sql = "SELECT id, nom, categorie FROM services ORDER BY categorie, nom";
    $stmt = $db->query($sql);
    return $stmt->fetchAll();
}

// Récupérer tous les services pour le filtre
$services = getAllServices();

// Récupérer les statistiques par statut
$statsSql = "SELECT statut, COUNT(*) as count FROM commandes GROUP BY statut";
$statsStmt = $db->query($statsSql);
$statutsStats = $statsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Définir les titres
$page_title = "Gestion des Commandes - Admin Shalom Digital Solutions";
$page_description = "Gérez les commandes de vos clients";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <link rel="stylesheet" href="includes/commandes.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-shopping-cart"></i> Gestion des Commandes</h1>
                <div class="header-actions">
                    <?php
                    // Construire l'URL d'export avec les filtres actuels
                    $exportParams = $_GET;
                    unset($exportParams['page']); // Enlever la pagination
                    $exportQuery = http_build_query($exportParams);
                    ?>
                    <a href="export-commandes.php?<?php echo $exportQuery; ?>" class="btn btn-success">
                        <i class="fas fa-file-export"></i> Exporter
                    </a>
                </div>
            </div>
            
            <!-- Statistiques rapides -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalItems; ?></h3>
                        <p>Commandes totales</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $statutsStats['confirmee'] ?? 0; ?></h3>
                        <p>Confirmées</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $statutsStats['en_attente'] ?? 0; ?></h3>
                        <p>En attente</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $statutsStats['annulee'] ?? 0; ?></h3>
                        <p>Annulées</p>
                    </div>
                </div>
            </div>
            
            <!-- Filtres -->
            <div class="filters-container">
                <form method="GET" class="filters-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Recherche</label>
                            <input type="text" name="search" placeholder="Numéro, client, email..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label>Statut</label>
                            <select name="statut">
                                <option value="">Tous les statuts</option>
                                <option value="en_attente" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'en_attente') ? 'selected' : ''; ?>>En attente</option>
                                <option value="confirmee" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'confirmee') ? 'selected' : ''; ?>>Confirmée</option>
                                <option value="annulee" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'annulee') ? 'selected' : ''; ?>>Annulée</option>
                                <option value="terminee" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'terminee') ? 'selected' : ''; ?>>Terminée</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Service</label>
                            <select name="service_id">
                                <option value="">Tous les services</option>
                                <?php foreach ($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>" 
                                        <?php echo (isset($_GET['service_id']) && $_GET['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($service['nom']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-row">
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
                            <label style="visibility: hidden;">Actions</label>
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filtrer
                                </button>
                                <a href="commandes.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Réinitialiser
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Tableau des commandes -->
            <div class="table-container">
                <?php if (empty($commandes)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucune commande trouvée.
                </div>
                <?php else: ?>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Montant</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                                <th>Facture</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commandes as $commande): ?>
                            <tr>
                                <td>
                                    <a href="commande_detail.php?id=<?php echo $commande['id']; ?>" class="order-number">
                                        <?php echo htmlspecialchars($commande['numero_commande']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="client-info">
                                        <strong><?php echo htmlspecialchars($commande['client_nom']); ?></strong>
                                        <small><?php echo htmlspecialchars($commande['client_email']); ?></small>
                                        <small><?php echo htmlspecialchars($commande['client_telephone']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($commande['service_nom'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <strong><?php echo formatPrice($commande['montant_fcfa']); ?></strong>
                                    <small><?php echo formatPrice($commande['montant_euro'], '€'); ?></small>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $commande['statut']; ?>">
                                        <?php 
                                        $statutLabels = [
                                            'en_attente' => 'En attente',
                                            'confirmee' => 'Confirmée',
                                            'annulee' => 'Annulée',
                                            'terminee' => 'Terminée'
                                        ];
                                        echo $statutLabels[$commande['statut']] ?? $commande['statut'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="commande_detail.php?id=<?php echo $commande['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($commande['statut'] == 'en_attente'): ?>
                                        <button onclick="updateStatus(<?php echo $commande['id']; ?>, 'confirmee')" 
                                                class="btn btn-sm btn-success" title="Confirmer">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="updateStatus(<?php echo $commande['id']; ?>, 'annulee')" 
                                                class="btn btn-sm btn-danger" title="Annuler">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($commande['statut'] == 'confirmee'): ?>
                                        <button onclick="updateStatus(<?php echo $commande['id']; ?>, 'terminee')" 
                                                class="btn btn-sm btn-warning" title="Marquer comme terminée">
                                            <i class="fas fa-flag-checkered"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                               <!-- Nouveau colonne ajouter-->
                                <td>
                                    <?php if ($commande['facture_id']): ?>
                                    <a href="facture-detail.php?id=<?php echo $commande['facture_id']; ?>" 
                                    class="btn btn-sm btn-info" title="Voir facture">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                    <?php else: ?>
                                    <a href="generer-facture.php?commande_id=<?php echo $commande['id']; ?>" 
                                    class="btn btn-sm btn-warning" title="Créer facture">
                                        <i class="fas fa-plus"></i> Facture
                                    </a>
                                    <?php endif; ?>
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
    
    <script>
    function updateStatus(orderId, newStatus) {
        if (!confirm('Êtes-vous sûr de vouloir modifier le statut de cette commande ?')) {
            return;
        }
        
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        // Utiliser FormData pour envoyer les données
        const formData = new FormData();
        formData.append('id', orderId);
        formData.append('statut', newStatus);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
        
        fetch('update_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recharger la page
                location.reload();
            } else {
                alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                button.innerHTML = originalHtml;
                button.disabled = false;
            }
        })
        .catch(error => {
            alert('Erreur réseau: ' + error.message);
            button.innerHTML = originalHtml;
            button.disabled = false;
        });
    }
    
    // Gérer la date de fin pour qu'elle ne soit pas avant la date de début
    document.querySelector('input[name="date_debut"]').addEventListener('change', function() {
        const dateFinInput = document.querySelector('input[name="date_fin"]');
        if (dateFinInput.value && this.value > dateFinInput.value) {
            dateFinInput.value = this.value;
        }
        dateFinInput.min = this.value;
    });
    
    document.querySelector('input[name="date_fin"]').addEventListener('change', function() {
        const dateDebutInput = document.querySelector('input[name="date_debut"]');
        if (dateDebutInput.value && this.value < dateDebutInput.value) {
            dateDebutInput.value = this.value;
        }
    });
    </script>
</body>
</html>