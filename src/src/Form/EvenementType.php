<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Eleve;
use App\Entity\Classe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sujet', TextType::class, [
                'label' => 'Sujet du RDV',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: Conseil de classe, Réunion parents']
            ])
            ->add('corps', TextareaType::class, [
                'label' => 'Description/Détails',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Détails du rendez-vous',
                    'rows' => 4
                ]
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu',
                'required' => false,
                'attr' => ['placeholder' => 'Salle, École, Adresse...']
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'required' => false,
                'attr' => ['placeholder' => '60'],
                'help' => 'Durée du rendez-vous en minutes'
            ])
            ->add('recurrence', ChoiceType::class, [
                'label' => 'Récurrence',
                'required' => true,
                'choices' => [
                    'Pas de récurrence' => 'aucune',
                    'Quotidien' => 'jour',
                    'Hebdomadaire' => 'semaine',
                    'Bi-hebdomadaire' => 'deuxSemaines',
                    'Mensuel' => 'mois',
                ],
                'help' => 'Fréquence de répétition du RDV'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Créer/Mettre à jour le RDV',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
            'csrf_protection' => true,
            'csrf_field_name' => 'token',
        ]);
    }
}
