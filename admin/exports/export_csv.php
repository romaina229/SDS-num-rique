<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = getDBConnection();

$stmt = $db->query("
    SELECT c.id, c.order_number, c.client_nom, c.montant_fcfa, c.statut, c.created_at
    FROM commandes c
    ORDER BY c.created_at DESC
");

$filename = 'commandes_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// En-têtes
fputcsv($output, ['ID', 'Commande', 'Client', 'Montant (FCFA)', 'Statut', 'Date']);

// Données
while ($row = $stmt->fetch()) {
    fputcsv($output, [
        $row['id'],
        $row['order_number'],
        $row['client_nom'],
        $row['montant_fcfa'],
        $row['statut'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
