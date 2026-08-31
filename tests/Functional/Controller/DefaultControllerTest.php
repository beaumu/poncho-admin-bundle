<?php

namespace Poncho\AdminBundle\Tests\Functional\Controller;

use Poncho\AdminBundle\PonchoAdminConfiguration;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function test(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $appName = $this->getContainer()->get(PonchoAdminConfiguration::class)->appName();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('title', $appName);
    }
}
