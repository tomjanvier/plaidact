# PlaidAct Agenda Timeline + Répertoire ONG (Plugin)

## Installation
1. Copier le dossier `plaidact-agenda-timeline` dans `wp-content/plugins/`.
2. Activer le plugin depuis l'admin WordPress.
3. Aller dans **Réglages > Permaliens** puis cliquer **Enregistrer**.

## Modules inclus
- Timeline Agenda (`agenda_timeline`) + shortcode :
```text
[plaidact_timeline term="saison-2026"]
```
- Répertoire ONG :
  - CPT `ong`
  - Taxonomies `cause`, `forme_engagement`, `odd`
  - Archive native `ong`
  - Single native `ong`
  - Template de page “Répertoire des ONG (PlaidAct)”
  - Shortcode optionnel :
```text
[plaidact_ong_directory]
```

## ACF Local JSON
- `acf-json/group_67d05232b5b56.json` (Agenda)
- `acf-json/group_ong.json` (ONG)

## Notes
- Le module n'a aucune dépendance à Algolia ni aux helpers legacy.
- Compatibilité gracieuse avec Polylang (pas de dépendance obligatoire).
