<?php
namespace App\Entity;

use App\Repository\UserToGroupRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserToGroupRepository::class)
 * @ORM\Table(name="user_to_group")
 */
class UserToGroup
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\ManyToOne(targetEntity=User::class, inversedBy="userToGroups") */
    private $user;

    /** @ORM\ManyToOne(targetEntity=Group::class, inversedBy="userToGroups") */
    private $group;

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getGroup(): ?Group { return $this->group; }
    public function setGroup(?Group $group): self { $this->group = $group; return $this; }
}
