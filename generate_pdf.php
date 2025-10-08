<?php
require __DIR__ . '/vendor/autoload.php';

use Spatie\Browsershot\Browsershot;

// --- Validation de l'environnement ---
if (!class_exists(Browsershot::class)) {
    http_response_code(500);
    die('<h1>Module Browsershot introuvable</h1><p>Assurez-vous que le paquet <code>spatie/browsershot</code> est installé via Composer.</p>');
}

// Augmenter la limite de temps d'exécution pour les gros documents
set_time_limit(180);

// --- Récupération et validation du HTML ---
if (!isset($_POST['html']) || empty($_POST['html'])) {
    http_response_code(400);
    die("Erreur : Le contenu HTML est manquant.");
}
$htmlContent = $_POST['html'];

/**
 * Intègre les ressources locales (images, polices) directement dans le HTML en Base64.
 * C'est essentiel pour que le moteur de rendu headless puisse afficher les fichiers locaux.
 *
 * @param string $content Le contenu HTML/CSS source.
 * @param string $asset_folder Le chemin du dossier contenant les ressources.
 * @return string Le contenu avec les ressources intégrées.
 */
function embed_assets_as_base64($content, $asset_folder) {
    // Expression régulière pour trouver les chemins d'URL dans les CSS et les attributs src des balises HTML
    $pattern = "#(?P<prefix>url\\(|src=)(?P<quote>['\"]?)(?P<path>(?:\\.\\./|\\./)?(?:assets|fonts|webfonts)/[^'\")]+)(?P=quote)(?P<suffix>\\))?#";

    return preg_replace_callback($pattern,
        function ($matches) use ($asset_folder) {
            $prefix = $matches['prefix'];
            $quote = $matches['quote'];

            // Nettoyer le chemin pour éviter les traversées de répertoire hasardeuses
            $relative_path = preg_replace('#^(?:\.\.?/)+#', '', $matches['path']);

            $asset_path = rtrim($asset_folder, '/\\') . '/' . $relative_path;

            if (file_exists($asset_path)) {
                $data = file_get_contents($asset_path);
                $mime_type = mime_content_type($asset_path) ?: 'application/octet-stream';
                $base64 = 'data:' . $mime_type . ';base64,' . base64_encode($data);

                // Reconstruire la sortie en fonction du contexte (CSS url() ou attribut src)
                if ($prefix === 'url(') {
                    return 'url(' . $quote . $base64 . $quote . ')';
                }

                // Pour les attributs src, on s'assure d'utiliser des guillemets
                $attr_quote = $quote !== '' ? $quote : '"';
                return 'src=' . $attr_quote . $base64 . $attr_quote;
            }

            // Si le fichier n'est pas trouvé, on retourne la chaîne originale pour ne pas casser le lien
            return $matches[0];
        }, $content);
}

// --- Préparation du HTML final ---
$assets_dir = __DIR__ . '/assets';
$htmlWithEmbeddedAssets = embed_assets_as_base64($htmlContent, $assets_dir);

// Ajouter un doctype et les balises de base si absents pour la robustesse
if (stripos(trim($htmlWithEmbeddedAssets), '<!DOCTYPE') !== 0 && stripos(trim($htmlWithEmbeddedAssets), '<html') !== 0) {
    $finalHtml = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Export PDF</title></head><body>' . $htmlWithEmbeddedAssets . '</body></html>';
} else {
    $finalHtml = $htmlWithEmbeddedAssets;
}

// --- Génération du PDF ---
$outputPath = sys_get_temp_dir() . '/export_' . uniqid() . '.pdf';

try {
    Browsershot::html($finalHtml)
        ->margins(10, 10, 10, 10, 'mm') // Marges par défaut (1cm), peut être surchargé par @page dans le CSS
        ->format('A4')
        ->showBackground()
        ->timeout(120)
        ->addChromiumArguments([
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--font-render-hinting=none' // Améliore la netteté des polices
        ])
        ->save($outputPath);

    // --- Envoi du fichier au client ---
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="document.pdf"');
    header('Content-Length: ' . filesize($outputPath));

    readfile($outputPath);

    // Nettoyage du fichier temporaire
    unlink($outputPath);
    exit;

} catch (\Throwable $e) {
    http_response_code(500);
    // Afficher une erreur claire en cas de problème
    die('Erreur lors de la génération du PDF : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>