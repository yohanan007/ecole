# Docker Simplification - Symfony + MySQL + PhpMyAdmin

## 📋 Résumé des Changements

Votre Docker a été simplifié pour ne contenir que **3 services**:

### Services inclus:
1. **MySQL 8.0** - Base de données
2. **PhpMyAdmin** - Interface d'administration MySQL
3. **Symfony** - Application Symfony avec serveur intégré

### Services supprimés:
- ❌ Nginx (remplacé par le serveur Symfony intégré)
- ❌ PHP-FPM (remplacé par PHP CLI)
- ❌ Supervisor
- ❌ Redis et Mailhog (optionnels - peuvent être réajoutés si nécessaire)

## 🚀 Comment Démarrer

### 1. Créer le fichier `.env` à la racine:

```bash
APP_ENV=dev
APP_DEBUG=1
APP_SECRET=ChangeMe!SecureRandomString123

DATABASE_NAME=ecole_dev
DATABASE_USER=ecole
DATABASE_PASSWORD=ecole
DATABASE_ROOT_PASSWORD=root

MAILER_DSN=smtp://localhost:1025
MESSENGER_TRANSPORT_DSN=sync://
```

### 2. Démarrer les conteneurs:

```bash
# Production
docker-compose up -d

# Développement (avec volumes et hot-reload)
docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d
```

### 3. Accéder à votre application:

| Service | URL | Notes |
|---------|-----|-------|
| **Symfony** | http://localhost:8000 | Serveur de développement |
| **PhpMyAdmin** | http://localhost:8080 | Admin MySQL (user: ecole / pass: ecole) |
| **MySQL** | localhost:3306 | Port direct pour clients MySQL |

## 📝 Ports utilisés

```
MySQL:       3306
Symfony:     8000  (au lieu de 80)
PhpMyAdmin:  8080  (au lieu de 80)
```

## 🔧 Commandes Utiles

### Démarrer les conteneurs:
```bash
docker-compose up -d
```

### Arrêter:
```bash
docker-compose down
```

### Voir les logs:
```bash
docker-compose logs -f app
docker-compose logs -f database
```

### Accéder au shell du conteneur Symfony:
```bash
docker-compose exec app bash
```

### Accéder au conteneur MySQL:
```bash
docker-compose exec database mysql -u ecole -p ecole
```

### Exécuter une commande Symfony:
```bash
docker-compose exec app symfony console make:migration
docker-compose exec app symfony console doctrine:migrations:migrate
```

## 📦 Construction de l'image

Si vous modifiez le `Dockerfile`, reconstruisez l'image:

```bash
docker-compose build --no-cache
docker-compose up -d
```

## ⚙️ Configuration Symfony

Le serveur Symfony écoute sur `0.0.0.0:8000` dans le conteneur, accessible depuis votre machine sur `http://localhost:8000`.

### Variable d'environnement DATABASE_URL:
```
mysql://ecole:ecole@database:3306/ecole_dev?serverVersion=8.0&charset=utf8mb4
```

## 🔄 Volumes

Les volumes suivants sont créés/utilisés:

- **mysql_data**: Persistance de la base de données MySQL
- **./src**: Code source monté en live dans le conteneur (développement)

## 📌 Notes Importantes

1. **Serveur de développement**: Symfony utilise son serveur intégré qui recharge automatiquement les changements
2. **Assets**: Les assets sont compilés pendant la construction de l'image (npm run build)
3. **Pas d'Nginx**: Le routage se fait directement via Symfony
4. **Santé des services**: Des health checks ont été configurés pour tous les services

## 🐛 Dépannage

### Connexion à MySQL refusée:
```bash
# Vérifier que le conteneur database est en bonne santé
docker-compose ps
```

### PhpMyAdmin inaccessible:
- Vérifier que MySQL est démarré d'abord
- Vérifier les credentials dans `.env`

### Port déjà en utilisation:
Modifier les ports dans `docker-compose.yml` ou `docker-compose.override.yml`

---

**Configuration simplifiée et optimisée! 🎉**
