<?php
session_start();

// Vérifier si l'utilisateur est admin
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Variables
$message = '';
$error = '';
$contact = null;
$reponses = [];
$contact_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupérer le message de contact
if($contact_id > 0) {
    $query = "SELECT * FROM contacts WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $contact_id);
    $stmt->execute();
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($contact) {
        // Marquer comme lu si c'est un nouveau message
        if($contact['statut'] == 'nouveau') {
            $update_query = "UPDATE contacts SET statut = 'lu' WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':id', $contact_id);
            $update_stmt->execute();
            $contact['statut'] = 'lu';
        }
        
        // Récupérer les réponses
        $query_reponses = "SELECT * FROM contact_reponses WHERE contact_id = :contact_id ORDER BY date_envoi DESC";
        $stmt_reponses = $db->prepare($query_reponses);
        $stmt_reponses->bindParam(':contact_id', $contact_id);
        $stmt_reponses->execute();
        $reponses = $stmt_reponses->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $error = "ID de contact non spécifié.";
}

// Traitement de la réponse
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'reply') {
    $reponse_message = trim($_POST['reponse_message']);
    $statut = $_POST['statut'] ?? 'repondu';
    $notes = trim($_POST['notes'] ?? '');
    
    if(empty($reponse_message)) {
        $error = "Le message de réponse est obligatoire.";
    } elseif(!$contact) {
        $error = "Contact non trouvé.";
    } else {
        try {
            // Démarrer une transaction
            $db->beginTransaction();
            
            // Insérer la réponse
            $query_reponse = "INSERT INTO contact_reponses (contact_id, auteur, message, date_envoi) 
                              VALUES (:contact_id, :auteur, :message, NOW())";
            $stmt_reponse = $db->prepare($query_reponse);
            $stmt_reponse->bindParam(':contact_id', $contact_id);
            $auteur = $_SESSION['admin_nom'] ?? 'Administrateur';
            $stmt_reponse->bindParam(':auteur', $auteur);
            $stmt_reponse->bindParam(':message', $reponse_message);
            
            if($stmt_reponse->execute()) {
                // Mettre à jour le statut du contact
                $query_update = "UPDATE contacts SET statut = :statut, notes = :notes, 
                                date_reponse = NOW(), traite_par = :traite_par 
                                WHERE id = :id";
                $stmt_update = $db->prepare($query_update);
                $stmt_update->bindParam(':statut', $statut);
                $stmt_update->bindParam(':notes', $notes);
                $traite_par = $_SESSION['admin_nom'] ?? 'Administrateur';
                $stmt_update->bindParam(':traite_par', $traite_par);
                $stmt_update->bindParam(':id', $contact_id);
                
                if($stmt_update->execute()) {
                    // Envoyer l'email de réponse
                    $to = $contact['email'];
                    $subject = "Réponse à votre message - " . $contact['reference'];
                    $headers = "From: Shalom DigitalSolutions<contact@liferopro.com>\r\n";
                    $headers .= "Reply-To: contact@liferopro.com\r\n";
                    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                    
                    $email_message = "
                        Bonjour {$contact['nom']},\n\n
                        En réponse à votre message du " . date('d/m/Y', strtotime($contact['date_creation'])) . " :\n
                        Sujet : {$contact['sujet']}\n
                        Référence : {$contact['reference']}\n\n
                        --- VOTRE MESSAGE ---\n
                        {$contact['message']}\n\n
                        --- NOTRE RÉPONSE ---\n
                        {$reponse_message}\n\n
                        Si vous avez d'autres questions, n'hésitez pas à nous contacter.\n\n
                        Cordialement,\n
                        L'équipe Shalom Digital Solutions\n
                        contact@liferopro.com
                    ";
                    
                    // En production, décommentez cette ligne
                    // mail($to, $subject, $email_message, $headers);
                    
                    // Valider la transaction
                    $db->commit();
                    
                    $message = "Réponse envoyée avec succès et statut mis à jour.";
                    
                    // Recharger le contact et les réponses
                    $query = "SELECT * FROM contacts WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $contact_id);
                    $stmt->execute();
                    $contact = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $query_reponses = "SELECT * FROM contact_reponses WHERE contact_id = :contact_id ORDER BY date_envoi DESC";
                    $stmt_reponses = $db->prepare($query_reponses);
                    $stmt_reponses->bindParam(':contact_id', $contact_id);
                    $stmt_reponses->execute();
                    $reponses = $stmt_reponses->fetchAll(PDO::FETCH_ASSOC);
                    
                } else {
                    $db->rollBack();
                    $error = "Erreur lors de la mise à jour du statut.";
                }
            } else {
                $db->rollBack();
                $error = "Erreur lors de l'enregistrement de la réponse.";
            }
            
        } catch (PDOException $e) {
            $db->rollBack();
            $error = "Erreur technique : " . $e->getMessage();
        }
    }
}

