<?php

namespace App\Controller;

use App\Form\ProfileModifType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;



class ProfileModifController extends AbstractController
{
    #[Route('/profilemodif', name: 'app_profilemodif_index')]
    public function modif(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $imagesDirectory = $this->getParameter('images_directory');
        // Create an instance of the ProfileModifType form
        $form = $this->createForm(ProfileModifType::class, $user, [
            'images_directory' => $imagesDirectory,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*$imageFile = $form['imageFile']->getData();
            if ($imageFile) {
                // Gérez l'upload du fichier ici, par exemple, en le déplaçant vers un répertoire d'images
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // Déplacez le fichier vers le répertoire où vous souhaitez le stocker
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );

                // Mettez à jour la propriété "image" de l'entité Participant avec le nom du fichier
                $this->getUser()->setImageFile($newFilename);*/

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile');
        }


        return $this->render('profile/profileModif.html.twig', [
            'controller_name' => 'ProfileModifController',
            'profileModifForm' => $form->createView(),
        ]);

    }
}
