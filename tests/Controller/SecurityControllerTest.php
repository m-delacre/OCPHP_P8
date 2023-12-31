<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class SecurityControllerTest extends WebTestCase
{
    private $userRepository;
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testSimpleUserLogin(): void
    {
        $this->client->request('GET', '/');

        $testUser = $this->userRepository->findOneBy(['username' => 'user0']);

        $this->client->loginUser($testUser);

        $this->client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('p', "user0");
        $this->assertSelectorTextContains('.btn-primary', "Se déconnecter");
    }

    public function testAdmineUserLogin(): void
    {

        $this->client->request('GET', '/');

        $testUser = $this->userRepository->findOneByUsername('admin0');

        $this->client->loginUser($testUser);

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('p', "admin0");
        $this->assertSelectorTextContains('.btn-primary', "Utilisateurs");
    }

    public function testLoginForm(): void
    {
        $crawler = $this->client->request('GET', '/login');

        // select the button
        $buttonCrawlerNode = $crawler->selectButton('Connexion');

        $this->assertSelectorTextContains('.btn-primary', 'Connexion');
        $this->assertCount(1, $crawler->filter('form'));

        // retrieve the Form object for the form belonging to this button
        $form = $buttonCrawlerNode->form();

        // set values on a form object
        $form['username'] = 'user0';
        $form['password'] = 'password';

        // submit the Form object
        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testLoginFormFailure(): void
    {
        $crawler = $this->client->request('GET', '/login');

        // select the button
        $buttonCrawlerNode = $crawler->selectButton('Connexion');

        $this->assertSelectorTextContains('.btn-primary', 'Connexion');
        $this->assertCount(1, $crawler->filter('form'));

        // retrieve the Form object for the form belonging to this button
        $form = $buttonCrawlerNode->form();

        // set values on a form object
        $form['username'] = 'user0';
        $form['password'] = 'passwo';

        // submit the Form object
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert-danger', "Invalid credentials.");
    }

    public function testLogOut(): void
    {
        $this->client->request('GET', '/');

        $testUser = $this->userRepository->findOneByUsername('admin0');

        $this->client->loginUser($testUser);

        $this->client->request('GET', '/logout');

        $crawler = $this->client->followRedirect();

        $this->assertSelectorTextContains('.btn-success', "Se connecter");
    }
}
