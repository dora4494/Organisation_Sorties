<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
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

    /*
        #[Route('/liste', name: '_liste')]
        public function liste(
            SortieRepository $sortieRepository
        ): Response
        {
            $sorties = $sortieRepository->findAll();
            return $this->render('liste_sorties/show.html.twig',
                compact('sorties'));
        }
    */

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


    //  Details de la sortie + inscription
    #[Route('/details/{sortie}', name: '_details', requirements: ["sortie" => "\d+"])]
    public function details(
        Sortie                 $sortie,
        SortieRepository       $sortieRepository,
        Request                $request,
        EntityManagerInterface $entityManager,
        ParticipantRepository  $participantRepository,
        EtatRepository         $etatRepository,
//        Etat                   $etat,
//        Participant            $participant

    ): Response
    {

        $etat = $sortie->getEtatsNoEtat()->getId();

        $participantForm = $this->createForm(ParticipantType::class);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {

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

                return $this->redirectToRoute('sortie_details');

            } else {
                $this->addFlash("fail", "Vous n'avez pas pu être ajouté-e");
            }
        }//

        return $this->render('sortie/detail.html.twig', [
            "sortie" => $sortie,
            "participantForm" => $participantForm->createView()
        ]);


    }


    // Se désister d'une sortie
    #[Route('/desister/{sortie}', name: '_desister')]
    public function desister(
        EntityManagerInterface $entityManager,
        ParticipantRepository  $participantRepository,
        Sortie                 $sortie,
        Participant            $participant,
        SortieRepository       $sortieRepository
    ): Response
    {

        // Vérifier que la sortie n'a pas débuté et que la date de limite d'inscription n'est pas dépassée
        if ($sortie->getDateHeureDebut() > new \DateTime()) {

            $participant = $participantRepository->find($this->getUser()->getUserIdentifier());

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

}


// Détails d'une sortie

/*  Pour afficher une sortie - il faut être connecté
    #[Route('/details/{sortie}', name: '_details', requirements: ["sortie" =>"\d+"])]
    public function details(
        Sortie $sortie,

    ): Response
    {
        return $this->render('sortie/detail.html.twig',
            compact('sortie'));
    }

*/


/*
// Pour afficher 1 sortie enregistrée manuellement dans la BDD     ---- A DECOMMENTER SI LE TEST INSCRIPTION NE FONCTIONNE PAS
#[Route('/details/{id}', name: '_details')]
public function details(
    SortieRepository $sortieRepository,
    int $id=2,
): Response
{

    // Pour récupérer une seule sortie en BDD
    $sortie = $sortieRepository->findOneBy(
        ["id" => $id]
    );
    return $this->render('sortie/detail.html.twig',
        compact('sortie')
    );
}
*/


/*
    // Inscription à une sortie ----- A DECOMMENTER si le test en dur ne fonctionne pas
    #[Route('/inscription/{sortie}', name: '_inscription')]
    public function inscription(
        Sortie $sortie,
        SortieRepository $sortieRepository,
        Request               $request,
        EntityManagerInterface $entityManager,
        ParticipantRepository $participantRepository,
        EtatRepository $etatRepository

    ): Response
    {

         $etat = $sortie->getEtatsNoEtat()->getId();

        $participantForm = $this->createForm(ParticipantType::class);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {

            // Vérifier la date de cloture, le nb d'inscription max // TODO : Ajouter la vérifification de l'état 'ouvert
            if ($sortie->getDateCloture() > new DateTime('NOW') && $sortie->getNbInscriptionsMax() > count($sortie->getParticipants())) {

                $participant = $participantRepository->find($this->getUser()->getUserIdentifier());

                $sortie->addParticipant($participant);
                /
                                // Si le nb d'inscrits est atteint

                             //   if ($sortie->getNbInscriptionsMax() == $sortie->getParticipants()->count()) {
                            //        $sortie->setEtatsNoEtat($etatRepository->find(3));

                               // }

                $entityManager->persist($sortie);

                $entityManager->flush();

                return $this->redirectToRoute('sortie_details');

            } else {
                $this->addFlash("fail", "Vous n'avez pas pu être ajouté-e");
            }
            }

            return $this->render('sortie/detail.html.twig', [
                "sortie" => $sortie,
                "participantForm" => $participantForm->createView()
            ]);
        }
*/




