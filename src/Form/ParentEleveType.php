<?php

namespace App\Form;

use App\Entity\ParentEleve;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParentEleveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('adresse')
            ->add('telephone')
            ->add('ville')
            ->add('code_postal')
            ->add('pays')
            ->add('enfant')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParentEleve::class,
        ]);
    }
}
