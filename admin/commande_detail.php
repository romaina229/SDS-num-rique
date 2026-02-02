<?php
// admin/commande-detail.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

// Vérifier l'ID de commande
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: commandes.php');
    exit();
}

$commande_id = (int)$_GET['id'];

// Récupérer les détails de la commande
$sql = "SELECT c.*, s.nom as service_nom, s.categorie, s.description as service_description,
               s.caracteristiques, s.icone, s.couleur
        FROM commandes c
        LEFT JOIN services s ON c.service_id = s.id
        WHERE c.id = :id";

$stmt = $db->prepare($sql);
$stmt->execute([':id' => $commande_id]);
$commande = $stmt->fetch();

if (!$commande) {
    header('Location: commandes.php');
    exit();
}

// Récupérer les statuts disponibles
$statuts = [
    'en_attente' => 'En attente',
    'confirmee' => 'Confirmée',
    'annulee' => 'Annulée',
    'terminee' => 'Terminée'
];

// Récupérer l'historique des statuts
$historySql = "SELECT * FROM commande_history 
               WHERE commande_id = :id 
               ORDER BY created_at DESC";
$historyStmt = $db->prepare($historySql);
$historyStmt->execute([':id' => $commande_id]);
$history = $historyStmt->fetchAll();

// Traitement du changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    
    // Mettre à jour le statut
    $updateSql = "UPDATE commandes SET statut = :statut, updated_at = NOW() WHERE id = :id";
    $updateStmt = $db->prepare($updateSql);
    
    if ($updateStmt->execute([':statut' => $new_status, ':id' => $commande_id])) {
        // Enregistrer dans l'historique
        $historySql = "INSERT INTO commande_history 
                      (commande_id, old_status, new_status, notes, admin_id)
                      VALUES (:commande_id, :old_status, :new_status, :notes, :admin_id)";
        $historyStmt = $db->prepare($historySql);
        $historyStmt->execute([
            ':commande_id' => $commande_id,
            ':old_status' => $commande['statut'],
            ':new_status' => $new_status,
            ':notes' => $notes,
            ':admin_id' => $_SESSION['admin_id']
        ]);
        
        // Actualiser les données de la commande
        $stmt->execute([':id' => $commande_id]);
        $commande = $stmt->fetch();
        
        $success_message = 'Statut mis à jour avec succès';
    } else {
        $error_message = 'Erreur lors de la mise à jour du statut';
    }
}

// Traitement de l'ajout de note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $note = $_POST['note'];
    
    $noteSql = "INSERT INTO commande_notes 
                (commande_id, note, admin_id)
                VALUES (:commande_id, :note, :admin_id)";
    $noteStmt = $db->prepare($noteSql);
    
    if ($noteStmt->execute([
        ':commande_id' => $commande_id,
        ':note' => $note,
        ':admin_id' => $_SESSION['admin_id']
    ])) {
        $success_message = 'Note ajoutée avec succès';
    } else {
        $error_message = 'Erreur lors de l\'ajout de la note';
    }
}

// Récupérer les notes
$notesSql = "SELECT cn.*, a.username as admin_name 
             FROM commande_notes cn
             LEFT JOIN admins a ON cn.admin_id = a.id
             WHERE cn.commande_id = :id 
             ORDER BY cn.created_at DESC";
$notesStmt = $db->prepare($notesSql);
$notesStmt->execute([':id' => $commande_id]);
$notes = $notesStmt->fetchAll();

