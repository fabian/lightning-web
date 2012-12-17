<?php

namespace Lightning\ApiBundle\Service;

use Buzz\Client\ClientInterface;
use Buzz\Message\RequestInterface;
use Buzz\Message\Request;
use Buzz\Message\Response;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 * @Service
 */
class AppStore
{
    protected $client;
    protected $verifyUrl;

    /**
     * @InjectParams({
     *     "client" = @Inject("lightning.api_bundle.buzz.client"),
     *     "verifyUrl" = @Inject("%lightning_api.appstore_verify_url%")
     * })
     *
     * @param ClientInterface $client
     * @param string          $verifyUrl
     */
    public function __construct(ClientInterface $client, $verifyUrl)
    {
        $this->client = $client;
        $this->verifyUrl = $verifyUrl;
    }

    /**
     * @param string $receipt
     */
    public function verify($receipt)
    {
        $payload = array(
            'receipt-data' => $receipt,
        );

        $request = new Request(RequestInterface::METHOD_POST, $this->verifyUrl);
        $request->addHeader('Content-Type: application/json');
        $request->setContent(json_encode($payload));

        $response = new Response();

        $this->client->send($request, $response);

        $content = $response->getContent();
        $data = json_decode($content);

        // ensure the expected data is present
        if (!$data || !isset($data->status) || $data->status !== 0) {
            var_dump($response);
            throw new \Exception('Invalid receipt: ' . $content);
        }

        return array(
            $data->receipt->product_id,
            new \DateTime($data->receipt->purchase_date),
        );
    }
}
