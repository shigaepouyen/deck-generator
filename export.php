<?php
// export.php - VERSION AVEC CHEMINS ABSOLUS FORCÉS

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/lib/php-utils.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$input = file_get_contents('php://input');
$data = json_decode($input, true);

extract_data($data);

// --- DÉBUT DE LA CORRECTION ULTIME POUR LES ICÔNES ---

// 1. On lit le contenu des fichiers CSS
$fa_css_content = file_get_contents('assets/all.min.css');
$fonts_css = file_get_contents('assets/fonts.css');
$default_card_css = file_get_contents('assets/default-card.css');
$print_marks_css = file_get_contents('assets/print-marks.css');

// 2. On transforme les chemins relatifs des polices en chemins absolus
// C'est la partie cruciale. On dit à dompdf exactement où trouver les polices.
$absolute_webfonts_path = __DIR__ . '/assets/webfonts';
$fa_css_content_fixed = str_replace('url(webfonts', 'url(' . $absolute_webfonts_path, $fa_css_content);

// --- FIN DE LA CORRECTION ---

$html = '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8">';
// 3. On injecte directement le contenu des CSS dans le HTML
$html .= '<style>' . $fa_css_content_fixed . $fonts_css . $default_card_css . $print_marks_css . '</style>';
$html .= '</head><body>';
$html .= generate_html_pages($rows, $tpl_front, $tpl_back, $mode, $cols, $rows_per_page, $bleed, $gutter, $flip, $offx, $offy);
$html .= '</body></html>';

$options = new Options();
// On garde chroot car il aide pour les images comme logo.svg
$options->set('chroot', __DIR__);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Inter');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper($paper, $orient);
$dompdf->render();

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Deck_Cards.pdf"');
echo $dompdf->output();