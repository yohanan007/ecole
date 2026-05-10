# Guide de Setup et Déploiement

Guide étape par étape pour configurer, développer et déployer le système de gestion d'agenda.

---

## 📋 Prérequis Système

### Environnement de Développement
- **OS:** Windows 10+, macOS 10.14+, Linux (Ubuntu 18.04+)
- **PHP:** 8.0 ou supérieur
- **Composer:** 2.0+
- **Node.js:** 14.0 ou supérieur
- **npm:** 6.0+
- **Git:** Pour le versioning

### Base de Données
- **MySQL:** 5.7.10+ OU
- **PostgreSQL:** 10+
- **MariaDB:** 10.3+

### Outils Recommended
- **Symfony CLI** - `https://symfony.com/download`
- **PHPStorm** ou **VS Code** - IDE/Éditeur
- **Postman** ou **Insomnia** - API testing
- **DBeaver** - Database client

---

## 🚀 Installation Locale (Development)

### Étape 1: Cloner le Dépôt

```bash
# Via HTTPS (plus facile)
git clone https://github.com/votre-org/ecole.git
cd ecole/src

# OU via SSH (si authenticated)
git clone git@github.com:votre-org/ecole.git
cd ecole/src
```

### Étape 2: Configurer l'Environnement

```bash
# Copier le fichier .env
cp .env.example .env

# OU si .env n'existe pas, Symfony le crée
# Éditer .env avec vos valeurs
nano .env
```

**Contenu recommandé pour développement:**

```env
# .env (développement)
APP_ENV=dev
APP_DEBUG=true
APP_SECRET=ChangeMe_SecureRandomString123!

# Database
DATABASE_URL="mysql://root:password@127.0.0.1:3306/ecole?serverVersion=5.7"
# OU pour PostgreSQL
# DATABASE_URL="postgresql://user:password@127.0.0.1:5432/ecole"

# Mailer (utiliser mailhog en dev)
MAILER_DSN="smtp://localhost:1025"

# Mode synchrone pour messages
MESSENGER_TRANSPORT_DSN="sync://"

# Trusted hosts pour développement
TRUSTED_HOSTS="localhost,127.0.0.1,[::1]"
```

### Étape 3: Installer les Dépendances

```bash
# Dépendances PHP (créé aussi vendor/autoload.php)
composer install

# Dépendances Node.js
npm install
```

**Temps estimé:** 3-5 minutes

### Étape 4: Créer la Base de Données

```bash
# Créer le schéma de base
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Confirmation interactif: "Êtes-vous sûr?" → [Y]
```

**Alternatives:**

```bash
# Générer schéma depuis les entités (sans migrations)
php bin/console doctrine:schema:create

# Mode dry-run (voir ce qui serait exécuté)
php bin/console doctrine:migrations:migrate --dry-run
```

### Étape 5: Créer une Fixture (Données de Test)

```bash
# Créer un utilisateur admin
php bin/console app:create-admin

# Ou insérer manuellement via SQL
mysql ecole < fixtures.sql
```

**Si aucune commande disponible, créer manuellement:**

```bash
# Ouvrir MySQL console
mysql -u root -p ecole

# Insérer user + admin
INSERT INTO "user" (email, roles, password, nom, prenom) VALUES 
('admin@ecole.fr', '["ROLE_ADMIN"]', password_hash, 'Dupont', 'Sophie');

INSERT INTO "admin" (user_id, nom, prenom, telephone, is_active) VALUES
(1, 'Dupont', 'Sophie', '06123456789', 1);
```

### Étape 6: Compiler les Assets

```bash
# Mode développement (watch mode)
npm run watch

# OU compilation simple
npm run dev
```

**Terminal dédié recommandé** (laisse npm en background)

### Étape 7: Démarrer le Serveur

**Option A: Symfony CLI (recommandé)**
```bash
symfony server:start

# Dans un autre terminal
npm run watch
```

**Option B: PHP built-in server**
```bash
php -S localhost:8000 -t public

# Accès: http://localhost:8000
```

**Option C: Docker (si disponible)**
```bash
docker-compose up -d
```

### ✅ Vérification Installation

```bash
# URL d'accès
http://localhost:8000

# Calendrier (public)
http://localhost:8000/agenda/

# Créer événement (requires admin)
http://localhost:8000/agenda/create
```

