# Tests du calcul des créneaux

Objectif : prouver que le chargement des créneaux par semaine (1 requête) renvoie **exactement**
les mêmes créneaux que l'ancien fonctionnement (1 requête par jour, 1 requête SQL par créneau).

## PHP (PHPUnit) — `tests/php`

```bash
tests/php/run.sh                      # tout (lance un MySQL jetable sur le port 33306)
tests/php/run.sh --filter Equivalence # un seul fichier / test
```

* Compare le nouveau code à `tests/php/Legacy/class-legacy-times-calculator.php`, **copie figée** de
  l'ancien code (v2.1.9). À supprimer une fois l'optimisation validée en production.
* 4 configurations de créneaux × 3 profils (anonyme, client, admin) × 5 nombres de participants × 7 jours,
  comparés avec `assertSame` (types inclus), sur des réservations aléatoires reproductibles.
* Installation une fois : `cd tests/php && composer install --ignore-platform-req=ext-mbstring`.

## Playwright — `tests/e2e`

```bash
# depuis ce dossier (le site local doit tourner : docker compose up -d à la racine du projet)
npx playwright test -c tests/e2e --project=chromium
# si aquatonic.local n'est pas dans /etc/hosts :
E2E_HOST_RESOLVER="MAP aquatonic.local 127.0.0.1" npx playwright test -c tests/e2e --project=chromium
```

* Compte les requêtes AJAX réellement envoyées par le navigateur (1 par semaine, 0 pour un autre jour de la semaine).
* Compare l'endpoint « semaine » aux 7 réponses de l'ancien endpoint, et aux listes affichées à l'écran.
* **Après tout changement du JS : vider le cache WP Rocket**, sinon la page charge l'ancienne copie
  (`wp-content/cache/min/.../calendar.js`).
