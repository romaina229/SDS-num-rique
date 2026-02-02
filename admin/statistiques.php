<?php
// admin/statistiques.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

// Période par défaut (30 derniers jours)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Statistiques générales
$statsSql = "SELECT 
                COUNT(*) as total_orders,
                SUM(montant_fcfa) as total_revenue,
                COUNT(DISTINCT client_email) as total_clients,
                AVG(montant_fcfa) as avg_order_value
            FROM commandes
            WHERE DATE(date_commande) BETWEEN :start_date AND :end_date";

$statsStmt = $db->prepare($statsSql);
$statsStmt->execute([':start_date' => $start_date, ':end_date' => $end_date]);
$generalStats = $statsStmt->fetch();

// Commandes par statut
$statusSql = "SELECT 
                statut,
                COUNT(*) as count,
                SUM(montant_fcfa) as total
              FROM commandes
              WHERE DATE(date_commande) BETWEEN :start_date AND :end_date
              GROUP BY statut";

$statusStmt = $db->prepare($statusSql);
$statusStmt->execute([':start_date' => $start_date, ':end_date' => $end_date]);
$statusStats = $statusStmt->fetchAll();

// Commandes par service
$serviceSql = "SELECT 
                s.nom as service_name,
                COUNT(*) as order_count,
                SUM(c.montant_fcfa) as total_revenue
              FROM commandes c
              LEFT JOIN services s ON c.service_id = s.id
              WHERE DATE(c.date_commande) BETWEEN :start_date AND :end_date
              GROUP BY c.service_id
              ORDER BY total_revenue DESC";

$serviceStmt = $db->prepare($serviceSql);
$serviceStmt->execute([':start_date' => $start_date, ':end_date' => $end_date]);
$serviceStats = $serviceStmt->fetchAll();

// Évolution des commandes (30 derniers jours)
$evolutionSql = "SELECT 
                    DATE(date_commande) as date,
                    COUNT(*) as daily_orders,
                    SUM(montant_fcfa) as daily_revenue
                 FROM commandes
                 WHERE DATE(date_commande) BETWEEN DATE_SUB(:end_date, INTERVAL 30 DAY) AND :end_date
                 GROUP BY DATE(date_commande)
                 ORDER BY date";

$evolutionStmt = $db->prepare($evolutionSql);
//$evolutionStmt->execute([':end_date' => $end_date]);
$evolutionData = $evolutionStmt->fetchAll();

// Top clients
$topClientsSql = "SELECT 
                    client_nom,
                    client_email,
                    COUNT(*) as order_count,
                    SUM(montant_fcfa) as total_spent
                  FROM commandes
                  WHERE DATE(date_commande) BETWEEN :start_date AND :end_date
                  GROUP BY client_email
                  ORDER BY total_spent DESC
                  LIMIT 10";

$topClientsStmt = $db->prepare($topClientsSql);
$topClientsStmt->execute([':start_date' => $start_date, ':end_date' => $end_date]);
$topClients = $topClientsStmt->fetchAll();

