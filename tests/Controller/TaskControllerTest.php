<?php

namespace App\Tests\Controller;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class TaskControllerTest extends WebTestCase
{
    private $userRepository;
    private $client;
    private $taskRepository;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->taskRepository = self::getContainer()->get(TaskRepository::class);
    }

    public function testTaskList(): void
    {
        $testUser = $this->userRepository->findOneByUsername('user0');

        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertCount(18, $crawler->filter('.card'));
    }

    public function testTaskListDone(): void
    {
        $testUser = $this->userRepository->findOneByUsername('user0');

        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/tasksDone');

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $crawler->filter('.card'));
    }

    public function testGoToTaskCreationPage(): void
    {
        $testUser = $this->userRepository->findOneByUsername('user0');

        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/tasks/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('button', 'Ajouter');
        $this->assertCount(1, $crawler->filter('form'));
    }

    public function testCreateTask(): void
    {
        $testUser = $this->userRepository->findOneByUsername('user0');

        $this->client->loginUser($testUser);

        $crawler = $this->client->request('POST', '/tasks/create');

        // select the button
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');

        $this->assertSelectorTextContains('button', 'Ajouter');
        $this->assertCount(1, $crawler->filter('form'));

        // retrieve the Form object for the form belonging to this button
        $form = $buttonCrawlerNode->form();

        // set values on a form object
        $form['task[title]'] = 'taskTitle';
        $form['task[content]'] = 'taskContent';

        // submit the Form object
        $this->client->submit($form);

        // optionally, you can combine the last 2 steps by passing an array of
        // field values while submitting the form:
        // $client->submit($form, [
        //     'my_form[name]'    => 'Fabien',
        //     'my_form[subject]' => 'Symfony rocks!',
        // ]);

        $this->assertResponseRedirects('/tasks');
    }

    public function testEditTask(): void
    {
        $testUser = $this->userRepository->findOneByUsername('user0');

        $this->client->loginUser($testUser);

        $taskToEdit = $this->taskRepository->findOneByUser($testUser);

        $crawler = $this->client->request('POST', '/tasks/' . $taskToEdit->getId() . '/edit');

        // select the button
        $buttonCrawlerNode = $crawler->selectButton('Modifier');

        $this->assertSelectorTextContains('button', 'Modifier');
        $this->assertCount(1, $crawler->filter('form'));

        // retrieve the Form object for the form belonging to this button
        $form = $buttonCrawlerNode->form();

        // set values on a form object
        $form['task[title]'] = 'taskTitlev3';
        $form['task[content]'] = 'taskContentv3';

        // submit the Form object
        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');

        $crawler = $this->client->followRedirect();

        $newTaskFound = $this->taskRepository->findOneByTitle('taskTitlev3');
        $this->assertNotEmpty($newTaskFound);
        $this->assertEquals('taskTitlev3', $newTaskFound->getTitle());
    }

    public function testToggleTask(): void
    {
        $testUser = $this->userRepository->findOneByUsername('user0');

        $this->client->loginUser($testUser);

        $taskToToggle = $this->taskRepository->findOneByUser($testUser);

        $this->assertFalse($taskToToggle->isDone());

        $crawler = $this->client->request('GET', '/tasks');

        $this->assertCount(18, $crawler->filter('.card'));

        $crawler = $this->client->request('GET', '/tasks/' . $taskToToggle->getId() . '/toggle');

        $this->assertResponseRedirects('/tasks');

        $crawler = $this->client->followRedirect();

        $this->assertCount(17, $crawler->filter('.card'));

        $crawler = $this->client->request('GET', '/tasksDone');

        $this->assertCount(1, $crawler->filter('.card'));
    }

    public function testDeleteTask(): void
    {
        $testUser = $this->userRepository->findOneByUsername('user0');

        $this->client->loginUser($testUser);

        $taskToDelete = $this->taskRepository->findOneByUser($testUser);

        $crawler = $this->client->request('GET', '/tasks');

        $this->assertCount(18, $crawler->filter('.card'));

        $crawler = $this->client->request('GET', '/tasks/' . $taskToDelete->getId() . '/delete');

        $this->assertResponseRedirects('/tasks');

        $crawler = $this->client->followRedirect();

        $this->assertCount(17, $crawler->filter('.card'));

        $ShouldNotExistAnymore = $this->taskRepository->findOneByUser($testUser);

        $this->assertEmpty($ShouldNotExistAnymore);
    }

    public function testDeleteAnonTaskWhenAdmin(): void
    {
        // Récupérez l'application Symfony
        $application = new Application($this->client->getKernel());

        // Remplacez 'your-command' par le nom de votre commande
        $command = $application->find('app:assign-anonyme');

        // Créez un testeur de commande
        $commandTester = new CommandTester($command);

        // Exécutez la commande avec les arguments et options nécessaires
        $commandTester->execute([]);

        // Vérifiez la sortie de la commande
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("L'utilisateur Anonyme a été attribué aux tâches sans utilisateur.", $output);

        $adminUser = $this->userRepository->findOneByUsername('admin0');
        $anonymeUser = $this->userRepository->findOneByUsername('anonyme');

        $this->client->loginUser($adminUser);

        $taskToDelete = $this->taskRepository->findOneByUser($anonymeUser);

        $crawler = $this->client->request('GET', '/tasks');

        $this->assertCount(18, $crawler->filter('.card'));

        $crawler = $this->client->request('GET', '/tasks/' . $taskToDelete->getId() . '/delete');

        $this->assertResponseRedirects('/tasks');

        $crawler = $this->client->followRedirect();

        $this->assertCount(17, $crawler->filter('.card'));

        $ShouldNotExistAnymore = $this->taskRepository->findOneById($taskToDelete->getId());

        $this->assertEmpty($ShouldNotExistAnymore);
    }
}
