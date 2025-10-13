<?php
namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Theme;

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
     * @ORM\OneToMany(targetEntity=Theme::class, mappedBy="group", orphanRemoval=true)
     */
    private $themes;

    public function __construct()
    {
        $this->userToGroups = new ArrayCollection();
        $this->themes = new ArrayCollection();
        // Если есть другие коллекции, оставь их
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): self
    {
        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
            $theme->setGroup($this);
        }

        return $this;
    }

    public function removeTheme(Theme $theme): self
    {
        if ($this->themes->removeElement($theme)) {
            // set the owning side to null (unless already changed)
            if ($theme->getGroup() === $this) {
                $theme->setGroup(null);
            }
        }

        return $this;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getUserToGroups(): Collection { return $this->userToGroups; }
}
