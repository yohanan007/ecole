# Documentation API - Système de Gestion d'Agenda

Documentation complète des endpoints REST/Web de l'application.

---

## Convention des Réponses

### Success Response

```
Status: 200 OK / 201 Created / 204 No Content
Content-Type: application/json ou text/html
```

### Error Response

```json
{
  "error": "Error message",
  "status": 400,
  "timestamp": "2026-03-22T14:30:00Z"
}
```

### Flash Messages

Redirection avec message flash (Symfony session):
```
Flash key: 'success', 'error', 'warning', 'info'
Affichage template: {{ app.flashes('success') }}
```

---

## AGENDA ENDPOINTS

### GET /agenda
**Affiche le calendrier principal**

- **Authentification:** ✅ Requise (ROLE_USER)
- **Méthode:** GET
- **Route name:** `app_agenda`
- **Paramètres:** Aucun

**Response:**
```
Status: 200 OK
Content-Type: text/html
Body: Twig template "agenda/index.html.twig"
```

**Variables Template:**
```php
[
    'agenda' => '{JSON encoded events array}',
    'agendaArray' => [                    // Array version
        [
            'id' => 1,
            'title' => 'Réunion parents',
            'recurrence' => 'aucune',
            'corps' => 'Détails réunion...',
            'lieu' => 'Salle 101',
            'duree' => 60,
            'dates' => [
                [
                    'date' => '2026-03-22 18:00:00',
                    'timestamp' => 1742814000000
                ]
            ]
        ]
    ],
    'statistics' => [
        'totalEvents' => 15,
        'thisMonthEvents' => 5,
        'nextWeekEvents' => 2
    ]
]
```

**Exemple cURL:**
```bash
curl -X GET http://localhost:8000/agenda \
  -H "Cookie: PHPSESSID=session_value"
```

---

### GET /agenda/me
**Affiche l'agenda personnel de l'utilisateur**

- **Authentification:** ✅ Requise (ROLE_USER)
- **Méthode:** GET
- **Route name:** `app_agenda_me`
- **Paramètres:** Aucun

**Response:**
```
Status: 200 OK
Content-Type: text/html
Body: Twig template "agenda/my_agenda.html.twig"
```

**Variables Template:**
```php
[
    'agenda' => '{JSON string}',           // Pour FullCalendar
    'agendaArray' => [                     // Events personnels
        [
            'id' => 1,
            'title' => 'Conseil de classe',
            'dates' => [ /* ... */ ],
            // ... same structure as /agenda
        ]
    ]
]
```

**Notes:** 
- Filtre les événements où `user` est participant
- Affiche sidebar avec prochain RDV + stats
- Recalcule les statistiques pour user uniquement

---

### GET /agenda/{id}
**Affiche les détails d'un événement**

- **Authentification:** ✅ Requise (ROLE_USER)
- **Méthode:** GET
- **Route name:** `app_agenda_show`
- **Paramètres:**
  - `id` (integer, required) - ID de l'événement

**Response:**
```
Status: 200 OK
Content-Type: text/html
Body: Twig template "agenda/show.html.twig"
```

**Variables Template:**
```php
[
    'evenement' => Evenement {
        'id' => 1,
        'sujet' => 'Réunion parents',
        'corps' => 'Présentation du projet...',
        'lieu' => 'Salle 102',
        'duree' => 90,
        'recurrence' => 'aucune',
        'createdAt' => DateTime('2026-03-20 10:00:00'),
        'adminCreateur' => Admin {
            'nom' => 'Dupont',
            'prenom' => 'Sophie',
            'user' => User { 'email' => 'sophie.dupont@ecole.fr' }
        },
        'users' => Collection[ User, User, ... ],  // Participants
        'agenda' => Agenda { 'heureDebut' => DateTime(...) }
    }
]
```

**Errors:**
- 404: Événement introuvable
- 403: Accès refusé (not ROLE_USER)

---

### GET/POST /agenda/create
**Crée un nouvel événement (form + processing)**

- **Authentification:** ✅ Requise (ROLE_ADMIN)
- **Méthode:** GET (form display), POST (submission)
- **Route name:** `app_agenda_create`
- **Paramètres:** Aucun

#### GET - Affiche le formulaire
```
Status: 200 OK
Content-Type: text/html
Body: Twig template "agenda/create.html.twig"
```

**Variables Template:**
```php
[
    'form' => FormView {
        // Champs du formulaire
        'sujet', 'corps', 'lieu', 'duree', 'recurrence'
    },
    'classes' => Collection[ Classe, Classe, ... ],
    'eleves' => Collection[ Eleve (isValidated=true), ... ]
]
```

