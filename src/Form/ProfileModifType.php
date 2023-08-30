<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\CallbackTransformer;

/**
 * @method getParameter(string $string)
 */
class ProfileModifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('pseudo')
            ->add('telephone')
            ->add('mail')
            ->add('image', FileType::class, [
                'label' => 'Image de profil',
                'required' => false,
            ]);

        $builder->get('image')->addModelTransformer(new CallbackTransformer(
            function (?File $file) {
                // Transformation de File à string
                return $file;
            },
            function ($string) {
                // Transformation de string à File
                if ($string) {
                    $filePath = $this->getParameter('images_directory') . '/' . $string;
                    return new File($filePath);
                }
                return null; // retourne null si $string est vide
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
        $resolver->setDefined('images_directory');
    }
}
