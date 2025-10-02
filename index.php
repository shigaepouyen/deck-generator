<?php /* index.php */ ?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Deck Generator</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="assets/all.min.css">
<style>
  /* --- Variables de couleurs pour une customisation facile --- */
  :root {
    --bg-main: #f0f2f5;
    --bg-panel: #ffffff;
    --text-primary: #1d2d35;
    --text-secondary: #5a6b74;
    --border-color: #dbe1e6;
    --accent-color: #2563eb;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  }

  body {
    font-family: 'Inter', Arial, sans-serif;
    margin: 0;
    padding: 32px;
    background-color: var(--bg-main);
    color: var(--text-primary);
  }

  h1 {
    font-size: 2.25rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin-bottom: 24px;
    text-align: center;
  }

  h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 16px 0;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border-color);
  }

  label {
    display: block;
    font-weight: 500;
    font-size: 0.875rem;
    margin-bottom: 6px;
    color: var(--text-secondary);
  }

  /* --- Mise en page principale --- */
  .grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
  }

  /* --- Panneaux "Cartes" pour grouper les éléments --- */
  .grid > div,
  .controls {
    background-color: var(--bg-panel);
    border-radius: 12px;
    padding: 24px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
  }
  
  .controls {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 16px;
    align-items: end;
  }

  /* --- Style des champs de formulaire --- */
  textarea,
  input,
  select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background-color: #f8fafc;
    font-size: 0.9rem;
    transition: all 0.2s ease-in-out;
  }
  
  textarea:focus,
  input:focus,
  select:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.2);
    background-color: var(--bg-panel);
  }

  textarea {
    min-height: 160px;
    font-family: 'ui-monospace', monospace;
    line-height: 1.5;
    resize: vertical;
  }

  input[type="file"] {
    background-color: var(--bg-panel);
    padding: 0;
  }
  
  input[type="file"]::file-selector-button {
    background-color: #f1f5f9;
    color: var(--text-primary);
    border: none;
    border-right: 1px solid var(--border-color);
    padding: 10px 16px;
    margin-right: 12px;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  input[type="file"]::file-selector-button:hover {
    background-color: #e2e8f0;
  }

  button {
    padding: 10px 16px;
    border: none;
    background-color: var(--accent-color);
    color: #fff;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.2s ease-in-out;
    box-shadow: var(--shadow-sm);
  }

  button:hover {
    background-color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
  }

  button#btnPreview {
    background-color: #94a3b8;
  }
  
  button#btnPreview:hover {
    background-color: #64748b;
  }
  
  /* --- Zone de prévisualisation --- */
  .preview {
    margin-top: 24px;
    border: 1px solid var(--border-color);
    background-color: var(--bg-panel);
    padding: 20mm;
    border-radius: 12px;
  }
  .sheet {
    display: grid;
    gap: var(--gutter, 3mm);
    margin-bottom: 20mm;
  }

</style>
<script src="lib/handlebars.min.js"></script>
<script src="lib/papaparse.min.js"></script>
</head><body>
<h1>Deck Generator</h1>
<div class="grid">
  <div><h3>1. Données (CSV)</h3><input id="csvfile" type="file" accept=".csv"><textarea id="csvtext" placeholder="…ou collez ici le contenu du CSV (avec en-têtes)"></textarea><div id="csv-error" style="color:red;font-size:12px;margin-top:4px;"></div></div>
  <div><h3>2. Templates</h3><label>Template HTML Recto</label><textarea id="tpl-front"></textarea><label>Template HTML Verso</label><textarea id="tpl-back"></textarea><label>CSS</label><textarea id="css"></textarea></div>
</div>
<h3>3. Imposition & Export</h3>
<form id="print-form" action="print.php" method="post" target="_blank"></form>
<div class="controls">
  <label>Format <select id="paper"><option value="A4">A4</option><option value="Letter">Letter</option></select></label>
  <label>Orientation <select id="orient"><option value="portrait">Portrait</option><option value="landscape">Paysage</option></select></label>
  <label>Colonnes <input id="cols" type="number" min="1" value="3"></label>
  <label>Lignes <input id="rows" type="number" min="1" value="3"></label>
  <label>Fond perdu (mm) <input id="bleed" type="number" value="3" step="0.5"></label>
  <label>Gouttière (mm) <input id="gutter" type="number" value="3" step="0.5"></label>
  <label>Flip (verso) <select id="flip"><option value="long-edge">Long edge</option><option value="short-edge">Short edge</option></select></label>
  <label>Offset X (mm) <input id="offx" type="number" value="0" step="0.1"></label>
  <label>Offset Y (mm) <input id="offy" type="number" value="0" step="0.1"></label>
  <label>Contenu <select id="mode"><option value="fronts">Rectos seuls</option><option value="backs">Versos seuls</option><option value="duplex">Recto-Verso</option></select></label>
  <button id="btnPreview">Prévisualiser</button>
  <button id="btnPrint">Imprimer (navigateur)</button>
  <button id="btnPDF">Exporter PDF (serveur)</button>
