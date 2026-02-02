<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/fpdf/fpdf.php';
requireAdmin();

$db = getDBConnection();

$stmt = $db->query("
    SELECT order_number, client_nom, montant_fcfa, statut, created_at
    FROM commandes
    ORDER BY created_at DESC
");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);

$pdf->Cell(0, 10, 'Liste des commandes - Shalom DigitalPro', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 8, 'Commande', 1);
$pdf->Cell(40, 8, 'Client', 1);
$pdf->Cell(30, 8, 'Montant', 1);
$pdf->Cell(30, 8, 'Statut', 1);
$pdf->Cell(40, 8, 'Date', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 9);

while ($row = $stmt->fetch()) {
    $pdf->Cell(40, 8, $row['order_number'], 1);
    $pdf->Cell(40, 8, $row['client_nom'], 1);
    $pdf->Cell(30, 8, $row['montant_fcfa'], 1);
    $pdf->Cell(30, 8, $row['statut'], 1);
    $pdf->Cell(40, 8, $row['created_at'], 1);
    $pdf->Ln();
}

$pdf->Output('D', 'commandes_' . date('Ymd_His') . '.pdf');
exit;
