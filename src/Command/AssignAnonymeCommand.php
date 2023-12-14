<?php

namespace App\Command;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:assign-anonyme',
    description: 'Assign the anonyme user to task without user.',
)]
class AssignAnonymeCommand extends Command
{

    private $entityManager = EntityManagerInterface::class;
    private $taskRepository = Task::class;
    private $userRepository = User::class;
    private $userPasswordHasher = UserPasswordHasherInterface::class;

    public function __construct(EntityManagerInterface $entityManager, TaskRepository $taskRepository, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;

        parent::__construct();
    }

    protected function configure(): void
    {
        // $this
        //     ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        //     ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        // ;
        $this
            ->setDescription('Assign the anonyme user to task without user.')
            ->setHelp('Assign the anonyme user to task without user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // $arg1 = $input->getArgument('arg1');

        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        // if ($input->getOption('option1')) {
        //     // ...
        // }

        $io->success("L'utilisateur Anonyme a été attribué aux tâches sans utilisateur.");

        // TODO: créer l'utilisateur anonyme si il existe pas
        $userAnonyme = $this->userRepository->findOneBy(['username' => "anonyme"]);
        if ($userAnonyme === null) {
            $newAnonyme = new User();
            $newAnonyme->setEmail('ano@nyme.com');
            $newAnonyme->setPassword($this->userPasswordHasher->hashPassword($newAnonyme, "password"));
            $newAnonyme->setRolesSimpleUser();
            $newAnonyme->setUsername('anonyme');

            $this->entityManager->persist($newAnonyme);
            $this->entityManager->flush();

            $userAnonyme = $newAnonyme;
        }

        $taskList = $this->taskRepository->findBy(['user' => null]);

        foreach ($taskList as $task) {
            $task->setUser($userAnonyme);

            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
