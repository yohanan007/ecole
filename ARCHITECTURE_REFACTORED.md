# 📁 Architecture du Projet - Structure Réorganisée

## 🎯 Principes

La structure du projet suit les principes de **Domain-Driven Design (DDD)** et une **Clean Architecture** pour une meilleure maintenabilité et scalabilité.

---

## 📊 Structure Complète

```
src/
├── src/                           # Code métier
│   ├── Application/               # Logique métier (Use Cases)
│   │   ├── UseCases/
│   │   │   ├── Agenda/            # Gestion agenda (créer, modifier, lister)
│   │   │   ├── Eleve/             # Gestion élèves
│   │   │   ├── Validation/        # Validation des élèves
│   │   │   └── ...
│   │   ├── DTO/                   # Data Transfer Objects
│   │   ├── Service/               # Services applicatifs
│   │   └── EventHandler/          # Gestion des événements
│   │
│   ├── Domain/                    # Entités métier (logique pure)
│   │   ├── Entity/                # Entités (Agenda, Eleve, User)
│   │   ├── Repository/            # Interfaces repositories
│   │   ├── Event/                 # Domain Events
│   │   ├── Value/                 # Value Objects
│   │   ├── Specification/         # Spécifications métier
│   │   └── Exception/             # Exceptions métier
│   │
│   ├── Infrastructure/            # Implémentation technique
│   │   ├── Repository/            # Implémentation des repositories
│   │   ├── Persistence/           # ORM, migrations
│   │   ├── Logger/                # Logging
│   │   ├── Bus/                   # Command/Query Bus
│   │   └── Cache/                 # Caching
│   │
│   ├── Interface/                 # Points d'entrée (Controllers, CLI)
│   │   ├── Api/
│   │   │   ├── Controller/        # API REST controllers (API Platform)
│   │   │   ├── Request/           # DTOs pour API
│   │   │   └── Response/          # DTOs pour réponses
│   │   ├── Web/
│   │   │   ├── Controller/        # Controllers Symfony classiques
│   │   │   └── Form/              # FormTypes Symfony
│   │   ├── Cli/                   # Commands Symfony
│   │   └── EventListener/         # Listeners d'événements
│   │
│   ├── Shared/                    # Code partagé
│   │   ├── Exception/             # Exceptions globales
│   │   ├── Traits/                # Traits réutilisables
│   │   ├── Constants/             # Constantes globales
│   │   └── Utils/                 # Utilitaires
│   │
│   ├── Security/                  # Authentification & Autorisation
│   ├── Kernel.php                 # Noyau Symfony
│   └── ...
│
├── config/                        # Configuration Symfony
│   ├── packages/                  # Configuration par package
│   ├── routes/                    # Routes
│   ├── services.yaml              # Services DI
│   └── ...
│
├── templates/                     # Templates Twig
│   ├── agenda/
│   ├── eleve/
│   ├── layout/
│   └── ...
│
├── assets/                        # Ressources frontend (JS, CSS)
│   ├── app.js
│   ├── styles/
│   ├── controllers/               # Stimulus controllers
│   └── ...
│
├── public/                        # Point d'entrée web
│   ├── index.php
│   └── build/                     # Assets compilés
│
├── tests/                         # Tests unitaires et fonctionnels
│   ├── Unit/                      # Tests unitaires
│   ├── Integration/               # Tests d'intégration
│   ├── Functional/                # Tests fonctionnels
│   └── ...
│
├── bin/                           # Exécutables
│   ├── console                    # CLI Symfony
│   └── phpunit
│
├── migrations/                    # Migrations Doctrine
├── var/                           # Fichiers générés (logs, cache)
├── vendor/                        # Dépendances Composer
└── ...
```

---

## 🔄 Flux de Données

```
Client HTTP/CLI
    ↓
Interface Layer (Controller/Command)
    ↓
Application Layer (UseCase/Service)
    ↓
Domain Layer (Entity/Repository)
    ↓
Infrastructure Layer (ORM, Cache, Logger)
    ↓
Database/External Services
```

---

## 📝 Conventions de Nommage

### Classes
- **UseCase**: `CreateAgendaUseCase`, `UpdateEleveUseCase`
- **Service**: `AgendaService`, `ValidationService`
- **Repository**: `AgendaRepositoryInterface`, `DoctrineAgendaRepository`
- **DTO**: `CreateAgendaRequest`, `AgendaResponse`
- **Entity**: `Agenda`, `Eleve`, `User`
- **Controller API**: `AgendaController` (dans `Interface\Api\Controller`)
- **Controller Web**: `AgendaController` (dans `Interface\Web\Controller`)

### Dossiers
- Pluriel pour collections: `UseCases/`, `Repositories/`, `DTOs/`
- Singulier pour concepts: `Entity/`, `Repository/`, `Service/`

---

## 🚀 Créer une Nouvelle Feature

### 1. Définir l'Entité (Domain Layer)
```
src/Domain/Entity/MonEntite.php
```

### 2. Créer le Repository Interface (Domain)
```
src/Domain/Repository/MonEntiteRepositoryInterface.php
```

### 3. Implémenter le Repository (Infrastructure)
```
src/Infrastructure/Repository/DoctrineMonEntiteRepository.php
```

### 4. Créer le UseCase (Application)
```
src/Application/UseCases/MonFeature/CreateMonEntiteUseCase.php
```

### 5. Créer le DTO (Application)
```
src/Application/DTO/CreateMonEntiteRequest.php
src/Application/DTO/MonEntiteResponse.php
```

### 6. Créer le Controller API (Interface)
```
src/Interface/Api/Controller/MonEntiteController.php
```

### 7. Enregistrer les Services (config/services.yaml)
```yaml
services:
  App\Application\UseCases\MonFeature\CreateMonEntiteUseCase:
    arguments:
      - '@App\Domain\Repository\MonEntiteRepositoryInterface'

  App\Domain\Repository\MonEntiteRepositoryInterface:
    class: App\Infrastructure\Repository\DoctrineMonEntiteRepository
```

### 8. Créer les Tests
```
tests/Unit/Application/UseCases/MonFeature/
tests/Functional/Api/MonEntiteControllerTest.php
```

---

## 🔧 Configuration Services (DI)

### Enregistrement Automatique (Recommandé)
```yaml
# config/services.yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true

  App\:
    resource: '../src/'
    exclude:
      - '../src/Domain/Exception'
```

### Enregistrement Manuel (Pour les Interfaces)
```yaml
services:
  App\Domain\Repository\AgendaRepositoryInterface:
    class: App\Infrastructure\Repository\DoctrineAgendaRepository
```

---

## 📚 Avantages de cette Structure

✅ **Séparation des concerns**: Chaque couche a une responsabilité claire  
✅ **Testabilité**: Les use cases peuvent être testés sans dépendances externes  
✅ **Maintenabilité**: Facile de naviguer et comprendre le code  
✅ **Scalabilité**: Ajout de nouvelles features sans modifier le code existant  
✅ **Flexibilité**: Facile de changer d'ORM ou de framework  
✅ **Réutilisabilité**: Services/Repositories utilisables par plusieurs controllers  

---

## 🐳 Docker

### Développement
```bash
docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d
```

### Production
```bash
docker-compose up -d
```

### Voir les logs
```bash
docker-compose logs -f php-fpm
docker-compose logs -f nginx
```

---

## 📖 Ressources

- [Domain-Driven Design](https://en.wikipedia.org/wiki/Domain-driven_design)
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)
- [Symfony Best Practices](https://symfony.com/doc/current/best_practices.html)