#### POST - Traite la soumission
```
Content-Type: application/x-www-form-urlencoded
Body:
  evenement[sujet]=Réunion+parents
  evenement[corps]=Détails...
  evenement[lieu]=Salle+101
  evenement[duree]=60
  evenement[recurrence]=aucune
  evenement[save]=Créer
  token=CSRF_TOKEN
  classe=-1
  eleve=1,2,3
  dateDebut=2026-04-15
  heureDebut=18:30
```

**Success Response:**
```
Status: 302 Found
Location: /agenda
Flash message: 'success' → 'RDV créé avec succès!'
```

**Error Responses:**
- 400: Erreur de validation du formulaire
- 403: Accès refusé (not ROLE_ADMIN)

**Notes:**
- Admin créateur est automatiquement assigné
- Seuls élèves validés (isValidated=true) sont ajoutés
- Agenda lié est créé automatiquement
- CSRF token obligatoire (via form_end)

---

### GET/POST /agenda/{id}/edit
**Modifie un événement existant**

- **Authentification:** ✅ Requise (ROLE_ADMIN)
- **Autorisation:** Voter EVENEMENT_EDIT (seul admin créateur)
- **Méthode:** GET (form display), POST (submission)
- **Route name:** `app_agenda_edit`
- **Paramètres:**
  - `id` (integer, required) - ID de l'événement

#### GET - Affiche le formulaire pré-rempli
```
Status: 200 OK
Content-Type: text/html
Body: Twig template "agenda/edit.html.twig"
```

**Variables Template:**
```php
[
    'form' => FormView { /* pré-rempli */ },
    'evenement' => Evenement { /* données actuelles */ },
    'classes' => Collection[ Classe, ... ],
    'eleves' => Collection[ Eleve, ... ]
]
```

#### POST - Traite la modification
```
Content-Type: application/x-www-form-urlencoded
Body: (même que POST /agenda/create)
```

**Success Response:**
```
Status: 302 Found
Location: /agenda
Flash message: 'success' → 'RDV mis à jour avec succès!'
```

**Error Responses:**
- 403: Accès refusé (not event creator)
- 404: Événement introuvable
- 400: Erreur validation

---

### POST /agenda/{id}/delete
**Supprime un événement**

- **Authentification:** ✅ Requise (ROLE_ADMIN)
- **Autorisation:** Voter EVENEMENT_DELETE (seul admin créateur)
- **Méthode:** POST
- **Route name:** `app_agenda_delete`
- **Paramètres:**
  - `id` (integer, required) - ID de l'événement

**Request:**
```
Content-Type: application/x-www-form-urlencoded
Body:
  _token=CSRF_TOKEN
```

**Success Response:**
```
Status: 302 Found
Location: /agenda
Flash message: 'success' → 'RDV supprimé'
```

**Error Responses:**
- 403: CSRF invalid OU accès refusé (not creator)
- 404: Événement introuvable

**Notes:**
- CSRF token obligatoire (via form hidden)
- Supprime l'Agenda associé si plus d'événements liés
- Confirmation JS avant soumission recommandée

---

## API JSON ENDPOINTS (Optionnel)

### GET /agenda/api/upcoming
**Liste les événements à venir (JSON)**

- **Authentification:** ✅ Requise
- **Méthode:** GET
- **Route name:** `app_agenda_api_upcoming`
- **Paramètres:**
  - `days` (integer, optional, default: 30) - Nombre de jours

**Response:**
```json
Status: 200 OK
Content-Type: application/json

{
  "success": true,
  "data": [
    {
      "id": 1,
      "sujet": "Réunion",
      "lieu": "Salle 101",
      "createdAt": "2026-03-22T10:00:00+00:00",
      "adminCreateur": {
        "nom": "Dupont",
        "prenom": "Sophie"
      }
    }
  ],
  "count": 5
}
```

---

### POST /agenda/api/create
**Crée un événement via API (JSON)**

- **Authentification:** ✅ Requise (ROLE_ADMIN)
- **Méthode:** POST
- **Route name:** `app_agenda_api_create`

**Request:**
```json
Content-Type: application/json

{
  "sujet": "Réunion parents",
  "corps": "Présentation projet pédagogique",
  "lieu": "Salle 101",
  "duree": 60,
  "recurrence": "aucune",
  "dateDebut": "2026-04-15",
  "heureDebut": "18:30",
  "classeIds": [1, 2],
  "eleveIds": [10, 11, 12]
}
```

**Success Response:**
```json
Status: 201 Created
Content-Type: application/json

{
  "success": true,
  "message": "RDV créé avec succès",
  "data": {
    "id": 15,
    "sujet": "Réunion parents",
    "createdAt": "2026-03-22T14:30:00+00:00"
  }
}
```

**Error Response:**
```json
Status: 400 Bad Request

{
  "success": false,
  "error": "Validation failed",
  "errors": {
    "sujet": "Ce champ est obligatoire"
  }
}
```

---

## ADMIN ENDPOINTS

