<?php

namespace App\Form;

use App\Entity\Eleve;
use App\Entity\Admin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

/**
 * Formulaire pour permettre aux admins de valider les inscriptions des élèves
 */
class EleveValidationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isValidated', CheckboxType::class, [
                'label' => 'Valider l\'inscription',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('validate', SubmitType::class, [
                'label' => 'Valider l\'élève',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('reject', SubmitType::class, [
                'label' => 'Rejeter l\'inscription',
                'attr' => ['class' => 'btn btn-danger']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Eleve::class,
            'csrf_protection' => true,
            'csrf_field_name' => 'token',
        ]);
    }
}
