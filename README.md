# PlaidAct Actualités (Plugin unifié)

Plugin WordPress unifié pour :
- les brèves,
- la timeline agenda,
- le répertoire des associations (fonction historique `ong` conservée côté données).

## Fonctionnalités
- Shortcodes :
  - `[plaidact_breves posts_per_page="12" title="Fil d’actualité"]`
  - `[plaidact_breves_latest_dropdown]` (20 dernières en liste déroulante)
  - `[plaidact_breves_all posts_per_page="5"]` (toutes les brèves, 3 colonnes, 5/page)
  - `[plaidact_timeline term="nom-de-la-timeline" title="Titre optionnel" fill_empty_months="0|1"]`
  - `[plaidact_asso_directory posts_per_page="9" cause="slug-cause"]`
  - `[plaidact_ong_directory]` (alias legacy)
- Blocs Gutenberg :
  - `plaidact/timeline`
  - `plaidact/asso-cause-list`
  - `plaidact/asso-directory` (répertoire complet avec recherche + filtre cause)
- Timeline : passé + présent + futur, gestion des événements mensuels sur plusieurs mois.
- Répertoire Asso : filtre par cause, archive/single/templates dédiés.
- Taxonomies supprimées : `odd` et `forme_engagement`.
- Import CSV associations : menu **Outils > Import Asso CSV** (inclut import logo via `logo_url`).

## Installation
1. Copier le plugin dans `wp-content/plugins/plaidact-breves-feed`.
2. Activer le plugin.
3. Ouvrir **Réglages > Permaliens** et enregistrer une fois.
4. Synchroniser les groupes ACF :
   - `acf-json/group_691c873303106.json` (brèves)
   - `acf-json/group_67d05232b5b56.json` (agenda)
   - `acf-json/group_asso.json` (associations)

## Import associations (CSV)
- Utiliser **Outils > Import Asso CSV**.
- Colonnes supportées : `title, content, excerpt, zone_dengagement, comment_agir, url_web, url_don, cause, logo_url` + `social_*`.
- `cause` accepte plusieurs valeurs séparées par `|`.
- `logo_url` : URL d’image publique (le plugin télécharge l’image et la définit en image mise en avant).
