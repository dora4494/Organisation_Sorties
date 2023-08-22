<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
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
        Request $request
    ): Response
    {
        $searchTerm = $request->query->get('search');
        $siteId = $request->query->get('site');
        $organisateurFilter = $request->query->get('organisateur');
        $inscritFilter = $request->query->get('inscrit');
        $pasInscritFilter = $request->query->get('pasInscrit');
        $passeesFilter = $request->query->get('passees');
        $startDate = $request->query->get('start');
        $endDate = $request->query->get('end');
        $sites = $siteRepository->findAll();
        if ($searchTerm || $siteId || $organisateurFilter || $inscritFilter || $pasInscritFilter || $passeesFilter || $startDate || $endDate) {
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

    #[Route('/{id}', name: 'app_liste_sorties_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('liste_sorties/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

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

    #[Route('/{id}', name: 'app_liste_sorties_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_liste_sorties_index', [], Response::HTTP_SEE_OTHER);
    }
}
