<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Symfony\Component\Serializer\Attribute\Groups as AttributeGroups;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: '`task`')]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[AttributeGroups(['task_read', 'user_section_read', 'task_toggle_active'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[AttributeGroups(['task_read', 'user_section_read'])]
    private ?string $content = null;
    
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[AttributeGroups(['task_read', 'user_section_read', 'task_toggle_active'])]
    private bool $active = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'tasks', cascade: ['persist'])]
    #[AttributeGroups(['task_read','task_users','user_section_read'])]
    private Collection $users;

    #[ORM\ManyToMany(targetEntity: Section::class, mappedBy: 'tasks', cascade: ['persist'])]
    private Collection $sections;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->users = new ArrayCollection();
        $this->sections = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
    
    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if(!$this->users->contains($user)) {
           $this->users->add($user);
           $user->addTask($this);
        }
        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);
        $user->removeTask($this);
        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->addTask($this);
        }

        return $this;
    }

    public function removeSection(Section $section): static
    {
        $this->sections->removeElement($section); 
            $section->removeTask($this);
       
        return $this;
    }
}
