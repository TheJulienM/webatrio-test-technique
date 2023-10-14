<?php

namespace App\Entity;

use App\Repository\PeopleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PeopleRepository::class)]
class People
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getPeoples', 'addJobPeople'])]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getPeoples', 'addJobPeople'])]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['getPeoples', 'addJobPeople'])]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\OneToMany(mappedBy: 'people', targetEntity: Job::class, orphanRemoval: true)]
    private Collection $jobs;

    #[ORM\Column(type: 'uuid')]
    #[Groups(['getPeoples', 'addJobPeople'])]
    private ?Uuid $uuid = null;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * @return Collection<int, Job>
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): static
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs->add($job);
            $job->setPeople($this);
        }

        return $this;
    }

    public function removeJob(Job $job): static
    {
        if ($this->jobs->removeElement($job)) {
            // set the owning side to null (unless already changed)
            if ($job->getPeople() === $this) {
                $job->setPeople(null);
            }
        }

        return $this;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

//    Cette fonction permet d'obtenir l'âge de lapersonne à partir de son année de naissance et de l'année en cours
//    Ainsi dans la réponse JSON de l'API on aficche uniquement l'âge comme souhaité
    #[Groups(['getPeoples'])]
    #[SerializedName('age')]
    public function getAge() : int {
        $now = new \DateTime('now');
        $yearNow = $now->format('Y');
        $birthYear = date_format($this->birthdate, "Y");
        return intval($yearNow) - intval($birthYear);
    }

//    Cette méthode permet d'obtenir la liste du (des) job(s)s actuel(s) de la personne
//    Ansi dans l'endpoint 3 on obtient que les jobs qui ne sont pas terminés (où la date de fin est nulle)
    #[Groups(['getPeoples'])]
    #[SerializedName('actualJobs')]
    public function getActualJobs() : array {
        $jobs = $this->jobs;
        $actualJobs = [];
        foreach ($jobs as $job) {
            if(!$job->getEndDate()) {
                array_push($actualJobs, $job);
            }
        }
        return $actualJobs;
    }
}
