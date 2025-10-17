<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/emprunt')]
class EmpruntController extends AbstractController
{
    #[Route('/add', name: 'emprunt_add', methods: ['POST'])]
    public function addEmprunt(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $em->getRepository(Utilisateur::class)->find($data['utilisateur_id'] ?? 0);
        $livre = $em->getRepository(Livre::class)->find($data['livre_id'] ?? 0);

        if (!$user || !$livre) {
            return new JsonResponse(['error' => 'utilisateur ou livre introuvable'], 404);
        }

        if (!$livre->getDisponible()) {
            return new JsonResponse(['error' => 'livre déjà emprunté'], 400);
        }

        $nbEmprunts = $em->getRepository(Emprunt::class)->count([
            'utilisateur' => $user,
            'dateRetour' => null
        ]);
        if ($nbEmprunts >= 4) {
            return new JsonResponse(['error' => 'limite de 4 emprunts atteinte'], 400);
        }

        $emprunt = new Emprunt();
        $emprunt->setUtilisateur($user);
        $emprunt->setLivre($livre);
        $emprunt->setDateEmprunt(new \DateTimeImmutable());

        $livre->setDisponible(false);

        $em->persist($emprunt);
        $em->flush();

        return new JsonResponse(['message' => 'livre emprunté avec succès']);
    }

    #[Route('/return', name: 'emprunt_return', methods: ['POST'])]
    public function returnLivre(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $em->getRepository(Utilisateur::class)->find($data['utilisateur_id'] ?? 0);
        $livre = $em->getRepository(Livre::class)->find($data['livre_id'] ?? 0);

        if (!$user || !$livre) {
            return new JsonResponse(['error' => 'utilisateur ou livre introuvable'], 404);
        }

        $emprunt = $em->getRepository(Emprunt::class)->findOneBy([
            'utilisateur' => $user,
            'livre' => $livre,
            'dateRetour' => null
        ]);

        if (!$emprunt) {
            return new JsonResponse(['error' => 'aucun emprunt en cours pour ce livre'], 404);
        }

        $emprunt->setDateRetour(new \DateTimeImmutable());
        $livre->setDisponible(true);

        $em->flush();

        return new JsonResponse(['message' => 'livre rendu avec succès']);
    }

    #[Route('/utilisateur/{id}', name: 'utilisateur_emprunts', methods: ['GET'])]
    public function userEmprunts(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(Utilisateur::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], 404);
        }

        $emprunts = $em->getRepository(Emprunt::class)->findBy(
            ['utilisateur' => $user, 'dateRetour' => null],
            ['dateEmprunt' => 'ASC']
        );

        $result = [];
        foreach ($emprunts as $e) {
            $result[] = [
                'livre' => $e->getLivre()->getTitre(),
                'dateEmprunt' => $e->getDateEmprunt()->format('Y-m-d H:i')
            ];
        }

        return new JsonResponse($result);
    }

    #[Route('/all', name: 'all_emprunts', methods: ['GET'])]
    public function allEmprunts(EntityManagerInterface $em): JsonResponse
    {
        $emprunts = $em->getRepository(Emprunt::class)->findAll();

        $result = [];
        foreach ($emprunts as $e) {
            $result[] = [
                'livre' => $e->getLivre()->getTitre(),
                'utilisateur' => $e->getUtilisateur()->getNom(),
                'dateEmprunt' => $e->getDateEmprunt()->format('Y-m-d H:i')
            ];
        }

        return new JsonResponse($result);
    }
}
