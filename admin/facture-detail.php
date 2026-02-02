<?php
// admin/facture-detail.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

$facture_id = $_GET['id'] ?? 0;

// Récupérer la facture
$sql = "SELECT f.*, 
               c.client_nom, c.client_email, c.client_telephone,
               c.client_entreprise,
               cmd.numero_commande, cmd.date_commande
        FROM factures f
        LEFT JOIN commandes c ON f.commande_id = c.id
        LEFT JOIN commandes cmd ON f.commande_id = cmd.id
        WHERE f.id = :id";

$stmt = $db->prepare($sql);
$stmt->execute([':id' => $facture_id]);
$facture = $stmt->fetch();

if (!$facture) {
    header('Location: factures.php');
    exit;
}

// Récupérer les lignes de la facture
$lignesSql = "SELECT fl.*, s.nom as service_nom 
              FROM facture_lignes fl
              LEFT JOIN services s ON fl.service_id = s.id
              WHERE fl.facture_id = :facture_id";
$lignesStmt = $db->prepare($lignesSql);
$lignesStmt->execute([':facture_id' => $facture_id]);
$lignes = $lignesStmt->fetchAll();

// Récupérer les paiements
$paiementsSql = "SELECT * FROM paiements 
                 WHERE facture_id = :facture_id 
                 ORDER BY date_paiement DESC";
$paiementsStmt = $db->prepare($paiementsSql);
$paiementsStmt->execute([':facture_id' => $facture_id]);
$paiements = $paiementsStmt->fetchAll();

// Calculer le total payé
$totalPayeFcfa = array_sum(array_column($paiements, 'montant_fcfa'));
$totalPayeEuro = array_sum(array_column($paiements, 'montant_euro'));

// Calculer le solde restant
$soldeFcfa = $facture['montant_total_fcfa'] - $totalPayeFcfa;
$soldeEuro = $facture['montant_total_euro'] - $totalPayeEuro;

