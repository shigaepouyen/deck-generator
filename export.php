<?php
// export.php

// Inclure l'autoloader de Composer et les fonctions utilitaires
require 'vendor/autoload.php';
require __DIR__ . '/lib/php-utils.php';

use Spatie\Browsershot\Browsershot;

// Récupérer le payload JSON envoyé depuis l'interface
if (!isset($_POST['payload'])) {
    http_response_code(400);
    die("Erreur : Aucune donnée reçue (payload manquant).");
}
$data = json_decode($_POST['payload'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die("Erreur : Les données JSON sont mal formées.");
}

// Extraire les données dans des variables globales (conservé pour la génération HTML)
extract_data($data);

// --- Préparation du contenu HTML pour le PDF ---

// Chemin vers la nouvelle feuille de style pour l'impression
$stylesheetPath = 'assets/print-styles.css';

// Début du document HTML
$htmlContent = '<!DOCTYPE html><html lang="fr"><head>
    <meta charset="UTF-8">
    <title>Planche de Cartes à Jouer</title>
    <link rel="stylesheet" href="' . $stylesheetPath . '">
</head><body><main class="deck">';

// Logique pour générer les planches recto puis verso
$cardsPerPage = $cols * $rows_per_page;
$pages = array_chunk($rows, $cardsPerPage);

// 1. Générer toutes les pages de rectos
foreach ($pages as $pageCards) {
    $htmlContent .= '<section class="page"><div class="card-grid">';
    foreach ($pageCards as $card) {
        $htmlContent .= '<article class="card card--recto">' . render_card($tpl_front, $card) . '</article>';
    }
    $htmlContent .= '</div></section>';
}

// 2. Générer toutes les pages de versos
foreach ($pages as $pageCards) {
    $htmlContent .= '<section class="page"><div class="card-grid">';
    // Remplir les emplacements vides pour conserver la grille
    $cardCount = count($pageCards);
    for ($i = 0; $i < $cardsPerPage; $i++) {
        // Le dos est le même pour toutes les cartes
        $htmlContent .= '<article class="card card--verso">' . $tpl_back . '</article>';
    }
    $htmlContent .= '</div></section>';
}

$htmlContent .= '</main></body></html>';


// --- Génération du PDF avec Browsershot ---

$outputPath = __DIR__ . '/planches_de_cartes.pdf'; // Sortie à la racine du projet

try {
    // Création de l'instance Browsershot
    Browsershot::html($htmlContent)
        // CRUCIAL: Applique les styles @media print, @page, etc.
        ->emulateMedia('print')
        // Définit le format du papier pour correspondre au CSS
        ->format('A4')
        // S'assure que les couleurs et images de fond sont imprimées
        ->showBackground()
        // Donne au navigateur le temps de charger toutes les ressources (images, polices web)
        ->waitUntilNetworkIdle()
        // Augmente le timeout pour les documents complexes (valeur en secondes)
        ->timeout(120)
        // Sauvegarde le fichier final
        ->save($outputPath);

    // Forcer le téléchargement du fichier généré
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($outputPath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($outputPath));
    readfile($outputPath);
    unlink($outputPath); // Supprimer le fichier du serveur après le téléchargement
    exit;

} catch (Exception $e) {
    // Gérer les erreurs potentielles (ex: timeout, binaire non trouvé)
    http_response_code(500);
    echo "<h1>Erreur lors de la génération du PDF</h1>";
    echo "<p>Message : " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>Assurez-vous que Node.js et Puppeteer sont correctement installés sur le serveur et accessibles par PHP.</pre>";
}

?>