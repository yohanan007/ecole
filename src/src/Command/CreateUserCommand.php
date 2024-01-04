<?php
// src/Command/CreateUserCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;




class CreateUserCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-user';

    protected static $defaultDescription = 'Creates a new user.';

    public function __construct(bool $requirePassword = false, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        $this->requirePassword = $requirePassword;
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action',true,'type d\'action ')
            ->addArgument('mail', true, 'User mail')
            ->addArgument('password', $this->requirePassword ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'User password')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ... put here the code to create the user
        $t_action = $input->getArgument('action');
        $t_mail = $input->getArgument('mail');
        $t_password = $input->getArgument('password');
        if ($t_action !== "")
        {
            if((filter_var($t_mail, FILTER_VALIDATE_EMAIL))&&($t_password!==""))
            {
                        $output->writeln([
                                            '<info>debut creation utilisateur</>',
                                            '<info>==========================</>',
                                            '',
                                        ]);
                $user = new User();
                $user->setEmail($t_mail);
                $user->setIsVerified(true);
                $user->setPassword($this->userPasswordHasher->hashPassword(
                $user,
                $t_password
                    ));
                $this->userRepository->add($user);
                $output->writeln([
                    '<info>creation ok</>',
                    '<info>==========================</>',
                    '',
                ]);
                return Command::SUCCESS;

            }
            else
            {
                $output->writeln([
                    '<info>problême de paramétre</>',
                    '<info>==========================</>',
                    '',
                ]);
                return Command::INVALID;
            }
        }
        else
        {
                $output->writeln([
                '<info>problême de paramétre</>',
                '<info>==========================</>',
                '',
                ]);
            return Command::INVALID;
        }
        
        // this method must return an integer number with the "exit status code"
        // of the command. You can also use these constants to make code more readable

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}