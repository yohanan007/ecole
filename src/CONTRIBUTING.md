# Guide de Contribution

Merci de vouloir contribuer ! Ce guide explique comment participer au développement du projet.

---

## 🎯 Types de Contributions

Nous acceptons les contributions dans les domaines suivants:

### 1. 🐛 Rapporter des Bugs

**Avant de signaler:**
- Vérifiez que le bug n'existe pas déjà dans les Issues
- Testez en utilisant la dernière version
- Reproduisez le bug sur une installation fraîche si possible

**Comment signaler:**

```markdown
## Description
[Brève description du problème]

## Étapes pour reproduire
1. Aller à [URL]
2. Cliquer sur [action]
3. Voir le [résultat]

## Comportement attendu
[Ce qui devrait se passer]

## Logs/Screenshots
[Ajouter logs de var/log/dev.log]
[Ajouter screenshot si pertinent]

## Environment
- OS: Windows/Mac/Linux
- PHP: 8.0.x
- Navigateur: Chrome/Firefox/Safari
```

### 2. ✨ Proposer des Features

**Template:**

```markdown
## Description
[Expliquer la feature en détail]

## Cas d'utilisation
[Pourquoi c'est utile ? Quel problème ça résout ?]

## Impact
- [ ] Breaking change
- [ ] Non-breaking feature
- [ ] Documentation seulement

## Examples
[Montrer comment ça marchait idealement]
```

### 3. 📝 Améliorer la Documentation

- Corriger typos
- Clarifier explications
- Ajouter examples
- Traduire en d'autres langues

**Pas besoin demander permission - créer une PR directement !**

### 4. 🎨 Code Contributions

Voir section **Development Workflow** ci-dessous.

---

## 🔧 Development Workflow

### Étape 1: Fork et Clone

```bash
# Sur GitHub, cliquer "Fork" en haut à droite

# Clone votre fork
git clone https://github.com/VOTRE_USERNAME/ecole.git
cd ecole/src

# Ajouter remote upstream
git remote add upstream https://github.com/original-repo/ecole.git
```

### Étape 2: Créer une Feature Branch

```bash
# Toujours basé sur develop (jamais main)
git fetch upstream
git checkout -b feature/ma-feature upstream/develop

# Branch names:
# feature/user-authentication       (nouvelle feature)
# bugfix/email-sending-error        (correction de bug)
# docs/api-documentation            (documentation)
# chore/update-dependencies          (maintenance)
# refactor/calendar-component        (refactoring)
```

### Étape 3: Développer

```bash
# Setup local environment
composer install
npm install

# Créer feature
# Ajouter tests
# Vérifier code

# Avant de committer, vérifier
php bin/phpunit                    # Tests
./vendor/bin/phpstan analyse src/  # Static analysis
npm run build                      # Assets compilent
```

### Étape 4: Committer

**Guidelines:**
- Commits petits et logiques (1 feature = 1 commit idealement)
- Messages clairs et descriptifs
- Format: `type: description`

**Types:**
- `feat:` - Nouvelle feature
- `fix:` - Bugfix
- `docs:` - Documentation
- `style:` - Formatting, missing semicolons, etc
- `refactor:` - Code restructure sans changement fonctionnel
- `perf:` - Performance improvement
- `test:` - Tests ajoutés/modifiés
- `chore:` - Build, dependencies, etc

**Exemples:**

```bash
git commit -m "feat: ajouter récurrence mensuelle aux événements"
git commit -m "fix: corriger bug validation formulaire événement"
git commit -m "docs: clarifier securityVoter dans README"
git commit -m "refactor: extraire logique calendar dans service"
```

**Commits mauvais:**
```bash
# ✗ Trop vague
git commit -m "updated calendar"

# ✗ Trop long
git commit -m "fixed the issue with the calendar when user clicks on date and also improved the styling and added tests"

# ✗ Sans type
git commit -m "ajouter fonction pour créer événement"
```

### Étape 5: Push et Pull Request

```bash
# Avant push, rebase sur upstream
git fetch upstream
git rebase upstream/develop

# Push votre feature
git push origin feature/ma-feature
```

**Sur GitHub:**
- Cliquer "Compare & pull request"
- Remplir template PR (voir ci-dessous)
- Demander review (via "Reviewers")

**Template PR:**

```markdown
## Description
[Courte description de la feature / bugfix]

## Type de changement
- [ ] Bugfix (correction sans breaking change)
- [ ] Feature (nouveau fonctionnel)
- [ ] Breaking change (incompatibilité)
- [ ] Documentation

## Changes
- Point 1
- Point 2
- Point 3

## Testing
- [ ] Tests unitaires ajoutés/passés
- [ ] Tests manuels effectués
- [ ] Aucun warning/error dans logs

## Screenshots/Videos
[Si pertinent pour l'UI]

## Checklist
- [ ] Mon code suit les conventions du projet
- [ ] J'ai ajouté des tests
- [ ] J'ai testé localement
- [ ] Documentation à jour
- [ ] Pas de logs d'erreur en prod
```

