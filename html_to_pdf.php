<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HTML to PDF Generator</title>
  <link rel="stylesheet" href="assets/fonts.css" />
  <link rel="stylesheet" href="assets/all.min.css" />
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
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: var(--font-sans); background-color: var(--color-bg); color: var(--color-text); font-size: 14px; overflow: hidden; }
    .container { display: flex; height: 100vh; }
    .editor-pane { width: 50%; display: flex; flex-direction: column; border-right: 1px solid var(--color-border); }
    .preview-pane { width: 50%; display: flex; flex-direction: column; }
    .pane-header { background-color: var(--color-surface); padding: 1rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
    .pane-header h1 { font-size: 1.25rem; font-weight: 700; }
    .pane-content { flex-grow: 1; display: flex; flex-direction: column; }
    textarea, iframe { width: 100%; height: 100%; border: none; background-color: var(--color-bg); color: var(--color-text); }
    textarea { padding: 1rem; font-family: monospace; font-size: 14px; resize: none; }
    textarea:focus { outline: none; }
    iframe { background-color: white; }
    button, .button { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background-color 0.2s, transform 0.1s; }
    button:active { transform: translateY(1px); }
    .button-primary { background-color: var(--color-accent); color: white; }
    .button-primary:hover { background-color: var(--color-accent-hover); }
    button:disabled { cursor: not-allowed; opacity: 0.6; }
    .file-input-wrapper { position: relative; overflow: hidden; display: inline-block; }
    .file-input-wrapper input[type="file"] { position: absolute; left: 0; top: 0; opacity: 0; width: 100%; height: 100%; cursor: pointer; }
    .button-group { display: flex; gap: 0.75rem; }
    .back-link { color: var(--color-text-muted); text-decoration: none; font-size: 0.9rem; }
    .back-link:hover { color: var(--color-accent); }
  </style>
</head>
<body>
  <div class="container">
    <div class="editor-pane">
      <div class="pane-header">
        <h1>Éditeur HTML</h1>
        <div class="button-group">
            <div class="file-input-wrapper button">
                <i class="fa-solid fa-upload"></i> Charger un fichier
                <input type="file" id="html-file-input" accept=".html,.htm">
            </div>
            <a href="index.php" class="back-link" title="Retour au générateur de cartes"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        </div>
      </div>
      <div class="pane-content">
        <textarea id="html-source" placeholder="Collez votre code HTML ici..."></textarea>
      </div>
    </div>
    <div class="preview-pane">
      <div class="pane-header">
        <h1>Prévisualisation</h1>
        <button id="print-pdf-button" class="button-primary">
          <i class="fa-solid fa-file-pdf"></i> Imprimer en PDF
        </button>
      </div>
      <div class="pane-content">
        <iframe id="preview-frame" title="Prévisualisation HTML"></iframe>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const htmlSourceTextarea = document.getElementById('html-source');
      const previewFrame = document.getElementById('preview-frame');
      const printPdfButton = document.getElementById('print-pdf-button');
      const htmlFileInput = document.getElementById('html-file-input');

      // Fonction pour mettre à jour la prévisualisation
      const updatePreview = () => {
        const sourceHtml = htmlSourceTextarea.value;
        // On injecte le contenu dans l'iframe. L'attribut sandbox est une mesure de sécurité,
        // et `allow-same-origin` est nécessaire pour que les scripts et autres ressources fonctionnent.
        // La balise <base> est cruciale pour que les chemins relatifs (ex: `assets/image.png`) fonctionnent depuis la racine du projet.
        const previewContent = `<base href="${window.location.origin}${window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'))}/">${sourceHtml}`;
        previewFrame.srcdoc = previewContent;
      };

      // Mettre à jour la prévisualisation à chaque modification du textarea
      htmlSourceTextarea.addEventListener('input', updatePreview);

      // Mettre à jour une première fois au chargement si du code est déjà présent
      updatePreview();

      // Gérer le chargement de fichier
      htmlFileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            htmlSourceTextarea.value = e.target.result;
            updatePreview();
          };
          reader.readAsText(file);
        }
      });

      // Gérer l'export PDF
      printPdfButton.addEventListener('click', async () => {
        const htmlContent = htmlSourceTextarea.value;
        if (!htmlContent.trim()) {
          alert("Le code HTML est vide. Veuillez entrer du contenu avant d'exporter.");
          return;
        }

        const originalButtonHtml = printPdfButton.innerHTML;
        printPdfButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Génération...';
        printPdfButton.disabled = true;

        try {
          const formData = new FormData();
          formData.append('html', htmlContent);

          const response = await fetch('generate_pdf.php', {
            method: 'POST',
            body: new URLSearchParams(formData) // Envoyer en x-www-form-urlencoded
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
          a.download = 'document.pdf';
          document.body.appendChild(a);
a.click();
          a.remove();
          window.URL.revokeObjectURL(url);

        } catch (error) {
          console.error("Erreur lors de l'export PDF:", error);
          alert("Une erreur est survenue lors de la génération du PDF. Consultez la console (F12) pour les détails.");
        } finally {
          printPdfButton.innerHTML = originalButtonHtml;
          printPdfButton.disabled = false;
        }
      });
    });
  </script>
</body>
</html>