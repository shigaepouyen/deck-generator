<?php
// On charge les templates par défaut au chargement de la page.
require_once __DIR__ . '/lib/php-utils.php';
$card_html_template = file_get_contents(__DIR__ . '/assets/default-card.html');
$card_css_template  = file_get_contents(__DIR__ . '/assets/default-card.css');
$back_html_template = file_get_contents(__DIR__ . '/assets/back-default.html');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Lato:wght@400;700&family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Deck Generator ✨</title>

  <!-- Icônes et polices locales -->
  <link rel="stylesheet" href="assets/all.min.css" />
  <link rel="stylesheet" href="assets/fonts.css" />

  <!-- Styles principaux de l'application - inchangés -->
  <style>
    :root {
      --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      --color-bg: #1e1e1e;
      --color-surface: #2d2d2d;
      --color-border: #4a4a4a;
      --color-text: #e0e0e0;
      --color-text-muted: #9e9e9e;
      --color-accent: #3b82f6;
      --color-accent-hover: #2563eb;
      --sidebar-width: 450px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: var(--font-sans); background-color: var(--color-bg); color: var(--color-text); font-size: 14px; overflow: hidden; }
    .container { display: flex; height: 100vh; }

    .sidebar { width: var(--sidebar-width); background-color: var(--color-surface); border-right: 1px solid var(--color-border); padding: 1.5rem; display: flex; flex-direction: column; overflow-y: auto; }
    .sidebar-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--color-border); }
    .logo { height: 40px; width: 40px; }
    .sidebar-header h1 { font-size: 1.5rem; font-weight: 800; }
    .control-section { margin-bottom: 2rem; }
    .control-section h2 { font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
    .control-group { margin-bottom: 1rem; }
    .control-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }

    input[type="file"], textarea, select, input[type="number"], input[type="text"] {
      width: 100%; padding: 0.75rem; background-color: var(--color-bg); border: 1px solid var(--color-border);
      border-radius: 6px; color: var(--color-text); font-family: inherit; font-size: 1rem; transition: border-color 0.2s, box-shadow 0.2s;
    }
    input[type="file"] { padding: 0.5rem; }
    textarea:focus, select:focus, input:focus { outline: none; border-color: var(--color-accent); box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); }
    .code-editor { font-family: monospace; font-size: 12px; min-height: 200px; resize: vertical; }

    button, .button { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background-color 0.2s, transform 0.1s; }
    button:active { transform: translateY(1px); }
    .button-primary { background-color: var(--color-accent); color: white; }
    .button-primary:hover { background-color: var(--color-accent-hover); }
    .button-secondary { background-color: var(--color-surface); color: var(--color-text); border: 1px solid var(--color-border); }
    .button-secondary:hover { background-color: #3a3a3a; }
    button:disabled { cursor: not-allowed; opacity: 0.6; }
    .button-group { display: flex; gap: 0.75rem; }

    .tabs { display: flex; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border); }
    .tab-link { padding: 0.75rem 1rem; cursor: pointer; border: none; background-color: transparent; color: var(--color-text-muted); border-bottom: 2px solid transparent; }
    .tab-link.active { color: var(--color-accent); border-bottom-color: var(--color-accent); }
    .tab-content { display: none; }

    .main-content { flex-grow: 1; display: flex; flex-direction: column; height: 100vh; }
    .preview-controls { padding: 1rem 1.5rem; background-color: var(--color-surface); border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; gap: 1.5rem; }
    .preview-controls h2 { font-size: 1.25rem; font-weight: 700; }
    .preview-area { flex-grow: 1; padding: 2rem; overflow: auto;
      background: linear-gradient(45deg, rgba(255,255,255,0.05) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.05) 75%),
                  linear-gradient(45deg, rgba(255,255,255,0.05) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.05) 75%) #1e1e1e;
      background-size: 20px 20px; background-position: 0 0, 10px 10px;
    }

    .view-controls { display: flex; align-items: center; gap: 1rem; }
    .toggle-switch { display: flex; align-items: center; gap: 0.75rem; font-weight: 600; }
    .switch { position: relative; display: inline-block; width: 50px; height: 28px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--color-surface); border: 1px solid var(--color-border); transition: .4s; border-radius: 28px; }
    .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: var(--color-accent); }
    input:checked + .slider:before { transform: translateX(22px); }

    .export-help-text { font-size: 12px; color: var(--color-text-muted); padding: 0 1.5rem 1rem; background-color: var(--color-surface); border-bottom: 1px solid var(--color-border); }
  </style>