### Étape 6: Code Review

**Qu'on cherche:**
- ✅ Code logic correct
- ✅ Pas de security issues
- ✅ Follows conventions
- ✅ Tests adequate
- ✅ Performance acceptable
- ✅ Documentation complète

**Attendre les approbations avant merge**

---

## 📐 Code Conventions

### Nommage

**Classes (PascalCase):**
```php
// ✓ Bon
class EventManager { }
class EvenementRepository { }
class AdminController { }

// ✗ Mauvais
class eventManager { }
class EVENTMANAGER { }
```

**Methods/Functions (camelCase):**
```php
// ✓ Bon
public function createEvent() { }
public function validateStudent() { }

// ✗ Mauvais
public function CreateEvent() { }
public function create_event() { }
```

**Constants (UPPER_SNAKE_CASE):**
```php
// ✓ Bon
const RECURRENCE_DAILY = 'jour';
const MAX_EVENTS_PER_ADMIN = 100;

// ✗ Mauvais
const recurrenceDaily = 'jour';
const max_events = 100;
```

**Variables (camelCase):**
```php
// ✓ Bon
$firstName = 'Sophie';
$isValidated = true;
$eventCount = 5;

// ✗ Mauvais
$first_name = 'Sophie';
$IsValidated = true;
$event_count = 5;
```

### PHP Code Style

**Type hints (obligatoire):**
```php
// ✓ Bon
public function findEvents(int $adminId, ?\DateTime $date): array {
    return [];
}

// ✗ Mauvais
public function findEvents($adminId, $date) {
    return [];
}
```

**Attributes (PHP 8 natif):**
```php
// ✓ Bon (Symfony 6.4+)
#[Entity]
#[Table(name: 'evenement')]
class Evenement { }

// ✗ Ancien (annotation comments)
/**
 * @Entity
 * @Table(name="evenement")
 */
class Evenement { }
```

**DocBlocks:**
```php
// ✓ Bon
/**
 * Créer un événement
 *
 * @param string $sujet
 * @param User $admin
 * @return Evenement
 * @throws InvalidArgumentException
 */
public function create(string $sujet, User $admin): Evenement {
    // ...
}
```

### Symfony Controllers

```php
// ✓ Bon
#[Route('/agenda', name: 'app_agenda', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
public function index(AgendaGenerator $generator): Response {
    $agenda = $generator->generate();
    
    return $this->render('agenda/index.html.twig', [
        'agenda' => $agenda,
    ]);
}

// ✗ Mauvais
public function indexAction() {  // Ancien style
    // ...
}
```

### Security/Authorization

```php
// ✓ Bon - Voter pattern
#[IsGranted('EVENEMENT_EDIT', 'evenement')]
public function edit(Evenement $evenement): Response { }

// ✓ Bon - Expression language
#[IsGranted("ROLE_ADMIN or object.created_by == user")]
public function dashboard(): Response { }

// ✗ Mauvais - direct condition check
if ($user->getId() === $event->getAdmin()->getId()) {
    // ...
}
```

### Form Types

```php
// ✓ Bon
public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder
        ->add('sujet', TextType::class, [
            'label' => 'Sujet de l\'événement',
            'help' => 'Descriptif court',
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 255]),
            ],
        ])
        ->add('eleves', EntityType::class, [
            'class' => Eleve::class,
            'multiple' => true,
            'choice_label' => 'label',
        ]);
}

// ✗ Mauvais - options manquantes
->add('sujet', TextType::class)
```

---

## 🧪 Testing Requirements

### PHPUnit Tests Obligatoires

**Règle:** Chaque feature doit avoir des tests

```bash
# Créer test
php bin/console make:test Service/AgendaGeneratorTest

# Ou manuellement
# tests/Unit/Service/AgendaGeneratorTest.php
```

**Exemple:**

```php
namespace App\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use App\Service\AgendaGenerator;

class AgendaGeneratorTest extends TestCase {
    
    private $generator;
    
    protected function setUp(): void {
        // Mock repositories
        $this->generator = new AgendaGenerator($mockRepo);
    }
    
    // ✓ Bon - test une seule chose
    public function testGenerateCalendarForAdmin(): void {
        $calendar = $this->generator->generate($adminId);
        
        $this->assertInstanceOf(Calendar::class, $calendar);
        $this->assertCount(5, $calendar->getEvents());
    }
    
    // ✓ Bon - test un cas d'erreur
    public function testThrowsExceptionForInvalidAdmin(): void {
        $this->expectException(InvalidArgumentException::class);
        
        $this->generator->generate(999); // Admin inexistent
    }
    
    // ✗ Mauvais - teste plusieurs choses
    public function testEverything(): void {
        $calendar = $this->generator->generate($adminId);
        $event = $calendar->getEvents()[0];
        $this->assertTrue($event->isRecurring());
        // ... 20 autres asserts
    }
}
```

**Coverage minimum:** 80% pour nouvelles features

