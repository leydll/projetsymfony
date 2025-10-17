<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    /**
     * @var Collection<int, livre>
     */
    #[ORM\OneToMany(targetEntity: livre::class, mappedBy: 'emprunt_id')]
    private Collection $utilisateur_id;

    /**
     * @var Collection<int, Emprunt>
     */
    #[ORM\OneToMany(targetEntity: Emprunt::class, mappedBy: 'utilisateur_id')]
    private Collection $emprunts;

    public function __construct()
    {
        $this->utilisateur_id = new ArrayCollection();
        $this->emprunts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * @return Collection<int, livre>
     */
    public function getUtilisateurId(): Collection
    {
        return $this->utilisateur_id;
    }

    public function addUtilisateurId(livre $utilisateurId): static
    {
        if (!$this->utilisateur_id->contains($utilisateurId)) {
            $this->utilisateur_id->add($utilisateurId);
            $utilisateurId->setEmpruntId($this);
        }

        return $this;
    }

    public function removeUtilisateurId(livre $utilisateurId): static
    {
        if ($this->utilisateur_id->removeElement($utilisateurId)) {
            // set the owning side to null (unless already changed)
            if ($utilisateurId->getEmpruntId() === $this) {
                $utilisateurId->setEmpruntId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Emprunt>
     */
    public function getEmprunts(): Collection
    {
        return $this->emprunts;
    }

    public function addEmprunt(Emprunt $emprunt): static
    {
        if (!$this->emprunts->contains($emprunt)) {
            $this->emprunts->add($emprunt);
            $emprunt->setUtilisateurId($this);
        }

        return $this;
    }

    public function removeEmprunt(Emprunt $emprunt): static
    {
        if ($this->emprunts->removeElement($emprunt)) {
            // set the owning side to null (unless already changed)
            if ($emprunt->getUtilisateurId() === $this) {
                $emprunt->setUtilisateurId(null);
            }
        }

        return $this;
    }
}