**Checklist de vérification:**
- [ ] Database créée (vérifier via `php bin/console doctrine:database:list`)
- [ ] Tables créées (`php bin/console doctrine:query:dql "SELECT u FROM App\Entity\User u"`)
- [ ] Assets compilés (vérifier dossier `public/build/`)
- [ ] Aucune erreur dans `var/log/dev.log`
- [ ] Page `/agenda/` répond (200 OK)

---

## 🔧 Configuration Avancée

### Variables d'Environnement Complètes

```env
# .env (toutes les options)

# Application
APP_ENV=dev|prod|test
APP_DEBUG=true|false
APP_SECRET=your_secret_key

# Database
DATABASE_URL=mysql://user:pass@host:3306/db
DATABASE_LOGGING=true|false          # SQL queries logs

# Email/Mailer
MAILER_DSN=smtp://localhost:1025     # Mailhog
# OU MAILER_DSN=sendmail://default    # Sendmail system
# OU MAILER_DSN=smtps://smtp.gmail.com:465?encryption=tls

# Session
SESSION_SAVE_PATH=%kernel.project_dir%/var/sessions

# Security
OAUTH_GOOGLE_ID=optional_for_google_auth
OAUTH_GOOGLE_SECRET=optional_for_google_auth

# Cache
CACHE_ADAPTER=redis://localhost:6379/0  # Pour production
# Default: array:// (en mémoire, ne persiste pas)

# Logging
LOG_CHANNEL=single|monolog    # Format logging

# Assets
ASSET_URL=/build/             # CDN URL pour production
```

### Configuration Services (services.yaml)

```yaml
# config/services.yaml

services:
  # Autowiring pour tous les services
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  # Services custom
  App\Service\AgendaGenerator:
    arguments:
      $evenementRepository: '@App\Repository\EvenementRepository'

  # Accès dans controllers via DI
  App\Controller\AgendaController:
    arguments:
      $agendaGenerator: '@App\Service\AgendaGenerator'
```

### Configuration Security (security.yaml)

```yaml
# config/packages/security.yaml

security:
  password_hashers:
    Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface:
      algorithm: bcrypt
      cost: 12

  providers:
    app_user_provider:
      entity:
        class: App\Entity\User
        property: email

  firewalls:
    dev:
      pattern: ^/(_(profiler|wdt)|css|images|js)/
      security: false

    main:
      lazy: true
      provider: app_user_provider
      custom_authenticator: App\Security\AppAuthenticator
      logout:
        path: app_logout

  access_control:
    - { path: ^/admin, role: ROLE_ADMIN }
    - { path: ^/agenda, role: ROLE_USER }
```

### Configuration Doctrine (doctrine.yaml)

```yaml
# config/packages/doctrine.yaml

doctrine:
  dbal:
    url: '%env(resolve:DATABASE_URL)%'
    # MySQL spécifique
    server_version: '5.7'
    charset: utf8mb4
    default_table_options:
      charset: utf8mb4
      collation: utf8mb4_unicode_ci

  orm:
    auto_generate_proxy_classes: true
    naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
    quote_strategy: doctrine.orm.quote_strategy.back_slashes
    
    entity_managers:
      default:
        mappings:
          App:
            is_bundle: false
            type: attribute
            dir: '%kernel.project_dir%/src/Entity'
            prefix: 'App\Entity'
```

---

## 🧪 Testing Setup

### Installation PHPUnit

```bash
# Déjà inclus avec Symfony
composer require --dev phpunit/phpunit

# Configuration
cp phpunit.xml.dist phpunit.xml
```

### Tests Database

```bash
# Créer une DB dédiée aux tests
DATABASE_URL=mysql://root:pass@localhost:3306/ecole_test php bin/console doctrine:database:create --env=test

# Migrations en test
DATABASE_URL=mysql://root:pass@localhost:3306/ecole_test php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

### Lancer les Tests

```bash
# Tous les tests
php bin/phpunit

# Test spécifique
php bin/phpunit tests/Unit/Service/AgendaGeneratorTest.php

# Avec couverture de code
php bin/phpunit --coverage-html=coverage

# Voir couverture: open coverage/index.html
```

---

## 📦 Build Production

### Étape 1: Préparer l'Environnement

```bash
# Créer fichier .env.prod (surcharge .env)
cat > .env.prod << 'EOF'
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=GeneratedVeryLongSecureStringHere!