### GET /admin/dashboard
**Dashboard administrateur**

- **Authentification:** ✅ Requise (ROLE_ADMIN)
- **Méthode:** GET
- **Route name:** `app_admin_dashboard`

**Response:**
```
Status: 200 OK
Content-Type: text/html
Body: Twig template
```

**Variables:**
```php
[
    'statistics' => [
        'totalAdmins' => 5,
        'totalEvents' => 42,
        'pendingValidations' => 3,
        'totalUsers' => 156
    ]
]
```

---

### POST /admin/eleves/{id}/validate
**Valide un élève**

- **Authentification:** ✅ Requise (ROLE_ADMIN)
- **Méthode:** POST
- **Route name:** `app_admin_eleve_validate`
- **Paramètres:**
  - `id` (integer, required) - ID de l'élève

**Request:**
```
Content-Type: application/x-www-form-urlencoded

Body:
  isValidated=1              // Pour validation
  _token=CSRF_TOKEN
```

**Success Response:**
```
Status: 302 Found
Location: /admin/eleves/pending
Flash message: 'success' → 'Élève validé'
```

**Notes:**
- Défini `isValidated = true`
- Défini `validatedByAdmin = current admin`
- Défini `validatedAt = now`

---

## HTTP Status Codes

| Code | Signification | Cas d'Usage |
|------|---------------|-----------|
| 200 | OK | GET réussi |
| 201 | Created | POST creation réussi |
| 204 | No Content | DELETE réussi |
| 302 | Found | Redirection après form |
| 400 | Bad Request | Erreur validation |
| 403 | Forbidden | Accès refusé (voter ou role) |
| 404 | Not Found | Ressource inexistante |
| 500 | Server Error | Erreur serveur |

---

## CSRF Protection

**Toutes les requêtes POST/PUT/DELETE requièrent un CSRF token**

### Obtention du token

**Via formulaire Twig:**
```twig
{{ form_start(form) }}
  <!-- Token auto-injecté avec form_end -->
{{ form_end(form) }}
```

**Manuellement:**
```twig
<input type="hidden" name="_token" value="{{ csrf_token('form_name') }}">
```

**Depuis variables globales de session:**
```twig
{{ csrf_token('preference_id') }}
```

### Validation serveur

```php
if (!$this->isCsrfTokenValid('delete_event', $request->request->get('_token'))) {
    throw $this->createAccessDeniedException('CSRF token invalide');
}
```

---

## Authentication

### Login Flow
```
POST /login
  ↓
Symfony Security (UserProvider)
  ↓
Password check via bcrypt hash
  ↓
Session cookie set
  ↓
Redirect to url_referer or /agenda
```

### Authorization Checks

**1. Route Access Control**
```yaml
access_control:
  - { path: ^/admin, role: ROLE_ADMIN }
  - { path: ^/agenda, role: ROLE_USER }
```

**2. IsGranted Decorator**
```php
#[IsGranted('ROLE_ADMIN')]
public function create(): Response { ... }
```

**3. Voter Pattern**
```php
#[IsGranted('EVENEMENT_EDIT', 'evenement')]
public function edit(Evenement $evenement): Response { ... }
```

---

## Taux Limiters (À implémenter)

Recommandé pour production:

```yaml
# config/packages/rate_limiter.yaml
framework:
  rate_limiter:
    login_limiter:
      policy: 'sliding_window'
      limit: 5
      interval: '15 minutes'
```

---

## Versioning (Futur)

Pour les futures versions API (v2, v3):

```
/api/v1/agenda/upcoming
/api/v2/agenda/upcoming      (changements non-compatible)
/api/v3/agenda/upcoming
```

---

## Erreurs Communes et Solutions

### 403 Forbidden
```
Cause: Rôle insuffisant ou voter deny
Solution: Vérifier @IsGranted, roles, credentials
```

### 404 Not Found
```
Cause: Ressource inexistante (Evenement supprimé)
Solution: Vérifier l'ID, recharger page
```

### 400 Bad Request
```
Cause: Erreur validation formulaire
Solution: Afficher errors du form (form.errors)
```

### Token CSRF Invalide
```
Cause: Session expirée oder token inexact
Solution: Rafraîchir la page, soumettre à nouveau
```

---

## Pagination (À implémenter)

Recommandé pour les listes longues:

```
GET /agenda/api/upcoming?page=1&limit=20
```

Response:
```json
{
  "data": [ ... ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 156,
    "pages": 8
  }
}
```

---

## Filtres Avancés (À implémenter)

```
GET /agenda/api/upcoming?filter[lieu]=Salle&filter[recurrence]=semaine
```

---

## Webhooks (Futur)

Notifier systèmes externes d'événements:

```
POST https://external-service.com/webhook
Body: {
  "event": "evenement.created",
  "data": { ... }
}
```

