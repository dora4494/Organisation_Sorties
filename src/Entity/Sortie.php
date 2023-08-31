<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nom = null;


   #[ORM\Column(type: Types::DATETIME_MUTABLE)]
   #[Assert\NotBlank]
    private ?\DateTimeInterface $dateHeureDebut = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $dateCloture = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(0)]
    private ?int $nbInscriptionsMax = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $descriptionInfos = null;

    #[ORM\Column(nullable: true)]
    private ?int $etatSortie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlPhoto = null;


    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $idOrganisateur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Lieu $lieuxNoLieu = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etats_no_etat = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $Site = null;

    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'sorties')]
    private Collection $participants;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motifAnnulation = null;

    #[ORM\ManyToOne(targetEntity: Participant::class, inversedBy: 'sortiesOrganisees')]
    #[ORM\JoinColumn(nullable: false)]
    private Participant $organisateur;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
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

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateCloture(): ?\DateTimeInterface
    {
        return $this->dateCloture;
    }

    public function setDateCloture(\DateTimeInterface $dateCloture): static
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): static
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getDescriptionInfos(): ?string
    {
        return $this->descriptionInfos;
    }

    public function setDescriptionInfos(?string $descriptionInfos): static
    {
        $this->descriptionInfos = $descriptionInfos;

        return $this;
    }

    public function getEtatSortie(): ?int
    {
        return $this->etatSortie;
    }

    public function setEtatSortie(?int $etatSortie): static
    {
        $this->etatSortie = $etatSortie;

        return $this;
    }

    public function getUrlPhoto(): ?string
    {
        return $this->urlPhoto;
    }

    public function setUrlPhoto(?string $urlPhoto): static
    {
        $this->urlPhoto = $urlPhoto;

        return $this;
    }

    public function getEtatsNoEtat(): ?Etat
    {
        return $this->etats_no_etat;
    }

    public function setEtatsNoEtat(?Etat $etatsNoEtat): static
    {
        $this->etats_no_etat = $etatsNoEtat;

        return $this;
    }

    public function getIdOrganisateur(): ?Participant
    {
        return $this->idOrganisateur;
    }

    public function setIdOrganisateur(?Participant $idOrganisateur): static
    {
        $this->idOrganisateur = $idOrganisateur;

        return $this;
    }

    public function getLieuxNoLieu(): ?Lieu
    {
        return $this->lieuxNoLieu;
    }

    public function setLieuxNoLieu(?Lieu $lieuxNoLieu): static
    {
        $this->lieuxNoLieu = $lieuxNoLieu;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->Site;
    }

    public function setSite(?Site $Site): static
    {
        $this->Site = $Site;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function setMotifAnnulation(?string $motifAnnulation): static
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }

    public function getOrganisateur(): Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(Participant $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }
}
