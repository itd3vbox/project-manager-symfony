<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Table(name: "projects")] 
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description_short = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Task::class)]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Automate::class)]
    private Collection $automates;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->automates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescriptionShort(): ?string
    {
        return $this->description_short;
    }

    public function setDescriptionShort(string $description_short): static
    {
        $this->description_short = $description_short;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) 
        {
            $this->tasks->add($task);
            $task->setProject($this);
        }

        return $this;
    }
 
    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) 
        {
            // set the owning side to null (unless already changed)
            if ($task->getProject() === $this) 
            {
                $task->setProject(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Automate>
     */
    public function getAutomates(): Collection
    {
        return $this->automates;
    }

    public function addAutomate(Automate $automate): static
    {
        if (!$this->automates->contains($automate)) 
        {
            $this->automates->add($automate);
            $automate->setProject($this);
        }
        return $this;
    }

    public function removeAutomate(Automate $automate): static
    {
        if ($this->automates->removeElement($automate)) 
        {
            // set the owning side to null (unless already changed)
            if ($automate->getProject() === $this) 
            {
                $automate->setProject(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
