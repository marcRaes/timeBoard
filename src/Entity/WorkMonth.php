<?php

namespace App\Entity;

use App\Repository\WorkMonthRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkMonthRepository::class)]
class WorkMonth
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'workMonths')]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $month = null;

    #[ORM\Column]
    private ?int $year = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, WorkDay>
     */
    #[ORM\OneToMany(targetEntity: WorkDay::class, mappedBy: 'workMonth')]
    private Collection $workDays;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->workDays = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(int $month): static
    {
        $this->month = $month;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, WorkDay>
     */
    public function getWorkDays(): Collection
    {
        return $this->workDays;
    }

    public function addWorkDay(WorkDay $workDay): static
    {
        if (!$this->workDays->contains($workDay)) {
            $this->workDays->add($workDay);
            $workDay->setWorkMonth($this);
        }

        return $this;
    }

    public function removeWorkDay(WorkDay $workDay): static
    {
        if ($this->workDays->removeElement($workDay)) {
            // set the owning side to null (unless already changed)
            if ($workDay->getWorkMonth() === $this) {
                $workDay->setWorkMonth(null);
            }
        }

        return $this;
    }

    public function getFormattedTotalTime(): string
    {
        $totalMinutes = 0;

        foreach ($this->getWorkDays() as $workDay) {
            foreach ($workDay->getWorkPeriods() as $period) {
                $totalMinutes += $period->getDuration() ?? 0;
            }
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%dH%02d', $hours, $minutes);
    }

    public function getLunchTickets(): int
    {
        $count = 0;

        foreach ($this->getWorkDays() as $workDay) {
            if ($workDay->hasLunchTicket()) {
                $count++;
            }
        }

        return $count;
    }
}
