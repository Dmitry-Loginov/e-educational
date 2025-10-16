<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LessonRepository::class)
 */
class Lesson
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=300)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=300)
     */
    private $target;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $task;

    /**
     * @ORM\Column(type="string", length=300)
     */
    private $instrumentation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $theory;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $video;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $test;

    /**
     * @ORM\ManyToOne(targetEntity=Subject::class, inversedBy="lessons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $subject;

    /**
     * @ORM\OneToMany(targetEntity=Answer::class, mappedBy="lesson", orphanRemoval=true)
     */
    private $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getTask(): ?string
    {
        return $this->task;
    }

    public function setTask(string $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getInstrumentation(): ?string
    {
        return $this->instrumentation;
    }

    public function setInstrumentation(string $instrumentation): self
    {
        $this->instrumentation = $instrumentation;

        return $this;
    }

    public function getTheory(): ?string
    {
        return $this->theory;
    }

    public function setTheory(?string $theory): self
    {
        $this->theory = $theory;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getTest(): ?string
    {
        return $this->test;
    }

    public function setTest(?string $test): self
    {
        $this->test = $test;

        return $this;
    }

    public function getSubject(): ?subject
    {
        return $this->subject;
    }

    public function setSubject(?subject $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setLesson($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getLesson() === $this) {
                $answer->setLesson(null);
            }
        }

        return $this;
    }
}
