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
        $usersList = [];

        // create simple user
        for ($a = 0; $a < 6; $a++) {
            $normalUser = new User();
            $normalUser->setEmail($faker->email());
            $normalUser->setUsername('user' . $a);
            $normalUser->setPassword($this->userPasswordHasher->hashPassword($normalUser, "password"));
            $normalUser->setRoles(['ROLE_USER']);
            array_push($usersList, $normalUser);

            $manager->persist($normalUser);
        }

        // create admin user
        for ($b = 0; $b <= 2; $b++) {
            $adminUser = new User();
            $adminUser->setEmail($faker->email());
            $adminUser->setUsername('admin' . $b);
            $adminUser->setPassword($this->userPasswordHasher->hashPassword($adminUser, "password"));
            $adminUser->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            array_push($usersList, $adminUser);

            $manager->persist($adminUser);
        }

        // create tasks with a user
        for ($i = 0; $i < 8; $i++) {
            $task = new Task();
            $task->setContent($faker->text(180));
            $task->setCreatedAt($faker->dateTimeThisYear());
            $task->setTitle($faker->word() . $i);
            $task->setIsDone(false);
            $task->setUser($usersList[$i]);

            $manager->persist($task);

            $task2 = new Task();
            $task2->setContent($faker->text(180));
            $task2->setCreatedAt($faker->dateTimeThisYear());
            $task2->setTitle($faker->word() . $i);
            $task2->setIsDone(false);
            $task2->setUser($usersList[$i]);

            $manager->persist($task2);
        }

        $Anonyme = new User();
        $Anonyme->setEmail($faker->email());
        $Anonyme->setUsername('anonyme');
        $Anonyme->setPassword($this->userPasswordHasher->hashPassword($normalUser, "password"));
        $Anonyme->setRoles(['ROLE_USER']);

        $manager->persist($Anonyme);

        $taskAnon = new Task();
        $taskAnon->setContent($faker->text(180));
        $taskAnon->setCreatedAt($faker->dateTimeThisYear());
        $taskAnon->setTitle($faker->word() . $i);
        $taskAnon->setIsDone(false);
        $taskAnon->setUser($Anonyme);

        $manager->persist($taskAnon);

        $manager->flush();
    }
}