$page_title = "Facture #" . $facture['numero_facture'] . " - Admin Shalom Digital Solutions";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <style>
        .invoice-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 20px;
        }
        
        .company-info h1 {
            color: #2c3e50;
            margin: 0 0 10px;
        }
        
        .invoice-info {
            text-align: right;
        }
        
        .invoice-number {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .invoice-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }
        
        .parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .party-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }
        
        .party-card h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .invoice-table th {
            background: #3498db;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .invoice-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals {
            margin-left: auto;
            width: 300px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .total-row.grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            border-top: 2px solid #3498db;
            margin-top: 10px;
            padding-top: 10px;
        }
        
        .payment-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
        }
        
        .btn-print {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-print:hover {
            background: #2980b9;
        }
        
        .actions-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="actions-bar">
                <a href="factures.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <a href="generer-pdf.php?facture_id=<?php echo $facture_id; ?>" class="btn btn-pdf" target="_blank">
                    <i class="fas fa-file-pdf"></i> Télécharger PDF
                </a>
                <a href="envoyer-facture.php?id=<?php echo $facture_id; ?>" class="btn btn-email">
                    <i class="fas fa-envelope"></i> Envoyer par email
                </a>
                <?php if ($facture['statut'] !== 'payee'): ?>
                <a href="enregistrer-paiement.php?facture_id=<?php echo $facture_id; ?>" class="btn btn-success">
                    <i class="fas fa-money-bill-wave"></i> Enregistrer paiement
                </a>
                <?php endif; ?>
            </div>
            
            <div class="invoice-container">
                <!-- En-tête de facture -->
                <div class="invoice-header">
                    <div class="company-info">
                        <h1>Shalom Digital Solutions</h1>
                        <p>Services Digitaux & Formation</p>
                        <p>Email: liferopro@gmail.com</p>
                        <p>Téléphone: +229 01 69 35 17</p>
                    </div>
                    
                    <div class="invoice-info">
                        <div class="invoice-number">FACTURE #<?php echo htmlspecialchars($facture['numero_facture']); ?></div>
                        <div>Date: <?php echo date('d/m/Y', strtotime($facture['date_facture'])); ?></div>
                        <div>Échéance: <?php echo $facture['date_echeance'] ? date('d/m/Y', strtotime($facture['date_echeance'])) : 'Non définie'; ?></div>
                        <div class="invoice-status status-<?php echo $facture['statut']; ?>">
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
                        </div>
                    </div>
                </div>
                
                <!-- Informations client et vendeur -->
                <div class="parties">
                    <div class="party-card">
                        <h3>Facturé à:</h3>
                        <p><strong><?php echo htmlspecialchars($facture['client_nom']); ?></strong></p>
                        <?php if ($facture['client_entreprise']): ?>
                        <p><?php echo htmlspecialchars($facture['client_entreprise']); ?></p>
                        <?php endif; ?>
                        <?php //if ($facture['client_adresse']): ?>
                        <p><?php //echo htmlspecialchars($facture['client_adresse']); ?></p>
                        <?php //endif; ?>
                        <p>Email: <?php echo htmlspecialchars($facture['client_email']); ?></p>
                        <p>Téléphone: <?php echo htmlspecialchars($facture['client_telephone']); ?></p>
                    </div>
                    
                    <div class="party-card">
                        <h3>Informations facturation:</h3>
                        <p><strong>Commande:</strong> <?php echo htmlspecialchars($facture['numero_commande'] ?? 'N/A'); ?></p>
                        <p><strong>Date commande:</strong> <?php echo date('d/m/Y', strtotime($facture['date_commande'])); ?></p>
                        <p><strong>Mode paiement:</strong> <?php echo htmlspecialchars($facture['mode_paiement'] ?? 'Non spécifié'); ?></p>
                    </div>
                </div>
                
                <!-- Tableau des articles -->
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-center">Quantité</th>
                            <th class="text-right">Prix unitaire</th>
                            <th class="text-right">Montant total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lignes as $ligne): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($ligne['service_nom'] ?? $ligne['description']); ?></strong>
                                <?php if ($ligne['description'] && $ligne['description'] !== $ligne['service_nom']): ?>
                                <br><small><?php echo htmlspecialchars($ligne['description']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?php echo $ligne['quantite']; ?></td>
                            <td class="text-right">
                                <?php echo formatPrice($ligne['prix_unitaire_fcfa']); ?> FCFA<br>
                                <small><?php echo formatPrice($ligne['prix_unitaire_euro'], '€'); ?></small>
                            </td>
                            <td class="text-right">
                                <strong><?php echo formatPrice($ligne['montant_total_fcfa']); ?></strong><br>
                                <small><?php echo formatPrice($ligne['montant_total_euro'], '€'); ?></small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Totaux -->
                <div class="totals">
                    <div class="total-row">
                        <span>Sous-total:</span>
                        <span>
                            <?php echo formatPrice($facture['montant_total_fcfa']); ?><br>
                            <small><?php echo formatPrice($facture['montant_total_euro'], '€'); ?></small>
                        </span>
                    </div>
                    
                    <?php if ($facture['remise'] > 0): ?>
                    <div class="total-row">
                        <span>Remise:</span>
                        <span>- <?php echo formatPrice($facture['remise']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($facture['tva'] > 0): ?>
                    <div class="total-row">
                        <span>AIB (<?php echo $facture['tva']; ?>%):</span>
                        <span>
                            <?php echo formatPrice($facture['montant_total_fcfa'] * $facture['tva'] / 100); ?><br>
                            <small><?php echo formatPrice($facture['montant_total_euro'] * $facture['tva'] / 100, '€'); ?></small>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="total-row grand-total">
                        <span>TOTAL:</span>
                        <span>
                            <strong><?php echo formatPrice($facture['montant_total_fcfa']); ?></strong><br>
                            <small><?php echo formatPrice($facture['montant_total_euro'], '€'); ?></small>
                        </span>
                    </div>
                </div>
                
                <!-- Paiements -->
                <?php if (!empty($paiements)): ?>
                <div class="payment-info">
                    <h3>Historique des paiements</h3>
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Mode de paiement</th>
                                <th>Référence</th>
                                <th class="text-right">Montant</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paiements as $paiement): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($paiement['date_paiement'])); ?></td>
                                <td><?php echo htmlspecialchars($paiement['mode_paiement']); ?></td>
                                <td><?php echo htmlspecialchars($paiement['reference_paiement'] ?? '-'); ?></td>
                                <td class="text-right">
                                    <?php echo formatPrice($paiement['montant_fcfa']); ?><br>
                                    <small><?php echo formatPrice($paiement['montant_euro'], '€'); ?></small>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $paiement['statut']; ?>">
                                        <?php echo ucfirst($paiement['statut']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="totals" style="margin-top: 20px;">
                        <div class="total-row">
                            <span>Total payé:</span>
                            <span>
                                <strong class="amount-paid"><?php echo formatPrice($totalPayeFcfa); ?></strong><br>
                                <small><?php echo formatPrice($totalPayeEuro, '€'); ?></small>
                            </span>
                        </div>
                        
                        <?php if ($soldeFcfa > 0): ?>
                        <div class="total-row grand-total">
                            <span>Solde à payer:</span>
                            <span>
                                <strong class="amount-due"><?php echo formatPrice($soldeFcfa); ?></strong><br>
                                <small><?php echo formatPrice($soldeEuro, '€'); ?></small>
                            </span>
                        </div>
                        <?php else: ?>
                        <div class="total-row grand-total">
                            <span>Solde:</span>
                            <span class="amount-paid">Payé intégralement</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Notes -->
                <?php if ($facture['notes']): ?>
                <div class="notes" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                    <h3>Notes:</h3>
                    <p><?php echo nl2br(htmlspecialchars($facture['notes'])); ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Boutons d'action -->
                <div style="margin-top: 40px; text-align: center;">
                    <button onclick="window.print()" class="btn-print">
                        <i class="fas fa-print"></i> Imprimer la facture
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Script pour l'impression
    function printInvoice() {
        window.print();
    }
    </script>
</body>
</html>