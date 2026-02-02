<?php
// admin/update_status.php
require_once '../includes/functions.php';

// Vérifier si c'est une requête AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès interdit']);
    exit();
}

// Vérifier l'authentification admin
session_start();
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
    exit();
}

// Vérifier les paramètres
if (!isset($_POST['id']) || !isset($_POST['statut'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit();
}

$id = (int)$_POST['id'];
$statut = $_POST['statut'];

// Valider le statut
$allowedStatuses = ['en_attente', 'confirmee', 'annulee', 'terminee'];
if (!in_array($statut, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Statut invalide']);
    exit();
}

$db = getDBConnection();

// Vérifier si la commande existe
$checkSql = "SELECT id, numero_commande, client_email, statut FROM commandes WHERE id = :id";
$checkStmt = $db->prepare($checkSql);
$checkStmt->execute([':id' => $id]);
$commande = $checkStmt->fetch();

if (!$commande) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Commande non trouvée']);
    exit();
}

// Mettre à jour le statut
$updateSql = "UPDATE commandes SET statut = :statut, updated_at = NOW() WHERE id = :id";
$updateStmt = $db->prepare($updateSql);
$success = $updateStmt->execute([':statut' => $statut, ':id' => $id]);

if ($success) {
    // Envoyer un email de notification si le statut change
    if ($commande['statut'] !== $statut) {
        sendStatusUpdateEmail($commande['client_email'], $commande['numero_commande'], $commande['statut'], $statut);
    }
    
    // Journaliser l'action
    logAction('update_status', [
        'admin_id' => $_SESSION['admin_id'],
        'admin_username' => $_SESSION['admin_username'],
        'commande_id' => $id,
        'ancien_statut' => $commande['statut'],
        'nouveau_statut' => $statut
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Statut mis à jour avec succès',
        'new_status' => $statut
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
}

/**
 * Envoyer un email de notification de changement de statut
 */
function sendStatusUpdateEmail($to, $orderNumber, $oldStatus, $newStatus) {
    $statusLabels = [
        'en_attente' => 'En attente',
        'confirmee' => 'Confirmée',
        'annulee' => 'Annulée',
        'terminee' => 'Terminée'
    ];
    
    $oldStatusLabel = $statusLabels[$oldStatus] ?? $oldStatus;
    $newStatusLabel = $statusLabels[$newStatus] ?? $newStatus;
    
    $subject = "Mise à jour du statut de votre commande Shalom Digital Solutions- $orderNumber";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { background-color: #f9f9f9; padding: 20px; }
            .status-change { background-color: white; border: 1px solid #ddd; padding: 15px; margin: 15px 0; text-align: center; }
            .old-status { color: #666; text-decoration: line-through; }
            .new-status { color: #3498db; font-weight: bold; font-size: 1.2rem; }
            .info-box { background-color: #e8f4fc; border-left: 4px solid #3498db; padding: 15px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Shalom Digital Solutions</h2>
                <p>Mise à jour du statut de commande</p>
            </div>
            
            <div class='content'>
                <p>Bonjour,</p>
                
                <p>Le statut de votre commande <strong>#$orderNumber</strong> a été mis à jour.</p>
                
                <div class='status-change'>
                    <span class='old-status'>$oldStatusLabel</span>
                    <i class='fas fa-arrow-right'></i>
                    <span class='new-status'>$newStatusLabel</span>
                </div>
                
                <div class='info-box'>
                    <p><strong>Que signifie ce changement ?</strong></p>
                    ";
    
    switch ($newStatus) {
        case 'confirmee':
            $message .= "<p>Votre commande a été confirmée. Notre équipe va maintenant commencer à travailler sur votre projet. Nous vous contacterons sous peu pour discuter des prochaines étapes.</p>";
            break;
        case 'annulee':
            $message .= "<p>Votre commande a été annulée. Si vous avez des questions concernant cette annulation, n'hésitez pas à nous contacter.</p>";
            break;
        case 'terminee':
            $message .= "<p>Votre commande est maintenant terminée. Nous espérons que vous êtes satisfait du résultat. N'hésitez pas à nous faire part de vos retours !</p>";
            break;
        default:
            $message .= "<p>Notre équipe traite actuellement votre commande. Nous vous tiendrons informé des prochaines étapes.</p>";
    }
    
    $message .= "
                </div>
                
                <p>Vous pouvez suivre l'avancement de votre commande en répondant à cet email.</p>
                
                <p>Cordialement,<br>
                <strong>L'équipe Shalom Digital Solutions</strong></p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: Shalom DigitalSolutions<liferopro@gmail.com>',
        'Reply-To: liferopro@gmail.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // En production, décommentez cette ligne
    // mail($to, $subject, $message, implode("\r\n", $headers));
    
    // Pour le développement, simuler l'envoi
    error_log("Status update email sent to: $to - Subject: $subject");
    return true;
}

/**
 * Journaliser les actions admin
 */
function logAction($action, $data = []) {
    $logMessage = date('Y-m-d H:i:s') . " - Action: $action";
    
    if (!empty($data)) {
        $logMessage .= " - Data: " . json_encode($data);
    }
    
    error_log($logMessage . PHP_EOL, 3, __DIR__ . '/../logs/admin-actions.log');
}
?>