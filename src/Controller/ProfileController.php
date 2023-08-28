<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/profile.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }


    // Lien vers le profil des personnes inscrites à une sortie
    #[Route('/profile/{id}', name: 'app_profile_inscrit')]
    public function profilInscrit(
        int $id,
        ParticipantRepository $participantRepository
    ): Response
    {
        // Pour récupérer l'id du participant inscrit
          $participant = $participantRepository->find($id);

        return $this->render('profile/profil_inscrit.html.twig', [
            'participant' => $participant
        ]);
    }



}
