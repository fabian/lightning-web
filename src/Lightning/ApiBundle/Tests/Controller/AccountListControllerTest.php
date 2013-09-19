<?php

namespace Lightning\ApiBundle\Tests\Controller;

use Buzz\Message\Response;
use Lightning\ApiBundle\Entity\Log;
use Lightning\ApiBundle\Entity\AccountList;
use Lightning\ApiBundle\Tests\AbstractTest;

class AccountListControllerTest extends AbstractTest
{
    protected $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = $this->createAccount();
    }

    public function testCreate()
    {
        $random = $this->getMock('Lightning\ApiBundle\Service\Random');
        $random->expects($this->any())
            ->method('code')
            ->will($this->returnValue('abc'));
        static::$kernel->getContainer()->set('lightning.api_bundle.service.random', $random);

        $this->client->request(
            'POST',
            '/accounts/1/lists',
            array('title' => 'Example'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"permission":"owner","deleted":false,"id":1,"title":"Example","modified":"2012-02-29T12:00:00+0000","invitation":"abc","url":"http:\/\/localhost\/lists\/1","url_items":"http:\/\/localhost\/lists\/1\/items"}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCreateWrongOwnerAccount()
    {
        $this->createAccount();

        $this->client->request(
            'POST',
            '/accounts/2/lists',
            array('title' => 'Example'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":403,"status_text":"Forbidden","current_content":"","message":"Account 2 doesn\'t match authenticated account."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testPush()
    {
        $accountList = $this->createList($this->account);

        $account = $this->createAccount();
        $this->createAccountList($account, $accountList->getList());
        $this->createList($account, new \DateTime('2012-05-25T12:00:00+02:00'));

        $item = $this->createItem($accountList->getList(), 'Milk');
        $this->createLog($this->account, $item, Log::ACTION_ADDED, null, '2012-05-25T12:00:00+02:00');

        $item = $this->createItem($accountList->getList(), 'Juice');
        $this->createLog($this->account, $item, Log::ACTION_ADDED, null, '2012-05-25T13:00:00+02:00');

        $item = $this->createItem($accountList->getList(), 'Eggs');
        $this->createLog($this->account, $item, Log::ACTION_ADDED, null, '2012-05-25T14:00:00+02:00');

        $item = $this->createItem($accountList->getList(), 'Bread');
        $this->createLog($this->account, $item, Log::ACTION_ADDED, null, '2012-05-25T09:00:00+02:00');
        $this->createLog($this->account, $item, Log::ACTION_DELETED, null, '2012-05-25T10:00:00+02:00');

        $item = $this->createItem($accountList->getList(), 'Wine');
        $this->createLog($this->account, $item, Log::ACTION_MODIFIED, 'Water', '2012-05-25T10:00:00+02:00');

        $item = $this->createItem($accountList->getList(), 'Cheese');
        $this->createLog($this->account, $item, Log::ACTION_MODIFIED, 'Milk', '2012-05-25T09:00:00+02:00');
        $this->createLog($this->account, $item, Log::ACTION_COMPLETED, null, '2012-05-25T10:00:00+02:00');

        $this->em->clear();

        $airship = $this->getMockBuilder('Lightning\ApiBundle\Service\UrbanAirship')
            ->disableOriginalConstructor()
            ->getMock();
        $airship->expects($this->once())
            ->method('push')
            ->with(array('http://localhost/accounts/2'), 1, 'Added Milk, Juice and Eggs. Changed Water to Wine. Completed Cheese.', 1)
            ->will($this->returnValue(new Response()));
        static::$kernel->getContainer()->set('lightning.api_bundle.service.urban_airship', $airship);

        $this->client->request(
            'POST',
            '/accounts/1/lists/1/push',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testPushEmpty()
    {
        $accountList = $this->createList($this->account);

        $account = $this->createAccount();
        $this->createAccountList($account, $accountList->getList());
        $this->createList($account, new \DateTime('2012-05-25T12:00:00+02:00'));

        $this->em->clear();

        $airship = $this->getMockBuilder('Lightning\ApiBundle\Service\UrbanAirship')
            ->disableOriginalConstructor()
            ->getMock();
        $airship->expects($this->never())
            ->method('push')
            ->will($this->returnValue(new Response()));
        static::$kernel->getContainer()->set('lightning.api_bundle.service.urban_airship', $airship);

        $this->client->request(
            'POST',
            '/accounts/1/lists/1/push',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testIndex()
    {
        $this->createList($this->account);

        $account = $this->createAccount();
        $accountList = $this->createList($account);
        $this->createAccountList($this->account, $accountList->getList());

        // expired account
        $account = $this->createAccount(new \DateTime('2012-01-01T12:00:00+0000'));
        $accountList = $this->createList($account);
        $this->createAccountList($this->account, $accountList->getList());

        $this->em->clear();

        $this->client->request(
            'GET',
            '/accounts/1/lists',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"lists":[{"permission":"owner","deleted":false,"id":1,"title":"Groceries","modified":"2012-02-29T12:00:00+0000","invitation":"Welcome123","url":"http:\/\/localhost\/lists\/1","url_items":"http:\/\/localhost\/lists\/1\/items"},{"permission":"guest","deleted":false,"id":2,"title":"Groceries","modified":"2012-02-29T12:00:00+0000","invitation":"Welcome123","url":"http:\/\/localhost\/lists\/2","url_items":"http:\/\/localhost\/lists\/2\/items"}]}',
            $response->getContent()
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIndexNoAccount()
    {
        $this->client->request(
            'GET',
            '/accounts/1/lists',
            array(),
            array(),
            array(
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":401,"status_text":"Unauthorized","current_content":"","message":"Account header not found."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testIndexWrongSecret()
    {
        $this->client->request(
            'GET',
            '/accounts/1/lists',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=987',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":403,"status_text":"Forbidden","current_content":"","message":"Account header authentication failed."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testIndexWrongAccount()
    {
        $this->createAccount();

        $this->client->request(
            'GET',
            '/accounts/2/lists',
            array(),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":403,"status_text":"Forbidden","current_content":"","message":"Account 2 doesn\'t match authenticated account."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testJoin()
    {
        $this->createList($this->account);
        $this->createAccount();
        $this->em->clear();

        $this->client->request(
            'PUT',
            '/accounts/2/lists/1',
            array('invitation' => 'Welcome123'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $accountList = $this->em
            ->getRepository('LightningApiBundle:AccountList')
            ->findOneBy(array('list' => 1, 'account' => 2));

        $this->assertEquals(AccountList::PERMISSION_GUEST, $accountList->getPermission());

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testJoinSameAccount()
    {
        $this->createList($this->account);
        $this->em->clear();

        $this->client->request(
            'PUT',
            '/accounts/1/lists/1',
            array('invitation' => 'Welcome123'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $accountList = $this->em
            ->getRepository('LightningApiBundle:AccountList')
            ->findOneBy(array('list' => 1, 'account' => 1));

        $this->assertEquals(AccountList::PERMISSION_OWNER, $accountList->getPermission());

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testJoinWrongList()
    {
        $this->createList($this->account);
        $this->createAccount();
        $this->em->clear();

        $this->client->request(
            'PUT',
            '/accounts/2/lists/999',
            array('invitation' => 'Welcome123'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":404,"status_text":"Not Found","current_content":"","message":"List 999 not found."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testJoinWrongInvitation()
    {
        $this->createList($this->account);
        $this->createAccount();
        $this->em->clear();

        $this->client->request(
            'PUT',
            '/accounts/2/lists/1',
            array('invitation' => 'Foobar'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/2?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":403,"status_text":"Forbidden","current_content":"","message":"Invitation Foobar doesn\'t match invitation for list."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testJoinExpiredAccount()
    {
        $account = $this->createAccount(new \DateTime('2012-01-01T12:00:00+0000'));
        $this->createList($account);
        $this->em->clear();

        $this->client->request(
            'PUT',
            '/accounts/1/lists/1',
            array('invitation' => 'Welcome123'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":402,"status_text":"Payment Required","current_content":"","message":"Account expired."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(402, $response->getStatusCode());
    }

    public function testRead()
    {
        $this->createList($this->account);
        $this->em->clear();

        $this->client->request(
            'PUT',
            '/accounts/1/lists/1/read',
            array('read' => '2012-03-01T12:00:00+00:00'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $accountList = $this->em
            ->getRepository('LightningApiBundle:AccountList')
            ->findOneBy(array('list' => 1, 'account' => 1));

        $this->assertEquals('2012-03-01T12:00:00+00:00', $accountList->getRead()->format('c'));

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testReadOld()
    {
        $this->createList($this->account);
        $this->em->clear();

        $this->client->request(
            'PUT',
            '/accounts/1/lists/1/read',
            array('read' => '2012-02-01T12:00:00+02:00'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $accountList = $this->em
            ->getRepository('LightningApiBundle:AccountList')
            ->findOneBy(array('list' => 1, 'account' => 1));

        $this->assertEquals('2012-02-29T12:00:00+00:00', $accountList->getRead()->format('c'));

        $response = $this->client->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testReadWrongAccount()
    {
        $this->createList($this->account);
        $this->em->clear();

        $this->client->request(
            'PUT',
            '/accounts/2/lists/1/read',
            array('read' => '2012-02-01T12:00:00+02:00'),
            array(),
            array(
                'HTTP_ACCOUNT' => 'http://localhost/accounts/1?secret=123',
                'HTTP_ACCEPT' => 'application/json',
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(
            '{"status":"error","status_code":403,"status_text":"Forbidden","current_content":"","message":"Account 2 doesn\'t match authenticated account."}',
            trim($response->getContent())
        );
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());
    }
}
