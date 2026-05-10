# Architecture du Système de Gestion d'Agenda

## Vue d'ensemble

Ce document décrit l'architecture technique du système de gestion d'agenda scolaire, incluant les couches, les patterns utilisés et les flux de données.

---

## 1. Architecture en Couches

Le projet suit une architecture classique Symfony en couches:

```
┌─────────────────────────────────────┐
│   Présentation (Frontend)           │
│   - Twig Templates                  │
│   - FullCalendar / Choices.js      │
│   - Bootstrap 5                     │
└──────────────┬──────────────────────┘
               │ HTTP/Request
┌──────────────▼──────────────────────┐
│   Web Layer (Controllers)           │
│   - AgendaController                │
│   - AdminController                 │
│   - Routing                         │
└──────────────┬──────────────────────┘
               │ Method Call
┌──────────────▼──────────────────────┐
│   Business Logic Layer (Services)   │
│   - AgendaGenerator                 │
│   - EventManager                    │
│   - Validators                      │
└──────────────┬──────────────────────┘
               │ Method Call
┌──────────────▼──────────────────────┐
│   Data Access Layer (Repositories)  │
│   - EvenementRepository             │
│   - EleveRepository                 │
│   - AdminRepository                 │
└──────────────┬──────────────────────┘
               │ Query/DQL
┌──────────────▼──────────────────────┐
│   Database Layer                    │
│   - Doctrine ORM                    │
│   - MySQL/PostgreSQL                │
└─────────────────────────────────────┘
```

---

## 2. Patterns de Conception

### 2.1 Model-View-Controller (MVC)

L'application suit le pattern MVC standard Symfony:

- **Model:** Entités Doctrine (User, Admin, Eleve, Evenement, Agenda)
- **View:** Templates Twig dans `templates/`
- **Controller:** Classes dans `src/Controller/`

### 2.2 Repository Pattern

Encapsule la logique de requête DB:

```php
// Utilisation
$evenements = $this->evenementRepository->findUpcoming();

// Implémentation
class EvenementRepository extends ServiceEntityRepository
{
    public function findUpcoming(): array { ... }
}
```

### 2.3 Service Layer

Logique métier complexe isolée:

```php
// AgendaGenerator.php
class AgendaGenerator {
    public function NextDateAgenda($monthsToGenerate = 7): array
    public function getAgendaForUser(User $user): array
}

// EventManager.php
class EventManager {
    public function createEvent(...): Evenement
    public function duplicateEvent(...): Evenement
}
```

### 2.4 Voter Pattern (Authorization)

Contrôle d'accès granulaire:

```php
class EvenementVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool { ... }
}
```

Usage:
```php
#[IsGranted('EVENEMENT_EDIT', 'evenement')]
public function edit(Evenement $evenement): Response { ... }
```

### 2.5 Form Type Pattern

Types de formulaires réutilisables:

```php
class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sujet', TextType::class)
            ->add('corps', TextareaType::class)
            ->add('recurrence', ChoiceType::class, [
                'choices' => [
                    'Pas de récurrence' => 'aucune',
                    'Quotidien' => 'jour',
                    // ...
                ]
            ]);
    }
}
```

---

## 3. Flux de Données

### 3.1 Création d'un Événement

```
HTTP POST /agenda/create
    ↓
AgendaController::create()
    ↓
EvenementType::handleRequest()
    ↓
Form validation
    ↓
EventManager::createEvent()
    ├─ Crée instance Evenement
    ├─ Crée instance Agenda
    ├─ Récupère Admin créateur via User
    └─ Ajout des participants validés
    ↓
EntityManager::persist() & flush()
    ↓
Database INSERT
    ↓
Redirect /agenda (Success Flash)
```

### 3.2 Affichage du Calendrier

```
HTTP GET /agenda/
    ↓
AgendaController::index()
    ↓
AgendaGenerator::NextDateAgenda()
    ├─ EvenementRepository::findAll()
    ├─ Pour chaque Evenement:
    │  └─ nextItemByRecurrence() → calcul dates
    └─ Retourne array structured
    ↓
json_encode() → JSON string
    ↓
Twig render avec variable 'agenda'
    ↓
Template agenda/index.html.twig
    ├─ Hidden input "agenda-data"
    └─ <div id="agenda_corps">
    ↓
JavaScript assets/agenda/action.js
    ├─ Parse JSON
    └─ Agenda.getAgenda() → FullCalendar init
    ↓
Browser renders FullCalendar
```

### 3.3 Navigation Event Click → Show

```
USER CLICK on Event in Calendar
    ↓
FullCalendar eventClick event
    ↓
JavaScript handler (agenda.js)
    └─ window.location.href = `/agenda/{eventId}`
    ↓
HTTP GET /agenda/{id}
    ↓
AgendaController::show(Evenement $evenement)
    ├─ Param converter injecte Evenement
    ├─ @IsGranted check (ROLE_USER)
    └─ render('agenda/show.html.twig')
    ↓
Show page avec détails complets
```

