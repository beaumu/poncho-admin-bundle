<?php

namespace Poncho\AdminBundle\Tests\Functional\Controller;

use Poncho\AdminBundle\Tests\App\Entity\AdminUser;
use Poncho\AdminBundle\Tests\Functional\DbUtils;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerCsrfTest extends WebTestCase
{
    public function testDeleteWithoutTokenIsRefused(): void
    {
        $client = static::createClient();
        DbUtils::initDb($client->getKernel());

        $repo = static::getContainer()->get('doctrine')->getRepository(AdminUser::class);
        $user = $repo->findOneBy(['email' => 'john.doe@ok.com']);
        $client->loginUser($user);

        $client->request('GET', '/user/delete/' . $user->id);

        $this->assertResponseStatusCodeSame(403);
        $this->assertNotNull($repo->find($user->id), 'The user must not have been deleted.');
    }

    public function testDeleteWithWrongTokenIsRefused(): void
    {
        $client = static::createClient();
        DbUtils::initDb($client->getKernel());

        $repo = static::getContainer()->get('doctrine')->getRepository(AdminUser::class);
        $user = $repo->findOneBy(['email' => 'john.doe@ok.com']);
        $client->loginUser($user);

        $client->request('GET', '/user/delete/' . $user->id, ['_token' => 'not-the-right-token']);

        $this->assertResponseStatusCodeSame(403);
        $this->assertNotNull($repo->find($user->id), 'The user must not have been deleted.');
    }

    /**
     * End-to-end: fetch the user table exactly as the browser does, follow the
     * delete link it rendered — including the token it carries — and confirm
     * it both works and actually deletes.
     */
    public function testDeleteLinkRenderedInTheTableCanBeFollowedToDelete(): void
    {
        $client = static::createClient();
        DbUtils::initDb($client->getKernel());

        $repo = static::getContainer()->get('doctrine')->getRepository(AdminUser::class);
        $user = $repo->findOneBy(['email' => 'john.doe@ok.com']);
        $id = $user->id;
        $client->loginUser($user);

        $client->request('POST', '/user', [
            '_dtid' => 'poncho_adminbundle_datatable_usertable',
            'start' => 0,
            'length' => 25,
        ]);
        $this->assertResponseIsSuccessful();

        $json = json_decode($client->getResponse()->getContent(), true);
        $row = null;
        foreach ($json['data'] as $r) {
            if (($r['DT_RowAttr']['data-id'] ?? null) === $id) {
                $row = $r;
            }
        }
        $this->assertNotNull($row, 'The user must appear in the table.');

        $actionsHtml = null;
        foreach ($row as $cell) {
            if (\is_string($cell) && str_contains($cell, '/user/delete/')) {
                $actionsHtml = $cell;
            }
        }
        $this->assertNotNull($actionsHtml, 'A delete link must be rendered for this row.');
        $this->assertMatchesRegularExpression('/_token=/', $actionsHtml, 'The rendered delete link must carry a CSRF token.');
        preg_match('#data-xhr="([^"]*/user/delete/' . $id . '[^"]*)"#', $actionsHtml, $m);
        $this->assertNotEmpty($m, 'The delete link URL must be extractable from the rendered HTML.');

        $client->request('GET', html_entity_decode($m[1]));

        $this->assertResponseIsSuccessful();
        $this->assertNull($repo->find($id), 'The user must have been deleted.');
    }
}
