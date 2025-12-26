<?php

namespace App\Service;

use App\Repository\ParentEleveRepository;
use Symfony\Bundle\SecurityBundle\Security;
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

   public function getParent(?User $user = null)
   {
        $this->user = ($user == null) ? $this->user : $user;
        $ent_parent = $this->ParentRepository->findOneBy(array("user"=>$this->user));
        $this->ParentCourant = $ent_parent;
        return $ent_parent;
   }

   public function isVerified(?User $user = null)
   {
     $user = ($user == null) ? $this->user : $user;
     $ent_parent = $this->getParent($user);
     $b_verified = ($ent_parent == null) ? false : $ent_parent->getUser()->isVerified();
     return $b_verified;
   }

   public function getParentCourant(?User $user = null)
   {
     $user = ($user == null) ? $this->user : $user;
     return ($this->isVerified($user)) ? $this->ParentCourant : null;
   }

   public function getEleveParent(?User $user = null)
   {
     $user = ($user == null) ? $this->user : $user;
     return  ($this->isVerified($user)) ? $this->ParentCourant->getEnfant() : null;
   }

}