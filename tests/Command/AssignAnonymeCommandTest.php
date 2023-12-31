<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use App\Repository\UserRepository;

class AssignAnonymeCommandTest extends KernelTestCase
{
    private $userRepository;

    public function setUp(): void
    {
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testCreateAnonUser(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $missingAnonUser = $this->userRepository->findByUsername('anonyme');

        $this->assertEmpty($missingAnonUser);

        $command = $application->find('app:assign-anonyme');
        $commandTester = new CommandTester($command);
        $commandTester->execute([], []);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("L'utilisateur Anonyme a été attribué aux tâches sans utilisateur.", $output);

        $anonUser = $this->userRepository->findByUsername('anonyme');

        $this->assertNotNull($anonUser);
    }
}
