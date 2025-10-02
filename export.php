<?php
// export.php - VERSION AVEC CHEMINS ABSOLUS FORCÉS

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/lib/php-utils.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$input = file_get_contents('php://input');
$data = json_decode($input, true);

extract_data($data);

// Récupère le CSS personnalisé en prévoyant une valeur par défaut vide
$css = $css ?? '';

// --- DÉBUT DE LA CORRECTION ULTIME POUR LES ICÔNES ---

// 1. On lit le contenu des fichiers CSS
$fa_css_content = file_get_contents(__DIR__ . '/assets/all.min.css');
$fonts_css = file_get_contents(__DIR__ . '/assets/fonts.css');
$default_card_css = file_get_contents(__DIR__ . '/assets/default-card.css');
$print_marks_css = file_get_contents(__DIR__ . '/assets/print-marks.css');

// 2. On transforme les chemins relatifs des polices en chemins absolus "file://"
// pour que Dompdf puisse embarquer les fontes locales.
$root_path = __DIR__ . '/';
$assets_path = __DIR__ . '/assets';
$rewrite_css_urls = static function (string $css, string $css_dir) use ($root_path) {
    return preg_replace_callback(
        "/url\((['\"]?)([^)]+)\1\)/",
        static function ($matches) use ($css_dir, $root_path) {
            $quote = $matches[1];
            $url = trim($matches[2]);

            // On laisse tranquilles les URLs déjà absolues (http, https, data, file).
            if (preg_match('#^(?:data:|https?:|file:)#i', $url)) {
                return 'url(' . $quote . $url . $quote . ')';
            }

            $normalized = str_replace('\\
', '/', $url);

            // Les chemins commençant par "/" ou "assets/" doivent être résolus
            // depuis la racine du projet (car le CSS est inliné dans le HTML).
            if ($normalized !== '' && $normalized[0] === '/') {
                $target = realpath($root_path . ltrim($normalized, '/'));
            } elseif (strncmp($normalized, 'assets/', 7) === 0) {
                $target = realpath($root_path . $normalized);
            } else {
                $target = realpath($css_dir . '/' . $normalized);
            }

            if ($target === false) {
                return 'url(' . $quote . $url . $quote . ')';
            }

            $target = str_replace('\\', '/', $target);

            return 'url(' . $quote . 'file://' . $target . $quote . ')';
        },
        $css
    );
};

$fa_css_content_fixed = $rewrite_css_urls($fa_css_content, $assets_path);
$fonts_css_fixed = $rewrite_css_urls($fonts_css, $assets_path);

// --- FIN DE LA CORRECTION ---

$html = '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8">';
// 3. On injecte directement le contenu des CSS dans le HTML
$card_styles = $default_card_css;

// Overrides spécifiques au rendu PDF : on neutralise les styles @media print
// (pensés pour l'impression navigateur) afin de conserver l'apparence écran,
// et on force un clamp multi-lignes compatible Dompdf.
$pdf_specific_css = <<<'CSS'
body.pdf-export .card-panel {
  padding: 12pt;
}

body.pdf-export .card-body {
  display: block;
  overflow: hidden;
  font-size: 9.3pt;
  line-height: 1.45;
  max-height: 23.2em;
}

body.pdf-export .card.compact .card-panel {
  padding: 10pt 10pt 6pt;
}

body.pdf-export .card.compact .card-body {
  font-size: 8.8pt;
  line-height: 1.35;
  max-height: 24.3em;
}

body.pdf-export .card.ultra .card-panel {
  padding: 9pt 9pt 5pt;
}

body.pdf-export .card.ultra .card-body {
  font-size: 8.4pt;
  line-height: 1.32;
  max-height: 26.4em;
}
CSS;

$card_styles .= "\n\n" . $pdf_specific_css;
if (trim($css) !== '') {
    $card_styles .= "\n\n" . $css;
}

$html .= '<style>' . $fa_css_content_fixed . $fonts_css_fixed . $card_styles . $print_marks_css . '</style>';
$html .= '</head><body class="pdf-export">';
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