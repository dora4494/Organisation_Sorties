<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\CallbackTransformer;
use Vich\UploaderBundle\Form\Type\VichImageType;


/**
 * @method getParameter(string $string)
 */
class ProfileModifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => ['class' => 'h-10 border mt-1 rounded px-4 w-full bg-gray-50'],
            ])
            ->add('prenom', null, [
                'attr' => ['class' => 'h-10 border mt-1 rounded px-4 w-full bg-gray-50'],
            ])
            ->add('pseudo', null, [
                'attr' => ['class' => 'h-10 border mt-1 rounded px-4 w-full bg-gray-50'],
            ])
            ->add('mail', null, [
                'attr' => ['class' => 'h-10 border mt-1 rounded px-4 w-full bg-gray-50'],
            ])
            ->add('telephone', null, [
                'attr' => ['class' => 'h-10 border mt-1 rounded px-4 w-full bg-gray-50'],
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => false,
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
                'attr' => ['class' => 'file-input file-input-bordered w-full max-w-xs'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
        $resolver->setDefined('images_directory');
    }
}
