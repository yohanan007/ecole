<?php

namespace App\Form;

use App\Entity\ParentEleve;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UpdateParentEleveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre nom']
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre prénom']
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre adresse complète']
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => '0X XX XX XX XX']
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre ville']
            ])
            ->add('code_postal', TextType::class, [
                'label' => 'Code postal',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'XXXXX']
            ])
            ->add('pays', TextType::class, [
                'label' => 'Pays',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'France']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer les modifications',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParentEleve::class,
        ]);
    }
}
