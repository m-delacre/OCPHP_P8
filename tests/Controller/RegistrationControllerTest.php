<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class RegistrationControllerTest extends WebTestCase
{
    private $userRepository;
    private $client;
    private $adminUser;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->adminUser = $this->userRepository->findOneByUsername('admin0');
    }

    public function testRegisterUserForm(): void
    {
        $this->client->loginUser($this->adminUser);

        $crawler = $this->client->request('GET', '/users');

        $this->assertCount(10, $crawler->filter('tr'));

        $crawler = $this->client->request('GET', '/register');

        // select the button
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');

        $this->assertSelectorTextContains('.uppercase', 'Ajouter');
        $this->assertCount(1, $crawler->filter('form'));

        // retrieve the Form object for the form belonging to this button
        $form = $buttonCrawlerNode->form();

        // set values on a form object
        $form['registration_form[username]'] = 'test';
        $form['registration_form[email]'] = 'test@mail.com';
        $form['registration_form[plainPassword]'] = 'password';
        $form['registration_form[isAdmin]'] = true;

        // submit the Form object
        $this->client->submit($form);

        $this->assertResponseRedirects('/');

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->request('GET', '/users');

        $this->assertCount(11, $crawler->filter('tr'));
    }
}
