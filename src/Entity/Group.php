<?php
namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=GroupRepository::class)
 * @ORM\Table(name="user_group")
 */
class Group
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\Column(type="string", length=255, unique=true) */
    private $name;

    /** @ORM\OneToMany(targetEntity=UserToGroup::class, mappedBy="group", orphanRemoval=true) */
    private $userToGroups;

    public function __construct()
    {
        $this->userToGroups = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getUserToGroups(): Collection { return $this->userToGroups; }
}
