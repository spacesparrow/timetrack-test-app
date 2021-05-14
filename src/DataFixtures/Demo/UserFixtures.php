<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface, OrderedFixtureInterface
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $usersArray = [
            [
                'email' => 'testinguser@example.com',
                'password' => '12345testing',
            ],
            [
                'email' => 'demo@example.com',
                'password' => '12345demo',
            ],
        ];

        foreach ($usersArray as $userArray) {
            $user = (new User())->setEmail($userArray['email']);
            $user->setPassword($this->encoder->encodePassword($user, $userArray['password']));
            $manager->persist($user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['demo'];
    }

    public function getOrder(): int
    {
        return 1;
    }
}
