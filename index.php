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
      const exportPdfButton  = document.getElementById('export-pdf-client');
      const viewToggle       = document.getElementById('view-toggle');

      const formCardsHtml = document.getElementById('form-cards-html');
      const formCardCss   = document.getElementById('form-card-css');
      const formPrintCss  = document.getElementById('form-print-css');

      const printForm = document.getElementById('print-form');
      const printButton = document.querySelector('button[form="print-form"]');

      let parsedData = [];
      let latestCardsHtml = '';

      printButton.disabled = true;

      // --- Font Awesome dans le Shadow DOM ---
      let FA_CSS_TEXT = '';
      fetch('assets/all.min.css')
        .then(r => r.ok ? r.text() : Promise.reject('all.min.css introuvable'))
        .then(css => {
          FA_CSS_TEXT = css
            .replace(/url\((['"])??\.\.\/webfonts\//g, "url($1assets/webfonts/")
            .replace(/url\((['"])??\.\/webfonts\//g,  "url($1assets/webfonts/");
        })
        .catch(err => console.error('Font Awesome non injecté dans le shadow:', err));

      // --- FONCTIONS PRINCIPALES ---
      function parseCSV(csvString) {
        Papa.parse(csvString, {
          header: true,
          skipEmptyLines: true,
          complete: (results) => {
            if (results.errors && results.errors.length > 0) {
              console.error("Erreurs de parsing CSV:", results.errors);
              alert("Le fichier CSV contient des erreurs. Vérifiez la console pour les détails.");
            }
            parsedData = results.data || [];
            generatePreview();
            printButton.disabled = parsedData.length === 0;
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
            let backContent   = compiledBackTemplate(cardData);
            // Retire le libellé "CSM Quest" s'il apparaît en texte dans le dos
            backContent = backContent.replace(/CSM\s*Quest/gi, '');

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
          shadow.innerHTML = `
            <style>
              /* Font Awesome injecté (webfonts locales) */
              ${FA_CSS_TEXT}

              /* Grille de prévisualisation */
              .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
              .card-container-wrapper { perspective: 1500px; }
              .card-container { width: 100%; aspect-ratio: 2.5 / 3.5; position: relative; border-radius: 12px; }
              .card-face { position: absolute; inset: 0; display: flex; flex-direction: column; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,.4); }

              /* Affichage recto/verso sans rotation: on montre l'une OU l'autre */
              .grid-container[data-view="recto"] .card-back { display: none !important; }
              .grid-container[data-view="verso"] .card-front { display: none !important; }

              /* CSS des cartes défini par l'utilisateur (default-card.css) */
              ${cardCss}

              /* Overrides lisibilité (après ${'${cardCss}'} ) */
              .card { font-family: 'Lato', Inter, Arial, sans-serif; }
              .card-client, .card .card-client { font-family: 'Roboto Slab', Georgia, serif; }
              .card-panel, .card-panel *:not(.card-malefice) { color:#111827 !important; -webkit-text-fill-color: currentColor; opacity:1 !important; filter:none !important; }
              .card-client, .card-panel h1, .card-panel h2, .card-panel h3, .card-title { color:#0f172a !important; }
              .card-malefice { color:#374151 !important; opacity:1 !important; }
              .card .fa, .card .fas, .card .far, .card .fal, .card .fab { color: currentColor !important; }
            </style>
            <div class="grid-container" data-view="recto">${allCardsHtml}</div>
          `;

          latestCardsHtml = allCardsHtml;                // on mémorise le dernier HTML
          updateFormFields(latestCardsHtml, cardCss);    // on met à jour les champs du form
          printButton.disabled = false;                  // on autorise l'impression
        } catch (error) {
          console.error("Erreur de compilation Handlebars:", error);
          previewArea.innerHTML = `<p style="color: #ff6b6b; font-family: monospace;">Erreur dans le template HTML: ${error.message}</p>`;
        }
      }

      function updateFormFields(cardsHtml, cardCss) {
        formCardsHtml.value = cardsHtml;
        formCardCss.value   = cardCss;
        formPrintCss.value  = '';
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

      viewToggle.addEventListener('change', () => {
        const grid = previewArea.shadowRoot?.querySelector('.grid-container');
        const isVerso = viewToggle.checked;
        if (grid) {
          grid.setAttribute('data-view', isVerso ? 'verso' : 'recto');
        }
        exportPdfButton.innerHTML = `<i class="fa-solid fa-file-pdf"></i> Exporter ${isVerso ? 'Versos' : 'Rectos'} (PDF)`;
      });

      printForm.addEventListener('submit', function onSubmit(e) {
        // Si pas de données, on bloque
        if (!parsedData.length) {
          e.preventDefault();
          alert("Charge un CSV ou colle des données avant d’imprimer.");
          return;
        }

        // Si le tampon est vide, on régénère une preview propre
        if (!latestCardsHtml || !latestCardsHtml.trim()) {
          e.preventDefault();
          generatePreview(); // alimente latestCardsHtml + champs du form
          // On renvoie le formulaire une fois les champs remplis
          setTimeout(() => printForm.submit(), 0);
          return;
        }

        // Par sécurité, on (re)remplit juste avant l’envoi
        formCardsHtml.value = latestCardsHtml;
        formCardCss.value   = cardCssTextarea.value || '';
        formPrintCss.value  = ''; // si tu n'utilises pas de CSS d'impression spécifique
      });

      exportPdfButton.addEventListener('click', async () => {
          const shadow = previewArea.shadowRoot;
          if (!shadow || !shadow.querySelector('.card-container-wrapper')) {
              alert("Veuillez d'abord générer une prévisualisation.");
              return;
          }

          const thisButton = exportPdfButton;
          thisButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Génération...';
          thisButton.disabled = true;

          const isVerso = viewToggle.checked;
          const faceSelector = isVerso ? '.card-back' : '.card-front';
          const fileName = isVerso ? 'deck-versos.pdf' : 'deck-rectos.pdf';

          const allCards = Array.from(shadow.querySelectorAll('.card-container-wrapper'));

          try {
              const { jsPDF } = window.jspdf;
              const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
              const pdfWidth = pdf.internal.pageSize.getWidth();
              const pdfHeight = pdf.internal.pageSize.getHeight();

              const CARD_WIDTH_PT = 63.5 * 2.83465;
              const CARD_HEIGHT_PT = 88.9 * 2.83465;
              const CARDS_PER_ROW = 3;
              const CARDS_PER_COL = 3;
              const HORIZONTAL_SPACING_PT = (pdfWidth - (CARDS_PER_ROW * CARD_WIDTH_PT)) / (CARDS_PER_ROW + 1); // Espace entre les cartes
              const VERTICAL_SPACING_PT = (pdfHeight - (CARDS_PER_COL * CARD_HEIGHT_PT)) / (CARDS_PER_COL + 1); // Espace entre les cartes
              const MARGIN_X = HORIZONTAL_SPACING_PT; // Marge latérale
              const MARGIN_Y = VERTICAL_SPACING_PT;   // Marge verticale
              
              let cardCount = 0;

              const drawCropMarks = (pdf, x, y, width, height) => {
                  const L = 10; // Longueur des traits
                  const M = 2;  // Marge entre le trait et la carte
                  pdf.setLineWidth(0.3); // Traits plus fins
                  pdf.setDrawColor(0); // Noir
                  
                  // Haut-gauche
                  pdf.line(x - M, y, x - M - L, y);
                  pdf.line(x, y - M, x, y - M - L);
                  // Haut-droit
                  pdf.line(x + width + M, y, x + width + M + L, y);
                  pdf.line(x + width, y - M, x + width, y - M - L);
                  // Bas-gauche
                  pdf.line(x - M, y + height, x - M - L, y + height);
                  pdf.line(x, y + height + M, x, y + height + M + L);
                  // Bas-droit
                  pdf.line(x + width + M, y + height, x + width + M + L, y + height);
                  pdf.line(x + width, y + height + M, x + width, y + height + M + L);
              };

              // On masque toutes les cartes pour n'afficher que celle qu'on veut capturer
              // C'est une astuce pour html2canvas pour éviter les interactions d'ombres ou de débordements.
              // On stocke les styles originaux pour les restaurer après.
              const originalDisplays = allCards.map(card => {
                  const display = card.style.display;
                  card.style.display = 'none'; // Temporairement masquer toutes les cartes
                  return display;
              });


              for (const cardWrapper of allCards) {
                  // Restaurer temporairement la carte actuelle pour la capture
                  const cardOriginalDisplay = originalDisplays[cardCount];
                  cardWrapper.style.display = cardOriginalDisplay; // Rendre visible uniquement la carte actuelle

                  const pageIndex = Math.floor(cardCount / (CARDS_PER_ROW * CARDS_PER_COL));
                  if (cardCount > 0 && cardCount % (CARDS_PER_ROW * CARDS_PER_COL) === 0) {
                      pdf.addPage();
                  }

                  const cardFaceNode = cardWrapper.querySelector(faceSelector);

                  // Important: On s'assure que la face visible est bien celle à capturer
                  // et que l'autre est masquée pour éviter des interférences de rendu.
                  const otherFaceSelector = isVerso ? '.card-front' : '.card-back';
                  const otherFaceNode = cardWrapper.querySelector(otherFaceSelector);

                  const originalFaceDisplay = cardFaceNode.style.display;
                  const originalOtherFaceDisplay = otherFaceNode ? otherFaceNode.style.display : '';

                  cardFaceNode.style.display = 'flex'; // S'assurer que la face à capturer est visible
                  if (otherFaceNode) otherFaceNode.style.display = 'none'; // S'assurer que l'autre face est masquée

                  const canvas = await html2canvas(cardFaceNode, {
                      scale: 4, // Augmenté à 4 pour une meilleure qualité (4 * 96 DPI = 384 DPI)
                      useCORS: true,
                      backgroundColor: '#ffffff' // Forcer un arrière-plan blanc explicite pour la capture
                  });
                  
                  // Restaurer les styles d'affichage après la capture
                  cardFaceNode.style.display = originalFaceDisplay;
                  if (otherFaceNode) otherFaceNode.style.display = originalOtherFaceDisplay;
                  cardWrapper.style.display = 'none'; // Masquer à nouveau la carte après traitement


                  const imgData = canvas.toDataURL('image/png');

                  const cardIndexOnPage = cardCount % (CARDS_PER_ROW * CARDS_PER_COL);
                  const row = Math.floor(cardIndexOnPage / CARDS_PER_ROW);
                  const col = cardIndexOnPage % CARDS_PER_ROW;

                  // Calcul des positions avec espacement
                  const x = MARGIN_X + (col * (CARD_WIDTH_PT + HORIZONTAL_SPACING_PT));
                  const y = MARGIN_Y + (row * (CARD_HEIGHT_PT + VERTICAL_SPACING_PT));

                  pdf.addImage(imgData, 'PNG', x, y, CARD_WIDTH_PT, CARD_HEIGHT_PT);
                  drawCropMarks(pdf, x, y, CARD_WIDTH_PT, CARD_HEIGHT_PT);

                  cardCount++;
              }

              // Restaurer l'affichage initial de toutes les cartes après l'export
              allCards.forEach((card, index) => card.style.display = originalDisplays[index]);
              shadow.querySelector('.grid-container').setAttribute('data-view', isVerso ? 'verso' : 'recto');

              // --- NOUVELLE MÉTHODE DE TÉLÉCHARGEMENT ---
              // Au lieu de pdf.save(fileName), qui peut être bloqué :
              console.log("Génération du PDF terminée. Préparation du téléchargement...");

              // 1. On génère le PDF sous forme de "Blob" (un objet binaire)
              const pdfBlob = pdf.output('blob');

              // 2. On crée une URL locale temporaire pour ce Blob
              const url = URL.createObjectURL(pdfBlob);

              // 3. On crée un élément de lien <a> invisible
              const a = document.createElement('a');
              a.style.display = 'none';
              a.href = url;
              a.download = fileName; // On lui donne le nom du fichier

              // 4. On l'ajoute au document (nécessaire pour certains navigateurs comme Firefox)
              document.body.appendChild(a);

              // 5. On simule un clic dessus pour lancer le téléchargement
              a.click();

              // 6. On nettoie en supprimant l'URL et l'élément du document
              URL.revokeObjectURL(url);
              document.body.removeChild(a);

              console.log("Téléchargement initié.");


          } catch (err) {
              console.error("Erreur lors de la génération du PDF:", err);
              alert("Une erreur est survenue pendant la génération du PDF. Vérifiez la console (F12) pour les détails.");
          } finally {
              thisButton.innerHTML = `<i class="fa-solid fa-file-pdf"></i> Exporter ${isVerso ? 'Versos' : 'Rectos'} (PDF)`;
              thisButton.disabled = false;
          }
      });
    });

    // Tabs
    function openTab(evt, tabName) {
      const tabcontent = document.getElementsByClassName("tab-content");
      for (let i = 0; i < tabcontent.length; i++) tabcontent[i].style.display = "none";
      const tablinks = document.getElementsByClassName("tab-link");
      for (let i = 0; i < tablinks.length; i++) tablinks[i].className = tablinks[i].className.replace(" active", "");
      document.getElementById(tabName).style.display = "block";
      evt.currentTarget.className += " active";
    }
  </script>
</body>
</html>