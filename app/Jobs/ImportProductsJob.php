<?php

namespace App\Jobs;

use App\Helpers\Shopify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;
    protected $shop;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @param array $product
     * @param array $shop
     * @param string $type
     */
    public function __construct($product, $shop, $type)
    {
        $this->product = $product;
        $this->shop = $shop;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shopify = new Shopify($this->shop);

        switch ($this->type) {
            case 'memory':
                $productData = $shopify->prepareProductDataMemory($this->product);
                break;
            case 'internal-drives':
                $productData = $shopify->prepareProductDataInternalDrives($this->product);
                break;
            case 'external-drives':
                $productData = $shopify->prepareProductDataExternalDrives($this->product);
                break;
            case 'cpu':
                $productData = $shopify->prepareProductDataCPU($this->product);
                break;
            case 'video-card':
                $productData = $shopify->prepareProductDataVideoCard($this->product);
                break;
            default:
                throw new \Exception("Unsupported product type: {$this->type}");
        }


        $payload = ['input' => $productData];

        $request['query'] = $this->shopifyMutationForProduct();
        $request['variables'] = $payload;
        $shopifyProduct = $shopify->post('graphql.json', $request);
        Log::info(var_export($shopifyProduct, true));
    }

    private function shopifyMutationForProduct(): string
    {
        return 'mutation productCreate($input: ProductInput!) {
            productCreate(input: $input) {
                product {
                    id
                }
                userErrors {
                    field
                    message
                }
            }
        }';
    }
}