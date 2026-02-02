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
var_dump($commande); // Décommentez pour voir la structure des données

// Vérifiez si les clés existent avant de les utiliser
$order_number = $commande['order_number'] ?? 
                $commande['numero_commande'] ?? 
                'N/A';
$client_email = $commande['client_email'] ?? '';
$client_nom = $commande['client_nom'] ?? 'Client';
$statut = $commande['statut'] ?? 'Inconnu';
$montant_fcfa = $commande['montant_fcfa'] ?? '0';

$mail = new PHPMailer(true);

try {
    // CONFIG SMTP (GMAIL EXEMPLE)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'liferopro@gmail.com';
    $mail->Password   = 'Lifero@2026.'; // IMPORTANT
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // EMAIL
    $mail->setFrom('liferopro@gmail.com', 'Shalom Digital Solutions');
    $mail->addAddress($client_email, $client_nom);

    $mail->isHTML(true);
    $mail->Subject = 'Commande ' . $order_number;

    $mail->Body = "
        <p>Bonjour <strong>{$client_nom}</strong>,</p>

        <p>Nous vous contactons concernant votre commande
        <strong>{$order_number}</strong>.</p>

        <p><strong>Statut :</strong> {$statut}</p>
        <p><strong>Montant :</strong> {$montant_fcfa} FCFA</p>

        <p>Cordialement,<br>
        <strong>Shalom Digital Solutions</strong></p>
    ";

    $mail->send();

    header('Location: commandes.php?success=mail_sent');
    exit;

} catch (Exception $e) {
    logError('Erreur email', ['error' => $mail->ErrorInfo]);
    die('Erreur lors de l\'envoi de l\'email');
}