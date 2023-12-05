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

#[AsCommand(
    name: 'app:assign-anonyme',
    description: 'Assign the anonyme user to task without user.',
)]
class AssignAnonymeCommand extends Command
{

    private $entityManager = EntityManagerInterface::class;
    private $taskRepository = Task::class;
    private $userRepository = User::class;

    public function __construct(EntityManagerInterface $entityManager, TaskRepository $taskRepository, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
        $this->userRepository = $userRepository;

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

        $userAnonyme = $this->userRepository->findOneBy(['username' => "anonyme"]);
        $taskList = $this->taskRepository->findBy(['user' => null]);

        foreach ($taskList as $task) {
            $task->setUser($userAnonyme);

            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
