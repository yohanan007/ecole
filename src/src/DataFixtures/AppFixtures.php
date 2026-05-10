<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Classe;
use App\Entity\ClasseEleve;
use App\Entity\Eleve;
use App\Entity\Evenement;
use App\Entity\Niveau;
use App\Entity\ParentEleve;
use App\Entity\User;
use App\Service\AgendaGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private AgendaGenerator $agendaGenerator;
    
    private array $noms = [
        'Dupont', 'Martin', 'Bernard', 'Dubois', 'Laurent', 'Simon',
        'Michel', 'Lefèvre', 'Leroy', 'Moreau', 'Girardin', 'André',
        'Lefevre', 'Leclerc', 'Lucet', 'Luchet', 'Luci', 'Luck',
        'Marchand', 'Martel', 'Masson', 'Mathieu', 'Matte', 'Mauborgne',
        'Mauger', 'Maulde', 'Maurel', 'Maurette', 'Mauri', 'Maurier'
    ];
    
    private array $prenoms = [
        'Jean', 'Marie', 'Pierre', 'Nathalie', 'Luc', 'Sophie',
        'Michel', 'Cécile', 'François', 'Christine', 'Antoine', 'Isabelle',
        'Marc', 'Caroline', 'Philippe', 'Valérie', 'Patrick', 'Evelyne',
        'Robert', 'Nicole', 'Paul', 'Barbara', 'Georges', 'Danielle'
    ];
    
    private array $prenoms_enfant = [
        'Lucas', 'Emma', 'Louis', 'Léa', 'Noah', 'Chloé',
        'Liam', 'Manon', 'Hugo', 'Alice', 'Maxime', 'Julie',
        'Arthur', 'Clara', 'Mathieu', 'Lucie', 'Gabriel', 'Sarah'
    ];

    private array $villes = [
        'Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice',
        'Nantes', 'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille',
        'Orly', 'Versailles', 'Boulogne', 'Neuilly', 'Créteil'
    ];

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        AgendaGenerator $agendaGenerator
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->agendaGenerator = $agendaGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer un admin
        $adminUser = new User();
        $adminUser->setEmail('admin@ecole.fr');
        $adminUser->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, 'admin123');
        $adminUser->setPassword($hashedPassword);
        $adminUser->setIsVerified(true);
        $manager->persist($adminUser);

        $admin = new Admin();
        $admin->setUser($adminUser);
        $admin->setNom('Dubois');
        $admin->setPrenom('Jean');
        $admin->setTelephone('0123456789');
        $admin->setIsActive(true);
        $manager->persist($admin);

        // Créer les niveaux
        $niveaux = [];
        $niveauxNames = ['Primaire', 'Collège', 'Lycée'];
        foreach ($niveauxNames as $levelName) {
            $niveau = new Niveau();
            $niveau->setNom($levelName);
            $manager->persist($niveau);
            $niveaux[$levelName] = $niveau;
        }

        // Créer les classes pour chaque niveau
        $classes = [];
        $classesPerLevel = 3;
        $classNames = [
            'Primaire' => ['CP', 'CE1', 'CE2', 'CM1', 'CM2'],
            'Collège' => ['6ème', '5ème', '4ème', '3ème'],
            'Lycée' => ['Seconde', 'Première', 'Terminale']
        ];

        foreach ($niveaux as $levelName => $niveau) {
            for ($i = 0; $i < $classesPerLevel; $i++) {
                $classe = new Classe();
                $classe->setNom($classNames[$levelName][$i] ?? $levelName . ' ' . ($i + 1));
                $classe->setNiveau($niveau);
                $manager->persist($classe);
                $classes[] = $classe;
            }
        }

        $manager->flush();

        // Créer 40 parents avec 5 enfants chacun et les associer aux classes
        $tous_eleves = [];
        $eleves_par_classe = []; // Structure pour tracker les élèves par classe
        $numParent = 0;

        for ($p = 0; $p < 40; $p++) {
            $numParent++;
            
            // Créer le parent
            $parentUser = new User();
            $parentUser->setEmail('parent' . $numParent . '@ecole.fr');
            $parentUser->setRoles(['ROLE_USER']);
            $parentPassword = $this->passwordHasher->hashPassword($parentUser, 'parent' . $numParent);
            $parentUser->setPassword($parentPassword);
            $parentUser->setIsVerified(true);
            $manager->persist($parentUser);

            $parent = new ParentEleve();
            $parent->setUser($parentUser);
            $parent->setNom($this->noms[array_rand($this->noms)]);
            $parent->setPrenom($this->prenoms[array_rand($this->prenoms)]);
            $parent->setAdresse($numParent . ' rue de l\'école');
            $parent->setTelephone('01' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT));
            $parent->setVille($this->villes[array_rand($this->villes)]);
            $parent->setCodePostal('750' . str_pad(mt_rand(0, 99), 2, '0', STR_PAD_LEFT));
            $parent->setPays('France');
            $manager->persist($parent);

            // Créer 5 enfants pour ce parent
            for ($e = 0; $e < 5; $e++) {
                $numEleve = ($p * 5) + $e + 1;
                
                $eleveUser = new User();
                $eleveUser->setEmail('eleve' . $numEleve . '@ecole.fr');
                $eleveUser->setRoles(['ROLE_USER']);
                $elevePassword = $this->passwordHasher->hashPassword($eleveUser, 'eleve' . $numEleve);
                $eleveUser->setPassword($elevePassword);
                $eleveUser->setIsVerified(true);
                $manager->persist($eleveUser);

                $eleve = new Eleve();
                $eleve->setUser($eleveUser);
                $eleve->setNom($parent->getNom());
                $eleve->setPrenom($this->prenoms_enfant[array_rand($this->prenoms_enfant)]);
                $eleve->setAdresse($parent->getAdresse());
                $eleve->setTelephone($parent->getTelephone());
                $eleve->setIsValidated(true);
                $eleve->setValidatedByAdmin($admin);
                $eleve->setValidatedAt(new \DateTime());
                $manager->persist($eleve);

                // Ajouter l'enfant au parent
                $parent->addEnfant($eleve);

                // Associer l'élève à une classe aléatoire immédiatement
                $classe = $classes[mt_rand(0, count($classes) - 1)];
                
                $classeEleve = new ClasseEleve();
                $classeEleve->setClasse($classe);
                $classeEleve->setEleve($eleve);
                $classeEleve->setDateValide(new \DateTime());
                $classeEleve->setDateFin(new \DateTime('+1 year'));
                $manager->persist($classeEleve);

                // Tracker l'élève par classe
                $classeObjId = $classe->getId(); // Utiliser l'ID de la classe pour le tracking
                if (!isset($eleves_par_classe[$classeObjId])) {
                    $eleves_par_classe[$classeObjId] = [];
                }
                $eleves_par_classe[$classeObjId][] = [
                    'eleve' => $eleve,
                    'parent' => $parent,
                    'classe' => $classe
                ];

                $tous_eleves[] = $eleve;
            }
        }

        $manager->flush();

        // ✅ CRÉER LES ÉVÉNEMENTS RÉCURRENTS (PARENT-CHILD PATTERN)
        // 1 événement parent par classe avec récurrence hebdomadaire
        $evenementCounter = 0;
        
        foreach ($classes as $classe) {
            // ✅ Créer un événement parent (réunion hebdomadaire)
            $evenementParent = new Evenement();
            $evenementParent->setAdminCreateur($admin);
            $evenementParent->setSujet('Réunion parents - ' . $classe->getNom());
            $evenementParent->setCorps('Réunion hebdomadaire avec les parents de la classe ' . $classe->getNom());
            $evenementParent->setLieu('Salle de classe ' . $classe->getNom());
            $evenementParent->setDuree(60); // 60 minutes
            $evenementParent->setCreatedAt(new \DateTime());
            
            // Définir la première occurrence : demain à 18h
            $heureDebut = (new \DateTime())->modify('tomorrow')->setTime(18, 0, 0);
            $evenementParent->setHeureDebut($heureDebut);
            
            // Définir la récurrence : hebdomadaire pendant 3 mois
            $evenementParent->setRecurrence('semaine');
            $recurrenceEnd = (clone $heureDebut)->modify('+3 months');
            $evenementParent->setRecurrenceEnd($recurrenceEnd);
            
            $manager->persist($evenementParent);
            $manager->flush(); // Flush intermédiaire pour avoir l'ID du parent

            // ✅ Ajouter tous les élèves de cette classe au parent
            $classeObjId = $classe->getId();
            if (isset($eleves_par_classe[$classeObjId])) {
                foreach ($eleves_par_classe[$classeObjId] as $eleveData) {
                    $eleve = $eleveData['eleve'];
                    $eleveUser = $eleve->getUser();
                    if ($eleveUser && !$evenementParent->getUsers()->contains($eleveUser)) {
                        $evenementParent->addUser($eleveUser);
                    }
                }
            }

            $manager->persist($evenementParent);
            $manager->flush();

            // ✅ CRÉER LES OCCURRENCES via le service
            $this->agendaGenerator->createRecurringEvent($evenementParent);
            $evenementCounter++;

            // ✅ CRÉER UN 2ème ÉVÉNEMENT : Contrôle (récurrence bi-hebdomadaire)
            $evenementControle = new Evenement();
            $evenementControle->setAdminCreateur($admin);
            $evenementControle->setSujet('Contrôle continu - ' . $classe->getNom());
            $evenementControle->setCorps('Évaluation bi-hebdomadaire de la classe ' . $classe->getNom());
            $evenementControle->setLieu('Salle d\'examen');
            $evenementControle->setDuree(120); // 2 heures
            $evenementControle->setCreatedAt(new \DateTime());
            
            // Commencer dans 5 jours
            $heureDebutControle = (new \DateTime())->modify('+5 days')->setTime(14, 0, 0);
            $evenementControle->setHeureDebut($heureDebutControle);
            $evenementControle->setRecurrence('deuxSemaines');
            
            $recurrenceEndControle = (clone $heureDebutControle)->modify('+3 months');
            $evenementControle->setRecurrenceEnd($recurrenceEndControle);
            
            $manager->persist($evenementControle);
            $manager->flush();

            // Ajouter les élèves
            if (isset($eleves_par_classe[$classeObjId])) {
                foreach ($eleves_par_classe[$classeObjId] as $eleveData) {
                    $eleve = $eleveData['eleve'];
                    $eleveUser = $eleve->getUser();
                    if ($eleveUser && !$evenementControle->getUsers()->contains($eleveUser)) {
                        $evenementControle->addUser($eleveUser);
                    }
                }
            }

            $manager->persist($evenementControle);
            $manager->flush();

            // Créer les occurrences du contrôle
            $this->agendaGenerator->createRecurringEvent($evenementControle);
            $evenementCounter++;

            // ✅ CRÉER UN ÉVÉNEMENT SIMPLE (sans récurrence)
            $evenementSimple = new Evenement();
            $evenementSimple->setAdminCreateur($admin);
            $evenementSimple->setSujet('Sortie scolaire - ' . $classe->getNom());
            $evenementSimple->setCorps('Visite pédagogique pour la classe ' . $classe->getNom());
            $evenementSimple->setLieu('Musée du Louvre');
            $evenementSimple->setDuree(240); // 4 heures
            $evenementSimple->setCreatedAt(new \DateTime());
            
            // Événement dans 2 semaines
            $heureDebutSimple = (new \DateTime())->modify('+14 days')->setTime(9, 0, 0);
            $evenementSimple->setHeureDebut($heureDebutSimple);
            $evenementSimple->setRecurrence('aucune'); // Pas de récurrence
            
            // Ajouter les élèves et parents
            if (isset($eleves_par_classe[$classeObjId])) {
                foreach ($eleves_par_classe[$classeObjId] as $eleveData) {
                    $eleve = $eleveData['eleve'];
                    $parent = $eleveData['parent'];
                    
                    $eleveUser = $eleve->getUser();
                    if ($eleveUser && !$evenementSimple->getUsers()->contains($eleveUser)) {
                        $evenementSimple->addUser($eleveUser);
                    }
                    
                    $parentUser = $parent->getUser();
                    if ($parentUser && !$evenementSimple->getUsers()->contains($parentUser)) {
                        $evenementSimple->addUser($parentUser);
                    }
                }
            }

            $manager->persist($evenementSimple);
            $manager->flush();
            $evenementCounter++;
        }

        $manager->flush();

        echo "✅ Fixture créée avec succès!\n";
        echo "- 1 Admin\n";
        echo "- 40 Parents\n";
        echo "- 200 Élèves\n";
        echo "- 3 Niveaux\n";
        echo "- 9 Classes\n";
        echo "- " . $evenementCounter . " Événements parents créés\n";
        echo "  ├─ Réunions hebdomadaires (parent + ~13 occurrences)\n";
        echo "  ├─ Contrôles bi-hebdomadaires (parent + ~6 occurrences)\n";
        echo "  └─ Sorties (événements simples, sans récurrence)\n";
    }
}
