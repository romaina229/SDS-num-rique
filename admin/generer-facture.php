<?php
// admin/generer-facture.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

// Récupérer les commandes sans facture
$commandesSql = "SELECT c.*, s.nom as service_nom 
                 FROM commandes c
                 LEFT JOIN services s ON c.service_id = s.id
                 WHERE c.facture_id IS NULL AND c.statut = 'confirmee'
                 ORDER BY c.date_commande DESC";
$commandes = $db->query($commandesSql)->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commande_id = $_POST['commande_id'];
    
    // Récupérer la commande
    $commandeSql = "SELECT * FROM commandes WHERE id = :id";
    $commandeStmt = $db->prepare($commandeSql);
    $commandeStmt->execute([':id' => $commande_id]);
    $commande = $commandeStmt->fetch();
    
    if ($commande) {
        // Générer un numéro de facture unique
        $prefix = 'FAC-' . date('Ym') . '-';
        $countSql = "SELECT COUNT(*) FROM factures WHERE numero_facture LIKE :prefix";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute([':prefix' => $prefix . '%']);
        $count = $countStmt->fetchColumn() + 1;
        $numero_facture = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        // Calculer la date d'échéance (30 jours par défaut)
        $date_echeance = date('Y-m-d', strtotime('+30 days'));
        
        // Créer la facture
        $factureSql = "INSERT INTO factures (
            numero_facture, commande_id, client_id,
            montant_total_fcfa, montant_total_euro,
            date_echeance, statut, tva, mode_paiement
        ) VALUES (
            :numero_facture, :commande_id, :client_id,
            :montant_total_fcfa, :montant_total_euro,
            :date_echeance, 'brouillon', :tva, :mode_paiement
        )";
        
        $factureStmt = $db->prepare($factureSql);
        $factureStmt->execute([
            ':numero_facture' => $numero_facture,
            ':commande_id' => $commande_id,
            ':client_id' => $commande['client_id'] ?? 1, // À adapter selon votre structure
            ':montant_total_fcfa' => $commande['montant_fcfa'],
            ':montant_total_euro' => $commande['montant_euro'],
            ':date_echeance' => $date_echeance,
            ':tva' => 5.00,
            ':mode_paiement' => $commande['mode_paiement'] ?? 'Virement bancaire'
        ]);
        
        $facture_id = $db->lastInsertId();
        
        // Créer la ligne de facture
        $ligneSql = "INSERT INTO facture_lignes (
            facture_id, service_id, description, quantite,
            prix_unitaire_fcfa, prix_unitaire_euro,
            montant_total_fcfa, montant_total_euro
        ) VALUES (
            :facture_id, :service_id, :description, 1,
            :prix_unitaire_fcfa, :prix_unitaire_euro,
            :montant_total_fcfa, :montant_total_euro
        )";
        
        $ligneStmt = $db->prepare($ligneSql);
        $ligneStmt->execute([
            ':facture_id' => $facture_id,
            ':service_id' => $commande['service_id'],
            ':description' => $commande['service_nom'] ?? 'Service',
            ':prix_unitaire_fcfa' => $commande['montant_fcfa'],
            ':prix_unitaire_euro' => $commande['montant_euro'],
            ':montant_total_fcfa' => $commande['montant_fcfa'],
            ':montant_total_euro' => $commande['montant_euro']
        ]);
        
        // Mettre à jour la commande avec l'ID de la facture
        $updateCommandeSql = "UPDATE commandes SET facture_id = :facture_id WHERE id = :id";
        $updateStmt = $db->prepare($updateCommandeSql);
        $updateStmt->execute([':facture_id' => $facture_id, ':id' => $commande_id]);
        
        // Rediriger vers la facture
        header('Location: facture-detail.php?id=' . $facture_id);
        exit;
    }
}

$page_title = "Générer une facture - Admin Shalom Digital Solutions";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-file-invoice-dollar"></i> Générer une facture</h1>
                <a href="factures.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
            
            <div class="content-box">
                <h2>Sélectionner une commande</h2>
                <p>Sélectionnez une commande confirmée qui n'a pas encore de facture.</p>
                
                <?php if (empty($commandes)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Toutes les commandes confirmées ont déjà une facture.
                </div>
                <?php else: ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="commande_id">Commande à facturer *</label>
                        <select name="commande_id" id="commande_id" class="form-control" required>
                            <option value="">Sélectionnez une commande...</option>
                            <?php foreach ($commandes as $commande): ?>
                            <option value="<?php echo $commande['id']; ?>">
                                #<?php echo htmlspecialchars($commande['numero_commande']); ?> - 
                                <?php echo htmlspecialchars($commande['client_nom']); ?> - 
                                <?php echo htmlspecialchars($commande['service_nom']); ?> - 
                                <?php echo formatPrice($commande['montant_fcfa']); ?>
                                (<?php echo date('d/m/Y', strtotime($commande['date_commande'])); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-invoice"></i> Générer la facture
                        </button>
                    </div>
                </form>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>