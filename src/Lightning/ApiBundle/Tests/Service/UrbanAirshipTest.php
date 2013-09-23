<?php

namespace Lightning\ApiBundle\Tests\Service;

use Buzz\Client\ClientInterface;
use Buzz\Message\Request;
use Buzz\Message\Response;
use Lightning\ApiBundle\Service\UrbanAirship;

class UrbanAirshipTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $urbanairship;

    protected function setUp()
    {
        $this->client = $this->getMock('Buzz\Client\ClientInterface');
        $this->urbanairship = new UrbanAirship($this->client, 'ABC', '123');
    }

    public function testRegister()
    {
        $request = new Request('PUT', 'https://go.urbanairship.com/api/device_tokens/ABC123');
        $request->addHeader('Authorization: Basic QUJDOjEyMw==');
        $request->addHeader('Content-Type: application/json');
        $request->setContent('{"alias":"1"}');

        $response = new Response();

        $this->client->expects($this->once())
            ->method('send')
            ->with($request, $response);

        $this->urbanairship->register('ABC123', '1');
    }

    public function testPush()
    {
        $request = new Request('POST', 'https://go.urbanairship.com/api/push/');
        $request->addHeader('Authorization: Basic QUJDOjEyMw==');
        $request->addHeader('Content-Type: application/json');
        $request->setContent('{"aliases":["ABC123"],"aps":{"badge":"2","alert":"Test","lightning_list":"1","lightning_access_token":""}}');

        $response = new Response();

        $this->client->expects($this->once())
            ->method('send')
            ->with($request, $response);

        $this->urbanairship->push(array('ABC123'), '2', 'Test', '1');
    }
}