</head>
<body>

  <div class="container">
    <!-- BARRE LATERALE AVEC LES CONTROLES -->
    <aside class="sidebar">
      <header class="sidebar-header">
        <img src="assets/logo.svg" alt="Deck Generator Logo" class="logo" />
        <h1>Deck Generator</h1>
      </header>

      <div class="control-section">
        <h2>1. Données (CSV)</h2>
        <div class="control-group">
          <label for="csv-file-input">Charger un fichier .csv</label>
          <input type="file" id="csv-file-input" accept=".csv" />
        </div>
        <div class="control-group">
          <label for="csv-data">Ou coller les données ici</label>
          <textarea id="csv-data" rows="6" placeholder="id,category,client..."></textarea>
        </div>
      </div>

      <div class="control-section">
        <h2>2. Templates</h2>
        <div class="tabs">
          <button class="tab-link active" onclick="openTab(event, 'tab-html')">Recto HTML</button>
          <button class="tab-link" onclick="openTab(event, 'tab-css')">CSS</button>
          <button class="tab-link" onclick="openTab(event, 'tab-back-html')">Verso HTML</button>
        </div>

        <div id="tab-html" class="tab-content" style="display: block;">
          <textarea id="card-html" class="code-editor"><?php echo htmlspecialchars($card_html_template); ?></textarea>
        </div>
        <div id="tab-css" class="tab-content">
          <textarea id="card-css" class="code-editor"><?php echo htmlspecialchars($card_css_template); ?></textarea>
        </div>
        <div id="tab-back-html" class="tab-content">
          <textarea id="back-html" class="code-editor"><?php echo htmlspecialchars($back_html_template); ?></textarea>
        </div>
      </div>

      <button id="preview-button" class="button-primary" style="width:100%; margin-top: auto;">
        <i class="fa-solid fa-sync"></i> Mettre à jour la prévisualisation
      </button>
    </aside>

    <!-- CONTENU PRINCIPAL AVEC LA PREVISUALISATION -->
    <main class="main-content">
      <div class="preview-controls">
        <h2>3. Imposition & Export</h2>
        <div class="view-controls">
          <div class="toggle-switch">
            <label for="view-toggle">Recto</label>
            <label class="switch">
              <input type="checkbox" id="view-toggle" />
              <span class="slider"></span>
            </label>
            <label for="view-toggle">Verso</label>
          </div>
        </div>
        <div class="button-group">
          <button type="submit" form="print-form" class="button-secondary">
            <i class="fa-solid fa-print"></i> Imprimer
          </button>
          <button id="export-pdf-client" class="button-secondary">
            <i class="fa-solid fa-file-pdf"></i> Export Simple
          </button>
          <button id="export-duplex-pdf" class="button-primary">
            <i class="fa-solid fa-right-left"></i> Exporter Recto/Verso (PDF)
          </button>
        </div>
      </div>
      <p class="export-help-text">Pour imprimer en recto-verso, exportez les rectos, puis les versos, et imprimez-les sur les deux faces de vos feuilles.</p>

      <!-- Formulaire caché pour l'impression -->
      <form id="print-form" action="print.php" method="post" target="_blank" style="display: none;">
        <input type="hidden" name="cards" id="form-cards-html" />
        <input type="hidden" name="card_css" id="form-card-css" />
        <input type="hidden" name="print_css" id="form-print-css" />
      </form>

      <div id="preview-area" class="preview-area"></div>
    </main>
  </div>

  <!-- Librairies JS -->
  <script src="lib/papaparse.min.js"></script>
  <script src="lib/handlebars.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

  <!-- Script principal de l'application -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
    // --- REFERENCES AUX ELEMENTS DU DOM ---
    const csvFileInput     = document.getElementById('csv-file-input');
    const csvDataTextarea  = document.getElementById('csv-data');
    const cardHtmlTextarea = document.getElementById('card-html');
    const cardCssTextarea  = document.getElementById('card-css');
    const backHtmlTextarea = document.getElementById('back-html');
    const previewButton    = document.getElementById('preview-button');
    const previewArea      = document.getElementById('preview-area');
    const viewToggle       = document.getElementById('view-toggle'); // On a de nouveau besoin du toggle
    const exportDuplexButton = document.getElementById('export-duplex-pdf');

    let parsedData = [];

    // --- Font Awesome dans le Shadow DOM (CHEMIN CORRIGÉ) ---
    let FA_CSS_TEXT = '';
    fetch('assets/all.min.css')
        .then(r => r.ok ? r.text() : Promise.reject('all.min.css introuvable'))
        .then(css => {
            // Correction pour s'assurer que le chemin est toujours relatif à la racine
            FA_CSS_TEXT = css.replace(/url\((['"])?(\.\.\/)?webfonts\//g, "url($1assets/webfonts/");
        })
        .catch(err => console.error('Font Awesome non injecté dans le shadow:', err));

    // --- FONCTIONS PRINCIPALES ---
    function parseCSV(csvString) {
        Papa.parse(csvString, {
            header: true, skipEmptyLines: true,
            complete: (results) => {
                if (results.errors && results.errors.length > 0) {
                    console.error("Erreurs de parsing CSV:", results.errors);
                    alert("Le fichier CSV contient des erreurs.");
                }
                parsedData = results.data || [];
                generatePreview();
            }
        });
    }

    function generatePreview() {
        if (!parsedData.length) {
            previewArea.innerHTML = '<p style="color: var(--color-text-muted); text-align:center;">Chargez des données CSV pour commencer.</p>';
            return;
        }

        const cardHtmlTemplate = cardHtmlTextarea.value;
        const cardCss          = cardCssTextarea.value;
        const backHtmlTemplate = backHtmlTextarea.value;

        try {
            const compiledCardTemplate = Handlebars.compile(cardHtmlTemplate);
            const compiledBackTemplate = Handlebars.compile(backHtmlTemplate);

            let allCardsHtml = '';
            parsedData.forEach(cardData => {
                const cardContent = compiledCardTemplate(cardData);
                const backContent = compiledBackTemplate(cardData);
                allCardsHtml += `
                    <div class="card-container-wrapper">
                        <div class="card-container">
                            <div class="card-face card-front">${cardContent}</div>
                            <div class="card-face card-back">${backContent}</div>
                        </div>
                    </div>`;
            });

            if (!previewArea.shadowRoot) {
                previewArea.attachShadow({ mode: 'open' });
            }

            const shadow = previewArea.shadowRoot;
            // -- CSS CORRIGÉ POUR LA PRÉVISUALISATION --
            shadow.innerHTML = `
                <style>
                    ${FA_CSS_TEXT}
                    .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
                    .card-container-wrapper { perspective: 1500px; }
                    .card-container { width: 100%; aspect-ratio: 2.5 / 3.5; position: relative; }
                    .card-face { 
                        position: absolute; 
                        inset: 0; 
                        display: flex; 
                        flex-direction: column; 
                        overflow: hidden; 
                        -webkit-backface-visibility: hidden; /* Pour Safari */
                        backface-visibility: hidden;
                    }

                    /* On cache le verso par défaut */
                    .card-back { display: none; }
                    
                    /* Logique pour afficher recto ou verso via un attribut */
                    .grid-container[data-view="recto"] .card-back { display: none !important; }
                    .grid-container[data-view="recto"] .card-front { display: flex !important; }
                    .grid-container[data-view="verso"] .card-front { display: none !important; }
                    .grid-container[data-view="verso"] .card-back { display: flex !important; }
                    
                    ${cardCss}
                </style>
                <div class="grid-container" data-view="recto">${allCardsHtml}</div>`;

        } catch (error) {
            console.error("Erreur Handlebars:", error);
            previewArea.innerHTML = `<p>Erreur: ${error.message}</p>`;
        }
    }

    // --- EVENEMENTS ---
    csvFileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                csvDataTextarea.value = e.target.result;
                parseCSV(e.target.result);
            };
            reader.readAsText(file);
        }
    });

    csvDataTextarea.addEventListener('input', () => parseCSV(csvDataTextarea.value));
    previewButton.addEventListener('click', generatePreview);

    // On réactive le toggle pour changer la vue dans la prévisualisation
    viewToggle.addEventListener('change', () => {
        const grid = previewArea.shadowRoot?.querySelector('.grid-container');
        if (grid) {
            grid.setAttribute('data-view', viewToggle.checked ? 'verso' : 'recto');
        }
    });

    // --- NOUVEL EXPORT RECTO-VERSO ---
    exportDuplexButton.addEventListener('click', async () => {
        const shadow = previewArea.shadowRoot;
        if (!shadow || !shadow.querySelector('.card-container-wrapper')) {
            alert("Veuillez d'abord générer une prévisualisation.");
            return;
        }
        
        console.log("Lancement de l'export Recto/Verso...");

        const thisButton = exportDuplexButton;
        thisButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Capture...';
        thisButton.disabled = true;

        const allCardsNodes = Array.from(shadow.querySelectorAll('.card-container-wrapper'));
        const frontImages = [];
        const backImages = [];

        try {
            // --- PHASE 1: CAPTURE DE TOUTES LES IMAGES ---
            // On s'assure que les faces sont bien affichées/masquées pendant la capture
            const grid = shadow.querySelector('.grid-container');
            const originalView = grid.getAttribute('data-view');
            
            let count = 0;
            for (const cardNode of allCardsNodes) {
                count++;
                thisButton.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Capture ${count}/${allCardsNodes.length}`;
                
                const frontFace = cardNode.querySelector('.card-front');
                const backFace = cardNode.querySelector('.card-back');

                // Capture Recto
                frontFace.style.display = 'flex';
                backFace.style.display = 'none';
                const frontCanvas = await html2canvas(cardNode, { scale: 4, useCORS: true, backgroundColor: '#ffffff' });
                frontImages.push(frontCanvas.toDataURL('image/png', 0.95));

                // Capture Verso
                frontFace.style.display = 'none';
                backFace.style.display = 'flex';
                const backCanvas = await html2canvas(cardNode, { scale: 4, useCORS: true, backgroundColor: '#ffffff' });
                backImages.push(backCanvas.toDataURL('image/png', 0.95));

                // On remet l'affichage par défaut
                frontFace.style.display = '';
                backFace.style.display = '';
            }

            // On restaure la vue initiale de la grille
            grid.setAttribute('data-view', originalView);
            
            console.log(`Capture terminée. ${frontImages.length} rectos et ${backImages.length} versos capturés.`);

            // --- PHASE 2: ASSEMBLAGE DU PDF ---
            thisButton.innerHTML = '<i class="fa-solid fa-cog fa-spin"></i> Assemblage PDF...';
            // ... le reste de la phase 2 est identique et devrait fonctionner maintenant ...

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });

            const CARD_WIDTH_PT = 63.5 * 2.83465;
            const CARD_HEIGHT_PT = 88.9 * 2.83465;
            const CARDS_PER_ROW = 3;
            const CARDS_PER_COL = 3;
            const CARDS_PER_PAGE = CARDS_PER_ROW * CARDS_PER_COL;

            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            const MARGIN_X = (pdfWidth - (CARDS_PER_ROW * CARD_WIDTH_PT)) / 2;
            const MARGIN_Y = (pdfHeight - (CARDS_PER_COL * CARD_HEIGHT_PT)) / 2;

            let cardsProcessed = 0;
            while (cardsProcessed < allCardsNodes.length) {
                const pageChunkFronts = frontImages.slice(cardsProcessed, cardsProcessed + CARDS_PER_PAGE);
                const pageChunkBacks = backImages.slice(cardsProcessed, cardsProcessed + CARDS_PER_PAGE);

                if (cardsProcessed > 0) pdf.addPage();
                console.log(`Création de la page RECTO N°${(cardsProcessed / CARDS_PER_PAGE) + 1}`);
                pageChunkFronts.forEach((imgData, index) => {
                    const row = Math.floor(index / CARDS_PER_ROW);
                    const col = index % CARDS_PER_ROW;
                    const x = MARGIN_X + (col * CARD_WIDTH_PT);
                    const y = MARGIN_Y + (row * CARD_HEIGHT_PT);
                    pdf.addImage(imgData, 'PNG', x, y, CARD_WIDTH_PT, CARD_HEIGHT_PT);
                });
                
                if(pageChunkBacks.length > 0) {
                    pdf.addPage();
                    console.log(`Création de la page VERSO N°${(cardsProcessed / CARDS_PER_PAGE) + 1}`);
                    pageChunkBacks.forEach((imgData, index) => {
                        const row = Math.floor(index / CARDS_PER_ROW);
                        const col = index % CARDS_PER_ROW;
                        const mirroredCol = CARDS_PER_ROW - 1 - col;
                        const x = MARGIN_X + (mirroredCol * CARD_WIDTH_PT);
                        const y = MARGIN_Y + (row * CARD_HEIGHT_PT);
                        pdf.addImage(imgData, 'PNG', x, y, CARD_WIDTH_PT, CARD_HEIGHT_PT);
                    });
                }

                cardsProcessed += CARDS_PER_PAGE;
            }

            // --- TÉLÉCHARGEMENT ROBUSTE ---
            console.log("PDF assemblé. Lancement du téléchargement.");
            const pdfBlob = pdf.output('blob');
            const url = URL.createObjectURL(pdfBlob);
            const a = document.createElement('a');
            a.style.display = 'none'; a.href = url; a.download = 'deck-recto-verso.pdf';
            document.body.appendChild(a); a.click();
            URL.revokeObjectURL(url); document.body.removeChild(a);
            console.log("Téléchargement initié.");

        } catch (err) {
            console.error("ERREUR FATALE LORS DE L'EXPORT:", err);
            alert("Une erreur est survenue pendant l'export. Ouvrez la console (F12) pour voir les détails.");
        } finally {
            thisButton.innerHTML = '<i class="fa-solid fa-right-left"></i> Exporter Recto/Verso (PDF)';
            thisButton.disabled = false;
        }
    });

    // --- Fonctions utilitaires (Tabs) ---
    window.openTab = function(evt, tabName) {
        const tabcontent = document.getElementsByClassName("tab-content");
        for (let i = 0; i < tabcontent.length; i++) tabcontent[i].style.display = "none";
        const tablinks = document.getElementsByClassName("tab-link");
        for (let i = 0; i < tablinks.length; i++) tablinks[i].className = tablinks[i].className.replace(" active", "");
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    };
});
</script>
  </script>
</body>
</html>