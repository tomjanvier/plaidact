# PlaidAct – Plugin WordPress (Brèves, Agenda/Timeline, Répertoire ONG)

Plugin unifié pour gérer :
- les **brèves**,
- les **agendas / timelines**,
- le **répertoire des ONG/associations**,
- les **hover cards** (associations + définitions).

## 1) Fonctionnalités clés

### Brèves
- Fil de brèves (standard ou ticker horizontal auto-défilant).
- Liste déroulante des dernières brèves.
- Grille paginée de toutes les brèves.
- Export back-office des textes newsletter (35 derniers jours).

### Agenda / Timeline
- Timeline verticale ou horizontale.
- Colonnes configurables + découpe des événements par colonne.
- Option d’affichage du titre (`show_title`).
- Option de téléchargement via impression (`show_download`) avec logo PLAID·ACT en bas de page imprimée.
- Import CSV agenda avec déduplication (titre + date_debut + timeline).

### Répertoire ONG
- Archive + fiches individuelles + filtres par cause.
- Boutons d’action en fiche individuelle : site web, don, **contact**.
- Import CSV associations (création/mise à jour), logo par URL ou ZIP.

### Hover cards
- Insertion rapide en contenu via `[[asso:slug|Texte]]` ou `[[definition:slug|Texte]]`.
- Shortcode dédié `[plaidact_hover_term]`.

---

## 2) Shortcodes

- `[plaidact_breves posts_per_page="12" title="Fil d’actualité"]`
- `[plaidact_breves_timeline posts_per_page="12" title=""]`
- `[plaidact_breves_latest_dropdown limit="20" label="Dernières actualités"]`
- `[plaidact_breves_all posts_per_page="5"]`

- `[plaidact_timeline term="slug-timeline" title="" show_title="1" show_download="1" layout="vertical|horizontal" columns="3" events_per_column="0" fill_empty_months="0|1"]`

- `[plaidact_asso_directory posts_per_page="9" cause="slug-cause"]`
- `[plaidact_hover_term type="asso|definition" id="slug" text="Texte"]`

---

## 3) Blocs Gutenberg

- `plaidact/timeline`
  - term, title, showTitle, showDownload, layout, columns, fillEmptyMonths, eventsPerColumn.
- `plaidact/asso-cause-list`

---

## 4) Installation

1. Copier le plugin dans `wp-content/plugins/plaidact-breves-feed`.
2. Activer le plugin dans WordPress.
3. Aller dans **Réglages > Permaliens** et enregistrer une fois.
4. Synchroniser les groupes ACF :
   - `acf-json/group_691c873303106.json` (brèves)
   - `acf-json/group_67d05232b5b56.json` (agenda)
   - `acf-json/group_asso.json` (associations)

---

## 5) Import CSV

### Import Associations
Menu : **Répertoire Asso > Import CSV**

Colonnes principales :
- `title`, `slug`
- `logo_url` ou `logo_file`
- `url_web`, `url_don`, `url_contact`
- `causes` (séparées par `|`)
- `resume_court`
- `social_*` + `social_links_csv`

### Import Agenda
Menu : **Agenda > Import CSV**

Colonnes principales :
- `title`, `slug`, `timeline`
- `date_debut`, `date_fin`, `type_evenement`, `lieu`, `lien_evenement`

---

## 6) Notes utiles

- Taxonomie timeline : template `taxonomy-agenda_timeline.php`.
- Paramètres URL utiles (archive timeline) :
  - `?layout=horizontal`
  - `?columns=4`
  - `?events_per_column=2`
  - `?show_title=0`
  - `?show_download=0`

