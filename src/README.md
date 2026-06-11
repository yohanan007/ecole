# 📚 Système de Gestion d'Agenda Scolaire

Plateforme complète de gestion des rendez-vous (RDV) pour les écoles et nounous. Permet aux administrateurs de créer et gérer des événements, et aux utilisateurs de consulter leur calendrier personnel avec une interface basée sur FullCalendar.

**Version:** 1.0.0  
**Framework:** Symfony 6.4  
**PHP:** 8.0+  
**Base de données:** Doctrine ORM

## 📋 Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Architecture](#architecture)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [API Routes](#api-routes)
- [Entités](#entités)
- [Formulaires](#formulaires)
- [Sécurité](#sécurité)
- [Contribution](#contribution)

---

## 🎯 Fonctionnalités

### Gestion des Administrateurs
- ✅ Création et édition des comptes administrateur
- ✅ Liaison avec entité User pour l'authentification
- ✅ Suivi du créateur pour chaque événement
- ✅ Dashboard avec statistiques

### Gestion des Événements/RDV
- ✅ Création d'événements avec sujet, description, lieu
- ✅ Support de la récurrence (quotidien, hebdomadaire, bi-hebdomadaire, mensuel)
- ✅ Durée configurable en minutes
- ✅ Gestion des participants (élèves/utilisateurs)
- ✅ Modification et suppression d'événements

### Validation des Élèves
- ✅ Système de validation obligatoire avant inscription
- ✅ Suivi de l'admin validateur et date de validation
- ✅ Seuls les élèves validés apparaissent dans les événements
- ✅ Gestion simplifiée des validations en attente

### Calendrier
- ✅ Vue mensuelle, hebdomadaire, quotidienne (FullCalendar)
- ✅ Navigation fluide entre les périodes
- ✅ Jours fériés français surlignés
- ✅ Clic sur événement → détails complets

### Agenda Personnel
- ✅ Vue personnalisée de l'agenda de chaque utilisateur
- ✅ Filtrage par date et récurrence
- ✅ Liste des prochains RDV en sidebar
- ✅ Statistiques mensuelles et hebdomadaires

---

## 🏗️ Architecture

### Hiérarchie des Dossiers

```
src/
├── Controller/           # Contrôleurs (AdminController, AgendaController)
├── Entity/              # Entités Doctrine (Admin, Eleve, Evenement, Agenda, User)
├── Repository/          # Requêtes personnalisées (EvenementRepository, EleveRepository)
├── Form/                # Types de formulaires Symfony
├── Service/             # Logique métier (AgendaGenerator, EventManager)
├── Security/            # Voters et contrôle d'accès (EvenementVoter)
├── Command/             # Commandes CLI
├── templates/           # Templates Twig
│   ├── agenda/          # Templates calendrier (index, create, show, edit, my_agenda)
│   ├── admin/           # Templates adminPanel
│   ├── base.html.twig   # Mise en page générale
│   └── ...
├── assets/              # Fichiers JavaScript et CSS
│   ├── agenda/          # Scripts calendrier (action.js, create.js, etc.)
│   ├── utilitaire/      # Classes utilitaires (Agenda, Element, Erreur)
│   ├── styles/          # Feuilles de style SCSS/CSS
│   └── controllers/     # Stimulus controllers (optionnel)
├── migrations/          # Migrations version de base de données
└── config/
    ├── packages/        # Configuration des bundles
    ├── routes/          # Définition des routes
    └── services.yaml    # Configuration des services

doctrine/
npm_modules/            # Dépendances Node.js (Webpack, FullCalendar, Choices.js)
public/
├── index.php            # Point d'entrée
├── build/               # Assets compilés (Webpack Encore)
└── bundles/             # Assets des bundles Symfony
```

### Flux de Données

```
User Request
    ↓
Router (config/routes/)
    ↓
Controller (AdminController, AgendaController)
    ↓
Service (AgendaGenerator, EventManager)
    ↓
Repository (EvenementRepository, EleveRepository)
    ↓
Entity (Evenement, Agenda, User, Eleve)
    ↓
Database (MySQL/PostgreSQL)
    ↓
Response (JSON/HTML via Twig)
    ↓
FullCalendar / Choices.js / Browser
```

---

## 🚀 Installation

### Prérequis
- PHP 8.0+
- Composer
- Node.js 14+ et npm
- MySQL 5.7+ ou PostgreSQL 10+
- Symfony CLI (optionnel)

### Étapes d'installation

```bash
# 1. Cloner le dépôt
git clone <repository-url>
cd src

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances Node.js
npm install

# 4. Configurer l'environnement
cp .env.example .env
# Éditer .env avec vos credentials DB

# 5. Créer la base de données
php bin/console doctrine:database:create

# 6. Exécuter les migrations
php bin/console doctrine:migrations:migrate

# 7. Compiler les assets
npm run dev
# ou pour la production
npm run build

# 8. Créer un utilisateur admin
php bin/console app:create-admin

# 9. Lancer le serveur
symfony server:start
# ou
php -S localhost:8000 -t public
```

---

## ⚙️ Configuration

### Variables d'environnement (.env)

```bash
# Base de données
DATABASE_URL="mysql://user:password@localhost:3306/ecole"

# Mailer
MAILER_DSN="smtp://localhost:1025"

# Sécurité
MESSENGER_TRANSPORT_DSN="sync://"

# App
APP_ENV=dev
APP_SECRET=your_secret_key
```

### Configuration des Bundles

#### SecurityBundle (config/packages/security.yaml)
```yaml
security:
  role_hierarchy:
    ROLE_ADMIN: ROLE_USER
    ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
  
  access_control:
    - { path: ^/admin, role: ROLE_ADMIN }
    - { path: ^/agenda, role: ROLE_USER }
```

#### DoctrineBundle (config/packages/doctrine.yaml)
```yaml
doctrine:
  dbal:
    # Configuration DB automatique depuis DATABASE_URL
  orm:
    auto_generate_proxy_classes: true
    naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
    quote_strategy: doctrine.orm.quote_strategy.back_slashes
```

---

## 📖 Utilisation

### Pour les Administrateurs

#### 1. Créer un RDV

```bash
URL: POST /agenda/create
```

1. Accédez à `/agenda/create`
2. Remplissez le formulaire :
   - **Sujet** : titre du RDV
   - **Description** : détails complets
   - **Lieu** : localisation
   - **Date/Heure** : début du RDV
   - **Durée** : en minutes (ex: 60)
   - **Récurrence** : mensuel, hebdomadaire, etc.
   - **Classe/Élèves** : participants via Choices.js multiselect
3. Cliquez "Créer l'événement"

#### 2. Modifier un RDV

```bash
URL: GET/POST /agenda/{id}/edit
```

- Seul l'admin créateur peut éditer
- L'historique est conservé via `createdAt` et `adminCreateur`

#### 3. Supprimer un RDV

```bash
URL: POST /agenda/{id}/delete
```

- Requête POST avec CSRF token obligatoire
- Confirmation via popup JavaScript

#### 4. Consulter le calendrier

```bash
URL: GET /agenda/
```

- Vue calendrier complète
- Jours fériés français surlignés
- Clic sur événement → page de détails

### Pour les Utilisateurs

#### 1. Accéder à son agenda personnel

```bash
URL: GET /agenda/me
```

- Calendar personnel avec vos RDV uniquement
- Statistiques (total, ce mois, prochains 7j)
- Sidebar avec liste des prochains RDV

#### 2. Voir les détails d'un RDV

```bash
URL: GET /agenda/{id}
```

- Infos complètes : sujet, lieu, description, durée
- Créateur du RDV
- Liste des participants
- Date/heure exacte

---

## 🔌 API Routes

### Agenda Routes

| Méthode | Route | Nom | Authentification | Rôle |
|---------|-------|------|-------------------|------|
| GET | `/agenda/` | `app_agenda` | ✅ | ROLE_USER |
| GET | `/agenda/me` | `app_agenda_me` | ✅ | ROLE_USER |
| GET/POST | `/agenda/create` | `app_agenda_create` | ✅ | ROLE_ADMIN |
| GET | `/agenda/{id}` | `app_agenda_show` | ✅ | ROLE_USER |
| GET/POST | `/agenda/{id}/edit` | `app_agenda_edit` | ✅ | ROLE_ADMIN |
| POST | `/agenda/{id}/delete` | `app_agenda_delete` | ✅ | ROLE_ADMIN |
| GET | `/agenda/api/upcoming` | `app_agenda_api_upcoming` | ✅ | ROLE_USER |
| POST | `/agenda/api/create` | `app_agenda_api_create` | ✅ | ROLE_ADMIN |

### Admin Routes (si existantes)

| Méthode | Route | Nom | Rôle |
|---------|-------|------|------|
| GET | `/admin/dashboard` | `app_admin_dashboard` | ROLE_ADMIN |
| GET/POST | `/admin/create` | `app_admin_create` | ROLE_ADMIN |
| GET | `/admin/list` | `app_admin_list` | ROLE_ADMIN |
| GET/POST | `/admin/{id}/edit` | `app_admin_edit` | ROLE_ADMIN |
| POST | `/admin/{id}/delete` | `app_admin_delete` | ROLE_ADMIN |

### Validation d'Élèves

| Méthode | Route | Rôle |
|---------|-------|------|
| GET | `/admin/eleves/pending` | ROLE_ADMIN |
| POST | `/admin/eleves/{id}/validate` | ROLE_ADMIN |

---

## 📊 Entités

### User
Entité core Symfony Security

```php
class User
{
    private ?int $id = null;
    private ?string $email = null;
    private array $roles = ['ROLE_USER'];
    private ?string $password = null;
    private ?string $nom = null;
    private ?string $prenom = null;
}
```

### Admin
Gestionnaire d'événements

```php
class Admin
{
    private ?int $id = null;
    private ?User $user = null;                    // One-to-One
    private ?string $nom = null;
    private ?string $prenom = null;
    private ?string $telephone = null;
    private bool $isActive = true;
    private Collection $evenementsCreated;         // One-to-Many
}
```

### Eleve (Étudiant)
Participant aux événements

```php
class Eleve
{
    private ?int $id = null;
    private ?User $user = null;
    private ?string $nom = null;
    private ?string $prenom = null;
    private bool $isValidated = false;             // Validation obligatoire
    private ?Admin $validatedByAdmin = null;       // Qui a validé
    private ?\DateTimeInterface $validatedAt = null;
}
```

### Evenement
Rendez-vous/Événement

```php
class Evenement
{
    private ?int $id = null;
    private ?string $sujet = null;
    private ?string $corps = null;
    private ?string $lieu = null;
    private ?int $duree = null;                    // en minutes
    private ?string $recurrence = 'aucune';        // jour|semaine|deuxSemaines|mois
    private ?Agenda $agenda = null;                // Many-to-One
    private Collection $users = [];                // Many-to-Many
    private ?Admin $adminCreateur = null;          // Many-to-One
    private ?\DateTimeInterface $createdAt = null;
}
```

### Agenda
Document de planification

```php
class Agenda
{
    private ?int $id = null;
    private ?\DateTimeInterface $heureDebut = null;
    private ?\DateTimeInterface $heureFinReccurence = null;
    private Collection $evenements = [];           // One-to-Many
}
```

### Classe
Groupement d'élèves

```php
class Classe
{
    private ?int $id = null;
    private ?string $nom = null;
    private Collection $eleves = [];
}
```

---

## 📝 Formulaires

### EvenementType

```php
// Champs disponibles
- sujet (TextType) - requis
- corps (TextareaType) - optionnel
- lieu (TextType) - optionnel
- duree (IntegerType) - optionnel
- recurrence (ChoiceType) - aucune|jour|semaine|deuxSemaines|mois
```

**Utilisation :**
```twig
{{ form_start(form) }}
  {{ form_widget(form.sujet) }}
  {{ form_widget(form.corps) }}
  {{ form_widget(form.recurrence) }}
{{ form_end(form) }}
```

### AdminType

Gestion des administrateurs avec User embedde

### EleveType

Gestion des élèves avec validation

### EleveValidationType

Formulaire simple pour valider/rejeter les élèves

---

## 🔒 Sécurité

### Authentification
- UserInterface de Symfony Security
- PasswordAuthenticatedUserInterface
- Hash de mots de passe via symfony/security

### Autorisation (Voters)

#### EvenementVoter
```php
// Décisions de vote
- VIEW: Accessible à tout utilisateur authentifié
- EDIT: Seulement l'admin créateur
- DELETE: Seulement l'admin créateur
```

**Utilisation :**
```php
#[IsGranted('EVENEMENT_EDIT', 'evenement')]
public function edit(Evenement $evenement) { ... }
```

### CSRF Protection
- Jetons CSRF sur tous les formulaires
- Vérifié automatiquement par Symfony Form
- POST /agenda/{id}/delete requiert CSRF token

### Validation d'Élèves
**Règle métier :** 
> Un élève ne peut participer à un événement que s'il est validé par un admin

```php
// Dans AgendaController.create()
if ($eleve->isValidated() && $eleve->getUser()) {
    $evenement->addUser($eleve->getUser());
}
```

### Rôles

```
ROLE_USER          - Utilisateur standard
ROLE_ADMIN         - Gestionnaire d'événements
ROLE_SUPER_ADMIN   - Accès complet
ROLE_PARENT        - Parent d'élève (optionnel)
ROLE_ENFANT        - Enfant/Élève (optionnel)
```

---

## 🛠️ Services

### AgendaGenerator

Service de génération d'agenda

```php
public function NextDateAgenda($monthsToGenerate = 7): array
public function nextItemByRecurrence($recurrence, $dateInit): array
public function getAgendaForUser(User $user): array
public function getNextAvailableSlots(User $user, $daysAhead = 30): array
public function isUserAvailable(User $user, DateTime $dateTime): bool
public function getStatistics(): array
```

### EventManager

Logique métier avancée pour événements

```php
public function createEvent(...): Evenement
public function addEleveToEvent($evenement, $eleve): void
public function removeEleveFromEvent($evenement, $eleve): void
public function duplicateEvent($originalEvent, $newStartDate): Evenement
public function hasTimeConflict($evenement, $startTime, $durationMinutes): bool
public function deleteEvent($evenement): void
public function getEventStats($evenement): array
```

---

## 📱 Frontend

### FullCalendar Integration

Calendrier interactif avec:
- Vue mensuelle/hebdomadaire/quotidienne
- Navigation fluide entre périodes
- Jours fériés français surlignés
- Clic sur événement → navigation vers détails
- Boutons personnalisés (Créer, Mon agenda)

**Localisation :** French (fr)
**Hauteur :** 600px
**Aspect ratio :** 1:1

### Choices.js Multiselect

Sélection avancée des Classes et Élèves dans le formulaire de création

**Caractéristiques :**
- Recherche textuelle
- Sélection multiple
- Suppression via bouton
- Placeholder personnalisé

### Assets Compilés

Utilise Webpack Encore pour bundling

```bash
# Développement
npm run dev

# Production
npm run build

# Watch
npm run watch
```

---

## 🧪 Tests (À implémenter)

### Structure recommandée

```bash
tests/
├── Unit/
│   ├── Service/
│   │   ├── AgendaGeneratorTest.php
│   │   └── EventManagerTest.php
│   └── Entity/
│       └── EvenementTest.php
├── Functional/
│   ├── Controller/
│   │   ├── AgendaControllerTest.php
│   │   └── AdminControllerTest.php
│   └── Security/
│       └── EvenementVoterTest.php
└── Integration/
    └── AgendaIntegrationTest.php
```

### Lancer les tests
```bash
php bin/phpunit
php bin/phpunit tests/Unit/
php bin/phpunit --coverage-html=coverage
```

---

## 📦 Dépendances Principales

### Backend
- **symfony/framework-bundle** v6.4 - Framework core
- **doctrine/orm** v2.12 - ORM
- **api-platform/core** v2.7 - REST API support
- **symfony/form** - Formulaires
- **symfony/security** - Authentification/Autorisation
- **symfony/mailer** - Envoi d'emails

### Frontend
- **@fullcalendar/core** - Calendrier
- **@fullcalendar/daygrid** - Vue calendrier
- **@fullcalendar/interaction** - Interactions souris
- **choices.js** - Multiselect avancé
- **bootstrap@5** - Responsive design
- **webpack-encore** - Asset bundling

---

## 📚 Conventions de Code

### Nommage

**Classes:** PascalCase (ex: `EvenementController`)  
**Méthodes:** camelCase (ex: `getAgendaForUser()`)  
**Constantes:** UPPER_SNAKE_CASE (ex: `RECURRENCE_JOUR`)  
**Routes:** snake_case (ex: `app_agenda_create`)  
**Templates Twig:** snake_case (ex: `agenda/create.html.twig`)

### Types Hints

Tous les paramètres et retours doivent avoir des type hints

```php
public function create(Request $request, EntityManagerInterface $em): Response
```

### Attributes Doctrine

Utiliser les attributs PHP 8 au lieu des annotations

```php
#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement { ... }
```

### IsGranted

Utiliser décorateurs pour l'autorisation

```php
#[IsGranted('ROLE_ADMIN')]
#[IsGranted('EVENEMENT_EDIT', 'evenement')]
public function edit(Evenement $evenement): Response { ... }
```

---

## 🔄 Workflow de Contribution

1. Fork le dépôt
2. Créer une branche feature (`git checkout -b feature/amazing-feature`)
3. Commit les changements (`git commit -m 'Add amazing feature'`)
4. Push la branche (`git push origin feature/amazing-feature`)
5. Ouvrir une Pull Request

### Checklist avant PR
- ✅ Code testé localement
- ✅ Conventions respectées
- ✅ PHPStan passing (`composer phpstan`)
- ✅ Migrations créées si changements DB
- ✅ Frontend compilé (`npm run build`)
- ✅ Documentation mise à jour

---

## 📄 Licence

Proprietary - Tous droits réservés

---

## 📞 Support

Pour toute question ou issue:
- Consulter la documentation
- Vérifier les logs: `var/log/`
- Contacter l'équipe de développement

---

## 🗺️ Roadmap Futures Améliorations

- [ ] Notifications email pour nouveaux RDV
- [ ] Synchronisation Google Calendar
- [ ] Récurrences plus complexes (ex: 2e mercredi du mois)
- [ ] Importation CSV d'élèves
- [ ] Export iCalendar
- [ ] Tests complets (Unit + Functional)
- [ ] API GraphQL (en plus de REST)
- [ ] Progressive Web App (PWA)
- [ ] Internationalisation (i18n)
- [ ] Dashboard analytics avancé
