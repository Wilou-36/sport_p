# Checklist de déploiement (Production)

1. Préparer l'environnement serveur
- PHP >= 8.2, extensions requises: pdo_mysql, ctype, iconv
- MySQL 8.0+ ou équivalent compatible Doctrine
- Composer installé
- Node.js + npm/yarn (si vous gérez des assets avec Webpack/Vite)

2. Dépendances
- Sur le serveur de production (ou pipeline CI), exécuter :

```bash
composer install --no-dev --optimize-autoloader --classmap-authoritative
```

3. Variables d'environnement
- Utiliser un fichier `.env` minimal ou variables d'environnement réelles.
- Exemple : `.env.prod` ou configuration des secrets dans la plateforme d'hébergement.

4. Migrations et base de données
- Générer/Appliquer les migrations :

```bash
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
```

5. Assets
- Si vous utilisez un bundler (Vite, Webpack, etc) :

```bash
# build assets
npm ci
npm run build
# ou (yarn)
yarn install --frozen-lockfile
yarn build
```

- Si vous utilisez Symfony Asset Mapper, vérifiez `assets/` et exécutez les commandes nécessaires.

6. Cache et configuration

```bash
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear --no-warmup
APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup
php bin/console doctrine:cache:clear-metadata
```

7. Sécurité
- Fixer `APP_SECRET` en production, ne pas le committer.
- Configurer HTTPS / certificats.
- Désactiver le profiler et bundles de debug en prod.

8. Logger
- Configurer Monolog pour rotation et niveau d'erreur approprié.

9. Worker / Cron / Messenger
- Mettre en place `symfony messenger:consume` ou worker supervisé si vous utilisez Messenger.

10. Monitoring et sauvegardes
- Sauvegarde régulière de la base.
- Monitoring (Sentry, NewRelic, etc.).

11. Rollback
- Préparer plan de rollback (dump DB, snapshot ou stratégie compatible).

12. Commandes rapides utiles

```bash
# Installer dépendances (prod)
composer install --no-dev --optimize-autoloader
# Exécuter migrations
php bin/console doctrine:migrations:migrate --no-interaction
# Vider et préchauffer le cache (prod)
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup
# Build assets
npm ci && npm run build
# Vérifier l'application
php bin/console lint:twig templates
php bin/console lint:yaml config
```

13. Notes spécifiques au projet
- Les routes sont préfixées par `/{_locale}`; configurez le serveur pour rediriger correctement (par ex. `RewriteBase /` ou configuration équivalente).
- Les fichiers de traduction se trouvent dans `translations/`.
- Les uploads utilisateurs sont dans `public/uploads/` — configurez sécurité et backups.

14. Hébergement gratuit recommandé
- Pour une app Symfony avec base de données, les options gratuites limitées : 
  - Railway (plan free avec PostgreSQL free tier) — simple pour tester mais limitations.
  - Render (free web services + managed Postgres free tiers parfois en promo).
  - Fly.io (petites instances gratuites, support Postgres via add-ons) — courbe moyenne.
  - Concernant MySQL gratuit : PlanetScale (MySQL compatible) ou ClearDB (limitations). 

15. Checklist avant bascule
- Tests unitaires & fonctionnels passés
- Migrations appliquées sur staging
- Backups effectués
- Variables d'environnement configurées


---

Si vous voulez que j'exécute les commandes (par ex. `composer install --no-dev`) sur cette machine, confirmez-le : cela supprimera les packages `require-dev` locaux (phpunit, maker, etc.).