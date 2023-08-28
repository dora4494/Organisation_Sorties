<?php

namespace App\Controller;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class ListeSortiesController extends AbstractController
{
    #[Route('/listeDesSorties', name: 'listeSorties')]
    public function index(
        SortieRepository $sortieRepository,
        SiteRepository $siteRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
    ): Response
    {


        $lstSorties = $sortieRepository->findAll();

        foreach ($lstSorties as $s) {
            $now = new DateTime('NOW');
           $dateHeureDebut = $s->getDateHeureDebut();
           // Ajout de la durée à la date de début de la sortie
            $dateHeureFin = date_add(clone $dateHeureDebut, date_interval_create_from_date_string($s->getDuree() . " minutes"));
            // Ajout de 30 jours à la date de début de la sortie
            $dateArchivage =date_add(clone $dateHeureDebut, date_interval_create_from_date_string("30 days"));
            $etat = $s->getEtatsNoEtat()->getId();

            // Passer de l'état "Clôturé" à "En cours" ou "Terminé"
            if ($s->getDateHeureDebut() <= $now) {
                if ($dateHeureFin >= $now) {
                    // En cours
                    $s->setEtatsNoEtat($etatRepository->find(4));
                    // Terminée
                } else {
                    $s->setEtatsNoEtat($etatRepository->find(5));
                }
            }
            // Archivée
            if (($etat == 5 || $etat == 6) && $dateArchivage < $now) {
                $s->setEtatsNoEtat($etatRepository->find(7));

            }

            // Clôturé
            if ( $s->getDateCloture() < $now && $etat == 2) {
                $s->setEtatsNoEtat($etatRepository->find(3));
            }
            $entityManager->persist($s);
            }



        $entityManager->flush();








        $searchTerm = $request->query->get('search');
        $siteId = $request->query->get('site');
        $organisateurFilter = $request->query->get('organisateur');
        $inscritFilter = $request->query->get('inscrit');
        $pasInscritFilter = $request->query->get('pasInscrit');
        $passeesFilter = $request->query->get('passees');
        $startDate = $request->query->get('start');
        $endDate = $request->query->get('end');
        $sites = $siteRepository->findAll();


        if ($searchTerm ||
            $siteId ||
            $organisateurFilter ||
            $inscritFilter ||
            $pasInscritFilter ||
            $passeesFilter ||
            $startDate ||
            $endDate) {
            $sorties = $sortieRepository->findBySearchTerm($searchTerm, $siteId, $organisateurFilter, $inscritFilter, $pasInscritFilter, $passeesFilter, $startDate, $endDate); //ajouter le $userId ici quand la connexion sera ok
        } else {
            $sorties = $sortieRepository->findAll();
        }
        return $this->render(
            'liste_sorties/show.html.twig',
            compact('sorties', 'sites')
        );
    }

#[Route('/new', name: 'app_liste_sorties_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_liste_sorties_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('liste_sorties/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

//    #[Route('/route/{id}', name: 'app_liste_sorties_show', methods: ['GET'])]
//    public function show(Sortie $sortie): Response
//    {
//        return $this->render('liste_sorties/show.html.twig', [
//            'sortie' => $sortie,
//        ]);
//    }

    #[Route('/{id}/edit', name: 'app_liste_sorties_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_liste_sorties_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('liste_sorties/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

//    #[Route('/{id}', name: 'app_liste_sorties_delete', methods: ['POST'])]
//    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
//    {
//        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
//            $entityManager->remove($sortie);
//            $entityManager->flush();
//        }
//
//        return $this->redirectToRoute('app_liste_sorties_index', [], Response::HTTP_SEE_OTHER);
//    }
}
