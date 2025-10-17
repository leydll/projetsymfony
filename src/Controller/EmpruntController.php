<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Auteur;
use App\Entity\Utilisateur;
use App\Entity\Emprunt;
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
            $categorie = $em->getRepository(\App\Entity\Categorie::class)->find($data['categorie_id']);
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

#[Route('/emprunt')]
class EmpruntController extends AbstractController
{
    #[Route('/emprunt', name: 'emprunt', methods: ['POST'])]
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
            'user' => $user,
            'dateRetour' => null
        ]);
        if ($nbEmprunts >= 4) {
            return new JsonResponse(['error' => 'limite de 4 emprunts atteinte'], 400);
        }

        $emprunt = new Emprunt();
        $emprunt->setUser($user);
        $emprunt->setLivre($livre);
        $emprunt->setDateEmprunt(new \DateTimeImmutable());

        $livre->setDisponible(false);

        $em->persist($emprunt);
        $em->flush();

        return new JsonResponse(['message' => 'livre emprunté avec succès']);
    }

    #[Route('/retour', name: 'retour', methods: ['POST'])]
    public function returnLivre(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $em->getRepository(Utilisateur::class)->find($data['utilisateur_id'] ?? 0);
        $livre = $em->getRepository(Livre::class)->find($data['livre_id'] ?? 0);

        if (!$user || !$livre) {
            return new JsonResponse(['error' => 'utilisateur ou livre introuvable'], 404);
        }

        $emprunt = $em->getRepository(Emprunt::class)->findOneBy([
            'user' => $user,
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

    #[Route('/utilisateur/{id}', name: 'utilisateurEmprunts', methods: ['GET'])]
    public function userEmprunts(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(Utilisateur::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], 404);
        }

        $emprunts = $em->getRepository(Emprunt::class)->findBy(
            ['user' => $user, 'dateRetour' => null],
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

    #[Route('/auteur/{id}/dates', name: 'livresAuteurDates', methods: ['GET'])]
    public function livresAuteurEntreDeuxDates(Request $request, int $id, EntityManagerInterface $em): JsonResponse
    {
        $dateDebut = new \DateTime($request->query->get('start'));
        $dateFin = new \DateTime($request->query->get('end'));

        $livres = $em->getRepository(Emprunt::class)->createQueryBuilder('e')
            ->join('e.livre', 'l')
            ->where('l.auteur = :auteur')
            ->andWhere('e.dateEmprunt BETWEEN :start AND :end')
            ->setParameters([
                'auteur' => $id,
                'start' => $dateDebut,
                'end' => $dateFin
            ])
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($livres as $e) {
            $result[] = [
                'livre' => $e->getLivre()->getTitre(),
                'utilisateur' => $e->getUser()->getNom(),
                'dateEmprunt' => $e->getDateEmprunt()->format('Y-m-d H:i')
            ];
        }

        return new JsonResponse($result);
    }
}