</div>
<div id="preview" class="preview"></div>
<script>
const $ = s => document.querySelector(s);
const requiredCols = ['id', 'category', 'category_slug', 'client', 'body', 'icon', 'malefice', 'malefice_points'];
let rows = [];

async function loadDefaults() {
  const [front, back, css] = await Promise.all([
    fetch('assets/default-card.html').then(r => r.text()),
    fetch('assets/back-default.html').then(r => r.text()),
    fetch('assets/default-card.css').then(r => r.text())
  ]);
  $('#tpl-front').value = front;
  $('#tpl-back').value = back;
  $('#css').value = css;
}
loadDefaults();

function validateAndParse(csvString) {
  $('#csv-error').textContent = '';
  Papa.parse(csvString, {
    header: true, skipEmptyLines: true,
    complete: res => {
      if (!res.data.length) return;
      const headers = Object.keys(res.data[0]);
      const missing = requiredCols.filter(h => !headers.includes(h));
      if (missing.length > 0) {
        $('#csv-error').textContent = `Erreur: Colonnes manquantes : ${missing.join(', ')}`;
        rows = []; return;
      }
      rows = res.data.filter(r => r.id);
    }
  });
}

$('#csvfile').addEventListener('change', e => {
  const reader = new FileReader();
  reader.onload = event => validateAndParse(event.target.result);
  reader.readAsText(e.target.files[0]);
});
$('#csvtext').addEventListener('input', e => validateAndParse(e.target.value));

function renderPreview() {
  if (!rows.length) { $('#preview').innerHTML = '<p>Aucune donnée CSV valide à afficher.</p>'; return; }
  const cols = +$('#cols').value, rws = +$('#rows').value;
  const gutter = +$('#gutter').value;
  const hbs = Handlebars.compile($('#tpl-front').value);
  const style = `<style>${$('#css').value}</style>`;
  const perPage = cols * rws;
  const pages = [];
  for (let i = 0; i < rows.length; i += perPage) pages.push(rows.slice(i, i + perPage));
  let html = style;
  html += `<div class="sheets">`;
  pages.forEach((pageData, pIndex) => {
    html += `<h3>Page ${pIndex+1}</h3><div class="sheet" style="--gutter:${gutter}mm;grid-template-columns: repeat(${cols}, 1fr);">`;
    pageData.forEach(card => {
      html += `<div class="card-slot" style="width:63.5mm; height:88.9mm;">${hbs(card)}</div>`;
    });
    html += `</div>`;
  });
  html += `</div>`;
  $('#preview').innerHTML = html;

  // ON APPELLE LA FONCTION D'AJUSTEMENT
  fitBodies();
}

// FONCTION POUR AJUSTER LE TEXTE QUI DÉPASSE
function fitBodies() {
  document.querySelectorAll('.preview .card-slot').forEach(slot => {
    const card = slot.querySelector('.card');
    if (!card) return;
    const body = card.querySelector('.card-body');
    if (!body) return;
    
    card.classList.remove('compact', 'ultra');
    
    if (body.scrollHeight > body.clientHeight) {
      card.classList.add('compact');
    }
    
    setTimeout(() => {
      if (body.scrollHeight > body.clientHeight) {
        card.classList.remove('compact');
        card.classList.add('ultra');
      }
    }, 0);
  });
}

$('#btnPreview').addEventListener('click', renderPreview);

function getPayload() {
    return {
      rows,
      tpl_front: $('#tpl-front').value,
      tpl_back: $('#tpl-back').value,
      css: $('#css').value,
      paper: $('#paper').value,
      orient: $('#orient').value,
      cols: +$('#cols').value,
      grid_rows: +$('#rows').value,
      bleed: +$('#bleed').value,
      gutter: +$('#gutter').value,
      flip: $('#flip').value,
      offx: +$('#offx').value,
      offy: +$('#offy').value,
      mode: $('#mode').value
    };
}

$('#btnPrint').addEventListener('click', () => {
  const payload = getPayload();
  const form = $('#print-form');
  form.innerHTML = '';
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'payload';
  input.value = JSON.stringify(payload);
  form.appendChild(input);
  form.submit();
});

$('#btnPDF').addEventListener('click', () => {
  fetch('export.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(getPayload())
  }).then(r => r.blob()).then(b => {
    const a = document.createElement('a');
    a.href = URL.createObjectURL(b);
    a.download = 'Deck_Cards.pdf';
    a.click();
    URL.revokeObjectURL(a.href);
  });
});
</script>
</body></html>