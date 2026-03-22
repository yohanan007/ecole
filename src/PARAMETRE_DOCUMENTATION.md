# Page Paramètres - Documentation des modifications

## Vue d'ensemble
La page `/parametre` a été complètement restructurée pour permettre aux utilisateurs de :
1. Consulter leurs informations de compte
2. Modifier leurs informations personnelles
3. Changer leur mot de passe
4. Voir que leur email (identifiant) est non modifiable

## Fonctionnalités implémentées

### 1. Formulaires créés
- **UpdateParentEleveType** : Pour les parents d'élèves
  - Modification : nom, prénom, adresse, téléphone, ville, code postal, pays
  
- **UpdateEleveType** : Pour les élèves
  - Modification : nom, prénom, adresse, téléphone
  
- **UpdateAdminType** : Pour les administrateurs
  - Modification : nom, prénom, téléphone
  
- **ChangePasswordType** : Pour tous les utilisateurs
  - Validation du mot de passe actuel
  - Confirmation du nouveau mot de passe
  - Vérification de la longueur minimale (6 caractères)

### 2. Modifications du contrôleur (HomeController)
- Détection automatique du type d'utilisateur (Admin, Parent, Élève)
- Récupération de l'entité associée via le repository
- Gestion de la soumission des formulaires
- Validation du mot de passe actuel
- Messages de succès/erreur

### 3. Template updated (parametre.html.twig)
- Affichage des informations de compte
- Formulaires adaptatifs selon le type d'utilisateur
- Gestion des messages (succès et erreur)
- Design cohérent avec Bootstrap

## Identifiants de test

### Admin
- Email : `admin@ecole.fr`
- Mot de passe : `admin123`

### Parent exemple
- Email : `parent1@ecole.fr`
- Mot de passe : `parent1`

### Élève exemple
- Email : `eleve1@ecole.fr`
- Mot de passe : `eleve1`

## Flux utilisateur

1. L'utilisateur se connecte
2. Il se rend sur `/parametre`
3. Le contrôleur détecte son type (Admin, Parent ou Élève)
4. Les formulaires et informations appropriées s'affichent
5. L'utilisateur peut :
   - Modifier ses informations (formulaire adapté à son rôle)
   - Changer son mot de passe
   - Voir son email (non modifiable)
6. Les modifications sont sauvegardées en base de données

## Points importants

✅ Email de l'utilisateur est non modifiable (username)
✅ Détection automatique du type d'utilisateur
✅ Validation forte du changement de mot de passe
✅ Messages clairs de succès/erreur
✅ Design responsive et user-friendly
✅ Formulaires adaptés à chaque type d'utilisateur
