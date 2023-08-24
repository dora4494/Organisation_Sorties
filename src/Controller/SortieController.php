<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{


    #[Route('/liste', name: '_liste')]
    public function liste(
        SortieRepository $sortieRepository
    ): Response
    {
        $sorties = $sortieRepository->findAll();
        return $this->render('liste_sorties/show.html.twig',
            compact('sorties'));
    }


    // Création d'une sortie
    #[Route('/creer', name: '_creer')]
    public function creer(
        EntityManagerInterface $entityManager,
        Request                $requete,
        // ParticipantRepository $participantRepository,
        //UserRepository $userRepository,
        EtatRepository         $etatRepository
    ): Response
    {
        $sortie = new Sortie();

        //  $sortie->setIdOrganisateur($this->getUser()->getUserIdentifier());

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($requete);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            // Si la sortie est "enregistrée"
            if ($sortieForm->get('enregistrer')->isClicked()) {

                $sortie->setEtatsNoEtat($etatRepository->find(1));

                //   $organisateur = $participantRepository->find($this->getUser());
                //  $sortie->setOrganisateur($organisateur);

                //   $sortie->setOrganisateur($this->getUser()->getUserIdentifier());

                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('sortie_liste');
            }


            // Si la sortie est "publiée"
            if ($sortieForm->get('publier')->isClicked()) {
                $sortie->setEtatsNoEtat($etatRepository->find(2));


                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('sortie_liste');
            }


        }
        return $this->render('sortie/creer-sortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
        ]);
    }



    // Détails d'une sortie

/*  Pour afficher une sortie - il faut être connecté
    #[Route('/details'/{sortie}, name: '_details', requirements: ["sortie" =>"\d+"])]
    public function details(
        Sortie $sortie,

    ): Response
    {
        return $this->render('sortie/detail.html.twig',
            compact('sortie'));
    }

*/
// Pour afficher 1 sortie enregistrée manuellement dans la BDD
    #[Route('/details/{id}', name: '_detail', requirements: ["id" => "\d+"])]
    public function detail(
        SortieRepository $sortieRepository,
        int $id=2,
    ): Response
    {

        // Pour récupérer une seule série en BDD
        $sortie = $sortieRepository->findOneBy(
            ["id" => $id]
        );
        return $this->render('sortie/detail.html.twig',
            compact('sortie')
        );
    }



}
