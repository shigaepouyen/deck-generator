<?php
// lib/php-utils.php

/**
 * Extrait les données du payload JSON dans des variables globales pour les rendre
 * accessibles dans les scripts principaux (print.php et export.php).
 */
function extract_data(array $data) {
    global $rows, $tpl_front, $tpl_back, $css, $paper, $orient, $cols, $rows_per_page, $bleed, $gutter, $flip, $offx, $offy, $mode;
    $rows = $data['rows'] ?? [];
    $tpl_front = $data['tpl_front'] ?? '';
    $tpl_back = $data['tpl_back'] ?? '';
    $css = $data['css'] ?? '';
    $paper = $data['paper'] ?? 'A4';
    $orient = $data['orient'] ?? 'portrait';
    $cols = max(1, (int)($data['cols'] ?? 3));
    $rows_per_page = max(1, (int)($data['grid_rows'] ?? 3));
    $bleed = (float)($data['bleed'] ?? 0);
    $gutter = (float)($data['gutter'] ?? 0);
    $flip = $data['flip'] ?? 'long-edge';
    $offx = (float)($data['offx'] ?? 0);
    $offy = (float)($data['offy'] ?? 0);
    $mode = $data['mode'] ?? 'fronts';
}

/**
 * Fonction de templating simple côté serveur.
 * Gère le remplacement des variables {{key}} et {{{key}}}.
 * Gère désormais la logique conditionnelle {{#if key}}...{{else}}...{{/if}}.
 */
function render_card(string $template, array $cardData): string {
    $output = $template;

    // Gère les blocs conditionnels {{#if key}}...{{else}}...{{/if}}
    $output = preg_replace_callback(
        '/\{\{#if (\w+)\}\}(.*?)\{\{else\}\}(.*?)\{\{\/if\}\}/s',
        function ($matches) use ($cardData) {
            $key = $matches[1]; // La clé à vérifier (ex: "malefice_points")
            $ifContent = $matches[2];
            $elseContent = $matches[3];
            
            // Si la clé existe et n'est pas une chaîne de caractères vide (après trim)
            if (!empty(trim($cardData[$key] ?? ''))) {
                return $ifContent; // On garde le contenu du bloc "if"
            } else {
                return $elseContent; // On garde le contenu du bloc "else"
            }
        },
        $output
    );

    // Remplace les variables simples {{key}}
    foreach ($cardData as $key => $value) {
        $output = str_replace('{{{' . $key . '}}}', $value, $output); // Variable brute (non échappée)
        $output = str_replace('{{' . $key . '}}', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $output); // Variable échappée
    }
    return $output;
}

/**
 * Réorganise un tableau de cartes pour l'impression du verso,
 * en appliquant un effet miroir horizontal (long-edge) ou vertical (short-edge).
 */
function reorder_for_backside(array $page_cards, int $cols, string $flip_type): array {
    $reordered = [];
    $num_cards = count($page_cards);
    for ($i = 0; $i < $num_cards; $i++) {
        $row = floor($i / $cols);
        $col = $i % $cols;

        if ($flip_type === 'long-edge') { // Miroir horizontal
            $new_col = $cols - 1 - $col;
            $new_index = $row * $cols + $new_col;
        } else { // short-edge, miroir vertical et horizontal
            $new_index = $num_cards - 1 - $i;
        }
        $reordered[$i] = $page_cards[$new_index];
    }
    return $reordered;
}

/**
 * Génère le code HTML pour une seule planche de cartes, en y plaçant
 * chaque carte avec les repères de coupe.
 */
function generate_sheet_html(array $page_cards, string $template, int $cols, int $rows_per_page, float $bleed, float $gutter, string $side, float $offx = 0, float $offy = 0): string {
    $sheet_style = "--cols: {$cols}; --rows: {$rows_per_page}; --bleed: {$bleed}mm; --gutter: {$gutter}mm;";
    if ($side === 'back') {
        $sheet_style .= "transform: translate({$offx}mm, {$offy}mm);";
    }
    $html = '<div class="sheet" style="' . $sheet_style . '">';
    
    foreach ($page_cards as $index => $card) {
        $row_idx = floor($index / $cols);
        $col_idx = $index % $cols;

        $slot_style = sprintf(
            'left: calc(%d * (var(--card-w) + 2 * var(--bleed) + var(--gutter))); top: calc(%d * (var(--card-h) + 2 * var(--bleed) + var(--gutter)));',
            $col_idx, $row_idx
        );
        
        $is_last_row = ($row_idx === $rows_per_page - 1 || $index + $cols >= count($page_cards));
        $is_last_col = ($col_idx === $cols - 1 || $index % $cols === ($cols - 1));

        $html .= sprintf(
            '<div class="card-slot" style="%s" data-row="%d" data-col="%d" data-row-last="%s" data-col-last="%s">',
            $slot_style,
            $row_idx,
            $col_idx,
            $is_last_row ? 'true' : 'false',
            $is_last_col ? 'true' : 'false'
        );
        $html .= ($side === 'front') ? render_card($template, $card) : $template;
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * Génère le HTML pour l'ensemble des pages (recto, verso, ou les deux).
 */
function generate_html_pages(array $rows, string $tpl_front, string $tpl_back, string $mode, int $cols, int $rows_per_page, float $bleed, float $gutter, string $flip, float $offx, float $offy): string {
    $perPage = $cols * $rows_per_page;
    $pages = array_chunk($rows, $perPage);
    $html = '';

    if ($mode === 'fronts' || $mode === 'duplex') {
        foreach ($pages as $page) {
            $html .= generate_sheet_html($page, $tpl_front, $cols, $rows_per_page, $bleed, $gutter, 'front');
        }
    }
    if ($mode === 'backs' || $mode === 'duplex') {
        foreach ($pages as $page) {
            // Pour le verso, on remplit les slots manquants pour conserver la grille
            while (count($page) < $perPage) {
                $page[] = []; // Carte vide
            }
            $reordered_page = reorder_for_backside($page, $cols, $flip);
            $html .= generate_sheet_html($reordered_page, $tpl_back, $cols, $rows_per_page, $bleed, $gutter, 'back', $offx, $offy);
        }
    }
    return $html;
}