<?php

namespace App\Service;

use Symfony\Component\Security\Core\Security;
use App\Entity\User;

class UserGenerator{

    private $user; 
    private $security;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
        $this->security = $security;
    }

    private function getUser(User $user = null)
    {
        $this->user = (is_null($user))? $this->user : $user;
        return $this->user;
    }

    public function isAdmin(User $user = null)
    {
        $b_isAdmin = false;
        if(!(is_null($this->getUser($user))))
        {
            $art_role = $this->user->getRoles();
            $b_isAdmin = ((in_array("ROLE_ADMIN",$art_role) === false))? false : true;
        }
        return $b_isAdmin;
    }

    
}