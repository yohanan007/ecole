<?php

namespace App\Security;

use App\Entity\Evenement;
use App\Entity\Admin;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EvenementVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $subject instanceof Evenement;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // S'il n'y a pas d'utilisateur, pas d'accès
        if (!$user instanceof UserInterface) {
            return false;
        }

        $evenement = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($evenement, $user);
            case self::EDIT:
                return $this->canEdit($evenement, $user);
            case self::DELETE:
                return $this->canDelete($evenement, $user);
        }

        return false;
    }

    private function canView(Evenement $evenement, UserInterface $user): bool
    {
        // Tous les utilisateurs peuvent voir un RDV
        return true;
    }

    private function canEdit(Evenement $evenement, UserInterface $user): bool
    {
        // Seul l'admin qui a créé le RDV peut l'éditer,
        // ou un SUPER_ADMIN
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($evenement->getAdminCreateur() && $evenement->getAdminCreateur()->getUser() === $user) {
            return true;
        }

        return false;
    }

    private function canDelete(Evenement $evenement, UserInterface $user): bool
    {
        // Seul l'admin qui a créé le RDV peut le supprimer,
        // ou un SUPER_ADMIN
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($evenement->getAdminCreateur() && $evenement->getAdminCreateur()->getUser() === $user) {
            return true;
        }

        return false;
    }
}
