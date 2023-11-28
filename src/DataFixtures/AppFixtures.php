<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $task = new Task();
            $task->setContent($faker->text(180));
            $task->setCreatedAt($faker->dateTimeThisYear());
            $task->setTitle($faker->word() . $i);
            $task->setIsDone(false);

            $manager->persist($task);
        }

        for ($a = 0; $a < 6; $a++) {
            $normalUser = new User();
            $normalUser->setEmail($faker->email());
            $normalUser->setUsername('user' . $a);
            $normalUser->setPassword($this->userPasswordHasher->hashPassword($normalUser, "password"));
            $normalUser->setRoles(['ROLE_USER']);

            $manager->persist($normalUser);
        }

        for ($b = 0; $b <= 2; $b++) {
            $adminUser = new User();
            $adminUser->setEmail($faker->email());
            $adminUser->setUsername('admin' . $b);
            $adminUser->setPassword($this->userPasswordHasher->hashPassword($adminUser, "password"));
            $adminUser->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

            $manager->persist($adminUser);
        }

        $anonUser = new User();
        $anonUser->setEmail($faker->email());
        $anonUser->setUsername('anonyme');
        $anonUser->setPassword($this->userPasswordHasher->hashPassword($anonUser, "password"));
        $anonUser->setRoles(['ROLE_USER']);

        $manager->persist($anonUser);

        $manager->flush();
    }
}
