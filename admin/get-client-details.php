<?php
// admin/get-client-details.php
require_once '../includes/functions.php';

// Vérifier si c'est une requête AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo '<div class="alert alert-danger">Accès interdit</div>';
    exit();
}

// Vérifier l'authentification admin
session_start();
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Non autorisé</div>';
    exit();
}

// Vérifier l'email
if (!isset($_GET['email']) || empty($_GET['email'])) {
    http_response_code(400);
    echo '<div class="alert alert-danger">Email manquant</div>';
    exit();
}

$client_email = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
$db = getDBConnection();

// Récupérer les détails du client
$sql = "SELECT 
            client_email,
            client_nom,
            client_telephone,
            client_entreprise,
            COUNT(*) as total_orders,
            SUM(montant_fcfa) as total_spent,
            MIN(date_commande) as first_order_date,
            MAX(date_commande) as last_order_date,
            GROUP_CONCAT(DISTINCT service_nom SEPARATOR ', ') as services_ordered
        FROM commandes
        WHERE client_email = :email
        GROUP BY client_email";

$stmt = $db->prepare($sql);
$stmt->execute([':email' => $client_email]);
$client = $stmt->fetch();

if (!$client) {
    echo '<div class="alert alert-warning">Client non trouvé</div>';
    exit();
}

// Récupérer les commandes récentes
$ordersSql = "SELECT 
                id,
                numero_commande,
                service_nom,
                montant_fcfa,
                statut,
                date_commande
              FROM commandes
              WHERE client_email = :email
              ORDER BY date_commande DESC
              LIMIT 5";
$ordersStmt = $db->prepare($ordersSql);
$ordersStmt->execute([':email' => $client_email]);
$recentOrders = $ordersStmt->fetchAll();

// Récupérer les statistiques par statut
$statsSql = "SELECT 
                statut,
                COUNT(*) as count,
                SUM(montant_fcfa) as total
             FROM commandes
             WHERE client_email = :email
             GROUP BY statut";
$statsStmt = $db->prepare($statsSql);
$statsStmt->execute([':email' => $client_email]);
$statusStats = $statsStmt->fetchAll();
?>

