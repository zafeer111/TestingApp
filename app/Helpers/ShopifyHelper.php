<?php

namespace App\Helpers;

use App\Models\User;
use GuzzleHttp\Client;
use InvalidArgumentException;

class ShopifyHelper
{
    protected $shopifyStoreUrl;
    protected $accessToken;
    protected $client;

    public function __construct()
    {
        $user = User::first();

        if (!$user) {
            throw new InvalidArgumentException('No user found in the database.');
        }

        $this->shopifyStoreUrl = $user->name;
        $this->accessToken = $user->password;

        if (empty($this->shopifyStoreUrl) || empty($this->accessToken)) {
            throw new InvalidArgumentException('Shopify store URL or access token is missing.');
        }

        if (!filter_var("https://{$this->shopifyStoreUrl}/admin/api/2024-04/graphql.json", FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid Shopify store URL.');
        }

        $this->client = new Client();
    }

    public function readCSVFile($filePath)
    {
        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);
        $products = [];

        while ($row = fgetcsv($file)) {
            $products[] = array_combine($header, $row);
        }

        fclose($file);
        return $products;
    }

    public function prepareProductData($products)
    {
        $preparedData = [];

        foreach ($products as $product) {
            $preparedData[] = [
                'title' => $product['Product'],
                'bodyHtml' => $product['Short Description'],
                'vendor' => $product['Brand'],
                'productType' => $product['DIMM Type'],
                'variants' => [
                    [
                        'price' => str_replace(['$', ','], '', $product['Price']),
                        'sku' => $product['SKU']
                    ]
                ],
                'metafields' => [
                    ['key' => 'system', 'value' => $product['Sytem'], 'type' => 'single_line_text_field'],
                    ['key' => 'brand', 'value' => $product['Brand'], 'type' => 'single_line_text_field'],
                    ['key' => 'capacity', 'value' => $product['Capacity'], 'type' => 'single_line_text_field'],
                    ['key' => 'memory_type', 'value' => $product['Memory Type'], 'type' => 'single_line_text_field'],
                    ['key' => 'memory_speed', 'value' => $product['Memory Speed'], 'type' => 'single_line_text_field'],
                    ['key' => 'error_correction', 'value' => $product['Error Correction'], 'type' => 'single_line_text_field'],
                    ['key' => 'length', 'value' => $product['Length'], 'type' => 'single_line_text_field'],
                    ['key' => 'width', 'value' => $product['Width'], 'type' => 'single_line_text_field'],
                    ['key' => 'height', 'value' => $product['Height'], 'type' => 'single_line_text_field'],
                    ['key' => 'weight', 'value' => $product['Weight'], 'type' => 'single_line_text_field'],
                    ['key' => 'pic_name', 'value' => $product['Pic name'], 'type' => 'single_line_text_field'],
                ]
            ];
        }

        return $preparedData;
    }

    public function createProduct($productData)
    {
        $query = <<<GRAPHQL
        mutation {
            productCreate(input: {
                title: "{$productData['title']}",
                bodyHtml: "{$productData['bodyHtml']}",
                vendor: "{$productData['vendor']}",
                productType: "{$productData['productType']}",
                variants: [
                    {
                        price: "{$productData['variants'][0]['price']}",
                        sku: "{$productData['variants'][0]['sku']}"
                    }
                ],
                metafields: [
                    {key: "system", value: "{$productData['metafields'][0]['value']}", type: SINGLE_LINE_TEXT_FIELD},
                    {key: "brand", value: "{$productData['metafields'][1]['value']}", type: SINGLE_LINE_TEXT_FIELD},
                    {key: "capacity", value: "{$productData['metafields'][2]['value']}", type: SINGLE_LINE_TEXT_FIELD},
                    {key: "memory_type", value: "{$productData['metafields'][3]['value']}", type: SINGLE_LINE_TEXT_FIELD},
                    {key: "memory_speed", value: "{$productData['metafields'][4]['value']}", type: SINGLE_LINE_TEXT_FIELD},
                    {key: "error_correction", value: "{$productData['metafields'][5]['value']}", type: SINGLE_LINE_TEXT_FIELD},
                    {key: "length", value: "{$productData['metafields'][6]['value']}", type: SINGLE_LINE_TEXT_FIELD},
                    {key: "width", value: "{$productData['metafields'][7]['value']}", type: SINGLE_LINE_TEXT_FIELD},
                    {key: "height", value: "{$productData['metafields'][8]['value']}", type: SINGLE_LINE_TEXT_FIELD},
                    {key: "weight", value: "{$productData['metafields'][9]['value']}", type: SINGLE_LINE_TEXT_FIELD},
                    {key: "pic_name", value: "{$productData['metafields'][10]['value']}", type: SINGLE_LINE_TEXT_FIELD}
                ]
            }) {
                product {
                    id
                    title
                }
                userErrors {
                    field
                    message
                }
            }
        }
        GRAPHQL;

        echo "GraphQL Query: " . $query;

        $response = $this->client->post("https://{$this->shopifyStoreUrl}/admin/api/2024-04/graphql.json", [
            'headers' => [
                'X-Shopify-Access-Token' => $this->accessToken,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode(['query' => $query])
        ]);

        $responseBody = $response->getBody()->getContents();

        echo "Response: " . $responseBody;

        return json_decode($responseBody, true);
    }
}