<?php

namespace Lightning\ApiBundle\Tests\Service;

use Buzz\Client\ClientInterface;
use Buzz\Message\Request;
use Buzz\Message\Response;
use Lightning\ApiBundle\Service\AppStore;

class AppStoreTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $appStore;

    protected function setUp()
    {
        $this->client = $this->getMock('Buzz\Client\ClientInterface');
        $this->appStore = new AppStore($this->client, 'http://example.com/verifyReceipt');
    }

    public function testVerify()
    {
        $request = new Request('POST', 'http://example.com/verifyReceipt');
        $request->addHeader('Content-Type: application/json');
        $request->setContent('{"receipt-data":"ABC123"}');

        $this->client->expects($this->once())
            ->method('send')
            ->with($request, $this->isInstanceOf('Buzz\Message\Response'))
            ->will(
                $this->returnCallback(
                    function (
                        $request,
                        $response
                    ) {
                        $response->setContent('{"status":0,"receipt":{"product_id":"ch.lightningapp.oneyear","purchase_date":"2011-09-23 15:18:22 Etc/GMT"}}');
                    }
                )
            );

        list($id, $purchased) = $this->appStore->verify('ABC123');

        $this->assertEquals('ch.lightningapp.oneyear', $id);
        $this->assertEquals('2011-09-23T15:18:22+00:00', $purchased->format('c'));
    }
}
