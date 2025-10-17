<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class EmpruntController extends AbstractController
{
    #[Route('/emprunt', name: 'app_emprunt')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/EmpruntController.php',
        ]);
    }

    #[Route('/rendre', name: 'rendre_livre', methods: ['POST'])]
    public function rendre(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['user_id'] ?? null;
        $bookId = $data['book_id'] ?? null;
    }
}

class EmpruntController extends AbstractController
{
    #[Route('/api/emprunter', name: 'emprunter_livre', methods: ['POST'])]
    public function emprunter(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['user_id'] ?? null;
        $bookId = $data['book_id'] ?? null;

        if (!$userId || !$bookId) {
            return new JsonResponse(['error' => 'Paramètres manquants'], 400);
        }

        $user = $em->getRepository(User::class)->find($userId);
        $book = $em->getRepository(Book::class)->find($bookId);

        if (!$user || !$book) {
            return new JsonResponse(['error' => 'Utilisateur ou livre introuvable'], 404);
        }

        $empruntExistant = $em->getRepository(Emprunt::class)->findOneBy([
            'book' => $book,
            'dateRetour' => null
        ]);

        if ($empruntExistant) {
            return new JsonResponse(['error' => 'Ce livre est déjà emprunté'], 400);
        }

        $nbEmprunts = $em->getRepository(Emprunt::class)->count([
            'user' => $user,
            'dateRetour' => null
        ]);

        if ($nbEmprunts >= 4) {
            return new JsonResponse(['error' => 'Vous avez déjà 4 livres empruntés'], 400);
        }

        $emprunt = new Emprunt();
        $emprunt->setUser($user);
        $emprunt->setBook($book);
        $emprunt->setDateEmprunt(new \DateTimeImmutable());

        $em->persist($emprunt);
        $em->flush();

        return new JsonResponse(['message' => 'Livre emprunté avec succès'], 201);
    }

    #[Route('/rendre', name: 'rendre_livre', methods: ['POST'])]
    public function rendre(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['user_id'] ?? null;
        $bookId = $data['book_id'] ?? null;

        if (!$userId || !$bookId) {
            return new JsonResponse(['error' => 'Paramètres manquants'], 400);
        }

        $user = $em->getRepository(User::class)->find($userId);
        $book = $em->getRepository(Book::class)->find($bookId);

        $emprunt = $em->getRepository(Emprunt::class)->findOneBy([
            'user' => $user,
            'book' => $book,
            'dateRetour' => null
        ]);

        if (!$emprunt) {
            return new JsonResponse(['error' => 'Aucun emprunt trouvé pour ce livre et cet utilisateur'], 404);
        }

        $emprunt->setDateRetour(new \DateTimeImmutable());
        $em->flush();

        return new JsonResponse(['message' => 'Livre rendu avec succès'], 200);
    }
}