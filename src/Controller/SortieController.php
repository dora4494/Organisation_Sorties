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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
                $this->addFlash('success', 'Sortie enregistrée !');
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
                $this->addFlash('success', 'Sortie publiée !');
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
        // Vérifier que le participant est connecté
        try {
            $participant = $this->getUser();
            if (!$participant) {
                throw new AccessDeniedException('Vous devez être connecté-e pour vous inscrire à une sortie ! ');
            }


            $etat = $sortie->getEtatsNoEtat()->getId();

            $participant = $participantRepository->find($this->getUser());


            // Vérifier que le participant n'est pas déjà inscrit
            if ($sortie->getParticipants()->contains($participant)) {
                $this->addFlash('fail', 'Vous êtes déjà inscrit-e à cette sortie !');
                return $this->redirectToRoute('listeSorties');
            }

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

                $this->addFlash('success', 'Vous êtes inscrit-e à la sortie !');
                return $this->redirectToRoute('listeSorties');

            } else {
                if ($participant === $sortie->getIdOrganisateur()) {
                    $this->addFlash("fail", "En tant qu'organisateur-trice, vous ne pouvez pas vous inscrire !");
                } else {
                    $this->addFlash("fail", "Vous n'avez pas pu être ajouté-e, la sortie n'est pas ouverte !");
                }
                return $this->redirectToRoute('listeSorties');
            }
            // Redirige l'utilisateur s'il n'est pas connecté
        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_login');
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
        // Vérifier que le participant est connecté
        try {
            $participant = $this->getUser();
            if (!$participant) {
                throw new AccessDeniedException('Vous devez être connecté-e pour vous désinscrire d\'une sortie ! ');
            }


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
                    $this->addFlash('success', 'Vous êtes désinscrit-e !');
                }
            } else {
                $this->addFlash('error', 'Action impossible car vous n\'êtes pas inscrit-e !');
            }
            return $this->redirectToRoute('listeSorties');


            // Redirige l'utilisateur s'il n'est pas connecté
        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
    }


// Modifier une sortie
    #[
        Route('/modifier/{sortie}', name: '_modifier')]
    public function modifier(
        EntityManagerInterface $entityManager,
        Request                $requete,
        Sortie                 $sortie,
        EtatRepository         $etatRepository,
        ParticipantRepository  $participantRepository
    ): Response
    {

        // Vérifier que le participant est connecté
        try {
            $participant = $this->getUser();
            if (!$participant) {
                throw new AccessDeniedException('Vous devez être connecté-e pour modifier votre sortie ! ');
            }


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
                $this->addFlash('fail', 'Action impossible : la sortie a déjà été publiée ou vous n\'êtes pas l\'organisateur-trice !');
                return $this->redirectToRoute('listeSorties');
            }
            return $this->render('sortie/modifier-sortie.html.twig', [
                'sortieForm' => $sortieForm->createView(),
            ]);

            // Redirige l'utilisateur s'il n'est pas connecté
        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_login');

        }
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
        // Vérifier que le participant est connecté
        try {
            $participant = $this->getUser();
            if (!$participant) {
                throw new AccessDeniedException('Vous devez être connecté-e pour annuler votre sortie ! ');
            }


            $participant = $participantRepository->find($this->getUser());

            // Vérifier que le User est bien l'organisateur
            if ($participant === $sortie->getIdOrganisateur() && ($sortie->getEtatsNoEtat()->getId() == 2 || $sortie->getEtatsNoEtat()->getId() == 3)) {


                $annulerSortieForm = $this->createForm(AnnulerSortieType::class, $sortie);

                $annulerSortieForm->handleRequest($requete);

                if ($annulerSortieForm->isSubmitted() && $annulerSortieForm->isValid()) {


                    $sortie->setEtatsNoEtat($etatRepository->find(6));


                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    return $this->redirectToRoute('listeSorties');
                }
            } else {
                $this->addFlash('fail', 'Action impossible : la sortie n\'est pas/plus active ou vous n\'êtes pas l\'organisateur-trice !');
                return $this->redirectToRoute('listeSorties');
            }
            return $this->render('sortie/annuler.html.twig', [
                'annulerSortieForm' => $annulerSortieForm->createView(),
                "sortie" => $sortie,]);

            // Redirige l'utilisateur s'il n'est pas connecté
        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_login');
        }

    }


// Supprimer une sortie - uniquement pour les sorties restées en état "enregistrée"
    #[Route('/supprimer/{sortie}', name: '_supprimer')]
    public function supprimer(
        EntityManagerInterface $entityManager,
        Sortie                 $sortie,
        ParticipantRepository  $participantRepository,
        EtatRepository         $etatRepository
    ): Response
    {
// Vérifier que le participant est connecté
        try {
            $participant = $this->getUser();
            if (!$participant) {
                throw new AccessDeniedException('Vous devez être connecté-e pour supprimer votre sortie ! ');
            }


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
                $this->addFlash('fail', 'Action impossible : seules les sorties enregistrées peuvent être supprimées ou vous n\'êtes pas l\'organisateur-trice !');
            }

            return $this->redirectToRoute('listeSorties');


            // Redirige l'utilisateur s'il n'est pas connecté
        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
    }


    // Lien vers le profil des personnes inscrites à une sortie
    #[Route('/profile/{id}', name: 'app_profile_inscrit')]
    public function profilInscrit(
        int                   $id,
        Sortie                $sortie,
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













