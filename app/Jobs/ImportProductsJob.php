<?php

namespace App\Jobs;

use App\Helpers\Shopify;
use App\Helpers\ShopifyHelper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $shop;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filePath, $shop)
    {
        $this->filePath = $filePath;
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shopifyHelper = new ShopifyHelper();
        $products = $shopifyHelper->readCSVFile($this->filePath);
        $shopify = new Shopify($this->shop);

        foreach ($products as $product) {
            $productData = $this->prepareProductData($product);
            $payload = ['input' => $productData];

            $request['query'] = $this->shopifyMutationForProduct();
            $request['variables'] = $payload ;
            $shopifyProduct = $shopify->post('graphql.json', $request);
            Log::info(var_export($shopifyProduct, true));
        }
    }

    private function prepareProductData($product)
    {
        return [
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
                ['namespace' => 'custom', 'key' => 'pic_name', 'value' => $product['Pic name'], 'type' => 'single_line_text_field'],
            ]
        ];
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
