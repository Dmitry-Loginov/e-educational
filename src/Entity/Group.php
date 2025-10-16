<?php
namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Subject;

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

    /**
     * @ORM\OneToMany(targetEntity=Subject::class, mappedBy="group", orphanRemoval=true)
     */
    private $subjects;

    public function __construct()
    {
        $this->userToGroups = new ArrayCollection();
        $this->subjects = new ArrayCollection();
        // Если есть другие коллекции, оставь их
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addsubject(subject $subject): self
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects->add($subject);
            $subject->setGroup($this);
        }

        return $this;
    }

    public function removesubject(subject $subject): self
    {
        if ($this->subjects->removeElement($subject)) {
            // set the owning side to null (unless already changed)
            if ($subject->getGroup() === $this) {
                $subject->setGroup(null);
            }
        }

        return $this;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getUserToGroups(): Collection { return $this->userToGroups; }
}
