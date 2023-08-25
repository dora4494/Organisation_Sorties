<?php

namespace App\Controller;

use App\Form\ProfileModifType;
use ContainerAL90Cse\getProfileModifTypeService;
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
        // Create an instance of the ProfileModifType form
        $form = $this->createForm(ProfileModifType::class, $user);

        // Handle the form submission if the request is POST
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the form data, e.g., update the user's profile
            $entityManager->persist($user);
            $entityManager->flush();
            // Redirect to another page after processing the form
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/profileModif.html.twig', [
            'controller_name' => 'ProfileModifController',
            'profileModifForm' => $form->createView(), // Pass the form to the template
        ]);

    }
}
