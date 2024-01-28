<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $userRepository;
    private $client;
    private $entityManager;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testRedirectToLoginOnAccessUsersList(): void
    {
        $this->client->request('GET', '/users');

        $this->assertResponseRedirects('/login');
        $crawler = $this->client->followRedirect();

        // select the button
        $buttonCrawlerNode = $crawler->selectButton('Connexion');

        $this->assertSelectorTextContains('.btn-primary', 'Connexion');
        $this->assertCount(1, $crawler->filter('form'));

        // retrieve the Form object for the form belonging to this button
        $form = $buttonCrawlerNode->form();

        // set values on a form object
        $form['username'] = 'admin0';
        $form['password'] = 'password';

        // submit the Form object
        $this->client->submit($form);

        $this->assertResponseRedirects();

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }

    public function testUnauthorizeAccessToUsersList(): void
    {
        $testUser = $this->userRepository->findOneByUsername('user0');

        $this->client->loginUser($testUser);

        $this->client->request('GET', '/users');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAccessUsersList(): void
    {
        $testUser = $this->userRepository->findOneByUsername('admin0');

        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/users');

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(11, $crawler->filter('tr'));
    }

    public function testChangeUserPassword(): void
    {
        $adminUser = $this->userRepository->findOneByUsername('admin0');
        $userToEdit = $this->userRepository->findOneByUsername('user0');

        $this->client->loginUser($adminUser);

        $crawler = $this->client->request('POST', '/users/' . $userToEdit->getId() . '/edit');

        $this->assertContains('ROLE_USER', $userToEdit->getRoles());

        // select the button
        $buttonCrawlerNode = $crawler->selectButton('Modifier');

        $this->assertSelectorTextContains('button', 'Modifier');
        $this->assertCount(1, $crawler->filter('form'));

        // retrieve the Form object for the form belonging to this button
        $form = $buttonCrawlerNode->form();

        // set values on a form object
        $form['modif_user_form[username]'] = 'user0v2';
        $form['modif_user_form[password]'] = 'test123456';
        $form['modif_user_form[isAdmin]'] = false;

        $this->client->submit($form);

        $this->assertResponseRedirects('/users');
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');

        $this->client->request('GET', '/logout');
        $crawler = $this->client->followRedirect();

        $crawler = $this->client->request('GET', '/login');

        $buttonCrawlerNode = $crawler->selectButton('Connexion');

        $this->assertSelectorTextContains('.btn-primary', 'Connexion');
        $this->assertCount(1, $crawler->filter('form'));

        $form = $buttonCrawlerNode->form();

        $form['username'] = 'user0v2';
        $form['password'] = 'test123456';

        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testEditUser(): void
    {
        $testUser = $this->userRepository->findOneByUsername('admin0');
        $userToEdit = $this->userRepository->findOneByUsername('user0');

        $this->client->loginUser($testUser);

        $crawler = $this->client->request('POST', '/users/' . $userToEdit->getId() . '/edit');

        $this->assertContains('ROLE_USER', $userToEdit->getRoles());

        // select the button
        $buttonCrawlerNode = $crawler->selectButton('Modifier');

        $this->assertSelectorTextContains('button', 'Modifier');
        $this->assertCount(1, $crawler->filter('form'));

        // retrieve the Form object for the form belonging to this button
        $form = $buttonCrawlerNode->form();

        // set values on a form object
        $form['modif_user_form[username]'] = 'user0v2';
        $form['modif_user_form[isAdmin]'] = true;

        // submit the Form object
        $this->client->submit($form);

        $verifEdit = $this->userRepository->findOneByUsername('user0v2');
        $this->assertContains('ROLE_ADMIN', $verifEdit->getRoles());
        $this->assertResponseRedirects('/users');

        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }
}
