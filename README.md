# PlaidAct Breves Feed (Plugin)

Plugin WordPress moderne pour afficher un fil de brèves (`breves`) compact, responsive et sans dépendances front.

## Fonctionnalités
- Shortcode: `[plaidact_breves posts_per_page="12" title="Fil d’actualité"]`
- Règle de lien:
  - `url_externe` (ACF) => onglet externe + `rel="noopener noreferrer"`
  - sinon => permalink WordPress
- Tri antéchronologique, pagination propre
- Templates surchargeables côté thème via:
  - `plaidact-breves/breves-feed.php`
  - `plaidact-breves/breve-item.php`
- Overrides archive/single `breves` automatiques si le thème ne fournit pas déjà `archive-breves.php` ou `single-breves.php`

## Installation
1. Copier le plugin dans `wp-content/plugins/plaidact-breves-feed`.
2. Activer le plugin.
3. Vérifier que le CPT `breves` existe.
4. Synchroniser le groupe ACF depuis `acf-json/group_691c873303106.json`.
