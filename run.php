<?php
require_once 'parser.php';
// TODO proxy
echo "run\n";


function get_car_dto(array $details): array {
    $dto = [];

    $dto['Condition'] = 'Used';
    $dto['google_product_category'] = '123';
    $dto['store_code'] = 'xpremium';
    $dto['vehicle_fulfillment(option:store_code)'] = 'in_store:premium';

    $dto['Brand'] = $details['make'] ?? '';
    $dto['Model'] = $details['model'] ?? '';
    $dto['Year'] = $details['year'] ?? '';
    $dto['Color'] = $details['color'] ?? '';
    $dto['Mileage'] = $details['mileage'] ? "{$details['mileage']} miles" : '';
    $dto['Price'] = $details['price'] ?? '';
    $dto['VIN'] = $details['vin'] ?? '';

    $dto['image_link'] = $details['images'] > 0 ? $details['images'][1] : '';

    $dto['link_template'] = "{$details['url']}?store={$dto['store_code']}";

    return $dto;
}


function write_csv_file(string $filename, array $items): void {
    $file = fopen($filename, 'w');
    $columsns = array_keys($items[0]);
    fputcsv($file, $columsns, ';');
    foreach ($items as $item) {
        fputcsv($file, $item, ';');
    }
    fclose($file);
}



libxml_use_internal_errors(true);

$URL='https://premiumcarsfl.com/listing-list-full/';


$parser = new Parser($URL);
$all_cars = $parser->getCarsAll();
//var_dump($all_cars);
