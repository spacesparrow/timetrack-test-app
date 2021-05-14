<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Entity\Task;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class TaskFixtures extends Fixture implements FixtureGroupInterface, OrderedFixtureInterface
{
    private UserRepository $userRepository;
    private Faker\Generator $faker;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->userRepository->findBy(['email' => ['testinguser@example.com', 'demo@example.com']]);

        foreach ($users as $user) {
            for ($i = 0; $i < $this->faker->numberBetween(5, 20); ++$i) {
                $task = (new Task());
                $task->setTitle($this->faker->text(100));
                $task->setComment($this->faker->text(255));
                $task->setCreatedDate($this->faker->dateTimeBetween('-1 year'));
                $task->setTimeSpent($this->faker->numberBetween(10, 9999));
                $task->setUser($user);

                $manager->persist($task);
            }

            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['demo'];
    }

    public function getOrder(): int
    {
        return 2;
    }
}
