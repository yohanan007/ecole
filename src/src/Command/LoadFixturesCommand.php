<?php

namespace App\Command;

use App\Entity\Admin;
use App\Entity\Agenda;
use App\Entity\Classe;
use App\Entity\ClasseEleve;
use App\Entity\Eleve;
use App\Entity\Evenement;
use App\Entity\Niveau;
use App\Entity\ParentEleve;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:load-fixtures',
    description: 'Charge les données de test pour l\'application'
)]
class LoadFixturesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Chargement des fixtures...');

        $noms = [
            'Dupont', 'Martin', 'Bernard', 'Dubois', 'Laurent', 'Simon',
            'Michel', 'Lefèvre', 'Leroy', 'Moreau', 'Girardin', 'André',
            'Lefevre', 'Leclerc', 'Marchand', 'Martel', 'Masson', 'Mathieu'
        ];
        
        $prenoms = [
            'Jean', 'Marie', 'Pierre', 'Nathalie', 'Luc', 'Sophie',
            'Michel', 'Cécile', 'François', 'Christine', 'Antoine', 'Isabelle'
        ];
        
        $prenoms_enfant = [
            'Lucas', 'Emma', 'Louis', 'Léa', 'Noah', 'Chloé',
            'Liam', 'Manon', 'Hugo', 'Alice', 'Maxime', 'Julie'
        ];

        $villes = [
            'Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice',
            'Nantes', 'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille'
        ];

        // Supprimer les données existantes (optionnel)
        $output->writeln('Suppression des données existantes...');
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        
        foreach ([
            'evenement_user',
            'parent_eleve_eleve', 
            'evenement', 
            'classe_eleve', 
            'eleve', 
            'parent_eleve', 
            'admin', 
            'user', 
            'agenda', 
            'classe', 
            'niveau'
        ] as $table) {
            try {
                $this->entityManager->getConnection()->executeStatement('TRUNCATE TABLE ' . $table);
            } catch (\Exception $e) {
                // Ignorer les erreurs de truncate
            }
        }
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        // Créer un admin
        $output->writeln('Création de l\'admin...');
        $adminUser = new User();
        $adminUser->setEmail('admin@ecole.fr');
        $adminUser->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, 'admin123');
        $adminUser->setPassword($hashedPassword);
        $adminUser->setIsVerified(true);
        $this->entityManager->persist($adminUser);

        $admin = new Admin();
        $admin->setUser($adminUser);
        $admin->setNom('Dubois');
        $admin->setPrenom('Jean');
        $admin->setTelephone('0123456789');
        $admin->setIsActive(true);
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        // Créer les niveaux
        $output->writeln('Création des niveaux...');
        $niveaux = [];
        $niveauxNames = ['Primaire', 'Collège', 'Lycée'];
        foreach ($niveauxNames as $levelName) {
            $niveau = new Niveau();
            $niveau->setNom($levelName);
            $this->entityManager->persist($niveau);
            $niveaux[$levelName] = $niveau;
        }
        $this->entityManager->flush();

        // Créer les classes pour chaque niveau
        $output->writeln('Création des classes...');
        $classes = [];
        $classesPerLevel = 3;
        $classNames = [
            'Primaire' => ['CP', 'CE1', 'CE2'],
            'Collège' => ['6ème', '5ème', '4ème'],
            'Lycée' => ['Seconde', 'Première', 'Terminale']
        ];

        foreach ($niveaux as $levelName => $niveau) {
            for ($i = 0; $i < $classesPerLevel; $i++) {
                $classe = new Classe();
                $classe->setNom($classNames[$levelName][$i]);
                $classe->setNiveau($niveau);
                $this->entityManager->persist($classe);
                $classes[] = $classe;
            }
        }
        $this->entityManager->flush();

        // Créer 40 parents avec 5 enfants chacun
        $output->writeln('Création des parents et enfants...');
        $tous_eleves = [];
        $numParent = 0;

        for ($p = 0; $p < 40; $p++) {
            $numParent++;
            
            $parentUser = new User();
            $parentUser->setEmail('parent' . $numParent . '@ecole.fr');
            $parentUser->setRoles(['ROLE_USER']);
            $parentPassword = $this->passwordHasher->hashPassword($parentUser, 'parent' . $numParent);
            $parentUser->setPassword($parentPassword);
            $parentUser->setIsVerified(true);
            $this->entityManager->persist($parentUser);

            $parent = new ParentEleve();
            $parent->setUser($parentUser);
            $parent->setNom($noms[array_rand($noms)]);
            $parent->setPrenom($prenoms[array_rand($prenoms)]);
            $parent->setAdresse($numParent . ' rue de l\'école');
            $parent->setTelephone('01' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT));
            $parent->setVille($villes[array_rand($villes)]);
            $parent->setCodePostal('750' . str_pad(mt_rand(0, 99), 2, '0', STR_PAD_LEFT));
            $parent->setPays('France');
            $this->entityManager->persist($parent);

            // Créer 5 enfants pour ce parent
            for ($e = 0; $e < 5; $e++) {
                $numEleve = ($p * 5) + $e + 1;
                
                $eleveUser = new User();
                $eleveUser->setEmail('eleve' . $numEleve . '@ecole.fr');
                $eleveUser->setRoles(['ROLE_USER']);
                $elevePassword = $this->passwordHasher->hashPassword($eleveUser, 'eleve' . $numEleve);
                $eleveUser->setPassword($elevePassword);
                $eleveUser->setIsVerified(true);
                $this->entityManager->persist($eleveUser);

                $eleve = new Eleve();
                $eleve->setUser($eleveUser);
                $eleve->setNom($parent->getNom());
                $eleve->setPrenom($prenoms_enfant[array_rand($prenoms_enfant)]);
                $eleve->setAdresse($parent->getAdresse());
                $eleve->setTelephone($parent->getTelephone());
                $eleve->setIsValidated(true);
                $eleve->setValidatedByAdmin($admin);
                $eleve->setValidatedAt(new \DateTime());
                $this->entityManager->persist($eleve);

                $parent->addEnfant($eleve);
                $tous_eleves[] = $eleve;
            }

            if ($p % 10 === 0) {
                $this->entityManager->flush();
                $output->writeln('  ' . $p . ' parents créés...');
            }
        }
        $this->entityManager->flush();

        // Associer les élèves aux classes
        $output->writeln('Association des élèves aux classes...');
        foreach ($tous_eleves as $eleve) {
            $classe = $classes[mt_rand(0, count($classes) - 1)];

            $classeEleve = new ClasseEleve();
            $classeEleve->setClasse($classe);
            $classeEleve->setEleve($eleve);
            $classeEleve->setDateValide(new \DateTime());
            $classeEleve->setDateFin(new \DateTime('+1 year'));
            $this->entityManager->persist($classeEleve);
        }
        $this->entityManager->flush();

        // Créer les rendez-vous
        $output->writeln('Création des rendez-vous...');
        $evenementCounter = 0;
        
        foreach ($classes as $classe) {
            $agenda = new Agenda();
            $dateDebut = new \DateTime('2026-04-15 14:00:00');
            $dateFinReccurence = new \DateTime('2026-06-30 16:00:00');
            $agenda->setHeureDebut($dateDebut);
            $agenda->setHeureFinReccurence($dateFinReccurence);
            $this->entityManager->persist($agenda);
            $this->entityManager->flush();

            // Créer 4 rendez-vous pour cette classe
            for ($i = 0; $i < 4; $i++) {
                $evenement = new Evenement();
                $evenement->setAgenda($agenda);
                $evenement->setAdminCreateur($admin);
                $evenement->setSujet('Réunion ' . $classe->getNom() . ' - Partie ' . ($i + 1));
                $evenement->setCorps('Rendez-vous avec les parents de la classe ' . $classe->getNom());
                $evenement->setLieu('Salle de classe ' . $classe->getNom());
                $evenement->setDuree(60);
                $evenement->setCreatedAt(new \DateTime());
                
                $this->entityManager->persist($evenement);
                
                // Ajouter les élèves et parents de cette classe
                foreach ($classe->getClasseEleves() as $classeEleve) {
                    $eleveTemp = $classeEleve->getEleve();
                    if ($eleveTemp && $eleveTemp->getUser()) {
                        $evenement->addUser($eleveTemp->getUser());
                    }
                    
                    foreach ($eleveTemp->getParentEleves() as $parentTemp) {
                        if ($parentTemp->getUser()) {
                            $evenement->addUser($parentTemp->getUser());
                        }
                    }
                }
                
                $evenementCounter++;
            }
            
            $this->entityManager->flush();
        }

        $output->writeln('✓ Fixtures chargées avec succès!');
        $output->writeln('');
        $output->writeln('Données créées:');
        $output->writeln('  - 1 Admin (admin@ecole.fr / admin123)');
        $output->writeln('  - 40 Parents');
        $output->writeln('  - 200 Enfants');
        $output->writeln('  - 3 Niveaux');
        $output->writeln('  - 9 Classes');
        $output->writeln('  - ' . $evenementCounter . ' Rendez-vous');

        return Command::SUCCESS;
    }
}
