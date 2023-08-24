<?php

namespace App\Controller;
use App\Entity\Sortie;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{

//    #[Route('/accueil', name: 'accueil')]
//    public function index(): Response
//    {
//        return $this->render('accueil/accueil.html.twig', [
//            'controller_name' => 'AccueilController',
//        ]);
//    }

    #[Route('/accueil', name: 'accueil')]
    public function index(
        SortieRepository $sortieRepository,
        ParticipantRepository $participantRepository,
    ): Response
    {
        $sorties = $sortieRepository->findLatestSix();
        $participants = $participantRepository->findAll();

        return $this->render(
            'accueil/accueil.html.twig',
            compact('sorties', 'participants')
        );
    }
}