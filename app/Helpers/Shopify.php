<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class Shopify
{

    protected $shop;
    protected $baseUrl;
    protected $url;
    protected $headers;
    protected $client;


    public function __construct($shop)
    {
        $this->shop = $shop;
        $this->baseUrl = '/admin/api/2024-01/'; //config('app.shopify_admin_base_url');
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
        $allowedMethods = ['POST', 'GET', 'PUT', 'DELETE'];
        if (!in_array($method, $allowedMethods)) {
            throw new MethodNotAllowedException($allowedMethods);
        }
        return $this->request($method, trim($args[0]), $args[1] ?? []);
    }

    protected function request(string $method, string $uri, array $payload)
    {
        if (!empty($payload)) {
            $response = $this->client->request(
                $method,
                "{$this->url}{$uri}",
                [
                    'json' => $payload
                ]
            );
        } else {
            $response = $this->client->request(
                $method,
                "{$this->url}{$uri}"
            );
        }
        return json_decode($response->getBody());
    }


    public function prepareProductDataMemory($product)
    {
        $descriptionLines = explode("\n", $product['Short Description']);
        $descriptionHtml = '<ul>';
        foreach ($descriptionLines as $line) {
            $descriptionHtml .= '<li>' . htmlspecialchars($line) . '</li>';
        }
        $descriptionHtml .= '</ul>';

        return [
            'title' => $product['Product'],
            'descriptionHtml' => $descriptionHtml,
            'vendor' => $product['Brand'],
            'productType' => $product['DIMM Type'],
            'variants' => [
                [
                    'price' => str_replace(['$', ','], '', $product['Price']),
                    'sku' => $product['SKU']
                ]
            ],
            'metafields' => [
                ['namespace' => 'custom', 'key' => 'system', 'value' => $product['Sytem'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'brand', 'value' => $product['Brand'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'capacity', 'value' => $product['Capacity'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'memory_type', 'value' => $product['Memory Type'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'memory_speed', 'value' => $product['Memory Speed'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'error_correction', 'value' => $product['Error Correction'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'length', 'value' => $product['Length'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'width', 'value' => $product['Width'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'height', 'value' => $product['Height'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'weight', 'value' => $product['Weight'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'pic_name', 'value' => $product['Pic name'], 'type' => 'single_line_text_field']
            ]
        ];
    }

    public function prepareProductDataInternalDrives($product)
    {
        $descriptionLines = explode("\n", $product['Short Description']);
        $descriptionHtml = '<ul>';
        foreach ($descriptionLines as $line) {
            $descriptionHtml .= '<li>' . htmlspecialchars($line) . '</li>';
        }
        $descriptionHtml .= '</ul>';

        return [
            'title' => $product['Product'],
            'descriptionHtml' => $descriptionHtml,
            'vendor' => $product['Brand'],
            'productType' => $product['Form Factor'],
            'variants' => [
                [
                    'price' => str_replace(['$', ','], '', $product['Price']),
                    'sku' => $product['SKU']
                ]
            ],
            'metafields' => [
                ['namespace' => 'custom', 'key' => 'brand', 'value' => $product['Brand'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'form_factor', 'value' => $product['Form Factor'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'interface', 'value' => $product['Ingerface'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'capacity', 'value' => $product['Capacity'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'rpm', 'value' => $product['RPM'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'cache', 'value' => $product['Cache'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'length', 'value' => $product['Length'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'width', 'value' => $product['Width'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'height', 'value' => $product['Height'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'weight', 'value' => $product['Weight'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'pic_name', 'value' => $product['Pic name'], 'type' => 'single_line_text_field']
            ]
        ];
    }

    public function prepareProductDataExternalDrives($product)
    {
        $descriptionLines = explode("\n", $product['Short Description']);
        $descriptionHtml = '<ul>';
        foreach ($descriptionLines as $line) {
            $descriptionHtml .= '<li>' . htmlspecialchars($line) . '</li>';
        }
        $descriptionHtml .= '</ul>';

        return [
            'title' => $product['Product'],
            'descriptionHtml' => $descriptionHtml,
            'vendor' => $product['Brand'],
            'productType' => $product['Form Factor'],
            'variants' => [
                [
                    'price' => str_replace(['$', ','], '', $product['Price']),
                    'sku' => $product['SKU']
                ]
            ],
            'metafields' => [
                ['namespace' => 'custom', 'key' => 'brand', 'value' => $product['Brand'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'form_factor', 'value' => $product['Form Factor'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'interface', 'value' => $product['Ingerface'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'capacity', 'value' => $product['Capacity'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'length', 'value' => $product['Length'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'width', 'value' => $product['Width'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'height', 'value' => $product['Height'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'weight', 'value' => $product['Weight'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'pic_name', 'value' => $product['Pic name'], 'type' => 'single_line_text_field']
            ]
        ];
    }

    public function prepareProductDataCPU($product)
    {
        $descriptionLines = explode("\n", $product['Short Description']);
        $descriptionHtml = '<ul>';
        foreach ($descriptionLines as $line) {
            $descriptionHtml .= '<li>' . htmlspecialchars($line) . '</li>';
        }
        $descriptionHtml .= '</ul>';

        $input = [
            'title' => $product['Title'],
            'descriptionHtml' => $descriptionHtml,
            'vendor' => $product['Brand'],
            'productType' => 'CPU',
            'templateSuffix' => 'template-full-width-2',
            'published' => true,
            'status' => 'ACTIVE',
            'variants' => [
                [
                    'price' => str_replace(['$', ','], '', $product['Price']),
                    'sku' => $product['SKU'],
                    'weight' => (double)$product['Weight'],
                    'weightUnit' => 'POUNDS',
                ]
            ],
            'metafields' => [
                ['namespace' => 'custom', 'key' => 'processor', 'value' => $product['Processor'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'number_of_cores', 'value' => $product['Number of Core'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'processor_speed', 'value' => $product['Processor Speed'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'specification', 'value' => $product['Expended Description'], 'type' => 'multi_line_text_field'],
                ['namespace' => 'custom', 'key' => 'length', 'value' => $product['Length'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'width', 'value' => $product['Width'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'height', 'value' => $product['Height'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'weight', 'value' => $product['Weight'], 'type' => 'single_line_text_field'],
            ],
            'seo' => [
                'title' => $product['Title']
            ],
        ];

        $media = [];
        if ($product['Image URL']){
            $media = [
                'mediaContentType' => 'IMAGE',
                'originalSource' => $product['Image URL']
            ];
        }


        return [
            'input' => $input,
            'media' => $media,
        ];
    }

    public function prepareProductDataVideoCard($product)
    {
        $descriptionLines = explode("\n", $product['Description']);
        $descriptionHtml = '<ul>';
        foreach ($descriptionLines as $line) {
            $descriptionHtml .= '<li>' . htmlspecialchars($line) . '</li>';
        }
        $descriptionHtml .= '</ul>';

        return [
            'title' => $product['Product'],
            'descriptionHtml' => $descriptionHtml,
            'vendor' => $product['Brand'],
            'productType' => 'Video Card',
            'variants' => [
                [
                    'price' => str_replace(['$', ','], '', $product['Price']),
                    'sku' => $product['SKU']
                ]
            ],
            'metafields' => [
                ['namespace' => 'custom', 'key' => 'brand', 'value' => $product['Brand'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'graphic_processor', 'value' => $product['Graphic Processor'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'video_memory', 'value' => $product['Video Memory'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'specifications', 'value' => $product['Specifications'], 'type' => 'multi_line_text_field'],
                ['namespace' => 'custom', 'key' => 'length', 'value' => $product['Length'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'width', 'value' => $product['Width'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'height', 'value' => $product['Height'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'weight', 'value' => $product['Weight'], 'type' => 'single_line_text_field'],
                ['namespace' => 'custom', 'key' => 'pic_name', 'value' => $product['Pic name'], 'type' => 'single_line_text_field']
            ]
        ];
    }
}
