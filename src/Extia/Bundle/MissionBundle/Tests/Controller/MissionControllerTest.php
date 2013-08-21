<?php

namespace Extia\Bundle\MissionBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MissionControllerTest extends WebTestCase
{
    public function testList()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/list/{page}');
    }

    public function testNew()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/new');
    }

    public function testEdit()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/edit/{id}');
    }

    public function testDelete()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/delete/{id}');
    }

}