$page_title = "Détails de la commande #" . $commande['numero_commande'] . " - Admin Shalom Digital Solutions";
$page_description = "Gérez les détails de cette commande";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <style>
        .order-detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .order-header {
            background: linear-gradient(135deg, var(--primary), #1a252f);
            color: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .order-info h1 {
            color: white;
            margin: 0 0 10px 0;
            font-size: 1.8rem;
        }
        
        .order-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .meta-item i {
            color: var(--secondary);
        }
        
        .order-status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .order-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 992px) {
            .order-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .order-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }
        
        .order-section h2 {
            color: var(--primary);
            font-size: 1.3rem;
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .client-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            display: block;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #333;
            font-size: 1rem;
        }
        
        .service-card {
            display: flex;
            gap: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .service-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            background-color: var(--secondary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        
        .service-details {
            flex: 1;
        }
        
        .service-details h3 {
            margin: 0 0 10px 0;
            color: var(--primary);
        }
        
        .price-tag {
            background-color: var(--accent);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.2rem;
            display: inline-block;
            margin: 10px 0;
        }
        
        .features-list {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }
        
        .features-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .features-list li i {
            color: var(--success);
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline:before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 25px;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--secondary);
            border: 3px solid white;
            box-shadow: 0 0 0 3px #e9ecef;
        }
        
        .timeline-date {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .timeline-content {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
        }
        
        .note-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .note-author {
            font-weight: 600;
            color: var(--primary);
        }
        
        .note-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .note-content {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary);
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .print-btn {
            background-color: #6c757d;
            color: white;
        }
        
        .email-btn {
            background-color: #17a2b8;
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="order-detail-container">
                <!-- En-tête de la commande -->
                <div class="order-header">
                    <div class="order-info">
                        <h1>
                            <i class="fas fa-shopping-cart"></i>
                            Commande #<?php echo htmlspecialchars($commande['numero_commande']); ?>
                        </h1>
                        <div class="order-meta">
                            <div class="meta-item">
                                <i class="far fa-calendar"></i>
                                <span><?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($commande['client_nom']); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($commande['client_email']); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-phone"></i>
                                <span><?php echo htmlspecialchars($commande['client_telephone']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-status">
                        <span class="order-status-badge status-<?php echo $commande['statut']; ?>">
                            <?php echo $statuts[$commande['statut']] ?? $commande['statut']; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Messages d'alerte -->
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                
                <div class="order-grid">
                    <!-- Colonne gauche -->
                    <div>
                        <!-- Informations client -->
                        <div class="order-section">
                            <h2><i class="fas fa-user-circle"></i> Informations client</h2>
                            <div class="client-info-grid">
                                <div class="info-item">
                                    <span class="info-label">Nom complet</span>
                                    <span class="info-value"><?php echo htmlspecialchars($commande['client_nom']); ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Email</span>
                                    <span class="info-value">
                                        <a href="mailto:<?php echo htmlspecialchars($commande['client_email']); ?>">
                                            <?php echo htmlspecialchars($commande['client_email']); ?>
                                        </a>
                                    </span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Téléphone</span>
                                    <span class="info-value">
                                        <a href="tel:<?php echo htmlspecialchars($commande['client_telephone']); ?>">
                                            <?php echo htmlspecialchars($commande['client_telephone']); ?>
                                        </a>
                                    </span>
                                </div>
                                
                                <?php if ($commande['client_entreprise']): ?>
                                <div class="info-item">
                                    <span class="info-label">Entreprise</span>
                                    <span class="info-value"><?php echo htmlspecialchars($commande['client_entreprise']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="info-item">
                                    <span class="info-label">Méthode de paiement</span>
                                    <span class="info-value">
                                        <?php 
                                        $payment_methods = [
                                            'carte' => 'Carte bancaire',
                                            'paypal' => 'PayPal',
                                            'virement' => 'Virement bancaire',
                                            'mobile' => 'Mobile Money'
                                        ];
                                        echo $payment_methods[$commande['methode_paiement']] ?? $commande['methode_paiement'];
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Détails du service -->
                        <div class="order-section">
                            <h2><i class="fas fa-cogs"></i> Détails du service</h2>
                            
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="<?php echo htmlspecialchars($commande['icone']); ?>"></i>
                                </div>
                                <div class="service-details">
                                    <h3><?php echo htmlspecialchars($commande['service_nom']); ?></h3>
                                    <p><?php echo htmlspecialchars($commande['service_description']); ?></p>
                                    
                                    <div class="price-tag">
                                        <?php echo formatPrice($commande['montant_fcfa']); ?> FCFA
                                        <small>(<?php echo formatPrice($commande['montant_euro'], '€'); ?>)</small>
                                    </div>
                                    
                                    <div><strong>Durée estimée :</strong> <?php echo htmlspecialchars($commande['duree_estimee']); ?></div>
                                    
                                    <?php if ($commande['caracteristiques']): ?>
                                    <ul class="features-list">
                                        <?php 
                                        $features = explode('|', $commande['caracteristiques']);
                                        foreach ($features as $feature):
                                            if (trim($feature)):
                                        ?>
                                        <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?></li>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Message du client -->
                        <?php if ($commande['message']): ?>
                        <div class="order-section">
                            <h2><i class="fas fa-comment"></i> Message du client</h2>
                            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; white-space: pre-wrap;">
                                <?php echo htmlspecialchars($commande['message']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Notes internes -->
                        <div class="order-section">
                            <h2><i class="fas fa-sticky-note"></i> Notes internes</h2>
                            
                            <?php if (empty($notes)): ?>
                            <p class="text-muted">Aucune note pour le moment.</p>
                            <?php else: ?>
                                <?php foreach ($notes as $note): ?>
                                <div class="note-item">
                                    <div class="note-header">
                                        <span class="note-author">
                                            <i class="fas fa-user"></i>
                                            <?php echo htmlspecialchars($note['admin_name']); ?>
                                        </span>
                                        <span class="note-date">
                                            <?php echo date('d/m/Y H:i', strtotime($note['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="note-content">
                                        <?php echo nl2br(htmlspecialchars($note['note'])); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Formulaire d'ajout de note -->
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="note">Ajouter une note</label>
                                    <textarea name="note" id="note" class="form-control" 
                                              placeholder="Ajoutez une note interne sur cette commande..." 
                                              rows="4" required></textarea>
                                </div>
                                <button type="submit" name="add_note" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Ajouter la note
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Colonne droite -->
                    <div>
                        <!-- Mise à jour du statut -->
                        <div class="order-section">
                            <h2><i class="fas fa-sync-alt"></i> Mise à jour du statut</h2>
                            
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="status">Nouveau statut</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <?php foreach ($statuts as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" 
                                            <?php echo $commande['statut'] == $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="notes">Notes (optionnel)</label>
                                    <textarea name="notes" id="notes" class="form-control" 
                                              placeholder="Ajoutez des notes sur le changement de statut..." 
                                              rows="3"></textarea>
                                </div>
                                
                                <button type="submit" name="update_status" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Mettre à jour le statut
                                </button>
                            </form>
                        </div>
                        
                        <!-- Historique des statuts -->
                        <div class="order-section">
                            <h2><i class="fas fa-history"></i> Historique</h2>
                            
                            <div class="timeline">
                                <?php if (empty($history)): ?>
                                <p class="text-muted">Aucun historique disponible.</p>
                                <?php else: ?>
                                    <?php foreach ($history as $event): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-date">
                                            <?php echo date('d/m/Y H:i', strtotime($event['created_at'])); ?>
                                        </div>
                                        <div class="timeline-content">
                                            <strong>Changement de statut :</strong>
                                            <span class="badge badge-light"><?php echo $statuts[$event['old_status']] ?? $event['old_status']; ?></span>
                                            <i class="fas fa-arrow-right"></i>
                                            <span class="badge badge-primary"><?php echo $statuts[$event['new_status']] ?? $event['new_status']; ?></span>
                                            <?php if ($event['notes']): ?>
                                            <p style="margin-top: 8px; font-size: 0.9rem; color: #666;">
                                                <?php echo htmlspecialchars($event['notes']); ?>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Actions rapides -->
                        <div class="order-section">
                            <h2><i class="fas fa-bolt"></i> Actions rapides</h2>
                            
                            <div class="action-buttons">
                                <button onclick="window.print()" class="btn print-btn">
                                    <i class="fas fa-print"></i> Imprimer
                                </button>
                                
                                <a href="send_email.php?id=<?= $commande['id']; ?>"
                                class="btn email-btn">
                                    <i class="fas fa-envelope"></i> Envoyer un email
                                </a>

                                
                                <a href="commandes.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </a>
                            </div>
                        </div>
                        
                        <!-- Informations techniques -->
                        <div class="order-section">
                            <h2><i class="fas fa-info-circle"></i> Informations techniques</h2>
                            
                            <div class="info-item">
                                <span class="info-label">ID Commande</span>
                                <span class="info-value"><?php echo $commande['id']; ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">ID Service</span>
                                <span class="info-value"><?php echo $commande['service_id']; ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Créée le</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?></span>
                            </div>
                            
                            <?php if ($commande['updated_at']): ?>
                            <div class="info-item">
                                <span class="info-label">Modifiée le</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($commande['updated_at'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Confirmation avant de changer le statut
    document.querySelector('form[method="POST"]').addEventListener('submit', function(e) {
        if (e.submitter && e.submitter.name === 'update_status') {
            const statusSelect = document.getElementById('status');
            const newStatus = statusSelect.options[statusSelect.selectedIndex].text;
            
            if (!confirm(`Êtes-vous sûr de vouloir changer le statut en "${newStatus}" ?`)) {
                e.preventDefault();
            }
        }
    });
    
    // Auto-save des notes
    const noteTextarea = document.getElementById('note');
    if (noteTextarea) {
        const autoSaveKey = 'order_note_<?php echo $commande_id; ?>';
        
        // Récupérer la note sauvegardée
        const savedNote = localStorage.getItem(autoSaveKey);
        if (savedNote) {
            noteTextarea.value = savedNote;
        }
        
        // Sauvegarder automatiquement
        noteTextarea.addEventListener('input', function() {
            localStorage.setItem(autoSaveKey, this.value);
        });
        
        // Nettoyer après soumission
        document.querySelector('form').addEventListener('submit', function(e) {
            if (e.submitter && e.submitter.name === 'add_note') {
                localStorage.removeItem(autoSaveKey);
            }
        });
    }
    
    // Impression améliorée
    function printOrder() {
        const printContent = document.querySelector('.order-detail-container').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Commande #<?php echo $commande['numero_commande']; ?> - Shalom Digital Solutions</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .print-header { text-align: center; margin-bottom: 30px; }
                    .print-header h1 { color: #2c3e50; }
                    .section { margin-bottom: 20px; }
                    .section h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
                    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
                    .info-item { margin-bottom: 10px; }
                    .info-label { font-weight: bold; }
                    @media print {
                        .no-print { display: none; }
                        .page-break { page-break-before: always; }
                    }
                </style>
            </head>
            <body>
                <div class="print-header">
                    <h1>Commande #<?php echo $commande['numero_commande']; ?></h1>
                    <p>Shalom DigitalSolutions- <?php echo date('d/m/Y H:i'); ?></p>
                </div>
                ${printContent}
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() {
                            document.body.innerHTML = originalContent;
                            location.reload();
                        }, 100);
                    }
                <\/script>
            </body>
            </html>
        `;
    }
    
    // Raccourcis clavier
    document.addEventListener('keydown', function(e) {
        // Ctrl + P pour imprimer
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            printOrder();
        }
        
        // Échap pour retourner à la liste
        if (e.key === 'Escape') {
            window.location.href = 'commandes.php';
        }
    });
    </script>
</body>
</html>