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
class UrbanAirship
{
    const URL = 'https://go.urbanairship.com/api';

    protected $client;
    protected $key;
    protected $secret;

    /**
     * @InjectParams({
     *     "client" = @Inject("lightning.api_bundle.buzz.client"),
     *     "key" = @Inject("%lightning_api.urbanairship_key%"),
     *     "secret" = @Inject("%lightning_api.urbanairship_secret%")
     * })
     *
     * @param ClientInterface $client
     * @param string          $key
     * @param string          $secret
     */
    public function __construct(ClientInterface $client, $key, $secret)
    {
        $this->client = $client;
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $payload
     */
    protected function request($method, $path, $payload)
    {
        $url = self::URL . $path;

        $request = new Request($method, $url);
        $request->addHeader('Authorization: Basic ' . base64_encode($this->key . ':' . $this->secret));
        $request->addHeader('Content-Type: application/json');
        $request->setContent(json_encode($payload));

        $response = new Response();

        $this->client->send($request, $response);
    }

    /**
     * @param string $deviceToken
     * @param string $alias
     */
    public function register($deviceToken, $alias)
    {
        $payload = array(
            'alias' => $alias,
        );

        $this->request(RequestInterface::METHOD_PUT, '/device_tokens/' . $deviceToken, $payload);
    }

    /**
     * @param array<string> $aliases
     * @param string        $badge
     * @param string        $alert
     * @param string        $list
     */
    public function push($aliases, $badge, $alert, $list)
    {
        $payload = array(
            'aliases' => $aliases,
            'aps' => array(
                'badge' => $badge,
                'alert' => $alert,
                'lightning_list' => $list,
            ),
        );

        $this->request(RequestInterface::METHOD_POST, '/push/', $payload);
    }
}
