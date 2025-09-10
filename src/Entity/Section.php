<?php

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as AttributeGroups;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
#[ORM\Table(name: '`section`')]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[AttributeGroups(['section_with_tasks', 'section_from_link'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[AttributeGroups(['section_with_tasks', 'section_from_link'])]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    #[AttributeGroups(['section_with_tasks'])]
    private ?int $position = null;
    
    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    #[AttributeGroups(['section_with_tasks'])]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false)]
    #[AttributeGroups(['section_default'])]
    private ?User $user = null;

    /**
     * @var Collection<int, SectionHasTasks>
     */
    #[ORM\OneToMany(targetEntity: SectionHasTasks::class, mappedBy: 'section', orphanRemoval: true)]
    #[AttributeGroups(['section_with_tasks'])]
    private Collection $hasTasks;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->hasTasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
    
    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, SectionHasTasks>
     */
    public function getHasTasks(): Collection
    {
        return $this->hasTasks;
    }

    public function addHasTask(SectionHasTasks $hasTask): static
    {
        if (!$this->hasTasks->contains($hasTask)) {
            $this->hasTasks->add($hasTask);
            $hasTask->setSection($this);
        }

        return $this;
    }

    public function removeHasTask(SectionHasTasks $hasTask): static
    {
        if ($this->hasTasks->removeElement($hasTask)) {
            if ($hasTask->getSection() === $this) {
                $hasTask->setSection(null);
            }
        }

        return $this;
    }
}