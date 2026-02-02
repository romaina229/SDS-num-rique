<?php
// config/tva.php

// Taux de AIB (en décimal)
define('TAUX_AIB', 0.05); // 5%

// Types de AIB selon les services (si nécessaire)
$tva_par_categorie = [
    'web' => 0.05,
    'excel' => 0.05,
    'survey' => 0.05,
    'formation' => 0.05, // Certaines formations peuvent être exonérées
];

// Fonction pour calculer la AIB
function calculerAIB($montantHT, $taux = TAUX_AIB) {
    return $montantHT * $taux;
}

// Fonction pour calculer le TTC
function calculerTTC($montantHT, $taux = TAUX_AIB) {
    return $montantHT * (1 + $taux);
}