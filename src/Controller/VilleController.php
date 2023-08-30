<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AjoutVilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ville', name: 'ville')]
class VilleController extends AbstractController
{
    #[Route('/creer', name: '_creer')]
    public function creer(
    EntityManagerInterface $entityManager,
    Request $request
    ): Response

    {
        $ville = new Ville();
        $sortie = new Sortie();

        $villeForm = $this->createForm(AjoutVilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            return $this->redirectToRoute(('lieu_creer'));
        }

return $this->render('ville/creer.html.twig', [
    'villeForm' => $villeForm->createView(),
        ]);
    }
}
