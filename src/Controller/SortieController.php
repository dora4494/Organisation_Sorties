<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{
    // Création d'une sortie
    #[Route('/creer', name: '_creer')]
    public function creer(
        EntityManagerInterface $entityManager,
        Request $requete,
       // ParticipantRepository $participantRepository,
       //UserRepository $userRepository,
        EtatRepository $etatRepository
    ) : Response
    {
        $sortie = new Sortie();

      //  $sortie->setIdOrganisateur($this->getUser()->getUserIdentifier());

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($requete);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            // Si la sortie est "enregistrée"
            if ($sortieForm->get('enregistrer')->isClicked()) {

               $sortie->setEtatsNoEtat($etatRepository->find(1));

            //   $organisateur = $participantRepository->find($this->getUser());
             //  $sortie->setOrganisateur($organisateur);

             //   $sortie->setOrganisateur($this->getUser()->getUserIdentifier());

                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('sortie_index');
            }


            // Si la sortie est "publiée"
            if ($sortieForm->get('publier')->isClicked()) {
                $sortie->setEtatsNoEtat($etatRepository->find(2));


                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('sortie_index');
            }


        }
            return $this->render('sortie/creer-sortie.html.twig', [
                'sortieForm' => $sortieForm->createView(),
            ]);
        }

}
