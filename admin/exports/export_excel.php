<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = getDBConnection();

$stmt = $db->query("
    SELECT c.order_number, c.client_nom, c.montant_fcfa, c.statut, c.created_at
    FROM commandes c
    ORDER BY c.created_at DESC
");

$filename = 'commandes_' . date('Ymd_His') . '.xls';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");

echo "<table border='1'>";
echo "<tr>
        <th>Commande</th>
        <th>Client</th>
        <th>Montant (FCFA)</th>
        <th>Statut</th>
        <th>Date</th>
      </tr>";

while ($row = $stmt->fetch()) {
    echo "<tr>
            <td>{$row['order_number']}</td>
            <td>{$row['client_nom']}</td>
            <td>{$row['montant_fcfa']}</td>
            <td>{$row['statut']}</td>
            <td>{$row['created_at']}</td>
          </tr>";
}

echo "</table>";
exit;
