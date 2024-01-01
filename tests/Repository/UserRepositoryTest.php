<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends KernelTestCase
{
    public function testPasswordChange(): void
    {
        $kernel = self::bootKernel();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $passwordHasher = self::getContainer()->get("security.user_password_hasher");

        $userTest = $userRepository->findOneByUsername('user0');

        $this->assertTrue($passwordHasher->isPasswordValid($userTest, 'password'));

        $newPassword = $passwordHasher->hashPassword($userTest, 'test');
        $userRepository->upgradePassword($userTest, $newPassword);

        $this->assertTrue($passwordHasher->isPasswordValid($userTest, 'test'));
    }

    // public function testPasswordChangeWrongEntity(): void
    // {
    //     $kernel = self::bootKernel();
    //     $userRepository = self::getContainer()->get(UserRepository::class);
    //     $taskRepository = self::getContainer()->get(TaskRepository::class);
    //     $passwordHasher = self::getContainer()->get("security.user_password_hasher");

    //     $userTest = $userRepository->findOneByUsername('user0');
    //     $taskTest = $taskRepository->findOneByTitle('iste0');

    //     $newPassword = $passwordHasher->hashPassword($userTest, 'test');
    //     $userRepository->upgradePassword($taskTest, $newPassword);

    //     //$this->assertTrue($passwordHasher->isPasswordValid($userTest, 'test'));
    //     $this->expectException(UnsupportedUserException::class);
    // }

    public function testUpgradePasswordThrowsExceptionForUnsupportedUser()
    {
        // Création du mock pour PasswordAuthenticatedUserInterface
        $mockUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

        // S'assurer que le mock n'est pas une instance de User
        $this->assertNotInstanceOf(User::class, $mockUser);

        // Récupérer le UserRepository
        $userRepository = self::getContainer()->get(UserRepository::class);

        // Attendre l'exception UnsupportedUserException
        $this->expectException(UnsupportedUserException::class);

        // Appeler upgradePassword avec le mock et vérifier l'exception
        $userRepository->upgradePassword($mockUser, 'new_hashed_password');
    }
}