### 3.4 Affichage Agenda Personnel

```
HTTP GET /agenda/me
    ↓
AgendaController::myAgenda()
    ├─ getUser() via SecurityBundle
    └─ AgendaGenerator::getAgendaForUser($user)
    ↓
EvenementRepository::findByUser($user)
    ├─ Query: WHERE user IN (evenement.users)
    └─ Retourne Collection
    ↓
Structuration agenda pour calendar
    ├─ Pour chaque Evenement:
    │  ├─ Calculate dates via recurrence
    │  └─ Format: {id, title, dates[], recurrence}
    └─ json_encode()
    ↓
Template my_agenda.html.twig
    ├─ Agenda principal
    ├─ Sidebar RDV list
    ├─ Statistics
    └─ Hidden agenda-data
    ↓
JavaScript calendar init idem
```

---

## 4. Gestion des État et Cycles de Vie

### 4.1 Élève (Student Lifecycle)

```
┌─────────────────┐
│  User creé      │  (via registration)
└────────┬────────┘
         │
         │ Création Eleve lié
         ↓
┌─────────────────────────┐
│ isValidated = false     │  État: En attente
└────────┬────────────────┘
         │ Admin valide le profile
         ↓
┌─────────────────────────────────────┐
│ isValidated = true                  │  État: Validé
│ validatedByAdmin = Admin instance   │  (peut participer aux RDV)
│ validatedAt = DateTime now          │
└─────────────────────────────────────┘
```

### 4.2 Événement (Event Lifecycle)

```
┌────────────────┐
│ Créé           │  (create form submission)
└────────┬───────┘
         │
         │ Formulaire soumis
         ↓
┌─────────────────────────┐
│ État: Validé            │  (form validation passed)
└────────┬────────────────┘
         │ Saved to DB
         ↓
┌──────────────────────────────────────────┐
│ État: Actif                              │  (visible in calendars)
│ - Participants ajoutés (eleves validés)  │
│ - Récurrence calculée                    │
└────────┬─────────────────────────────────┘
         │
         ├─ Éditable (EVENEMENT_EDIT voter)
         │
         └─ Supprimable (EVENEMENT_DELETE voter)
              ↓
          État: Archivé (soft delete ou suppression)
```

---

## 5. Sécurité

### 5.1 Authentification

Utilise `UserInterface` et `PasswordAuthenticatedUserInterface`:

```php
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Gestion des rôles
    private array $roles = ['ROLE_USER'];
    
    // Hash du mot de passe
    private ?string $password = null;
}
```

Configuration: `config/packages/security.yaml`

### 5.2 Autorisation

Trois niveaux:

**1. Access Control (Routes)**
```yaml
access_control:
  - { path: ^/admin, role: ROLE_ADMIN }
  - { path: ^/agenda, role: ROLE_USER }
```

**2. IsGranted Decorator (Controllers)**
```php
#[IsGranted('ROLE_ADMIN')]
public function create(): Response { ... }
```

**3. Voters (Domain Logic)**
```php
#[IsGranted('EVENEMENT_EDIT', 'evenement')]
public function edit(Evenement $evenement): Response
{
    // Voter a validé que c'est l'admin créateur
}
```

### 5.3 CSRF Protection

Automatique pour tous les formulaires:

```php
// Form generation
{{ form_end(form) }}  // Ajoute le token automatiquement

// Validation
#[Route('/{id}/delete', methods: ['POST'])]
public function delete(Request $request, Evenement $evenement)
{
    if (!$this->isCsrfTokenValid('delete' . $evenement->getId(), 
        $request->request->get('_token'))) {
        throw $this->createAccessDeniedException();
    }
}
```

### 5.4 Règle Métier: Validation d'Élèves

**Important:** Seuls les élèves validés peuvent participer aux événements.

```php
// AgendaController.create()
if ($eleve->isValidated() && $eleve->getUser()) {
    $evenement->addUser($eleve->getUser());
}

// EvenementRepository.findByUser() implique que l'user
// a été ajouté et donc était validé au moment de l'ajout
```

---

## 6. Dépendances et Injections

### 6.1 Service Container

Défini dans `config/services.yaml`:

```yaml
services:
  App\Service\AgendaGenerator:
    arguments:
      $evenementRepository: '@App\Repository\EvenementRepository'
      $eleveRepository: '@App\Repository\EleveRepository'
  
  App\Service\EventManager:
    arguments:
      $em: '@doctrine.orm.entity_manager'
```

### 6.2 Dependency Injection en Controllers

```php
class AgendaController extends AbstractController
{
    public function __construct(
        private AgendaGenerator $agendaGenerator,
        private EvenementRepository $evenementRepository
    ) {}
    
    #[Route('/')]
    public function index(): Response
    {
        // Services auto-injectés
        $agenda = $this->agendaGenerator->NextDateAgenda();
    }
}
```

---

## 7. Database Schema

### Relations

