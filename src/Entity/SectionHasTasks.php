<?php

namespace App\Entity;

use App\Repository\SectionHasTasksRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as AttributeGroups;

#[ORM\Entity(repositoryClass: SectionHasTasksRepository::class)]
class SectionHasTasks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[AttributeGroups(['section_with_tasks'])]
    private ?int $position = null;

    #[ORM\ManyToOne(inversedBy: 'hasSections')]
    #[ORM\JoinColumn(nullable: false)]
    #[AttributeGroups(['section_with_tasks'])]
    private ?Task $task = null;

    #[ORM\ManyToOne(inversedBy: 'hasTasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Section $section = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

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

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): static
    {
        $this->task = $task;

        return $this;
    }

        public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): static
    {
        $this->section = $section;

        return $this;
    }
}
