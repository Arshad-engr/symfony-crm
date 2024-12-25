<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use \DateTime;
use \DateTimeImmutable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private $id;
   
    #[ORM\Column(type: 'string', length: 50)]
    private $firstName;
    #[ORM\Column(type: 'string', length: 50)]
    private $lastName;
    
    #[ORM\Column(type: 'string', length: 255)]
    private $password;
    #[ORM\Column(type: 'text')]
    private $address;
    #[ORM\Column(type: 'string', length: 50)]
    private $email;   
    #[ORM\Column(type: 'string', length: 50)]
    private $phone;
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dob = null;
    #[ORM\Column(type: 'text')]
    private $profile;
    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'array')]
    private array $roles = [];

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'user')]
    private Collection $tasks;
    public function __construct()
    {
        // If you want to initialize these values during object construction
        $this->createdAt = new DateTimeImmutable();
        $this->roles[] = 'ROLE_USER';
        // $this->roles[] = 'ROLE_ADMIN';
        $this->tasks = new ArrayCollection();
    }
    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
     public function getFirstName(): ?string
    {
        return $this->firstName;
    }
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }
  
    public function getLastName(): ?string
    {
        return $this->lastName;
    }
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }
   
    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
    
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
   
    public function getAddress(): ?string
    {
        return $this->address;
    }
    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }
   
    public function getPhone(): ?string
    {
        return $this->phone;
    }
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

   

    public function getDob(): ?DateTime
    {
        return $this->dob;
    }

    public function setDob(?DateTime $dob): self
    {
        $this->dob = $dob;

        return $this;
    }
   
    public function getProfile(): ?string
    {
        return $this->profile;
    }
    public function setProfile(string $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

   
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
    
    // Lifecycle callback for setting updatedAt on update
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    // 1. Implement getRoles() from UserInterface
    public function getRoles(): array
    {
        return $this->roles;
    }
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    // 2. Implement getUserIdentifier() from UserInterface
    public function getUserIdentifier(): string
    {
        // Usually, this is the email or username field.
        return $this->email;
    }

    // 3. Implement eraseCredentials() from UserInterface
    public function eraseCredentials(): void
    {
        // If you had sensitive data (e.g., temporary passwords or tokens), you would clear it here.
        // Since we don't have any sensitive data in this case, you can leave it empty.
    }
   
}
