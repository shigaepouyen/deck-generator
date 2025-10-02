# Deck Generator ✨

Une application web simple pour générer des planches de cartes prêtes à imprimer à partir d'un fichier de données CSV et de templates HTML/CSS.

---

## Fonctionnalités

- Import de données via CSV (par fichier ou copier-coller)  
- Templates HTML et CSS personnalisables avec pré-remplissage  
- Prévisualisation en temps réel dans le navigateur  
- Export PDF côté serveur avec la librairie **dompdf**  
- Impression directe via le dialogue d'impression du navigateur  
- Contrôles d’imposition complets : format de page, grille, fond perdu, gouttière, options recto-verso et décalages (offsets)

---

## Stack Technique ⚙️

- **Front-end** : HTML5, Vanilla JavaScript  
- **Templating** : Handlebars.js  
- **Parsing CSV** : PapaParse  
- **Back-end** : PHP 8.x  
- **Génération PDF** : dompdf  
- **Dépendances PHP** : Composer  

---

## Arborescence du Projet 📁

```
/deck-generator/
├── index.php               # Interface utilisateur principale
├── print.php               # Génère la page pour l'impression navigateur
├── export.php              # Génère le PDF côté serveur
├── lib/php-utils.php       # Fonctions PHP partagées
├── composer.json           # Fichier de configuration Composer
├── vendor/                 # Dossier des dépendances (installé par Composer)
│   └── dompdf/
├── assets/
│   ├── default-card.html   # Template HTML par défaut pour une carte
│   ├── default-card.css    # Styles CSS par défaut
│   ├── back-default.html   # Template pour le dos des cartes
│   ├── print-marks.css     # CSS pour les repères de coupe
│   ├── all.min.css         # Fichier CSS de Font Awesome (local)
│   ├── fonts.css           # Fichier CSS pour les polices de texte (local)
│   ├── /webfonts/          # Dossier des polices Font Awesome
│   └── /fonts/             # Dossier pour les polices de texte (ex: Inter)
└── README.md               # Ce fichier
```

---

## Installation 🚀

Ce projet nécessite **PHP** et **Composer** pour être installé localement avant d’être uploadé sur un serveur d’hébergement.

### 1. Prérequis

Assurez-vous d’avoir **PHP** et **Composer** installés sur votre ordinateur.

### 2. Télécharger le projet

Clonez ce dépôt ou téléchargez les fichiers dans un dossier sur votre ordinateur.

### 3. Installer les dépendances

Ouvrez un terminal, placez-vous dans le dossier du projet :

```bash
cd chemin/vers/deck-generator
composer require dompdf/dompdf
```

Cette commande créera le dossier `vendor/`.

### 4. Installer les polices (crucial pour le PDF)

#### Icônes (Font Awesome)

1. Téléchargez l’archive **“Free for Web”** depuis le site de Font Awesome.  
2. Dézippez-la.  
3. Copiez le dossier `webfonts` dans `assets/`.  
4. Copiez le fichier `css/all.min.css` dans `assets/`.  
5. Ouvrez `assets/all.min.css` et remplacez toutes les occurrences de `../webfonts` par `webfonts`.

#### Police de texte (Inter)

1. Téléchargez la famille de polices **Inter** depuis Google Fonts.  
2. Créez un dossier `assets/fonts`.  
3. Copiez les fichiers `.ttf` (ex: Inter-Regular.ttf, Inter-Bold.ttf).  
4. Vérifiez que `assets/fonts.css` contient les bonnes déclarations `@font-face`.

### 5. Déploiement

Uploadez tous les fichiers et dossiers (y compris `vendor/`) sur votre serveur d’hébergement (ex: Infomaniak).

---

## Utilisation 📝

1. **Préparez votre CSV**  
   Le fichier CSV doit contenir les en-têtes suivants (ordre libre mais noms exacts) :  
   `id, category, category_slug, client, body, icon, malefice, malefice_points`

2. **Accédez à l’application**  
   Ouvrez `index.php` dans votre navigateur.

3. **Chargez vos données**  
   Utilisez le sélecteur de fichier ou collez le contenu de votre CSV.

4. **Personnalisez (optionnel)**  
   Modifiez les templates HTML ou le CSS directement depuis l’interface.

5. **Configurez l’impression**  
   Choisissez le format, la grille, le fond perdu, etc. dans le panneau “Imposition & Export”.

6. **Générez vos cartes**

   - Cliquez sur **“Prévisualiser”** pour un aperçu.  
   - Cliquez sur **“Imprimer (navigateur)”** pour imprimer directement.  
   - Cliquez sur **“Exporter PDF (serveur)”** pour générer un PDF.

---

## Gestion des Fichiers Locaux

### Icônes (Font Awesome)

- Téléchargez “Free for Web” sur le site de Font Awesome.  
- Copiez le dossier `webfonts` et le fichier `css/all.min.css` dans `assets/`.  
- Éditez `all.min.css` pour corriger les chemins (`../webfonts` → `webfonts`).

### Police de texte (Inter)

- Téléchargez la police **Inter** depuis Google Fonts.  
- Copiez les `.ttf` dans `assets/fonts`.  
- Vérifiez les `@font-face` dans `assets/fonts.css`.

---
