<?php

namespace App\DataFixtures;

use App\DTO\DemoWorkDayDTO;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Enum\WorkPeriodType;
use App\Service\Demo\DemoWorkDayGenerator;
use App\Service\Demo\DemoWorkReportGenerator;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Random\RandomException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DemoFixtures extends Fixture
{
    private array $locations = [
        'GS 1',
        'GS 2',
        'GS 3',
        'GS 4',
        'GS 5',
        'GS 6',
        'GS 7',
        'GS 8',
        'GS 9',
        'GS 10',
        'GS 11',
        'GS 12',
        'GS 13',
        'GS 14',
        'GS 15',
        'GS 16',
        'GS 17',
        'GS 18',
        'GS 19',
        'GS 20',
        'GS 21',
        'GS 22',
        'CLAE 3',
        'CLAE 18',
        'Mairie',
        'CTM',
    ];

    public function __construct(
        private readonly DemoWorkDayGenerator $dayGenerator,
        private readonly DemoWorkReportGenerator $reportGenerator,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    /**
     * @throws \DateMalformedPeriodStringException
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $user = new User();
        $user->setEmail('demo@timeboard.fr');
        $user->setRoles(['ROLE_USER']);
        $user->setFirstName('Jean');
        $user->setLastName('Demo');
        $user->setIsVerified(true);

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'demo');
        $user->setPassword($hashedPassword);

        $manager->persist($user);

        $now = new DateTimeImmutable();
        $start = $now->modify('-12 months')->modify('first day of this month');

        while (in_array((int)$start->format('N'), [6, 7])) {
            $start = $start->modify('+1 day');
        }

        $period = new DatePeriod($start, new DateInterval('P1D'), $now);

        foreach ($period as $date) {
            if (in_array((int)$date->format('N'), [6, 7])) {
                continue;
            }

            $types = WorkPeriodType::cases();

            $dto = new DemoWorkDayDTO(
                date: $date,
                location: $this->locations[array_rand($this->locations)],
                replacedAgent: $faker->lastName('F'),
                lunchTicket: $faker->boolean(),
                type: $types[array_rand($types)],
                slotCount: random_int(1, 3),
            );

            $this->dayGenerator->createDay($user, $dto);
        }

        $manager->flush();

        $workMonths = $manager->getRepository(WorkMonth::class)->findBy(['user' => $user]);
        $countWorkMonths = count($workMonths) - 1;

        foreach ($workMonths as $keyMonth => $workMonth) {
            if ($keyMonth < $countWorkMonths || intval(date("d")) >= 26) {
                $this->reportGenerator->generateSubmission(
                    $workMonth,
                    $user->getEmail()
                );
            }
        }

        $manager->flush();
    }
}
