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

$contact_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if($contact_id > 0) {
    try {
        // Démarrer une transaction
        $db->beginTransaction();
        
        // Supprimer d'abord les réponses (si la table existe)
        $query_reponses = "DELETE FROM contact_reponses WHERE contact_id = :contact_id";
        $stmt_reponses = $db->prepare($query_reponses);
        $stmt_reponses->bindParam(':contact_id', $contact_id);
        $stmt_reponses->execute();
        
        // Puis supprimer le contact
        $query_contact = "DELETE FROM contacts WHERE id = :id";
        $stmt_contact = $db->prepare($query_contact);
        $stmt_contact->bindParam(':id', $contact_id);
        $stmt_contact->execute();
        
        // Valider la transaction
        $db->commit();
        
        $_SESSION['success_message'] = "Message supprimé avec succès.";
        
    } catch (PDOException $e) {
        $db->rollBack();
        $_SESSION['error_message'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "ID invalide.";
}

// Rediriger vers la liste des contacts
header('Location: contacts-admin.php');
exit();