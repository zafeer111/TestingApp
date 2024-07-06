<?php

namespace App\Console\Commands;

use App\Helpers\Shopify;
use App\Helpers\ShopifyHelper;
use App\Jobs\ImportProductsJob;
use App\Models\User;
use Illuminate\Console\Command;

class ImportShopifyProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-shopify-product {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * 
     */
    public function handle()
    {
        $type = $this->option('type');

        $shop = User::select('name', 'password')
            ->where('name', env('SHOP', 'upgradedaddy.myshopify.com'))
            ->first()
            ->toArray();

        $shopify = new Shopify($shop);
        $filePath = null;
        switch ($type) {
            case 'memory':
                $filePath = storage_path('app/Sample product sheet.csv');
                break;

            case 'internal-drives':
                $filePath = storage_path('app/Internal drives product sheet.csv');
                break;

            case 'external-drives':
                $filePath = storage_path('app/External drives product sheet.csv');
                break;

            case 'cpu':
                $filePath = storage_path('app/CPU product sheet.csv');
                break;

            case 'video-card':
                $filePath = storage_path('app/Video Card product sheet.csv');
                break;
        }

        if ($filePath) {
            $shopifyHelper = new ShopifyHelper();
            $products = $shopifyHelper->readCSVFile($filePath);

            foreach ($products as $product) {

                ImportProductsJob::dispatch($product, $shop, $type);
            }
        } else {
            $this->warn('Data sheet of given type not found! Please contact Zafeer bhai');
        }
    }
}
