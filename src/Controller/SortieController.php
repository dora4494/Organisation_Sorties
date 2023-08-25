<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\AnnulerSortieType;
use App\Form\ParticipantType;
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
use Symfony\Component\Validator\Constraints\DateTime;

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{


    // Création d'une sortie
    #[Route('/creer', name: '_creer')]
    public function creer(
        EntityManagerInterface $entityManager,
        Request                $requete,
         ParticipantRepository $participantRepository,
        EtatRepository         $etatRepository
    ): Response
    {
        $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($requete);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            // Si la sortie est "enregistrée"
            if ($sortieForm->get('enregistrer')->isClicked()) {

                $sortie->setEtatsNoEtat($etatRepository->find(1));
                $organisateur = $participantRepository->find($this->getUser()->getUserIdentifier());
                $sortie->setIdOrganisateur($organisateur);


                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('listeSorties');
            }


            // Si la sortie est "publiée"
            if ($sortieForm->get('publier')->isClicked()) {
                $sortie->setEtatsNoEtat($etatRepository->find(2));
                $organisateur = $participantRepository->find($this->getUser()->getUserIdentifier());
                $sortie->setIdOrganisateur($organisateur);

                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('listeSorties');
            }


        }
        return $this->render('sortie/creer-sortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
        ]);
    }


    // Détails d'une sortie
    #[Route('/details/{sortie}', name: '_details')]
    public function details(
        Sortie $sortie,

    ): Response
    {
        return $this->render('sortie/detail.html.twig',
            compact('sortie'));
    }


    //  Inscription à une sortie
    #[Route('/inscription/{sortie}', name: '_inscription', requirements: ["sortie" => "\d+"])]
    public function inscription(
        Sortie                 $sortie,
        SortieRepository       $sortieRepository,
        Request                $request,
        EntityManagerInterface $entityManager,
        ParticipantRepository  $participantRepository,
        EtatRepository         $etatRepository,
        //  Etat                   $etat,
        // Participant            $participant

    ): Response
    {

        $etat = $sortie->getEtatsNoEtat()->getId();

    //    $participantForm = $this->createForm(ParticipantType::class);
    //    $participantForm->handleRequest($request);

      //  if ($participantForm->isSubmitted() && $participantForm->isValid()) {

            // Vérifier la date de cloture, le nb d'inscription max et le statut 'ouvert'
            if ($sortie->getDateCloture() > new DateTime('NOW') && $sortie->getNbInscriptionsMax() > count($sortie->getParticipants()) && $etat == 2) {

                $participant = $participantRepository->find($this->getUser()->getUserIdentifier());

                $sortie->addParticipant($participant);

                // Si le nb d'inscrits est atteint ou que la date de clôture est dépassée

                if ($sortie->getNbInscriptionsMax() == $sortie->getParticipants()->count() || $sortie->getDateCloture() == new DateTime('NOW')) {
                    $sortie->setEtatsNoEtat($etatRepository->find(3));

                }

                $entityManager->persist($sortie);

                $entityManager->flush();

                return $this->redirectToRoute('listeSorties');

            } else {
                $this->addFlash("fail", "Vous n'avez pas pu être ajouté-e");
            }
      //  }

        return $this->render('sortie/detail.html.twig', [
            "sortie" => $sortie,
           // "participantForm" => $participantForm->createView()
        ]);


    }


    // Se désister d'une sortie
    #[Route('/desister/{sortie}', name: '_desister')]
    public function desister(
        EntityManagerInterface $entityManager,
        ParticipantRepository  $participantRepository,
       // Participant            $participant,
        SortieRepository       $sortieRepository
    ): Response
    {
        $sortie = new Sortie();

        // Vérifier que la sortie n'a pas débuté et que la date de limite d'inscription n'est pas dépassée
        if ($sortie->getDateHeureDebut() > new \DateTime()) {

            $participant = $participantRepository->find($this->getUser()->getUserIdentifier());
            //var_dump($participant);
            // Supprime la sortie du profil participant
            $participant->removeSorty($sortie);

            // Vérifier que la date de limite d'inscription n'est pas dépassée
            if ($sortie->getDateCloture() > new \DateTime()) {
                // Supprime le participant de la sortie
                $sortie->removeParticipant($participant);
            }
            if ($sortie->getEtatsNoEtat()->getId() == 3) {
                $sortie->setEtatsNoEtat($entityManager->getRepository(Etat::class)->find(2));
            }

            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Vous êtes désinscrit-e');
        }
        return $this->redirectToRoute('listeSorties');


    }


    // Modifier une sortie
    #[Route('/modifier/{sortie}', name: '_modifier')]
    public function modifier(
        EntityManagerInterface $entityManager,
        Request                $requete,
        Sortie                 $sortie,
        EtatRepository         $etatRepository
    ): Response
    {
/*     TODO:A DECOMMENTER UNE FOIS QUE LE LOGIN FONCTIONNERA
        if ($sortie->getIdOrganisateur() != $this->getUser()->getUserIdentifier()) {
            return $this->redirectToRoute('listeSorties');
        }*/

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($requete);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            // Si la sortie est "enregistrée"
            if ($sortieForm->get('enregistrer')->isClicked()) {

                $sortie->setEtatsNoEtat($etatRepository->find(1));


                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('listeSorties');
            }


            // Si la sortie est "publiée"
            if ($sortieForm->get('publier')->isClicked()) {
                $sortie->setEtatsNoEtat($etatRepository->find(2));


                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('listeSorties');
            }


        }
        return $this->render('sortie/modifier-sortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
        ]);
    }



// Annuler une sortie
    #[Route('/annuler/{sortie}', name: '_annuler')]
    public function annuler(
        EntityManagerInterface $entityManager,
        Request                $requete,
        Sortie                 $sortie,
        EtatRepository         $etatRepository
    ): Response
    {
        $annulerSortieForm = $this->createForm(AnnulerSortieType::class, $sortie);

        $annulerSortieForm->handleRequest($requete);

        if ($annulerSortieForm->isSubmitted() && $annulerSortieForm->isValid()) {


            $sortie->setEtatsNoEtat($etatRepository->find(6));


                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('listeSorties');
            }

        return $this->render('sortie/annuler.html.twig', [
            'annulerSortieForm' => $annulerSortieForm->createView(),
                "sortie" => $sortie,]);
    }







}













