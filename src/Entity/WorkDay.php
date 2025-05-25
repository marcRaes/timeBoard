<?php

namespace App\Entity;

use App\Repository\WorkDayRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkDayRepository::class)]
class WorkDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'workDays')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WorkMonth $workMonth = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    private ?bool $isFullDay = null;

    #[ORM\Column]
    private ?bool $hasLunchTicket = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkMonth(): ?WorkMonth
    {
        return $this->workMonth;
    }

    public function setWorkMonth(?WorkMonth $workMonth): static
    {
        $this->workMonth = $workMonth;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function isFullDay(): ?bool
    {
        return $this->isFullDay;
    }

    public function setIsFullDay(bool $isFullDay): static
    {
        $this->isFullDay = $isFullDay;

        return $this;
    }

    public function hasLunchTicket(): ?bool
    {
        return $this->hasLunchTicket;
    }

    public function setHasLunchTicket(bool $hasLunchTicket): static
    {
        $this->hasLunchTicket = $hasLunchTicket;

        return $this;
    }
}
