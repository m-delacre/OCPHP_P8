<?php

namespace App\Tests\Command;

use App\Repository\TaskRepository;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class AssignAnonymeCommandTest extends KernelTestCase
{
    private $userRepository;
    private $taskRepository;
    private $entityManager;

    public function setUp(): void
    {
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->taskRepository = self::getContainer()->get(TaskRepository::class);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testCreateAnonUser(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        /*
        =======================================================================
        //recuperer anon
        $anonymeUser = $this->userRepository->findOneByUsername('anonyme');
        //attribuer user null aux taches d'anonyme
        $anonTasks = $this->taskRepository->findByUser($anonymeUser);
        foreach($anonTasks as $task) {
            $task->setUser(null);
            $this->entityManager->persist($task);
        }
        $this->entityManager->flush();
        $tasksListWithNull = $this->taskRepository->findBy(['user'=>null]);
        $this->assertNotEmpty($tasksListWithNull);
        dump($tasksListWithNull);
        $anonTasks = $this->taskRepository->findBy(['user'=>$anonymeUser]);
        dump($anonTasks);
        // //effacer le user
        // $this->entityManager->remove($anonymeUser);
        // $this->entityManager->flush();
        // $deletedAnonyme = $this->userRepository->findByUsername('anonyme');
        // $this->assertNull($deletedAnonyme);
        ======================================================================= 
        */
        //get anon
        $anonymeUser = $this->userRepository->findOneByUsername('anonyme');
        //get anon tasks
        $anonTasks = $this->taskRepository->findByUser($anonymeUser);

        //delete anon tasks
        foreach($anonTasks as $task) {
            $task = $this->entityManager->getReference(Task::class, $task->getId());
            $this->entityManager->remove($task);
        }
        $this->entityManager->flush();
        $emptyAnonTasksList = $this->taskRepository->findByUser($anonymeUser);
        $this->assertEmpty($emptyAnonTasksList);

        //delete anon user
        $userToDelete = $this->entityManager->getReference(User::class, $anonymeUser->getId());
        $this->entityManager->remove($userToDelete);
        $this->entityManager->flush();
        $deletedAnonymeUser = $this->userRepository->findByUsername('anonyme');
        $this->assertEmpty($deletedAnonymeUser);

        //create task without user
        $newTask = new Task();
        $newTask->setContent('im a new task content');
        $newTask->setCreatedAt(new DateTime());
        $newTask->setTitle('the new task title');
        $newTask->setIsDone(false);
        $this->entityManager->persist($newTask);
        $this->entityManager->flush();

        $taskWithoutUser = $this->taskRepository->findByUser(null);
        $this->assertNotEmpty($taskWithoutUser);

        //exec la commande
        $command = $application->find('app:assign-anonyme');
        $commandTester = new CommandTester($command);
        $commandTester->execute([], []);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("L'utilisateur Anonyme a été attribué aux tâches sans utilisateur.", $output);

        $anonUser = $this->userRepository->findByUsername('anonyme');
        $this->assertNotNull($anonUser);

        $tasksWithNullUser = $this->taskRepository->findByUser(null);
        $this->assertEmpty($tasksWithNullUser);
    }
}
