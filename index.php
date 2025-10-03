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
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Deck Generator ✨</title>

  <!-- Les polices et icônes sont maintenant chargées via ces fichiers CSS locaux -->
  <link rel="stylesheet" href="assets/fonts.css" />
  <link rel="stylesheet" href="assets/all.min.css" />

  <!-- Styles principaux de l'application -->
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
    input[type="file"], textarea {
      width: 100%; padding: 0.75rem; background-color: var(--color-bg); border: 1px solid var(--color-border);
      border-radius: 6px; color: var(--color-text); font-family: inherit; font-size: 1rem; transition: border-color 0.2s, box-shadow 0.2s;
    }
    input[type="file"] { padding: 0.5rem; }
    textarea:focus, input:focus { outline: none; border-color: var(--color-accent); box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); }
    .code-editor { font-family: monospace; font-size: 12px; min-height: 200px; resize: vertical; }
    button, .button { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background-color 0.2s, transform 0.1s; }
    button:active { transform: translateY(1px); }
    .button-primary { background-color: var(--color-accent); color: white; }
    .button-primary:hover { background-color: var(--color-accent-hover); }
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
  </style>
</head>
<body>
  <div class="container">
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

    <main class="main-content">
      <div class="preview-controls">
        <h2>3. Export PDF Professionnel</h2>
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
          <button id="export-duplex-pdf" class="button-primary">
            <i class="fa-solid fa-file-pdf"></i> Exporter le PDF
          </button>
        </div>
      </div>
      <div id="preview-area" class="preview-area"></div>
    </main>
  </div>

  <script src="lib/papaparse.min.js"></script>
  <script src="lib/handlebars.min.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const csvFileInput     = document.getElementById('csv-file-input');
      const csvDataTextarea  = document.getElementById('csv-data');
      const cardHtmlTextarea = document.getElementById('card-html');
      const cardCssTextarea  = document.getElementById('card-css');
      const backHtmlTextarea = document.getElementById('back-html');
      const previewButton    = document.getElementById('preview-button');
      const previewArea      = document.getElementById('preview-area');
      const viewToggle       = document.getElementById('view-toggle');
      const exportDuplexButton = document.getElementById('export-duplex-pdf');

      let parsedData = [];
      exportDuplexButton.disabled = true;

      function parseCSV(csvString) {
        Papa.parse(csvString, {
          header: true, skipEmptyLines: true,
          complete: (results) => {
            if (results.errors && results.errors.length > 0) {
              console.error("Erreurs de parsing CSV:", results.errors);
              alert("Le fichier CSV contient des erreurs."); return;
            }
            parsedData = results.data || [];
            if (parsedData.length > 0) {
              generatePreview();
              exportDuplexButton.disabled = false;
            } else {
              previewArea.innerHTML = '<p style="color: var(--color-text-muted); text-align:center;">Aucune donnée valide trouvée.</p>';
              exportDuplexButton.disabled = true;
            }
          }
        });
      }

      function generatePreview() {
        if (!parsedData.length) {
          previewArea.innerHTML = '<p style="color: var(--color-text-muted); text-align:center;">Chargez des données CSV pour commencer.</p>'; return;
        }
        const cardHtmlTemplate = cardHtmlTextarea.value;
        const cardCss = cardCssTextarea.value;
        const backHtmlTemplate = backHtmlTextarea.value;

        try {
          const compiledCardTemplate = Handlebars.compile(cardHtmlTemplate);
          const compiledBackTemplate = Handlebars.compile(backHtmlTemplate);
          let allCardsHtml = '';
          parsedData.forEach(cardData => {
            allCardsHtml += `
              <div class="card-container-wrapper">
                <div class="card-container">
                  <div class="card-face card-front">${compiledCardTemplate(cardData)}</div>
                  <div class="card-face card-back">${compiledBackTemplate(cardData)}</div>
                </div>
              </div>`;
          });

          if (!previewArea.shadowRoot) previewArea.attachShadow({ mode: 'open' });
          const shadow = previewArea.shadowRoot;
          shadow.innerHTML = `
            <style>
              @import url('assets/fonts.css');
              @import url('assets/all.min.css');
              .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
              .card-container-wrapper { perspective: 1500px; }
              .card-container { width: 100%; aspect-ratio: 63.5 / 88.9; position: relative; }
              .card-face { position: absolute; inset: 0; }
              .grid-container[data-view="recto"] .card-back { display: none; }
              .grid-container[data-view="verso"] .card-front { display: none; }
              ${cardCss}
            </style>
            <div class="grid-container" data-view="recto">${allCardsHtml}</div>
          `;
          exportDuplexButton.disabled = false;
        } catch (error) {
          console.error("Erreur Handlebars:", error);
          previewArea.innerHTML = `<p style="color: #ff6b6b; font-family: monospace;">Erreur dans le template HTML: ${error.message}</p>`;
        }
      }

      csvFileInput.addEventListener('change', e => {
        if (e.target.files[0]) {
          const reader = new FileReader();
          reader.onload = e => { csvDataTextarea.value = e.target.result; parseCSV(e.target.result); };
          reader.readAsText(e.target.files[0]);
        }
      });
      csvDataTextarea.addEventListener('input', () => parseCSV(csvDataTextarea.value));
      previewButton.addEventListener('click', generatePreview);
      viewToggle.addEventListener('change', () => {
        const grid = previewArea.shadowRoot?.querySelector('.grid-container');
        if (grid) grid.setAttribute('data-view', viewToggle.checked ? 'verso' : 'recto');
      });

      exportDuplexButton.addEventListener('click', async () => {
        if (!parsedData || parsedData.length === 0) {
          alert("Veuillez charger des données CSV avant d'exporter."); return;
        }
        const thisButton = exportDuplexButton;
        thisButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Préparation...';
        thisButton.disabled = true;

        try {
          const [printCssRes, faCssRes, fontsCssRes] = await Promise.all([
            fetch('assets/print-styles.css'),
            fetch('assets/all.min.css'),
            fetch('assets/fonts.css')
          ]);

          const payload = {
            rows: parsedData,
            tpl_front: cardHtmlTextarea.value,
            tpl_back: backHtmlTextarea.value,
            css_card: cardCssTextarea.value,
            css_print: await printCssRes.text(),
            css_fa: await faCssRes.text(),
            css_fonts: await fontsCssRes.text(),
            cols: 3, rows_per_page: 3 
          };

          thisButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Génération PDF...';

          const response = await fetch('export.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'payload=' + encodeURIComponent(JSON.stringify(payload))
          });

          if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Erreur du serveur (${response.status}):\n${errorText}`);
          }
          
          const blob = await response.blob();
          if (blob.type !== 'application/pdf' || blob.size < 100) {
             const errorText = await blob.text();
             throw new Error(`La réponse n'est pas un PDF valide. Contenu:\n${errorText}`);
          }

          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = 'planches_de_cartes.pdf';
          document.body.appendChild(a);
          a.click();
          a.remove();
          window.URL.revokeObjectURL(url);

        } catch (error) {
          console.error("Erreur lors de l'export PDF:", error);
          alert("Une erreur est survenue. Consultez la console (F12) pour les détails techniques.");
        } finally {
          thisButton.innerHTML = '<i class="fa-solid fa-file-pdf"></i> Exporter le PDF';
          thisButton.disabled = false;
        }
      });
    });

    function openTab(evt, tabName) {
      const tabcontent = document.getElementsByClassName("tab-content");
      [...tabcontent].forEach(tc => tc.style.display = "none");
      const tablinks = document.getElementsByClassName("tab-link");
      [...tablinks].forEach(tl => tl.className = tl.className.replace(" active", ""));
      document.getElementById(tabName).style.display = "block";
      evt.currentTarget.className += " active";
    }
  </script>
</body>
</html>

