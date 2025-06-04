<?php

namespace App\Entity;

use App\Repository\WorkDayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: WorkDayRepository::class)]
#[UniqueEntity(fields: ['date', 'workMonth'], message: 'Journée de travail déjà existante à cette date.', errorPath: 'date')]
class WorkDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: WorkMonth::class, inversedBy: 'workDays')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WorkMonth $workMonth = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, unique: true)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    private ?bool $hasLunchTicket = null;

    /**
     * @var Collection<int, WorkPeriod>
     */
    #[ORM\OneToMany(targetEntity: WorkPeriod::class, mappedBy: 'workDay', cascade: ['persist'])]
    private Collection $workPeriods;

    public function __construct()
    {
        $this->workPeriods = new ArrayCollection();
    }

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

    public function hasLunchTicket(): ?bool
    {
        return $this->hasLunchTicket;
    }

    public function setHasLunchTicket(bool $hasLunchTicket): static
    {
        $this->hasLunchTicket = $hasLunchTicket;

        return $this;
    }

    /**
     * @return Collection<int, WorkPeriod>
     */
    public function getWorkPeriods(): Collection
    {
        return $this->workPeriods;
    }

    public function addWorkPeriod(WorkPeriod $workPeriod): static
    {
        if (!$this->workPeriods->contains($workPeriod)) {
            $this->workPeriods->add($workPeriod);
            $workPeriod->setWorkDay($this);
        }

        return $this;
    }

    public function removeWorkPeriod(WorkPeriod $workPeriod): static
    {
        if ($this->workPeriods->removeElement($workPeriod)) {
            // set the owning side to null (unless already changed)
            if ($workPeriod->getWorkDay() === $this) {
                $workPeriod->setWorkDay(null);
            }
        }

        return $this;
    }
}
