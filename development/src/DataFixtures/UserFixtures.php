<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setEmail('cristobal@trainingdiary.local')
            ->setName('Cristóbal Jurado')
            ->setRoles(['ROLE_USER'])
            ->setPassword(password_hash('password123', PASSWORD_BCRYPT));

        $manager->persist($user);
        $manager->flush();

        $this->addReference('user-cristobal', $user);
    }
}