$page_title = "Statistiques - Admin Shalom Digital Solutions";
$page_description = "Analyses et statistiques de votre activité";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }
        
        .stats-grid-detailed {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-box h3 {
            color: var(--primary);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .stat-list {
            list-style: none;
            padding: 0;
        }
        
        .stat-list li {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .stat-list li:last-child {
            border-bottom: none;
        }
        
        .stat-value {
            font-weight: 600;
            color: var(--primary);
        }
        
        .date-filters {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filters-form {
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
            color: var(--primary);
        }
        
        .top-clients-list {
            margin-top: 20px;
        }
        
        .client-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s;
        }
        
        .client-item:hover {
            background-color: #f8f9fa;
        }
        
        .client-item:last-child {
            border-bottom: none;
        }
        
        .client-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .client-avatar-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary), #2980b9);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .client-details h4 {
            margin: 0 0 5px 0;
            color: var(--primary);
        }
        
        .client-details p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .client-stats {
            text-align: right;
        }
        
        .client-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        .client-orders {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .no-data {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
        
        .no-data i {
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
                <h1><i class="fas fa-chart-bar"></i> Statistiques et Analyses</h1>
                <div class="header-actions">
                    <button class="btn btn-success" onclick="exportStatistics()">
                        <i class="fas fa-file-export"></i> Exporter les stats
                    </button>
                </div>
            </div>
            
            <!-- Filtres de date -->
            <div class="date-filters">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label>Date de début</label>
                        <input type="date" name="start_date" 
                               value="<?php echo htmlspecialchars($start_date); ?>"
                               max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Date de fin</label>
                        <input type="date" name="end_date" 
                               value="<?php echo htmlspecialchars($end_date); ?>"
                               max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Appliquer
                        </button>
                        <a href="statistiques.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> 30 derniers jours
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Statistiques générales -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $generalStats['total_orders'] ?? 0; ?></h3>
                        <p>Commandes totales</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo formatPrice($generalStats['total_revenue'] ?? 0); ?></h3>
                        <p>Chiffre d'affaires</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $generalStats['total_clients'] ?? 0; ?></h3>
                        <p>Clients uniques</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo formatPrice($generalStats['avg_order_value'] ?? 0); ?></h3>
                        <p>Panier moyen</p>
                    </div>
                </div>
            </div>
            
            <!-- Graphiques -->
            <div class="stats-container">
                <h2>Évolution des commandes (30 derniers jours)</h2>
                <div class="chart-container">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>
            
            <!-- Statistiques détaillées -->
            <div class="stats-grid-detailed">
                <!-- Commandes par statut -->
                <div class="stat-box">
                    <h3>Commandes par statut</h3>
                    <?php if (empty($statusStats)): ?>
                    <p class="text-muted">Aucune donnée pour cette période.</p>
                    <?php else: ?>
                    <ul class="stat-list">
                        <?php 
                        $statusLabels = [
                            'en_attente' => 'En attente',
                            'confirmee' => 'Confirmée',
                            'annulee' => 'Annulée',
                            'terminee' => 'Terminée'
                        ];
                        
                        foreach ($statusStats as $stat): 
                        ?>
                        <li>
                            <span class="status-badge status-<?php echo $stat['statut']; ?>">
                                <?php echo $statusLabels[$stat['statut']] ?? $stat['statut']; ?>
                            </span>
                            <div>
                                <span class="stat-value"><?php echo $stat['count']; ?></span>
                                <small>(<?php echo formatPrice($stat['total']); ?>)</small>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                
                <!-- Commandes par service -->
                <div class="stat-box">
                    <h3>Commandes par service</h3>
                    <?php if (empty($serviceStats)): ?>
                    <p class="text-muted">Aucune donnée pour cette période.</p>
                    <?php else: ?>
                    <ul class="stat-list">
                        <?php foreach ($serviceStats as $stat): ?>
                        <li>
                            <span><?php echo htmlspecialchars($stat['service_name'] ?? 'Non spécifié'); ?></span>
                            <div>
                                <span class="stat-value"><?php echo $stat['order_count']; ?></span>
                                <small>(<?php echo formatPrice($stat['total_revenue']); ?>)</small>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                
                <!-- Top clients -->
                <div class="stat-box">
                    <h3>Top 10 clients</h3>
                    <?php if (empty($topClients)): ?>
                    <p class="text-muted">Aucune donnée pour cette période.</p>
                    <?php else: ?>
                    <div class="top-clients-list">
                        <?php foreach ($topClients as $index => $client): ?>
                        <div class="client-item">
                            <div class="client-info">
                                <div class="client-avatar-small">
                                    <?php echo getInitials($client['client_nom']); ?>
                                </div>
                                <div class="client-details">
                                    <h4><?php echo htmlspecialchars($client['client_nom']); ?></h4>
                                    <p><?php echo htmlspecialchars($client['client_email']); ?></p>
                                </div>
                            </div>
                            <div class="client-stats">
                                <div class="client-total"><?php echo formatPrice($client['total_spent']); ?></div>
                                <div class="client-orders"><?php echo $client['order_count']; ?> commandes</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Graphique circulaire des services -->
            <div class="stats-container">
                <h2>Répartition par service</h2>
                <div class="chart-container">
                    <canvas id="servicesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Préparer les données pour le graphique d'évolution
    const evolutionData = <?php echo json_encode($evolutionData); ?>;
    
    // Extraire les dates et les données
    const dates = evolutionData.map(item => item.date);
    const dailyOrders = evolutionData.map(item => parseInt(item.daily_orders) || 0);
    const dailyRevenue = evolutionData.map(item => parseFloat(item.daily_revenue) || 0);
    
    // Graphique d'évolution des commandes
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    const ordersChart = new Chart(ordersCtx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Commandes par jour',
                data: dailyOrders,
                borderColor: 'rgb(52, 152, 219)',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Revenus (FCFA)',
                data: dailyRevenue,
                borderColor: 'rgb(46, 204, 113)',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 0) {
                                label += context.parsed.y + ' commande(s)';
                            } else {
                                label += new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FCFA';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Nombre de commandes'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Revenus (FCFA)'
                    },
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                        }
                    }
                }
            }
        }
    });
    
    // Graphique circulaire des services
    const serviceStats = <?php echo json_encode($serviceStats); ?>;
    const serviceNames = serviceStats.map(item => item.service_name || 'Non spécifié');
    const serviceRevenue = serviceStats.map(item => parseFloat(item.total_revenue) || 0);
    
    const servicesCtx = document.getElementById('servicesChart').getContext('2d');
    const servicesChart = new Chart(servicesCtx, {
        type: 'doughnut',
        data: {
            labels: serviceNames,
            datasets: [{
                data: serviceRevenue,
                backgroundColor: [
                    'rgb(52, 152, 219)',
                    'rgb(46, 204, 113)',
                    'rgb(241, 196, 15)',
                    'rgb(231, 76, 60)',
                    'rgb(155, 89, 182)',
                    'rgb(26, 188, 156)',
                    'rgb(52, 73, 94)',
                    'rgb(149, 165, 166)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            
                            return `${label}: ${new Intl.NumberFormat('fr-FR').format(value)} FCFA (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    
    // Exporter les statistiques
    function exportStatistics() {
        const filters = new URLSearchParams(window.location.search);
        window.location.href = 'exports/export_csv.php?' + filters.toString();
    }
    
    // Mettre à jour les graphiques lors du redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        ordersChart.resize();
        servicesChart.resize();
    });
    
    // Télécharger les graphiques
    function downloadChart(chartId, filename) {
        const link = document.createElement('a');
        link.download = filename;
        link.href = document.getElementById(chartId).toDataURL('image/png', 1.0);
        link.click();
    }
    
    // Boutons de téléchargement pour les graphiques
    document.addEventListener('DOMContentLoaded', function() {
        // Ajouter un bouton de téléchargement pour le graphique des commandes
        const ordersContainer = document.querySelector('#ordersChart').parentElement;
        const downloadBtn1 = document.createElement('button');
        downloadBtn1.className = 'btn btn-sm btn-secondary';
        downloadBtn1.innerHTML = '<i class="fas fa-download"></i> Télécharger';
        downloadBtn1.style.position = 'absolute';
        downloadBtn1.style.top = '10px';
        downloadBtn1.style.right = '10px';
        downloadBtn1.onclick = () => downloadChart('ordersChart', 'evolution-commandes.png');
        ordersContainer.style.position = 'relative';
        ordersContainer.appendChild(downloadBtn1);
        
        // Ajouter un bouton de téléchargement pour le graphique des services
        const servicesContainer = document.querySelector('#servicesChart').parentElement;
        const downloadBtn2 = document.createElement('button');
        downloadBtn2.className = 'btn btn-sm btn-secondary';
        downloadBtn2.innerHTML = '<i class="fas fa-download"></i> Télécharger';
        downloadBtn2.style.position = 'absolute';
        downloadBtn2.style.top = '10px';
        downloadBtn2.style.right = '10px';
        downloadBtn2.onclick = () => downloadChart('servicesChart', 'repartition-services.png');
        servicesContainer.style.position = 'relative';
        servicesContainer.appendChild(downloadBtn2);
    });
    </script>
</body>
</html>