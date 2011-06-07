<?php

namespace AnketaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AnketaControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($crawler->filter('html:contains("Anketa")')->count() > 0);
    }
}
