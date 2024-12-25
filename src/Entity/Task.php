<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: '`tasks`')]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $description;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $status = null;

    // Store createdAt as a string in 'Y-m-d H:i:s' format
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $createdAt;

    // Store updatedAt as string and allow null values
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tasks')]
    private ?User $user = null;

    public function __construct()
    {
        // Initialize createdAt with the current date in 'Y-m-d H:i:s' format
        $this->createdAt = date('Y-m-d H:i:s');
    }

    // Getter for createdAt
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    // Setter for createdAt
    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    // Lifecycle callback for setting updatedAt on update
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    // Getter for updatedAt
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter and Setter for user
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    // Getter and Setter for name
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    // Getter and Setter for description
    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

     // Getter and Setter for status
     public function getStatus():  ?string
     {
         return $this->status;
     }
 
     public function setStatus(?string $status): self
     {
         $this->status = $status;
         return $this;
     }
}
