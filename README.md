# PlaidAct Actualités (Plugin unifié)

Plugin WordPress unifié pour :
- le fil de brèves (`breves`),
- la timeline agenda,
- le répertoire ONG.

## Fonctionnalités
- Shortcodes:
  - `[plaidact_breves posts_per_page="12" title="Fil d’actualité"]`
  - `[plaidact_timeline term="nom-de-la-timeline" title="Titre optionnel"]`
  - `[plaidact_ong_directory posts_per_page="9"]`
- Règle de lien:
  - `url_externe` (ACF) => onglet externe + `rel="noopener noreferrer"`
  - sinon => permalink WordPress
- Tri antéchronologique, pagination propre, templates dédiés
- Templates surchargeables côté thème via:
  - `plaidact-breves/breves-feed.php`
  - `plaidact-breves/breve-item.php`
- Overrides archive/single `breves` automatiques si le thème ne fournit pas déjà `archive-breves.php` ou `single-breves.php`
- Inclut les templates timeline/ONG du module `plaidact-agenda-timeline`

## Installation
1. Copier le plugin dans `wp-content/plugins/plaidact-breves-feed`.
2. Activer le plugin.
3. Vérifier que le CPT `breves` existe.
4. Synchroniser les groupes ACF :
   - `acf-json/group_691c873303106.json` (brèves)
   - `plaidact-agenda-timeline/acf-json/group_67d05232b5b56.json` (agenda)
   - `plaidact-agenda-timeline/acf-json/group_ong.json` (ONG)