```
User (1) ←────────────→ (1) Admin
  │                        │
  │                        └─ (1) ←──────────── (Many) Evenement
  │                            (adminCreateur)
  │
  ├─ (1) ←──────────── (Many) Eleve
  │     (user)
  │
  └─ (Many) ←────────── (Many) Evenement
        (users in evenement)
        
Agenda (1) ←──────────── (Many) Evenement
  
Classe (1) ←──────────── (Many) Eleve
```

### Approche Doctrine

Utilise Attributes PHP 8:

```php
#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $sujet = null;
    
    #[ORM\ManyToOne(targetEntity: Admin::class)]
    private ?Admin $adminCreateur = null;
    
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'evenement_user')]
    private Collection $users;
}
```

---

## 8. Frontend Architecture

### Webpack Encore

Assets bundling:

```javascript
// webpack.config.js
Encore
    .addEntry('agenda', './assets/agenda/agenda.js')
    .addEntry('agenda_action', './assets/agenda/action.js')
    .addEntry('agenda_create', './assets/agenda/create.js')
    .splitEntryChunks()
```

### Assets Flow

```
assets/
├── agenda/
│   ├── action.js          → obj_action (init calendar)
│   ├── create.js          → CreateAgenda (Choices.js)
│   ├── action_day.js      → Day view logic
│   ├── action_month.js    → Month view logic
│   └── ...
├── utilitaire/
│   ├── agenda.js          → Agenda class (FullCalendar wrapper)
│   ├── element.js
│   └── erreur.js
└── styles/
    ├── app.scss
    └── ckeditor_plus.css

↓ npm run dev ↓

/public/build/
├── agenda.js
├── agenda_action.js
├── agenda_create.js
├── css/...
└── entrypoints.json
```

### FullCalendar Integration

```javascript
// assets/utilitaire/agenda.js
export default class Agenda {
    constructor(elementId = 'agenda_corps') {
        this.elementId = elementId;
        this.calendar = null;
    }
    
    getAgenda() {
        this.calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin],
            locale: 'fr',
            events: arr_event,
            eventClick: (info) => {
                window.location.href = `/agenda/${info.event.extendedProps.id}`;
            }
        });
    }
}
```

### Template Data Passing

```twig
<!-- templates/agenda/index.html.twig -->
<div id="agenda_corps"></div>

<input type="hidden" id="agenda-data" value="{{ agenda|raw }}">

<script>
// action.js reads and parses
const agendaData = document.getElementById("agenda-data").value;
const coll_agenda = JSON.parse(agendaData);
</script>
```

---

## 9. Performances et Optimisations

### 9.1 Requêtes Optimisées

```php
// Repository methods avec JOINs
public function findUpcoming(): array
{
    return $this->createQueryBuilder('e')
        ->join('e.adminCreateur', 'a')
        ->addSelect('a')
        ->where('e.agenda > :now')
        ->setParameter('now', new \DateTime())
        ->orderBy('e.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}
```

### 9.2 Caching

À implémenter:
```php
#[Cache(maxAge: 3600)]
public function index(): Response { ... }
```

### 9.3 Assets Minification

```bash
npm run build  # Production build avec minification
```

---

## 10. Tests Recommandés

### Unit Tests

```php
// tests/Unit/Service/AgendaGeneratorTest.php
public function testNextDateAgendaReturnsArrayOfEvents(): void
{
    $result = $this->agendaGenerator->NextDateAgenda(1);
    $this->assertIsArray($result);
}
```

### Functional Tests

```php
// tests/Functional/Controller/AgendaControllerTest.php
public function testCreateRequiresAdminRole(): void
{
    $this->client->request('GET', '/agenda/create');
    $this->assertResponseStatusCodeSame(403);
}
```

### Integration Tests

```php
// tests/Integration/CalendarIntegrationTest.php
public function testFullEventCreationFlow(): void
{
    // Admin login
    // Create event
    // Verify in calendar
}
```

---

## 11. Déploiement

### Environnement Production

```bash
# .env.prod
APP_ENV=prod
APP_DEBUG=false
DATABASE_URL=mysql://prod_user:prod_pass@prod_host:3306/ecole_prod

# Cache clearing
php bin/console cache:clear --env=prod

# Migrations
php bin/console doctrine:migrations:migrate --env=prod --no-interaction

# Assets compilation
APP_ENV=prod npm run build
```

### Monitoring

Logs:
- `var/log/prod.log` → Erreurs production
- `var/log/dev.log` → Erreurs développement

---

## 12. Évolution Future

### Court terme (prochaines versions)
- [ ] Authentification multifacteur (2FA)
- [ ] Tests complets (>80% coverage)
- [ ] Caching Redis

### Moyen terme
- [ ] API GraphQL
- [ ] Notifications realtime (WebSockets)
- [ ] Synchronisation calendriers externes

### Long terme
- [ ] Microservices
- [ ] Machine learning (prédiction disponibilité)
- [ ] Mobile app native

