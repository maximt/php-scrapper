<?php

class CSVWriter
{
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function write(array $items)
    {
        $f = fopen($this->filename, 'w');

        $columsns = [
            'Condition',
            'google_product_category',
            'store_code',
            'vehicle_fulfillment(option:store_code)',
            'Brand',
            'Model',
            'Year',
            'Color',
            'Mileage',
            'Price',
            'VIN',
            'image_link',
            'link_template',
        ];

        fputcsv($f, $columsns, ';');

        foreach ($items as $item) {
            $_item = $this->processItem($item);
            fputcsv($f, $_item , ';');
        }
    
        fclose($f);
    }

    private function processItem(array $item): array {
        $_item = [];

        $_item['Condition'] = $_ENV['CONDITION'] ?? '';
        $_item['google_product_category'] = $_ENV['GOOGLE_PRODUCT_CATEGORY'] ?? '';
        $_item['store_code'] = $_ENV['STORE_CODE'] ?? '';
        $_item['vehicle_fulfillment(option:store_code)'] = $_ENV['VEHICLE_FULFILLMENT'] ?? 'in_store:premium';
    
        $_item['Brand'] = $item['make'] ?? '';
        $_item['Model'] = $item['model'] ?? '';
        $_item['Year'] = $item['year'] ?? '';
        $_item['Color'] = $item['color'] ?? '';
        $_item['Mileage'] = isset($item['mileage']) ? "{$item['mileage']} miles" : '';
        $_item['Price'] = $item['price'] ?? '';
        $_item['VIN'] = $item['vin'] ?? '';
    
        $_item['image_link'] = count($item['images']) > 0 ? $item['images'][1] : ''; 
        $_item['link_template'] = "{$item['url']}?store={$_item['store_code']}";

        return $_item;
    }
}