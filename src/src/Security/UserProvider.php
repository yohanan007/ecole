<?php
// src/Security/UserProvider.php
namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;


class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    /**
     * The loadUserByIdentifier() method was introduced in Symfony 5.3.
     * In previous versions it was called loadUserByUsername()
     *
     * Symfony calls this method if you use features like switch_user
     * or remember_me. If you're not using these features, you do not
     * need to implement this method.
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByUsername(string $identifier): UserInterface
    {
        $userRepository = new UserRepository();
        $user = $userRepository->findByLogin($identifier);
        if ($user !== null)
        {
            if($user->isVerified)
            {
                switch($user->getRoles())
                {
                    //eleve
                    case []:
                    return new RedirectResponse('app_parent_eleve_index', []);
                    break;
                    //parent
                    case ['ROLE_USER']:
                    return new RedirectResponse('app_home', []);
                    break;
                    default:
                    return new RedirectResponse('app_home', []);
                    break;
                }
            }
            else
            {
                return new RedirectResponse('app_home', []);
            }
        }
        else
        {
            throw new \Exception('no authorizated ');
        }
        
        // Load a User object from your data source or throw UserNotFoundException.
        // The $identifier argument is whatever value is being returned by the
        // getUserIdentifier() method in your User class.
        
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Return a User object after making sure its data is "fresh".
        // Or throw a UserNotFoundException if the user no longer exists.
        throw new \Exception('TODO: fill in refreshUser() inside '.__FILE__);
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class)
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    /**
     * Upgrades the hashed password of a user, typically for using a better hash algorithm.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // TODO: when hashed passwords are in use, this method should:
        // 1. persist the new password in the user storage
        // 2. update the $user object with $user->setPassword($newHashedPassword);
    }
}