<?php
// print.php

if (!isset($_POST['payload'])) {
    die("Aucune donnée reçue.");
}
$data = json_decode($_POST['payload'], true);

// Fonctions utilitaires communes
require __DIR__ . '/lib/php-utils.php';

// Extraction des données du payload
extract_data($data);

// CSS pour les repères de coupe
$print_marks_css = file_get_contents('assets/print-marks.css');

$html = '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Impression des cartes</title>';
// On ajoute le lien Font Awesome pour les icônes
$html .= '<link rel="stylesheet" href="assets/all.min.css">';
$html .= '<style>' . $css . $print_marks_css . '
    @media print { .print-ui { display: none; } }
    .print-ui { position: fixed; top: 10px; right: 10px; background: #ffc107; padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); font-family: sans-serif; }
</style>';
$html .= '</head><body>';
$html .= '<div class="print-ui">Aperçu. Lancez l\'impression (Ctrl+P) à 100% / Taille réelle.</div>';

// Génération des pages HTML avec les variables dans le bon ordre
$html .= generate_html_pages($rows, $tpl_front, $tpl_back, $mode, $cols, $rows_per_page, $bleed, $gutter, $flip, $offx, $offy);

$html .= '<script>window.onload = () => window.print();</script>';
$html .= '</body></html>';

echo $html;