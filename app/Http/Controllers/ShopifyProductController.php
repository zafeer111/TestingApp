<?php

namespace App\Http\Controllers;

use App\Helpers\ShopifyHelper;
use App\Jobs\ImportProductsJob;
use App\Models\User;
use Illuminate\Http\Request;

class ShopifyProductController extends Controller
{
    protected $shopifyHelper;

    public function __construct(ShopifyHelper $shopifyHelper)
    {
        $this->shopifyHelper = $shopifyHelper;
    }

    public function importProducts()
    {
        $shop = User::select('name', 'password')
            ->where('name', env('SHOP', 'zafeer-development.myshopify.com'))
            ->first()
            ->toArray();

        $filePath = storage_path('app/Sample product sheet.csv');

        // Dispatch the job
        ImportProductsJob::dispatch($filePath, $shop);

        return response()->json(['status' => 'Import process has been started.']);
    }
}
