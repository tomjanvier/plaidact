# PlaidAct Actualités (Plugin unifié)

Extension WordPress unifiée pour :
- les brèves,
- la timeline agenda,
- le répertoire des associations.

## Fonctionnalités
- Shortcodes :
  - `[plaidact_breves posts_per_page="12" title="Fil d’actualité"]`
  - `[plaidact_breves_latest_dropdown]` (20 dernières en liste déroulante)
  - `[plaidact_breves_timeline]` (alias horizontal de `[plaidact_breves]`)
  - `[plaidact_breves_all posts_per_page="5"]` (toutes les brèves, 3 colonnes, 5/page)
  - `[plaidact_timeline term="nom-de-la-timeline" title="Titre optionnel" fill_empty_months="0|1"]`
  - `[plaidact_asso_directory posts_per_page="9" cause="slug-cause"]`
  - `[plaidact_hover_term type="asso|definition" id="slug" text="Texte survolé"]`
- Blocs Gutenberg :
  - `plaidact/timeline` (slug, titre, mode vertical/horizontal, nb de colonnes, remplissage des mois vides, événements par colonne)
  - `plaidact/asso-cause-list`
- Timeline : passé + présent + futur, gestion des événements mensuels sur plusieurs mois.
- Répertoire Asso : filtre par cause, archive/single/templates dédiés.
- Fiche association : résumé, boutons site web/don, réseaux sociaux (top 10 + Bluesky).
- Back-office : import CSV (création/mise à jour) avec support logo via URL ou ZIP de logos.
- Back-office : export texte newsletter des brèves des 35 derniers jours.
- Back-office : import CSV agenda/timelines avec détection de doublons dans le fichier.
- Définitions/infobulles : CPT privé `Définitions` + taxonomie interne `Catégories de définitions`.
- Taxonomies supprimées : `odd` et `forme_engagement`.

## Installation
1. Copier le plugin dans `wp-content/plugins/plaidact-breves-feed`.
2. Activer le plugin.
3. Ouvrir **Réglages > Permaliens** et enregistrer une fois.
4. Synchroniser les groupes ACF :
   - `acf-json/group_691c873303106.json` (brèves)
   - `acf-json/group_67d05232b5b56.json` (agenda)
   - `acf-json/group_asso.json` (associations)

## Import associations (back-office)
1. Aller dans **Répertoire Asso > Import CSV**.
2. Télécharger le modèle CSV depuis l’écran.
3. Colonnes principales :
   - `title`, `slug`
   - `logo_url` (URL du logo) **ou** `logo_file` (nom du fichier dans un ZIP uploadé)
   - `url_web`, `url_don`, `zone_dengagement`, `resume_court`
   - `causes` (séparées par `|`)
   - `social_*` (facebook, x, instagram, linkedin, youtube, tiktok, twitch, whatsapp, telegram, discord, bluesky)
4. Importer le CSV, et (optionnel) un ZIP contenant les logos.

## Import agenda/timeline (back-office)
1. Aller dans **Agenda > Import CSV**.
2. Colonnes principales :
   - `title`, `slug`, `timeline`
   - `date_debut`, `date_fin`, `type_evenement`, `lieu`, `lien_evenement`
3. Les doublons dans le CSV (titre + date_debut + timeline) sont ignorés automatiquement.

## Hover cards (associations + définitions)
- Dans un contenu, utilisez le format `[[asso:slug|Texte]]` ou `[[definition:slug|Texte]]`.
- Ou le shortcode `[plaidact_hover_term]`.
- Les définitions sont gérées dans **Définitions** (non public).
- Les infobulles sont affichées en bulle flottante au survol (compatible thèmes avec `overflow:hidden`).
