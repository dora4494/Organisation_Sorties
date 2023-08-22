<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LieuRepository::class)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $rue = null;

    #[ORM\Column(nullable: true)]
    private ?bool $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?bool $longitude = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $villesNoVille = null;

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

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(?string $rue): static
    {
        $this->rue = $rue;

        return $this;
    }

    public function isLatitude(): ?bool
    {
        return $this->latitude;
    }

    public function setLatitude(?bool $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function isLongitude(): ?bool
    {
        return $this->longitude;
    }

    public function setLongitude(?bool $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getVillesNoVille(): ?Ville
    {
        return $this->villesNoVille;
    }

    public function setVillesNoVille(?Ville $villesNoVille): static
    {
        $this->villesNoVille = $villesNoVille;

        return $this;
    }
}
