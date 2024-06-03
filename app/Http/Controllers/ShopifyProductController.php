<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ShopifyHelper;

class ShopifyProductController extends Controller
{
    protected $shopifyHelper;

    public function __construct(ShopifyHelper $shopifyHelper)
    {
        $this->shopifyHelper = $shopifyHelper;
    }

    public function importProducts()
    {
        $filePath = storage_path('app/Sample product sheet.csv');
        $products = $this->shopifyHelper->readCSVFile($filePath);
        $preparedData = $this->shopifyHelper->prepareProductData($products);

        foreach ($preparedData as $productData) {
            $response = $this->shopifyHelper->createProduct($productData);
            if (isset($response['errors'])) {
                // Handle errors
                echo "Error: " . json_encode($response['errors']);
            } else {
                // Product created successfully
                echo "Product created: " . $response['data']['productCreate']['product']['title'] . "\n";
            }
        }
    }
}