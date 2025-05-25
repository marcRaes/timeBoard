<?php

namespace App\Entity;

use App\Repository\WorkPeriodRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkPeriodRepository::class)]
class WorkPeriod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'workPeriods')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WorkDay $workDay = null;

    #[ORM\Column(length: 50)]
    private ?string $label = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $timeStart = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $timeEnd = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $replacedAgent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkDay(): ?WorkDay
    {
        return $this->workDay;
    }

    public function setWorkDay(?WorkDay $workDay): static
    {
        $this->workDay = $workDay;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getTimeStart(): ?\DateTimeImmutable
    {
        return $this->timeStart;
    }

    public function setTimeStart(\DateTimeImmutable $timeStart): static
    {
        $this->timeStart = $timeStart;

        return $this;
    }

    public function getTimeEnd(): ?\DateTimeImmutable
    {
        return $this->timeEnd;
    }

    public function setTimeEnd(\DateTimeImmutable $timeEnd): static
    {
        $this->timeEnd = $timeEnd;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getReplacedAgent(): ?string
    {
        return $this->replacedAgent;
    }

    public function setReplacedAgent(?string $replacedAgent): static
    {
        $this->replacedAgent = $replacedAgent;

        return $this;
    }
}