```bash
php bin/phpunit --coverage-html=coverage
# Ouvrir coverage/index.html
```

### Tests Manuels (pour UI)

Avant PR, tester:
- [ ] Créer événement - affiche correctement
- [ ] Éditer événement - sauvegarde changements
- [ ] Supprimer événement - disparaît du calendrier
- [ ] Validation élève - button apparaît/disparaît
- [ ] Responsive design - mobile/tablet/desktop

---

## 📋 Pre-commit Checks

**Avant de pousser, vérifier:**

```bash
#!/bin/bash
# .git/hooks/pre-commit (faire executable: chmod +x)

set -e

echo "🔍 Running PHPStan..."
./vendor/bin/phpstan analyse src/ --level=8

echo "🧪 Running tests..."
php bin/phpunit

echo "📦 Building assets..."
npm run build > /dev/null 2>&1

echo "✅ All checks passed!"
```

**Commande rapide:**

```bash
php bin/phpunit && ./vendor/bin/phpstan analyse src/ && npm run build
```

---

## 🐛 Bug Fix Workflow

**Exemple:** Corriger bug où les événements ne s'affichent pas

### 1. Créer issue

```markdown
Title: Les événements ne s'affichent pas sur le calendrier
Description: 
Après créer un événement, il n'apparaît pas dans le calendrier
```

### 2. Créer branch bugfix

```bash
git checkout -b bugfix/calendar-display-issue upstream/develop
```

### 3. Écrire test qui échoue

```php
public function testEventAppearsInCalendar(): void {
    $event = $this->createEvent('Test');
    $calendar = $this->generator->generate($adminId);
    
    $this->assertTrue($calendar->hasEvent($event->getId())); // ÉCHOUE
}
```

### 4. Fixer le code

```php
// S'apercevoir que getEventsVisible() filtre avec isPublished=true
// Mais événement créé n'a pas is_published=true
public function create(CreateRequest $req): Evenement {
    $event = new Evenement();
    // ... set properties
    $event->setIsPublished(true); // ← AJOUTÉ
    
    $this->em->persist($event);
    $this->em->flush();
    
    return $event;
}
```

### 5. Test passe maintenant

```bash
php bin/phpunit tests/Unit/Service/AgendaGeneratorTest::testEventAppearsInCalendar

✓ PASSED
```

### 6. Créer PR

```markdown
Title: fix: afficher événement créé dans le calendrier

Description:
Les événements créés n'étaient pas visible à cause du flag is_published=false

Solution:
Définir is_published=true au création de l'événement

Fixes #123
```

---

## 📚 Documentation Workflow

**Pour corriger/améliorer docs:**

```bash
git checkout -b docs/clarify-api-docs upstream/develop

# Éditer README.md, ARCHITECTURE.md, etc
nano README.md

git commit -m "docs: clarifier endpoint POST /agenda/create"
git push origin docs/clarify-api-docs

# PR sera revue et mergée rapidement
```

**Examples de bon docs:**
- ✅ Explique le "pourquoi" pas juste le "quoi"
- ✅ Inclut exemples de code réels
- ✅ Structure claire avec headings et listes
- ✅ Correctement grammaticalement (ou demander relecture)

---

## 🚀 Merging & Release

**Maintainers mergent quand:**
- ✅ 2 code reviews approuving
- ✅ All tests passing
- ✅ No merge conflicts
- ✅ Documentation updated
- ✅ Commits squashed si nécessaire

**Release Process:**

```bash
# Sur branche main
git checkout main
git merge --no-ff develop

# Tag version
git tag -a v1.1.0 -m "Version 1.1.0 - New features"
git push origin main --tags

# Back to develop
git checkout develop
```

**Version format:** v1.0.0 (semantic versioning)

---

## ❓ Questions ?

- Créer une Issue pour poser une question
- Joindre le Discord/Slack du projet
- Demander dans discussions GitHub

**Pas trop timide pour demander aide !**

---

## 🎓 Ressources Utiles

### Documentation Officielle
- [Symfony Docs](https://symfony.com/doc/)
- [Doctrine ORM](https://www.doctrine-project.org/)
- [PHPUnit](https://phpunit.de/)
- [Webpack Encore](https://symfony.com/doc/current/frontend.html)

### Code Style
- [PSR-12 PHP Coding Standard](https://www.php-fig.org/psr/psr-12/)
- [Symfony Coding Standards](https://symfony.com/doc/current/contributing/code/standards.html)

### Git
- [Git Branching Model](https://nvie.com/posts/a-successful-git-branching-model/)
- [Conventional Commits](https://www.conventionalcommits.org/)

### Testing
- [Testing Best Practices](https://phpunit.de/documentation.html)
- [TDD: Test Driven Development](https://en.wikipedia.org/wiki/Test-driven_development)

---

## 📝 License

En contributant au projet, vous acceptez que votre code soit publié sous la même licence que le projet (voir LICENSE file).

---

**Merci de rendre ce projet meilleur ! 🙏**

