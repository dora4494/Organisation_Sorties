<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/lieu', name: 'lieu')]
class LieuController extends AbstractController
{
    #[Route('/creer', name: '_creer')]
    public function creer(
        EntityManagerInterface $entityManager,
        Request $request
    ): Response

    {
        $lieu = new Lieu();
        $sortie = new Sortie();

        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            return $this->redirectToRoute(('sortie_creer'));
        }

        return $this->render('lieu/creer-lieu.html.twig', [
            'lieuForm' => $lieuForm->createView(),
        ]);
    }

    #[Route('/get-details/{lieuId}', name: '_get_details', methods: ['GET'])]
    public function getDetails($lieuId, EntityManagerInterface $entityManager): JsonResponse
    {
        $lieu = $entityManager->getRepository(Lieu::class)->find($lieuId);
        if (!$lieu) {
            return new JsonResponse(['error' => 'Lieu not found'], Response::HTTP_NOT_FOUND);
        }
        $lieuDetails = [
            'rue' => $lieu->getRue(),
            'ville' => [
                'id' => $lieu->getVillesNoVille()->getId(),
                'nom' => $lieu->getVillesNoVille()->getNom(),
            ]
        ];
        return new JsonResponse($lieuDetails);
    }
}
