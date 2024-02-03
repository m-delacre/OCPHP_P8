<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends KernelTestCase
{
    private $userPasswordHasher;
    private $validator;
    private $userRepository;
    private $taskRepository;

    public function setUp(): void
    {
        $this->userPasswordHasher = self::getContainer()->get("security.user_password_hasher");
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->taskRepository = self::getContainer()->get(TaskRepository::class);
    }

    public function testUserClass(): void
    {
        $user = (new User());
        $user->setEmail('user@test.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        //$user->setPassword('password');
        $user->setRoles(['ROLE_USER']);
        $user->setUsername('BotUser');

        $this->assertEquals('BotUser', $user->getUsername());
        $this->assertEquals('user@test.com', $user->getEmail());
        $this->assertNotNull($user->getUserIdentifier());
        $this->assertNull($user->getId());
        $this->assertIsString($user->getEmail());
        $this->assertIsString($user->getUsername());
        $this->assertIsArray($user->getRoles());

        $this->assertTrue($this->userPasswordHasher->isPasswordValid($user, 'password'));

        $this->assertCount(1, $user->getRoles());
        $user->setRolesAdminUser();
        $this->assertCount(2, $user->getRoles());
        $user->setRolesSimpleUser();
        $this->assertCount(1, $user->getRoles());

        $errors = $this->validator->validate($user);
        $this->assertCount(0, $errors);
    }

    public function testRecoverUserTasks(): void
    {
        $user = $this->userRepository->findOneByUsername('admin1');

        $this->assertCount(2, $user->getTasks());

        $taskToBeRemoved = $this->taskRepository->findOneByUser($user);

        $user->removeTask($taskToBeRemoved);

        $ShouldNotExistAnymore = $this->taskRepository->findOneByUser($taskToBeRemoved->getId());

        $this->assertEmpty($ShouldNotExistAnymore);

        $this->assertCount(1, $user->getTasks());
    }

    public function testAddTask(): void
    {
        $user = $this->userRepository->findOneByUsername('admin1');

        $this->assertCount(2, $user->getTasks());

        $newTask = new Task();
        $newTask
            ->setTitle('testingNewTask')
            ->setContent('test123')
            ->setCreatedAt(new DateTime())
            ->setIsDone(false);

        $user->addTask($newTask);

        $this->assertCount(3, $user->getTasks());
    }
}
