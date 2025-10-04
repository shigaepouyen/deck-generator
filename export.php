<?php
// Inclure l'autoloader de Composer et les fonctions utilitaires
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/lib/php-utils.php';

use Spatie\Browsershot\Browsershot;
use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;

if (!class_exists(Browsershot::class)) {
    http_response_code(500);
    die('<h1>Module Browsershot introuvable</h1><p>Assurez-vous que le paquet <code>spatie/browsershot</code> est installé via Composer.</p>');
}

// --- Augmenter la limite de temps d'exécution ---
set_time_limit(180);

// --- Récupérer et valider le payload ---
if (!isset($_POST['payload'])) { http_response_code(400); die("Erreur : Payload manquant."); }
$data = json_decode($_POST['payload'], true);
if (json_last_error() !== JSON_ERROR_NONE) { http_response_code(400); die("Erreur : JSON mal formé : " . json_last_error_msg()); }

// --- Extraire les données ---
$rows = $data['rows'] ?? [];
$tpl_front = $data['tpl_front'] ?? '';
$tpl_back = $data['tpl_back'] ?? '';
$css_card = $data['css_card'] ?? '';
$css_fonts = $data['css_fonts'] ?? '';
$css_fa = $data['css_fa'] ?? '';
$cols = $data['cols'] ?? 3;
$rows_per_page = $data['rows_per_page'] ?? 3;
if (empty($rows)) { http_response_code(400); die("Erreur: Le tableau de données des cartes ('rows') est vide."); }

// --- Préparation du contenu HTML 100% autonome ---
function embed_assets_as_base64($content, $asset_folder) {
    $pattern = "#(?P<prefix>url\\(|src=)(?P<quote>['\"]?)(?P<path>(?:\\.\\./|\\./)?(?:assets|fonts|webfonts)/[^'\")]+)(?P=quote)(?P<suffix>\\))?#";
    return preg_replace_callback($pattern,
        function ($matches) use ($asset_folder) {
            $prefix = $matches['prefix'];
            $quote = $matches['quote'];
            $relative_path = preg_replace('#^(?:\.\.?/)+#', '', $matches['path']);
            $relative_path = preg_replace('#^assets/#', '', $relative_path);
            $asset_path = rtrim($asset_folder, '/\\') . '/' . $relative_path;
            if (file_exists($asset_path)) {
                $data = file_get_contents($asset_path);
                $mime_type = mime_content_type($asset_path) ?: 'application/octet-stream';
                $base64 = 'data:' . $mime_type . ';base64,' . base64_encode($data);
                if ($prefix === 'url(') {
                    return 'url(' . $quote . $base64 . $quote . ')';
                }
                $attr_quote = $quote !== '' ? $quote : '"';
                return 'src=' . $attr_quote . $base64 . $attr_quote;
            }
            return $matches[0];
        }, $content);
}

// CSS pour la mise en page PDF + Surcharges pour un rendu net
$css_print_layout = "
    @page { size: A4 portrait; margin: 0; }
    body { margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .deck { --card-width: 63.5mm; --card-height: 88.9mm; --grid-gap: 5mm; }
    .page { width: 210mm; height: 297mm; padding: 10mm; box-sizing: border-box; display: flex; justify-content: center; align-items: center; page-break-after: always; }
    .card-grid {
        display: grid;
        grid-template-columns: repeat(var(--grid-cols, 3), var(--card-width));
        grid-auto-rows: var(--card-height);
        gap: var(--grid-gap);
        align-items: stretch;
        justify-items: stretch;
    }
    
    /* Styles spécifiques à l'export PDF pour supprimer les ombres */
    .card, .card-badge, .card-back .logo { 
        box-shadow: none !important; 
        filter: none !important;
    }
    .card--back {
        box-shadow: inset 0 0 0 1pt #0f172a !important;
    }
    .card--back img {
      filter: invert(1) !important;
    }
    .card--placeholder {
        background: #e2e8f0;
        border: 1pt solid rgba(15, 23, 42, 0.12);
        border-radius: var(--radius, 12pt);
    }
";

$assets_dir = __DIR__ . '/assets';
$css_fonts_embedded = embed_assets_as_base64($css_fonts, $assets_dir);
$css_fa_embedded = embed_assets_as_base64($css_fa, $assets_dir);
$tpl_back_embedded = embed_assets_as_base64($tpl_back, $assets_dir);
$tpl_front_embedded = embed_assets_as_base64($tpl_front, $assets_dir);

$all_css = $css_fonts_embedded . "\n" . $css_fa_embedded . "\n" . $css_card . "\n" . $css_print_layout;

$htmlContent = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Planche de Cartes</title><style>' . $all_css . '</style></head><body><main class="deck">';
$cardsPerPage = $cols * $rows_per_page;
$pages = array_chunk($rows, $cardsPerPage);

// Génération des pages recto/verso intercalées
foreach ($pages as $pageCards) {
    $cardCountOnPage = count($pageCards);
    $pageCards = array_values($pageCards);

    // Recto
    $htmlContent .= '<section class="page" style="--grid-cols: ' . $cols . ';"><div class="card-grid">';
    for ($i = 0; $i < $cardsPerPage; $i++) {
        if ($i < $cardCountOnPage) {
            $htmlContent .= render_card($tpl_front_embedded, $pageCards[$i]);
        } else {
            $htmlContent .= '<div class="card card--placeholder"></div>';
        }
    }
    $htmlContent .= '</div></section>';

    // Verso
    $htmlContent .= '<section class="page" style="--grid-cols: ' . $cols . ';"><div class="card-grid">';
    for ($i = 0; $i < $cardsPerPage; $i++) {
        $htmlContent .= ($i < $cardCountOnPage ? $tpl_back_embedded : '<div class="card card--placeholder"></div>');
    }
    $htmlContent .= '</div></section>';
}
$htmlContent .= '</main></body></html>';

// --- Génération du PDF ---
$outputPath = sys_get_temp_dir() . '/planches_de_cartes_' . uniqid() . '.pdf';

try {
    Browsershot::html($htmlContent)
        ->margins(0, 0, 0, 0)
        ->format('A4')
        ->showBackground()
        ->timeout(60)
        ->addChromiumArguments(['--no-sandbox', '--disable-setuid-sandbox'])
        ->save($outputPath);

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="planches_de_cartes.pdf"');
    header('Content-Length: ' . filesize($outputPath));
    readfile($outputPath);
    unlink($outputPath);
    exit;
} catch (\Throwable $e) {
    http_response_code(500);
    die('Erreur de génération PDF: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>
