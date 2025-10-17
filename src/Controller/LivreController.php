<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Auteur;
use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/livre')]
class LivreController extends AbstractController
{
    #[Route('/create', name: 'creerLivre', methods: ['POST'])]
    public function createLivre(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $livre = new Livre();
        $livre->setTitre($data['titre'] ?? '');
        $livre->setDatePublication(isset($data['datePublication']) ? new \DateTime($data['datePublication']) : null);
        $livre->setDisponible(true);

        if (!empty($data['auteur_id'])) {
            $auteur = $em->getRepository(Auteur::class)->find($data['auteur_id']);
            $livre->setAuteur($auteur);
        }
        if (!empty($data['categorie_id'])) {
            $categorie = $em->getRepository(Categorie::class)->find($data['categorie_id']);
            $livre->setCategorie($categorie);
        }

        $em->persist($livre);
        $em->flush();

        return new JsonResponse(['message' => 'Livre créé', 'id' => $livre->getId()], 201);
    }

    #[Route('/{id}/editer', name: 'editerLivre', methods: ['PUT', 'PATCH'])]
    public function editLivre(Request $request, EntityManagerInterface $em, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $livre = $em->getRepository(Livre::class)->find($id);

        if (!$livre) {
            return new JsonResponse(['error' => 'livre introuvable'], 404);
        }

        $livre->setTitre($data['titre'] ?? $livre->getTitre());
        if (!empty($data['datePublication'])) {
            $livre->setDatePublication(new \DateTime($data['datePublication']));
        }

        $em->persist($livre);
        $em->flush();

        return new JsonResponse(['message' => 'livre modifié', 'id' => $livre->getId()]);
    }

    #[Route('/{id}/supprimer', name: 'supprimerLivre', methods: ['DELETE'])]
    public function deleteLivre(EntityManagerInterface $em, int $id): JsonResponse
    {
        $livre = $em->getRepository(Livre::class)->find($id);
        if (!$livre) {
            return new JsonResponse(['error' => 'livre introuvable'], 404);
        }

        $em->remove($livre);
        $em->flush();

        return new JsonResponse(['message' => 'livre supprimé']);
    }

    #[Route('/all', name: 'all_livre', methods: ['GET'])]
    public function allLivres(EntityManagerInterface $em): JsonResponse
    {
        $livres = $em->getRepository(Livre::class)->findAll();
        $result = [];
        foreach ($livres as $livre) {
            $result[] = [
                'id' => $livre->getId(),
                'titre' => $livre->getTitre(),
                'disponible' => $livre->getDisponible()
            ];
        }

        return new JsonResponse($result);
    }
}
