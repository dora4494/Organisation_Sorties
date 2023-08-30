<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => [
                    'placeholder' => 'ex : Cinéma',
                    ]
            ])
            ->add('dateHeureDebut', null, [
                'label' => "Date et heure : ",
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d\TH:i'),
                ],
            ])
            ->add('dateCloture', null, [
                'label' => "Date limite d'inscription : ",
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d\TH:i'),
                ],
            ])
            ->add('nbInscriptionsMax', null, [
                'label' => 'Nombre de places : '
            ])
            ->add('duree', null, [
                'label' => 'Durée (minutes) : ',
                'attr'=>['min'=>0]
            ])
            ->add('descriptionInfos', null, [
                'label' => 'Description et infos : ',
                'attr' => [
                    'placeholder' => 'ex : Je propose une sortie au cinéma pour voir le film ...',
                ]
            ])

            ->add('Site', null, [
            ])

           // ->add('etatSortie')
           //->add('urlPhoto')
           // ->add('etatsNoEtat')
           // ->add('idOrganisateur')
            ->add('lieuxNoLieu', EntityType::class, [
                'class' => Lieu::class,
               'choice_label' => 'nom',
               'label' => 'Lieu : ',
               'placeholder' => '--- Sélectionnez un lieu ---',
           ])

            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer'
            ])

            ->add('publier', SubmitType::class, [
                'label' => 'Publier'
            ])
 /*
                        ->add('annuler', SubmitType::class, [
                            'label' => 'Annuler'
                        ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
