# Deck Generator ✨

Une application web simple pour générer des planches de cartes prêtes à imprimer à partir d'un fichier de données CSV et de templates HTML/CSS.

---

## Fonctionnalités

- Import de données via un fichier CSV.  
- Templates HTML et CSS personnalisables pour le recto et le verso des cartes, avec des valeurs par défaut.  
- Prévisualisation en temps réel dans le navigateur.  
- Export PDF haute résolution côté serveur avec la librairie **Spatie Browsershot**, garantissant un rendu fidèle du HTML.  
- Impression directe depuis le navigateur pour un prototypage rapide.  
- Contrôles d’imposition complets : format de page, grille (colonnes et rangées), fond perdu (bleed), gouttière (gap), options recto-verso et décalages (offsets).

---

## Stack Technique ⚙️

- **Front-end** : HTML5, Vanilla JavaScript  
- **Templating** : Handlebars.js  
- **Parsing CSV** : PapaParse  
- **Back-end** : PHP 8+  
- **Génération PDF** : Spatie Browsershot (utilisant Puppeteer / Google Chrome en headless)  
- **Dépendances PHP** : Composer  
- **Environnement d'exécution JS** : Node.js (requis pour Browsershot)

---

## Arborescence du Projet 📁

```
/deck-generator/
├── index.php               # Interface utilisateur principale
├── print.php               # Page de prévisualisation pour l'impression navigateur
├── export.php              # Script de génération du PDF côté serveur
├── lib/
│   ├── php-utils.php       # Fonctions PHP partagées
│   ├── handlebars.min.js   # Librairie Handlebars
│   └── papaparse.min.js    # Librairie PapaParse
├── composer.json           # Dépendances PHP
├── composer.lock           # Fichier de verrouillage des versions
├── package.json            # Dépendances Node.js (pour Puppeteer)
├── vendor/                 # Dépendances installées par Composer
├── node_modules/           # Dépendances installées par npm
├── assets/
│   ├── default-card.html   # Template HTML par défaut pour le recto
│   ├── back-default.html   # Template HTML par défaut pour le verso
│   ├── default-card.css    # Styles CSS par défaut
│   ├── print-marks.css     # CSS pour les repères de coupe du PDF
│   ├── print-styles.css    # CSS pour la page d'impression navigateur
│   ├── all.min.css         # Fichier CSS de Font Awesome
│   ├── fonts.css           # Déclarations @font-face pour les polices
│   ├── logo.svg            # Logo de l'application
│   ├── /webfonts/          # Fichiers de police pour Font Awesome
│   └── /fonts/             # Fichiers de police (ex: Lato, Roboto Slab)
└── README.md               # Ce fichier
```

---

## Installation sur macOS 🚀

Ce projet nécessite un environnement de développement complet (**PHP**, **Node.js**, **Composer**) pour fonctionner localement.

### 1. Prérequis : Homebrew

Homebrew est un gestionnaire de paquets qui simplifie l'installation. Si vous ne l'avez pas, ouvrez le Terminal et lancez :

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### 2. Installer PHP, Composer et Node.js

Installez toutes les dépendances nécessaires avec Homebrew :

```bash
brew install php
brew install composer
brew install node
```

### 3. Cloner le projet

Clonez ce dépôt ou téléchargez les fichiers dans un dossier sur votre ordinateur.

### 4. Installer les dépendances du projet

Ouvrez un terminal, placez-vous dans le dossier du projet et exécutez les commandes suivantes :

```bash
# Se placer dans le bon dossier
cd chemin/vers/deck-generator

# 1. Installer les dépendances PHP
composer install

# 2. Installer Puppeteer (nécessaire pour la génération de PDF)
npm install puppeteer
```

*Note : L'installation de Puppeteer peut prendre du temps car elle télécharge une version de Chromium.*

### 5. Lancement du serveur local

Utilisez le serveur intégré de PHP pour lancer l'application :

```bash
php -S localhost:8000
```

Ouvrez votre navigateur et allez à l'adresse **http://localhost:8000**.

---

## Utilisation 📝

1. **Préparez votre CSV**  
   Le fichier CSV doit contenir des en-têtes (headers) sur la première ligne.  
   Les noms des colonnes peuvent être librement utilisés dans les templates Handlebars (ex: `{{Titre}}`, `{{Description}}`).

2. **Accédez à l’application**  
   Ouvrez `index.php` dans votre navigateur (via le serveur local).

3. **Chargez vos données**  
   Utilisez le sélecteur de fichier pour charger votre fichier CSV.

4. **Personnalisez les templates**  
   Modifiez le HTML et le CSS pour le recto et le verso des cartes directement dans l'interface.  
   Utilisez la syntaxe `{{nom_de_la_colonne}}` pour insérer les données de votre CSV.

5. **Configurez l’imposition**  
   Dans le panneau “Imposition & Export”, ajustez le format de page, la disposition en grille, le fond perdu et les autres paramètres selon vos besoins.

6. **Générez vos cartes**  
   - **Prévisualiser** : met à jour l'aperçu dans le navigateur.  
   - **Imprimer (navigateur)** : ouvre une nouvelle fenêtre avec la planche de cartes, prête pour le dialogue d'impression du navigateur (idéal pour un test rapide).  
   - **Exporter PDF (serveur)** : génère un fichier PDF haute résolution avec les repères de coupe, parfait pour l'impression professionnelle.

---

## Conseils et bonnes pratiques 💡

- Vérifiez toujours que les images ou icônes appelées dans les templates existent localement.  
- Pour un rendu optimal des polices, installez les fichiers `.ttf` correspondants et déclarez-les dans `fonts.css`.  
- Lors de l’impression, utilisez des marges minimales et désactivez le redimensionnement automatique du navigateur.  
- Pour l’export PDF, privilégiez l’usage du serveur pour éviter les différences de rendu liées aux moteurs de navigateur.  
- Vous pouvez combiner ce projet avec un outil de design (ex: Figma, Canva) pour générer des visuels avant intégration dans les templates.

---

## Licence 📜

Projet open source librement modifiable et redistribuable.  
Créé pour les créateurs, game designers et imprimeurs indépendants souhaitant prototyper ou produire leurs propres cartes sans dépendances complexes.
