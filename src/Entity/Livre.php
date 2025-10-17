<?php

namespace App\Entity;

use App\Repository\LivreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LivreRepository::class)]
class Livre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $datePublication = null;

    #[ORM\Column(type: "boolean")]
    private bool $disponible = true;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?auteur $auteur = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    private ?categorie $categorie_id = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateur_id')]
    private ?Utilisateur $emprunt_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDatePublication(): ?\DateTime
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTime $datePublication): static
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getDisponible(): ?string
    {
        return $this->disponible;
    }

    public function setDisponible(string $disponible): static
    {
        $this->disponible = $disponible;

        return $this;
    }

    public function getLivreId(): ?auteur
    {
        return $this->livre_id;
    }

    public function setLivreId(?auteur $livre_id): static
    {
        $this->livre_id = $livre_id;

        return $this;
    }

    public function getCategorieId(): ?categorie
    {
        return $this->categorie_id;
    }

    public function setCategorieId(?categorie $categorie_id): static
    {
        $this->categorie_id = $categorie_id;

        return $this;
    }

    public function getEmpruntId(): ?Utilisateur
    {
        return $this->emprunt_id;
    }

    public function setEmpruntId(?Utilisateur $emprunt_id): static
    {
        $this->emprunt_id = $emprunt_id;

        return $this;
    }
}