// Traitement du changement de statut
if(isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $statut = $_POST['statut'];
    $notes = trim($_POST['notes'] ?? '');
    
    $query = "UPDATE contacts SET statut = :statut, notes = :notes WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':statut', $statut);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':id', $contact_id);
    
    if($stmt->execute()) {
        $message = "Statut mis à jour avec succès.";
        $contact['statut'] = $statut;
        $contact['notes'] = $notes;
    } else {
        $error = "Erreur lors de la mise à jour du statut.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/images/Logosds.png">
    <title>Voir le message - Admin Shalom Digital Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php include '../assets/css/style.css'; ?>
        <?php include '../assets/css/contact-view.css'; ?>        
    </style>
</head>
<body>
    <!-- Header simplifié -->
    <header>
        <div class="container header-container">
            <a href="../index.php" class="logo">
                <img src="../assets/images/Logosds.png" alt="logo site" style="width: 50px; height: 50px; margin-right: 10px; vertical-align: middle;">
                Shalom Digital<span>Solutions</span>
            </a>
            
            <nav>
                <ul>
                    <li><a href="../index.php">Site public</a></li>
                    <li><a href="index.php" class="btn">Tableau de bord</a></li>
                    <li><a href="../logout.php" class="btn btn-delete">Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Contenu principal -->
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-envelope"></i> Message de contact</h1>
        </div>
        
        <!-- Navigation admin -->
        <nav class="admin-nav">
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                <li><a href="contacts-admin.php"><i class="fas fa-envelope"></i> Tous les messages</a></li>
                <li><a href="contact-view.php?id=<?php echo $contact_id; ?>" class="active"><i class="fas fa-eye"></i> Voir le message</a></li>
            </ul>
        </nav>
        
        <!-- Messages -->
        <?php if($message): ?>
        <div class="message success">
            <i class="fas fa-check-circle"></i>
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="message error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if(!$contact): ?>
        
        <div class="contact-card" style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: var(--danger); margin-bottom: 20px;"></i>
            <h3>Message non trouvé</h3>
            <p>Le message que vous essayez de consulter n'existe pas ou a été supprimé.</p>
            <a href="contacts-admin.php" class="btn btn-back" style="margin-top: 20px;">
                <i class="fas fa-arrow-left"></i> Retour à la liste des messages
            </a>
        </div>
        
        <?php else: ?>
        
        <div class="contact-view-content">
            <!-- Contenu principal -->
            <main>
                <!-- Carte du message -->
                <div class="contact-card">
                    <div class="contact-header">
                        <h2>Message de <?php echo htmlspecialchars($contact['nom']); ?></h2>
                        <div class="reference-badge">
                            <?php echo $contact['reference']; ?>
                        </div>
                    </div>
                    
                    <div class="contact-info-grid">
                        <div class="info-block">
                            <h4><i class="fas fa-user"></i> Contact</h4>
                            <p><strong>Nom :</strong> <?php echo htmlspecialchars($contact['nom']); ?><br>
                            <strong>Email :</strong> <?php echo htmlspecialchars($contact['email']); ?><br>
                            <strong>Téléphone :</strong> <?php echo htmlspecialchars($contact['telephone'] ?: 'Non spécifié'); ?><br>
                            <strong>Entreprise :</strong> <?php echo htmlspecialchars($contact['entreprise'] ?: 'Non spécifié'); ?></p>
                        </div>
                        
                        <div class="info-block">
                            <h4><i class="fas fa-info-circle"></i> Métadonnées</h4>
                            <p><strong>Date :</strong> <?php echo date('d/m/Y à H:i', strtotime($contact['date_creation'])); ?><br>
                            <strong>Sujet :</strong> <?php echo htmlspecialchars($contact['sujet']); ?><br>
                            <strong>Statut :</strong> <span class="status-badge status-<?php echo $contact['statut']; ?>">
                                <?php echo $contact['statut']; ?>
                            </span><br>
                            <strong>Traîté par :</strong> <?php echo htmlspecialchars($contact['traite_par'] ?: 'Non assigné'); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-message">
                        <h3><i class="fas fa-comment-alt"></i> Message</h3>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($contact['message'])); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire de réponse -->
                <div class="reply-form">
                    <h3><i class="fas fa-reply"></i> Répondre au message</h3>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="reply">
                        
                        <div class="form-group">
                            <label for="reponse_message">Votre réponse <span style="color: red;">*</span></label>
                            <textarea id="reponse_message" name="reponse_message" class="form-control" 
                                      placeholder="Tapez votre réponse ici..." required></textarea>
                            <small style="color: var(--text-light); margin-top: 5px; display: block;">
                                Cette réponse sera envoyée par email à <?php echo htmlspecialchars($contact['email']); ?>
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="statut">Mettre à jour le statut</label>
                            <select id="statut" name="statut" class="form-control">
                                <option value="en_cours">En cours</option>
                                <option value="repondu" selected>Répondu</option>
                                <option value="archive">Archivé</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes internes (optionnel)</label>
                            <textarea id="notes" name="notes" class="form-control" 
                                      placeholder="Notes pour l'équipe..."><?php echo htmlspecialchars($contact['notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <div style="display: flex; gap: 15px; justify-content: flex-end;">
                            <button type="button" class="btn btn-back" onclick="history.back()">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                            <button type="submit" class="btn btn-email">
                                <i class="fas fa-paper-plane"></i> Envoyer la réponse
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Réponses précédentes -->
                <?php if(!empty($reponses)): ?>
                <div class="reply-form">
                    <h3><i class="fas fa-history"></i> Historique des réponses (<?php echo count($reponses); ?>)</h3>
                    
                    <div class="responses-container">
                        <?php foreach($reponses as $reponse): ?>
                        <div class="response-item">
                            <div class="response-header">
                                <span class="response-author">
                                    <i class="fas fa-user-circle"></i>
                                    <?php echo htmlspecialchars($reponse['auteur']); ?>
                                </span>
                                <span class="response-date">
                                    <?php echo date('d/m/Y à H:i', strtotime($reponse['date_envoi'])); ?>
                                </span>
                            </div>
                            <div class="response-content">
                                <?php echo nl2br(htmlspecialchars($reponse['message'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </main>
            
            <!-- Sidebar -->
            <aside class="contact-sidebar">
                <!-- Statut -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-tag"></i> Statut</h3>
                    <div class="status-container">
                        <div class="current-status">
                            <div class="status-badge status-<?php echo $contact['statut']; ?>">
                                <?php echo $contact['statut']; ?>
                            </div>
                            <p style="margin: 10px 0 0 0; color: var(--text-light); font-size: 0.9rem;">
                                Dernière mise à jour : <?php echo $contact['date_reponse'] ? date('d/m/Y H:i', strtotime($contact['date_reponse'])) : 'Jamais'; ?>
                            </p>
                        </div>
                        
                        <form method="POST" action="" class="status-form">
                            <input type="hidden" name="action" value="update_status">
                            <div class="form-group">
                                <label for="new_status">Changer le statut</label>
                                <select id="new_status" name="statut" class="form-control">
                                    <option value="nouveau" <?php echo $contact['statut'] == 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                                    <option value="lu" <?php echo $contact['statut'] == 'lu' ? 'selected' : ''; ?>>Lu</option>
                                    <option value="en_cours" <?php echo $contact['statut'] == 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                                    <option value="repondu" <?php echo $contact['statut'] == 'repondu' ? 'selected' : ''; ?>>Répondu</option>
                                    <option value="archive" <?php echo $contact['statut'] == 'archive' ? 'selected' : ''; ?>>Archivé</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-email">
                                <i class="fas fa-sync-alt"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-sticky-note"></i> Notes internes</h3>
                    <div class="notes-container">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="statut" value="<?php echo $contact['statut']; ?>">
                            <div class="form-group">
                                <textarea id="notes_sidebar" name="notes" class="form-control" rows="4"
                                          placeholder="Ajouter des notes internes..."><?php echo htmlspecialchars($contact['notes'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-email">
                                <i class="fas fa-save"></i> Enregistrer les notes
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-bolt"></i> Actions rapides</h3>
                    <div class="contact-actions">
                        <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>?subject=Réponse à votre message <?php echo $contact['reference']; ?>" 
                           class="action-btn btn-email">
                            <i class="fas fa-envelope"></i>
                            Répondre par email
                        </a>
                        
                        <a href="javascript:void(0)" 
                           class="action-btn btn-delete"
                           onclick="openDeleteModal()">
                            <i class="fas fa-trash"></i>
                            Supprimer ce message
                        </a>
                        
                        <a href="contacts-admin.php" class="action-btn btn-back">
                            <i class="fas fa-arrow-left"></i>
                            Retour à la liste
                        </a>
                    </div>
                </div>
                
                <!-- Historique -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-history"></i> Historique</h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">
                                <?php echo date('d/m/Y H:i', strtotime($contact['date_creation'])); ?>
                            </div>
                            <div class="timeline-action">
                                Message reçu
                            </div>
                        </div>
                        
                        <?php if($contact['date_reponse']): ?>
                        <div class="timeline-item">
                            <div class="timeline-date">
                                <?php echo date('d/m/Y H:i', strtotime($contact['date_reponse'])); ?>
                            </div>
                            <div class="timeline-action">
                                Répondu par <?php echo htmlspecialchars($contact['traite_par'] ?: 'Administrateur'); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php foreach($reponses as $reponse): ?>
                        <div class="timeline-item">
                            <div class="timeline-date">
                                <?php echo date('d/m/Y H:i', strtotime($reponse['date_envoi'])); ?>
                            </div>
                            <div class="timeline-action">
                                Réponse envoyée
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
        </div>
        
        <?php endif; ?>
    </div>

    <!-- Modal de suppression -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmer la suppression</h3>
            <p>Êtes-vous sûr de vouloir supprimer ce message ? Cette action est irréversible et supprimera également toutes les réponses associées.</p>
            <div class="modal-actions">
                <button type="button" class="btn btn-back" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <form method="POST" action="contact-delete.php" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $contact_id; ?>">
                    <button type="submit" class="btn btn-delete">
                        <i class="fas fa-trash"></i> Supprimer définitivement
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Gestion du modal de suppression
        function openDeleteModal() {
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Fermer le modal en cliquant à l'extérieur
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeDeleteModal();
            }
        }
        
        // Compteur de caractères pour la réponse
        document.addEventListener('DOMContentLoaded', function() {
            const reponseTextarea = document.getElementById('reponse_message');
            const charCounter = document.createElement('div');
            charCounter.style.cssText = 'color: var(--text-light); font-size: 0.9rem; margin-top: 5px; text-align: right;';
            charCounter.id = 'charCounter';
            charCounter.textContent = '0 caractères';
            reponseTextarea.parentNode.insertBefore(charCounter, reponseTextarea.nextSibling);
            
            reponseTextarea.addEventListener('input', function() {
                const length = this.value.length;
                charCounter.textContent = `${length} caractères`;
                
                if (length < 10) {
                    charCounter.style.color = '#dc3545';
                } else if (length < 50) {
                    charCounter.style.color = '#ffc107';
                } else {
                    charCounter.style.color = '#28a745';
                }
            });
            
            // Initialiser le compteur
            reponseTextarea.dispatchEvent(new Event('input'));
            
            // Copier l'email au clic
            const emailElement = document.querySelector('strong:contains("Email")');
            if (emailElement) {
                const email = emailElement.parentElement.textContent.split(':')[1].trim();
                emailElement.parentElement.style.cursor = 'pointer';
                emailElement.parentElement.title = 'Cliquer pour copier';
                emailElement.parentElement.addEventListener('click', function() {
                    navigator.clipboard.writeText(email).then(() => {
                        const original = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-check"></i> Email copié !';
                        setTimeout(() => {
                            this.innerHTML = original;
                        }, 2000);
                    });
                });
            }
            
            // Auto-sauvegarde des notes
            const notesTextarea = document.getElementById('notes_sidebar');
            let notesTimeout;
            
            notesTextarea.addEventListener('input', function() {
                clearTimeout(notesTimeout);
                notesTimeout = setTimeout(() => {
                    // Ici vous pourriez implémenter une sauvegarde auto via AJAX
                    console.log('Auto-sauvegarde des notes...');
                }, 2000);
            });
            
            // Prévisualisation de l'email
            const reponseForm = document.querySelector('form[action=""]');
            if (reponseForm) {
                const previewBtn = document.createElement('button');
                previewBtn.type = 'button';
                previewBtn.className = 'btn';
                previewBtn.innerHTML = '<i class="fas fa-eye"></i> Prévisualiser';
                previewBtn.style.marginLeft = '10px';
                
                previewBtn.addEventListener('click', function() {
                    const message = reponseTextarea.value;
                    const subject = `Réponse à votre message ${<?php echo json_encode($contact['reference']); ?>}`;
                    
                    const previewWindow = window.open('', '_blank');
                    previewWindow.document.write(`
                        <html>
                        <head>
                            <title>Prévisualisation de l'email</title>
                            <style>
                                body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
                                .header { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
                                .content { line-height: 1.6; white-space: pre-wrap; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h2>À : ${<?php echo json_encode($contact['email']); ?>}</h2>
                                <h3>Sujet : ${subject}</h3>
                            </div>
                            <div class="content">
                                ${message}
                            </div>
                        </body>
                        </html>
                    `);
                });
                
                const submitBtn = reponseForm.querySelector('button[type="submit"]');
                submitBtn.parentNode.insertBefore(previewBtn, submitBtn);
            }
        });
        
        // Raccourci clavier Ctrl+Entrée pour soumettre
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                const activeElement = document.activeElement;
                if (activeElement.tagName === 'TEXTAREA') {
                    e.preventDefault();
                    activeElement.form.submit();
                }
            }
        });
    </script>
</body>
</html>