<?php
// src/Command/CreateUserCommand.php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user.',
)]
class CreateUserCommand extends Command
{
    private bool $requirePassword;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, bool $requirePassword = true)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->requirePassword = $requirePassword;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, 'Type of action')
            ->addArgument('mail', InputArgument::REQUIRED, 'User mail')
            ->addArgument('password', $this->requirePassword ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');
        $mail = $input->getArgument('mail');
        $password = $input->getArgument('password');

        if ($action !== "") {
            if (filter_var($mail, FILTER_VALIDATE_EMAIL) && ($this->requirePassword ? $password !== null : true)) {
                $output->writeln([
                    '<info>Starting user creation</info>',
                    '<info>==========================</info>',
                    '',
                ]);

                $user = new User();
                $user->setEmail($mail);
                $user->setIsVerified(true);
                $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
                $this->userRepository->add($user);

                $output->writeln([
                    '<info>User creation successful</info>',
                    '<info>==========================</info>',
                    '',
                ]);

                return Command::SUCCESS;
            } else {
                $output->writeln([
                    '<error>Invalid parameters</error>',
                    '<error>==========================</error>',
                    '',
                ]);
                return Command::INVALID;
            }
        } else {
            $output->writeln([
                '<error>Invalid parameters</error>',
                '<error>==========================</error>',
                '',
            ]);
            return Command::INVALID;
        }
    }
}