<div class="client-detail-content">
    <div class="client-header">
        <div class="client-avatar-large">
            <?php echo getInitials($client['client_nom']); ?>
        </div>
        <div class="client-info-large">
            <h3><?php echo htmlspecialchars($client['client_nom']); ?></h3>
            <p class="client-email"><?php echo htmlspecialchars($client['client_email']); ?></p>
            <p class="client-phone"><?php echo htmlspecialchars($client['client_telephone']); ?></p>
            <?php if ($client['client_entreprise']): ?>
            <p class="client-company"><?php echo htmlspecialchars($client['client_entreprise']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="client-stats-detailed">
        <div class="stat-row">
            <div class="stat-box">
                <div class="stat-value"><?php echo $client['total_orders']; ?></div>
                <div class="stat-label">Commandes totales</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo formatPrice($client['total_spent']); ?></div>
                <div class="stat-label">Total dépensé</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">
                    <?php 
                    $avgOrder = $client['total_spent'] / $client['total_orders'];
                    echo formatPrice($avgOrder);
                    ?>
                </div>
                <div class="stat-label">Moyenne par commande</div>
            </div>
        </div>
    </div>
    
    <div class="client-dates">
        <p><strong>Première commande :</strong> <?php echo date('d/m/Y', strtotime($client['first_order_date'])); ?></p>
        <p><strong>Dernière commande :</strong> <?php echo date('d/m/Y', strtotime($client['last_order_date'])); ?></p>
    </div>
    
    <?php if ($client['services_ordered']): ?>
    <div class="client-services">
        <h4>Services commandés</h4>
        <p><?php echo htmlspecialchars($client['services_ordered']); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="client-status-stats">
        <h4>Statistiques par statut</h4>
        <div class="status-grid">
            <?php foreach ($statusStats as $stat): ?>
            <div class="status-item">
                <span class="status-badge status-<?php echo $stat['statut']; ?>">
                    <?php 
                    $statusLabels = [
                        'en_attente' => 'En attente',
                        'confirmee' => 'Confirmée',
                        'annulee' => 'Annulée',
                        'terminee' => 'Terminée'
                    ];
                    echo $statusLabels[$stat['statut']] ?? $stat['statut'];
                    ?>
                </span>
                <span class="status-count"><?php echo $stat['count']; ?> commandes</span>
                <span class="status-total"><?php echo formatPrice($stat['total']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="recent-orders">
        <h4>Commandes récentes (5 dernières)</h4>
        <?php if (empty($recentOrders)): ?>
        <p class="text-muted">Aucune commande récente.</p>
        <?php else: ?>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>N° Commande</th>
                    <th>Service</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td>
                        <a href="commande-detail.php?id=<?php echo $order['id']; ?>" target="_blank">
                            <?php echo htmlspecialchars($order['numero_commande']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($order['service_nom']); ?></td>
                    <td><?php echo formatPrice($order['montant_fcfa']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $order['statut']; ?>">
                            <?php 
                            echo $statusLabels[$order['statut']] ?? $order['statut'];
                            ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($order['date_commande'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    
    <div class="client-actions">
        <button onclick="sendEmailToClient('<?php echo htmlspecialchars($client['client_email']); ?>')" 
                class="btn btn-primary btn-sm">
            <i class="fas fa-envelope"></i> Envoyer un email
        </button>
        <button onclick="viewAllOrders('<?php echo htmlspecialchars($client['client_email']); ?>')" 
                class="btn btn-secondary btn-sm">
            <i class="fas fa-list"></i> Voir toutes les commandes
        </button>
        <button onclick="exportClientData('<?php echo htmlspecialchars($client['client_email']); ?>')" 
                class="btn btn-success btn-sm">
            <i class="fas fa-download"></i> Exporter les données
        </button>
    </div>
</div>

<style>
.client-detail-content {
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
}

.client-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.client-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--secondary), #2980b9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
    flex-shrink: 0;
}

.client-info-large h3 {
    margin: 0 0 5px 0;
    color: var(--primary);
    font-size: 1.4rem;
}

.client-info-large p {
    margin: 3px 0;
    color: #666;
}

.client-stats-detailed {
    margin: 25px 0;
}

.stat-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.stat-box {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.85rem;
    color: #6c757d;
}

.client-dates {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
}

.client-dates p {
    margin: 5px 0;
}

.client-services {
    background-color: #e8f4fc;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    border-left: 4px solid var(--secondary);
}

.client-services h4 {
    margin: 0 0 10px 0;
    color: var(--primary);
}

.client-status-stats {
    margin: 25px 0;
}

.client-status-stats h4 {
    margin: 0 0 15px 0;
    color: var(--primary);
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
}

.status-item {
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.status-count {
    font-size: 0.9rem;
    color: #666;
}

.status-total {
    font-weight: bold;
    color: var(--primary);
}

.recent-orders {
    margin: 25px 0;
}

.recent-orders h4 {
    margin: 0 0 15px 0;
    color: var(--primary);
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    text-align: left;
    padding: 10px;
    background-color: #f8f9fa;
    color: var(--primary);
    font-weight: 600;
    font-size: 0.85rem;
    border-bottom: 2px solid #dee2e6;
}

.orders-table td {
    padding: 10px;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.9rem;
}

.orders-table tr:hover {
    background-color: #f8f9fa;
}

.client-actions {
    display: flex;
    gap: 10px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn-sm {
    padding: 8px 15px;
    font-size: 0.85rem;
}
</style>

<script>
function getInitials(name) {
    return name.split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
}

function sendEmailToClient(email) {
    window.location.href = 'mailto:' + email + '?subject=Votre%20commande%20Shalom%20Digital%20Solutions';
}

function viewAllOrders(email) {
    window.open('commandes.php?search=' + encodeURIComponent(email), '_blank');
}

function exportClientData(email) {
    window.location.href = 'export-client.php?email=' + encodeURIComponent(email);
}
</script>