DATABASE_URL="mysql://prod_user:prod_pass@prod_host:3306/ecole_prod"
MAILER_DSN="smtps://smtp.gmail.com:465?encryption=tls&username=noreply@ecole.fr&password=app_pass"
EOF
```

### Étape 2: Installer Dépendances (Production)

```bash
# Sans dev dependencies
composer install --no-dev --optimize-autoloader

# Node assets
npm ci  # Lockfile exact (mieux que npm install)
```

### Étape 3: Compiler Assets

```bash
# Build production (minified)
APP_ENV=prod npm run build

# Vérifier: dossier public/build/ contient fichiers minifiés
ls -la public/build/
```

### Étape 4: Migrations

```bash
# Exécuter migrations
APP_ENV=prod php bin/console doctrine:migrations:migrate --no-interaction

# OU vérifier avant
php bin/console doctrine:migrations:migrate --dry-run
```

### Étape 5: Cache Warming

```bash
# Précalculer caches (optionnel mais recommandé)
APP_ENV=prod php bin/console cache:warmup
APP_ENV=prod php bin/console cache:clear
```

### Étape 6: Permissions Dossiers

```bash
# Linux/Mac: Écriture pour les logs et cache
HTTPDUSER=$(ps axo user= -p $(pgrep -f "apache|nginx|php-fpm|www-data" | head -1) | tr -d ' ')
setfacl -dR -m u:"$HTTPDUSER":rwX var/
setfacl -R -m u:"$HTTPDUSER":rwX var/

# OU simpler
chmod -R 775 var/
chmod -R 775 public/
```

### Étape 7: Web Server Configuration

#### Nginx

```nginx
# /etc/nginx/sites-available/ecole.conf

server {
    listen 80;
    server_name ecole.fr www.ecole.fr;
    root /var/www/ecole/public;

    location / {
        try_files $uri @rewriteapp;
    }

    location @rewriteapp {
        rewrite ^(.*)$ /index.php/$1 last;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.0-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;

        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTPS off;
    }

    client_max_body_size 20M;
}

# Enable:
# sudo a2ensite ecole.conf
# sudo nginx -t && sudo systemctl reload nginx
```

#### Apache

```apache
# /etc/apache2/sites-available/ecole.conf

<VirtualHost *:80>
    ServerName ecole.fr
    ServerAlias www.ecole.fr
    DocumentRoot /var/www/ecole/public

    <Directory /var/www/ecole/public>
        AllowOverride All
        Order Allow,Deny
        Allow from All

        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/ecole_error.log
    CustomLog ${APACHE_LOG_DIR}/ecole_access.log combined
</VirtualHost>

# Enable:
# sudo a2enmod rewrite
# sudo a2ensite ecole
# sudo apache2ctl configtest
# sudo systemctl reload apache2
```

#### PHP-FPM

```bash
# /etc/php/8.0/fpm/pool.d/ecole.conf

[ecole]
listen = /run/php/php8.0-fpm.sock
listen.owner = www-data
listen.group = www-data

user = www-data
group = www-data

pm = dynamic
pm.max_children = 75
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20

# Reload:
sudo systemctl restart php8.0-fpm
```

### Étape 8: SSL/HTTPS (Recommandé)

```bash
# Let's Encrypt + Certbot
sudo apt install certbot python3-certbot-nginx

# Obtenir certificat
sudo certbot certonly --nginx -d ecole.fr -d www.ecole.fr

# Nginx
sudo certbot install --nginx

# Vérifier renouvellement auto
sudo certbot renew --dry-run
```

### ✅ Vérification Production

```bash
# Tests
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Logs errors
tail -f var/log/prod.log

# Accès URL
https://ecole.fr
https://ecole.fr/agenda/
```

---

## 🐳 Deployment avec Docker

### Dockerfile

```dockerfile
# Dockerfile
FROM php:8.0-fpm-alpine

# Extensions
RUN docker-php-ext-install pdo_mysql opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# App
WORKDIR /app
COPY . .

# Dépendances
RUN composer install --no-dev --optimize-autoloader

# Assets
RUN npm ci && npm run build

# Permissions
RUN chown -R www-data:www-data /app

EXPOSE 9000
CMD ["php-fpm"]
```

### docker-compose.yml

```yaml
version: '3'

services:
  web:
    image: app:latest
    ports:
      - "8000:9000"
    environment:
      - APP_ENV=prod
      - DATABASE_URL=mysql://app:secret@db:3306/ecole
    depends_on:
      - db
    volumes:
      - ./var:/app/var

  db:
    image: mysql:5.7
    environment:
      MYSQL_DB: ecole
      MYSQL_USER: app
      MYSQL_PASSWORD: secret
      MYSQL_ROOT_PASSWORD: root
    volumes:
      - db_data:/var/lib/mysql

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
      - ./app:/app:ro
    depends_on:
      - web

