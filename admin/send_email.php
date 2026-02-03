<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../includes/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/src/SMTP.php';

if (!isset($_GET['id'])) {
    die('Commande invalide');
}

$id = (int) $_GET['id'];
$commande = getCommandeById($id);

if (!$commande) {
    die('Commande introuvable');
}

// DEBUG: Vérifiez les données reçues
// var_dump($commande); // À décommenter temporairement pour debug

// Vérifiez si les clés existent avant de les utiliser
$order_number = $commande['numero_commande'] ?? 'N/A'; // Votre tableau montre 'numero_commande'
$client_email = $commande['client_email'] ?? '';
$client_nom = $commande['client_nom'] ?? 'Client';
$statut = $commande['statut'] ?? 'Inconnu';
$montant_fcfa = isset($commande['montant_fcfa']) ? number_format($commande['montant_fcfa'], 0, ',', ' ') : '0';
$service_nom = $commande['service_nom'] ?? 'Service';

// Vérification de l'email
if (empty($client_email) || !filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
    die('Adresse email client invalide : ' . htmlspecialchars($client_email));
}

$mail = new PHPMailer(true);

try {
    // CONFIG SMTP (GMAIL)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'liferopro@gmail.com';
    $mail->Password   = 'qdjdncuwwbrllnbq'; // Vérifiez que c'est bien un mot de passe d'application
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    
    // Timeout config
    $mail->SMTPDebug = 0; // Mettez à 2 pour debug détaillé
    $mail->Timeout = 30;
    
    // EMAIL
    $mail->setFrom('liferopro@gmail.com', 'Shalom Digital Solutions');
    $mail->addAddress($client_email, $client_nom);
    $mail->addReplyTo('liferopro@gmail.com', 'Shalom Digital Solutions');

    $mail->isHTML(true);
    $mail->Subject = 'Votre commande #' . $order_number . ' - Shalom Digital Solutions';
    
    // Version texte pour les clients sans HTML
    $mail->AltBody = "Bonjour {$client_nom},

Nous vous contactons concernant votre commande #{$order_number}.

Statut : {$statut}
Service : {$service_nom}
Montant : {$montant_fcfa} FCFA

Cordialement,
Shalom Digital Solutions";

    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px; }
            .content { padding: 20px; background-color: #fff; }
            .order-details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Shalom Digital Solutions</h2>
            </div>
            
            <div class='content'>
                <p>Bonjour <strong>{$client_nom}</strong>,</p>
                
                <p>Nous vous contactons concernant votre commande :</p>
                
                <div class='order-details'>
                    <h3>Détails de la commande</h3>
                    <p><strong>Numéro de commande :</strong> #{$order_number}</p>
                    <p><strong>Service :</strong> {$service_nom}</p>
                    <p><strong>Statut :</strong> <span style='color: #007bff;'>{$statut}</span></p>
                    <p><strong>Montant :</strong> {$montant_fcfa} FCFA</p>
                </div>
                
                <p>Nous restons à votre disposition pour toute information complémentaire.</p>
                
                <p>Cordialement,<br>
                <strong>L'équipe Shalom Digital Solutions</strong></p>
            </div>
            
            <div class='footer'>
                <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                <p>© " . date('Y') . " Shalom Digital Solutions</p>
            </div>
        </div>
    </body>
    </html>";

    // Envoi de l'email
    if ($mail->send()) {
        // Log du succès
        error_log("Email envoyé avec succès à " . $client_email . " pour la commande #" . $order_number);
        
        // Redirection avec message de succès
        $_SESSION['success_message'] = 'Email envoyé avec succès à ' . $client_email;
        header('Location: commandes.php?success=mail_sent&order=' . $order_number);
        exit;
    } else {
        throw new Exception('Échec de l\'envoi sans exception');
    }

} catch (Exception $e) {
    // Log détaillé de l'erreur
    $error_msg = "Erreur email pour la commande #{$order_number}: " . $mail->ErrorInfo;
    error_log($error_msg);
    
    // Afficher un message d'erreur détaillé en mode debug
    if (isset($_SESSION['is_debug']) && $_SESSION['is_debug']) {
        die('Erreur lors de l\'envoi de l\'email :<br><br>' . 
            htmlspecialchars($mail->ErrorInfo) . 
            '<br><br>Destinataire : ' . htmlspecialchars($client_email));
    } else {
        die('Erreur lors de l\'envoi de l\'email. Contactez l\'administrateur.');
    }
}