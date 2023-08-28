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
use DateTime;
//use Symfony\Component\Validator\Constraints\DateTime;


#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{


    // Création d'une sortie
    #[Route('/creer', name: '_creer')]
    public function creer(
        EntityManagerInterface $entityManager,
        Request                $requete,
        ParticipantRepository  $participantRepository,
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


                $sortie->setIdOrganisateur($participantRepository->find($this->getUser()));


                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', 'Sortie enregistrée');
                return $this->redirectToRoute('listeSorties');
            }


            // Si la sortie est "publiée"
            if ($sortieForm->get('publier')->isClicked()) {

                $sortie->setEtatsNoEtat($etatRepository->find(2));

                $sortie->setIdOrganisateur($participantRepository->find($this->getUser()));

                //  $organisateur = $participantRepository->find($this->getUser()->getUserIdentifier());
                // $sortie->setIdOrganisateur($organisateur);

                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', 'Sortie publiée');
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
    #[Route('/inscription/{sortie}', name: '_inscription')]
    public function inscription(

        SortieRepository       $sortieRepository,
        Request                $request,
        EntityManagerInterface $entityManager,
        ParticipantRepository  $participantRepository,
        EtatRepository         $etatRepository,
        Sortie                 $sortie

    ): Response
    {


        $etat = $sortie->getEtatsNoEtat()->getId();

        $participant = $participantRepository->find($this->getUser());


        // Vérifier la date de cloture, le nb d'inscription max, le statut 'ouvert' et que le user ne soit pas l'organisateur
        if ($sortie->getDateCloture() > new DateTime('NOW') && $sortie->getNbInscriptionsMax() > $sortie->getParticipants()->count() && $etat == 2 && $participant !== $sortie->getIdOrganisateur()) {

            $participant = $participantRepository->find($this->getUser());
            $sortie->addParticipant($participant);

            // Si le nb d'inscrits est atteint ou que la date de clôture est dépassée

            if ($sortie->getNbInscriptionsMax() == $sortie->getParticipants()->count() || $sortie->getDateCloture() < new DateTime('NOW')) {
                $sortie->setEtatsNoEtat($etatRepository->find(3));
            }


            $entityManager->persist($sortie);

            $entityManager->flush();

            $this->addFlash('success', 'Vous êtes inscrit-e à la sortie');
            return $this->redirectToRoute('listeSorties');

        } else {
            if ($participant === $sortie->getIdOrganisateur()) {
                $this->addFlash("fail", "En tant qu'organisateur-trice, vous ne pouvez pas vous inscrire");
            } else {
                $this->addFlash("fail", "Vous n'avez pas pu être ajouté-e");
            }
            return $this->redirectToRoute('accueil');

        }
    }


    // Se désister d'une sortie
    #[Route('/desister/{sortie}', name: '_desister')]
    public function desister(
        EntityManagerInterface $entityManager,
        ParticipantRepository  $participantRepository,
        // Participant            $participant,
        SortieRepository       $sortieRepository,
        Sortie                 $sortie
    ): Response
    {

        $participant = $participantRepository->find($this->getUser());

        if ($sortie->getParticipants()->contains($participant)) {


            // Vérifier que la sortie n'a pas débuté
            if ($sortie->getDateHeureDebut() > new \DateTime()) {


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
        } else {
            $this->addFlash('error', 'Action impossible car vous n\'êtes pas inscrit-e');
        }
        return $this->redirectToRoute('listeSorties');
    }


    // Modifier une sortie
    #[Route('/modifier/{sortie}', name: '_modifier')]
    public function modifier(
        EntityManagerInterface $entityManager,
        Request                $requete,
        Sortie                 $sortie,
        EtatRepository         $etatRepository,
        ParticipantRepository  $participantRepository
    ): Response
    {

        $participant = $participantRepository->find($this->getUser());

        // Vérifier que le User est bien l'organisateur et que la sortie est à l'état "créée"
        if ($participant === $sortie->getIdOrganisateur() && $sortie->getEtatsNoEtat()->getId() == 1) {

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

        } else {
            $this->addFlash('fail', 'Action impossible');
            return $this->redirectToRoute('listeSorties');
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
        EtatRepository         $etatRepository,
        ParticipantRepository  $participantRepository
    ): Response
    {

        $participant = $participantRepository->find($this->getUser());

        // Vérifier que le User est bien l'organisateur
        if ($participant === $sortie->getIdOrganisateur()) {


            $annulerSortieForm = $this->createForm(AnnulerSortieType::class, $sortie);

            $annulerSortieForm->handleRequest($requete);

            if ($annulerSortieForm->isSubmitted() && $annulerSortieForm->isValid()) {


                $sortie->setEtatsNoEtat($etatRepository->find(6));


                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('listeSorties');
            }
        } else {
            $this->addFlash('fail', 'Action impossible');
            return $this->redirectToRoute('listeSorties');
        }
        return $this->render('sortie/annuler.html.twig', [
            'annulerSortieForm' => $annulerSortieForm->createView(),
            "sortie" => $sortie,]);
    }




// Supprimer une sortie - uniquement pour les sorties restées en état "enregistrée"
    #[Route('/supprimer/{sortie}', name: '_supprimer')]
    public function supprimer(
        EntityManagerInterface $entityManager,
        Sortie                 $sortie,
        ParticipantRepository   $participantRepository,
        EtatRepository          $etatRepository
    ): Response
    {

        $participant = $participantRepository->find($this->getUser());

        // Vérifier que le User est bien l'organisateur
        if ($participant === $sortie->getIdOrganisateur() && $sortie->getEtatsNoEtat()->getId() == 1) {

            // Modifie l'état en "supprimée"
            $sortie->setEtatsNoEtat($etatRepository->find(8));
            $participant->removeSorty($sortie);
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été supprimée');
        } else {
            $this->addFlash('fail', 'Action impossible');
        }

        return $this->redirectToRoute('listeSorties');
    }



    // Lien vers le profil des personnes inscrites à une sortie
    #[Route('/profile/{id}', name: 'app_profile_inscrit')]
    public function profilInscrit(
        int $id,
        Sortie $sortie,
        ParticipantRepository $participantRepository
    ): Response
    {
        // Pour récupérer l'id du participant inscrit
        $participant = $participantRepository->find($id);

        return $this->render('profile/profil_inscrit.html.twig', [
            'participant' => $participant
        ]);
    }



    /*
      // Changement "Etat" des sorties"
      #[Route('/etat/', name: '_etat')]
      public function etat(
          EntityManagerInterface $entityManager,
          Sortie                 $sortie,
          EtatRepository         $etatRepository,
          SortieRepository       $sortieRepository
      ): Response
      {

          $lstSorties = $sortieRepository->findAll();
          $cloneDateHeureDebut = clone $sortie->getDateHeureDebut();
         $dateHeureFin = $cloneDateHeureDebut->modify('+' . $sortie->getDuree() . ' minutes');
         $dateArchivage = $cloneDateHeureDebut->modify('+30 days');

          foreach ($lstSorties as $s) {

              // Passer de l'état "Clôturé" à "En cours" ou "Terminé"
              if ($s->getDateHeureDebut() <= new DateTime('NOW')) {

                  // En cours
                  if ($dateHeureFin >= new DateTime()) { // pour convertir la durée en secondes
                      $s->setEtatsNoEtat($etatRepository->find(4));

                      // Terminée
                  } else {
                      $s->setEtatsNoEtat($etatRepository->find(5));
                  }


              }

              // Archivée
              if ($s->getEtatsNoEtat()->getId() == 5 || $s->getEtatsNoEtat()->getId() == 6) {
                  if ($dateArchivage >= new DateTime()) {
                      $s->setEtatsNoEtat($etatRepository->find(7));
                  }
              }


              $entityManager->persist($s);
              $entityManager->flush();

          }
          return $this->render('liste_sorties/show.html.twig');

      }


      #[Route('/etat/', name: '_etat')]
      public function etat(
          EntityManagerInterface $entityManager,
          EtatRepository $etatRepository,
          SortieRepository $sortieRepository
      ): Response {

      $lstSorties = $sortieRepository->findAll();

  foreach ($lstSorties as $s) {
      $now = new DateTime('NOW');
      $cloneDateHeureDebut = clone $s->getDateHeureDebut();
      $dateHeureFin = $cloneDateHeureDebut->modify('+' . $s->getDuree() . ' minutes');
      $dateArchivage = clone $s->getDateHeureDebut()->modify('+30 days');

      if ($s->getDateHeureDebut() <= $now) {
          if ($dateHeureFin <= $now) {
              $s->setEtatsNoEtat($etatRepository->find(5)); // Terminée
          } else {
              $s->setEtatsNoEtat($etatRepository->find(4)); // En cours
          }

          // Vérifiez si l'état est "Clôturé" (5) ou "Annulé" (6) avant de passer à "Archivée" (7)
          if (($s->getEtatsNoEtat()->getId() == 5 || $s->getEtatsNoEtat()->getId() == 6) && $dateArchivage <= $now) {
              $s->setEtatsNoEtat($etatRepository->find(7)); // Archivée
          }

          $entityManager->persist($s);
      }
  }

  $entityManager->flush();

          return $this->render('liste_sorties/show.html.twig');
      }
  */



}