volumes:
  db_data:
```

### Lancer

```bash
docker-compose up -d
docker-compose exec web php bin/console doctrine:migrations:migrate
```

---

## 🔄 Git Workflow

### Branches

- `main` - Production prête
- `develop` - Integration branche
- `feature/*` - Nouvelles features
- `bugfix/*` - Fixes/patches
- `hotfix/*` - Urgent production fixes

### Commits

```bash
# Ne jamais faire
git push --force

# Bonne pratique
git add .
git commit -m "feat: ajouter bouton créer événement"
git push origin feature/create-button

# Puis Pull Request sur develop
```

### Versioning (Semantic)

```
v1.0.0 = MAJOR.MINOR.PATCH

- MAJOR: Breaking changes (incompatibilité)
- MINOR: Features (backwards compatible)
- PATCH: Bugfixes

v1.0.0 → v1.1.0 (nouvelle feature)
v1.1.0 → v1.1.1 (bugfix)
v1.1.1 → v2.0.0 (breaking change)
```

---

## 📋 Checklist Déploiement

**Avant la production:**

- [ ] Code testé localement
- [ ] All tests passing: `php bin/phpunit`
- [ ] PHPStan check: `./vendor/bin/phpstan analyse src/`
- [ ] Assets compilés en prod: `npm run build`
- [ ] Database migrations testées
- [ ] Fichiers sensibles ignorés (.env, secrets)
- [ ] Permissions dossiers correctes
- [ ] HTTPS/SSL configuré
- [ ] Backup database avant déploiement
- [ ] Status page pour monitoring
- [ ] Logs consolidés (centralized logging)
- [ ] Performance tested (load testing)

**Post-déploiement:**

- [ ] Health check endpoint répond
- [ ] Database accessible
- [ ] Assets chargent correctement
- [ ] Authentification fonctionne
- [ ] Événements créent/affichent correctement
- [ ] Emails envoient (test)
- [ ] Logs clean (pas d'erreurs)
- [ ] Monitoring actif

---

## 🆘 Troubleshooting

### 403 Forbidden

```
Symptôme: Apache refuse l'accès
Solution: 
  - Vérifier permissions var/, public/
  - Vérifier owwner www-data:www-data
  - Rebuild web server config
```

### Database Connection Error

```
Symptôme: "Could not open connection to server"
Cause: Database not running OU wrong credentials
Solution:
  - Vérifier db running: mysql -u root -p
  - Vérifier .env DATABASE_URL
  - Vérifier firewall port 3306 (MySQL)
```

### Class Not Found Error

```
Symptôme: "Class App\Entity\User not found"
Cause: Autoloader pas à jour
Solution:
  composer dump-autoload
  php bin/console cache:clear
```

### Template Not Found

```
Symptôme: "Template not found: agenda/create.html.twig"
Cause: Fichier template manquant
Solution:
  - Vérifier chemin: templates/agenda/create.html.twig
  - Vérifier case sensitivity (Linux is case-sensitive!)
```

### Assets Not Loading

```
Symptôme: CSS/JS files 404
Cause: npm run build not executed OU wrong paths
Solution:
  npm run dev
  Vérifier public/build/ existe
  Vérifier {{ encore_entry_script_tags('agenda') }}
```

### Permission Denied var/log

```
Cause: Web server user ≠ git user
Solution:
  sudo chown -R www-data:www-data var/
  sudo chmod -R 775 var/
```

---

## 📊 Monitoring

### Logs à Surveiller

```bash
# Erreurs application
tail -f var/log/prod.log

# Erreurs web server
tail -f /var/log/nginx/ecole_error.log

# Requêtes web
tail -f /var/log/nginx/ecole_access.log

# PHP errors
tail -f /var/log/php-errors.log
```

### Commandes Utiles

```bash
# Santé application
php bin/console about

# Vérifier config
php bin/console debug:config doctrine

# Lister routes
php bin/console debug:router

# Environ vars
php bin/console debug:dotenv

# Vérifier sécurité
php bin/console security:check

# Stats DB
php bin/console doctrine:query:dql "SELECT COUNT(e) FROM App\Entity\Evenement e"
```

