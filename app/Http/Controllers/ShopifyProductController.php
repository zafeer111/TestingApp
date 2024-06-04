<?php

namespace App\Http\Controllers;

use App\Helpers\Shopify;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ShopifyHelper;
use Illuminate\Support\Facades\Log;

class ShopifyProductController extends Controller
{
    protected $shopifyHelper;

    public function __construct(ShopifyHelper $shopifyHelper)
    {
        $this->shopifyHelper = $shopifyHelper;
    }

    public function importProducts()
    {
        $shop = User::select('name', 'password')->where('name', env('SHOP','zafeer-development.myshopify.com'))->first()->toArray();

        $filePath = storage_path('app/Sample product sheet.csv');
        $products = $this->shopifyHelper->readCSVFile($filePath);
        $preparedData = $this->shopifyHelper->prepareProductData($products);
        $shopify = new Shopify($shop);
        foreach ($preparedData as $productData) {
 #           $response = $this->shopifyHelper->createProduct($productData);
            $payload = ['input' => $productData];

            $request['query'] = $this->shopifyMutationForProduct();
            $request['variables'] = $payload ;
            $shopifyProduct = $shopify->post('graphql.json', $request);
            Log::info(var_export($shopifyProduct,true));
//            if (isset($response['errors'])) {
//
//                echo "Error: " . json_encode($response['errors']);
//            } else {
//
//                echo "Product created: " . $response['data']['productCreate']['product']['title'] . "\n";
//            }
        }
    }

    private function shopifyMutationForProduct(): string
    {
        return 'mutation productCreate($input: ProductInput!) {
            productCreate(input: $input) {
                product {
                    id
                    title
                    handle
                    variants(first: 1) {
                        edges {
                            node {
                                id
                                title
                            }
                        }
                    }
                }
              userErrors {
                field
                message
              }
            }
        }';
    }
}