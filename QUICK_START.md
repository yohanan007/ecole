# 🚀 Guide de Démarrage Rapide

## ⚙️ Prérequis

- **Docker Desktop** (Windows/Mac) ou **Docker + Docker Compose** (Linux)
- **Make** (optionnel, pour utiliser le Makefile)
- **Git**

**Vérifier l'installation:**
```bash
docker --version
docker-compose --version
make --version  # optionnel
```

---

## 🎯 Installation (3 minutes)

### 1️⃣ Cloner le projet
```bash
git clone <repo-url>
cd ecole
```

### 2️⃣ Configurer l'environnement
```bash
# Copier le fichier .env.example en .env
cp .env.example .env

# Les variables par défaut conviennent pour le développement
# Pour changer quelque chose, éditer .env
cat .env
```

### 3️⃣ Démarrer les containers (une seule commande!)
```bash
# Avec Make (recommandé)
make install

# OU sans Make
docker-compose -f docker-compose.yml -f docker-compose.override.yml build
docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec app composer install
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec app npm install
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec app symfony console doctrine:migrations:migrate
```

### 4️⃣ Charger les données de test (optionnel)
```bash
make db-seed
# OU
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec app \
  symfony console doctrine:fixtures:load --no-interaction
```

---

## 🌐 Accéder à l'Application

| Service | URL | Description |
|---------|-----|-------------|
| **Symfony App** | http://localhost:8000 | Serveur développement |
| **PHPMyAdmin** | http://localhost:8080 | Gestion BD MySQL |
| **MySQL** | localhost:3306 | Base de données |

---

## 📝 Commandes Utiles

### Avec Make (Simple) ✨
```bash
make help                # Liste toutes les commandes
make logs               # Voir les logs
make logs-app          # Logs Symfony seulement
make bash              # Entrer dans le container Symfony
make db-migrate        # Exécuter les migrations
make db-create         # Créer la base de données
make test              # Lancer les tests
make clear-cache       # Vider le cache
make npm-dev           # Watch des assets (dev)
make npm-build         # Compiler les assets (prod)
```

### Sans Make
```bash
# Containers
docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d
docker-compose -f docker-compose.yml -f docker-compose.override.yml down
docker-compose -f docker-compose.yml -f docker-compose.override.yml logs -f

# Entre dans Symfony
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec app bash

# Symfony Console
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec app symfony console

# Tests
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec app php bin/phpunit
```

---

## 📂 Structure du Projet

```
ecole/
├── src/                          # Code Symfony
│   ├── src/                      # Logique métier
│   ├── config/                   # Configuration Symfony
│   ├── templates/                # Twig templates
│   ├── public/                   # Point d'entrée web
│   └── tests/                    # Tests
├── docker-compose.yml            # Configuration Docker
├── docker-compose.override.yml   # Override développement
├── php/Dockerfile                # Docker image (Symfony)
├── database/                     # Scripts BD
├── Makefile                      # Commandes utiles
├── QUICK_START.md               # Ce fichier
├── DOCKER_SIMPLIFICATION.md     # Détails Docker
└── .env.example                 # Variables d'environnement
```

### 📚 Voir aussi:
- [DOCKER_SIMPLIFICATION.md](./DOCKER_SIMPLIFICATION.md) - Configuration Docker détaillée
- [ARCHITECTURE_REFACTORED.md](./ARCHITECTURE_REFACTORED.md) - Architecture du projet
- [src/README.md](./src/README.md) - Documentation app
- [src/SETUP.md](./src/SETUP.md) - Setup détaillé

---

## 🐛 Troubleshooting

### Container ne démarre pas
```bash
# Voir les erreurs
docker-compose -f docker-compose.yml -f docker-compose.override.yml logs -f app

# Reconstruire les images
docker-compose -f docker-compose.yml -f docker-compose.override.yml build --no-cache
```

### Port déjà utilisé
```bash
# Voir quel processus utilise le port (Linux/Mac)
lsof -i :8000     # ou :3306, :8080, etc.

# Sur Windows, modifier docker-compose.override.yml
# Changer les ports : "9000:8000" au lieu de "8000:8000"
```

### Base de données ne se crée pas
```bash
# Vérifier que MySQL est prêt
docker-compose -f docker-compose.yml -f docker-compose.override.yml logs database

# Essayer de se connecter manuellement
make mysql
```

### Erreur de permission sur les fichiers
```bash
# Donner les permissions au container
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec app \
  chmod -R 777 var/
```

### Node modules / Vendor pas à jour
```bash
# Nettoyer et réinstaller
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec app bash
composer install
npm install
exit
```

---

## ✅ Vérification

Après le démarrage, vérifier que tout fonctionne:

```bash
# 1. Containers sont actifs
docker-compose -f docker-compose.yml -f docker-compose.override.yml ps

# 2. Symfony répond
curl http://localhost:8000

# 3. PhpMyAdmin accessible
open http://localhost:8080

# 4. DB créée et migrée
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec app \
  symfony console doctrine:migrations:status
```

🎉 **Tout est prêt!**

# Créer manuellement
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec php-fpm \
  php bin/console doctrine:database:create
```

### Permission denied sur var/
```bash
# Donner les permissions
docker-compose -f docker-compose.yml -f docker-compose.override.yml exec php-fpm \
  chmod -R 777 var/
```

---

## 🛑 Arrêter l'Application

```bash
# Arrêter les containers (garde les données)
make down
# OU
docker-compose -f docker-compose.yml -f docker-compose.override.yml down

# Arrêter et SUPPRIMER les données
make clean-deep
# OU
docker-compose -f docker-compose.yml -f docker-compose.override.yml down -v
```

---

## 📊 Environment Variables Importantes

| Variable | Valeur Par Défaut | Description |
|----------|-------------------|-------------|
| `APP_ENV` | `dev` | Environnement (dev/prod) |
| `APP_DEBUG` | `1` | Mode debug activé |
| `DATABASE_NAME` | `ecole` | Nom de la DB |
| `DATABASE_USER` | `ecole` | Utilisateur DB |
| `DATABASE_PASSWORD` | `ecole` | Mot de passe DB |
| `MAILER_DSN` | `smtp://mailhog:1025` | Serveur email |

**Voir [.env.example](./.env.example) pour toutes les variables**

---

## 🚀 Next Steps

1. ✅ Lire [ARCHITECTURE_REFACTORED.md](./ARCHITECTURE_REFACTORED.md)
2. ✅ Explorer `src/src/` pour comprendre la structure
3. ✅ Lire [src/README.md](./src/README.md) pour la documentation
4. ✅ Lancer les tests: `make test`
5. ✅ Commencer à développer! 🎉

---

## 💡 Tips

- Utiliser `make` pour les commandes courantes (plus rapide et lisible)
- Pour le développement, garder les logs en tab séparé: `make logs`
- Watch les assets automatiquement: `make npm-dev`
- Consulter les logs si quelque chose ne fonctionne pas
- Les emails de test arrivent dans Mailhog (http://localhost:8025)

---

**Besoin d'aide?**
- Voir [src/SETUP.md](./src/SETUP.md) pour le setup détaillé
- Voir [ARCHITECTURE_REFACTORED.md](./ARCHITECTURE_REFACTORED.md) pour l'architecture
- Consulter les logs: `make logs`
