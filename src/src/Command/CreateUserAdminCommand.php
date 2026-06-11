<?php
// src/Command/CreateUserAdminCommand.php
namespace App\Command;

use App\Entity\Admin;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user-admin',
    description: 'Creates a new admin user.',
)]
class CreateUserAdminCommand extends Command
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;
    private $entityManager;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->entityManager = $userRepository->getEntityManager();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('mail', InputArgument::REQUIRED, 'Admin user mail')
            ->addArgument('prenom', InputArgument::REQUIRED, 'Admin user prenom')
            ->addArgument('nom', InputArgument::REQUIRED, 'Admin user nom')
            ->addArgument('telephone', InputArgument::OPTIONAL, 'Admin user telephone')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin user password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mail = $input->getArgument('mail');
        $password = $input->getArgument('password');
        $nom = $input->getArgument('nom');
        $prenom = $input->getArgument('prenom');
        $telephone = $input->getArgument('telephone') ?? '0000000000';

        if (filter_var($mail, FILTER_VALIDATE_EMAIL) && !empty($password)) {
            $output->writeln([
                '<info>Starting admin user creation</info>',
                '<info>==============================</info>',
                '',
            ]);

            $user = new User();
            $user->setEmail($mail);
            $user->setIsVerified(true);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
            $this->userRepository->add($user);

            $admin = new Admin();
            $admin->setNom($nom);
            $admin->setPrenom($prenom);
            $admin->setTelephone($telephone);
            $admin->setUser($user);
            $admin->setIsActive(true);
            $this->entityManager->persist($admin);
            $this->entityManager->flush();

            $output->writeln([
                '<info>Admin user created successfully!</info>',
                '',
            ]);

            return Command::SUCCESS;
        } else {
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $output->writeln('<error>Invalid email format.</error>');
            }
            if (empty($password)) {
                $output->writeln('<error>Password cannot be empty.</error>');
            }
            return Command::FAILURE;
        }
    }
}