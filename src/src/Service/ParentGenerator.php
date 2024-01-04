<?php

namespace App\Service;

use App\Repository\ParentEleveRepository;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;


Class ParentGenerator{

   private  $ParentRepository;
   private  $user;
   private $ParentCourant;

   function __construct(ParentEleveRepository $ParentEleveRepository, Security $security)
   {
        $this->ParentRepository = $ParentEleveRepository;
        $this->user = $security->getUser();
   }

   public function getParent(User $user = null)
   {
        $this->user = ($user == null) ? $this->user : $user;
        $ent_parent = $this->ParentRepository->findOneBy(array("user"=>$this->user));
        $this->ParentCourant = $ent_parent;
        return $ent_parent;
   }

   public function isVerified(User $user = null)
   {
        $ent_parent = $this->getParent($user);
        $b_verified = ($ent_parent == null) ? false : $this->user->isVerified();
        return $b_verified;
   }

   public function getParentCourant()
   {
    return $this->ParentCourant;
   }

   public function getEleveParent(User $user = null)
   {
       return  ($this->isVerified($user)) ? $this->ParentCourant->getEnfant() : null;
   }

}