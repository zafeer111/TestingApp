<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class Shopify {

    protected $shop;
    protected $baseUrl;
    protected $url;
    protected $headers;
    protected $client;


    public function __construct($shop) {
        $this->shop = $shop;
        $this->baseUrl = '/admin/api/2024-04/'; //config('app.shopify_admin_base_url');
        $this->url = "https://{$shop['name']}{$this->baseUrl}";
        $this->headers = [
            'X-Shopify-Access-Token'     => $shop['password'],
        ];
        $this->client = new Client([
            'stream' => true,
            'keep_alive' => true,
            'headers' => $this->headers,
        ]);
    }

    public function __call($method, $args)
    {
        $method = strtoupper($method);
        $allowedMethods = ['POST','GET','PUT','DELETE'];
        if(!in_array($method,$allowedMethods)){
            throw new MethodNotAllowedException($allowedMethods);
        }
        return $this->request($method,trim($args[0]),$args[1] ?? []);
    }

    protected function request(string $method, string $uri, array $payload)
    {
        if(!empty($payload)){
            $response = $this->client->request(
                $method,
                "{$this->url}{$uri}",
                [
                    'json' => $payload
                ]
            );
        }else{
            $response = $this->client->request(
                $method,
                "{$this->url}{$uri}"
            );
        }
        return json_decode($response->getBody());
    }

}
