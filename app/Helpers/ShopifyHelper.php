<?php

namespace App\Helpers;

use App\Models\User;
use GuzzleHttp\Client;
use InvalidArgumentException;

class ShopifyHelper
{

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
}