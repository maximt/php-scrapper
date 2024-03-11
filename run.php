<?php
// TODO proxy
echo "run\n";


function get_page($url) {
    $html = file_get_contents($url);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $xpath = new DOMXPath($doc);
    return $xpath;
}


function get_page_count(DOMXPath $xpath) {
    $pages = $xpath->evaluate('//li/a[@class[contains(.,"page-numbers")]]');
    if ($pages->length == 0) {
        return 0;
    }
    $max_page = 0;
    foreach($pages as $page) {
        $link = $page->getAttribute('href');
        $_link = explode('/', trim($link, '/'));
        $page = intval(end($_link));
        $max_page = max($max_page, $page);
    }
    return $max_page;
}


function get_all_pages($url) {   
    $count = get_page_count(get_page($url));
    $url = trim($url, '/');
    return array_map(function($page) use ($url) {
        return "{$url}/page/{$page}/";
    },range(1, $count));
}


function get_car_links(DOMXPath $xpath) {
    $cars = $xpath->evaluate('//a[@class[contains(.,"listing-image")]]');
    if ($cars->length == 0) {
        return null;
    }

    $cars_data = [];
    foreach ($cars as $car) {
        $cars_data[] = $car->getAttribute('href');
    }

    return $cars_data;
}


function get_all_cars_links($url){
    $page_links = get_all_pages($url);

    $cars_links = [];
    $page_cur = 1;
    $page_cnt = count($page_links);

    echo "pages: {$page_cnt}\n";

    foreach ($page_links as $page_link) {
        $cars = get_car_links(get_page($page_link));
        $car_cnt = count($cars);

        echo "page: {$page_cur}/{$page_cnt}; cars on page: {$car_cnt}\n";
        $page_cur++;

        if ($cars) {
            $cars_links = array_merge($cars_links, $cars);
        }
    }

    $cars_links = array_unique(array_filter($cars_links));

    $cars_links_cnt = count($cars_links);
    echo "total cars count: {$cars_links_cnt}\n";

    return $cars_links;
}


function __parse_car_detail_item($car_detail) {
    $item = [];
    foreach ($car_detail->childNodes as $node) {
        if ($node->nodeName == 'div' && !empty($node->className)) {
            $item[trim($node->className)] = trim($node->textContent);
        }
    }
    return $item;
}


function get_car_details(DOMXPath $xpath) {
    $items = $xpath->evaluate('//*[@id="listing-detail-detail"]/ul/li');

    if ($items->length == 0) {
        return null;
    }

    $details = [];
    foreach ($items as $item) {
        $_item = __parse_car_detail_item($item);

        if ($_item['text']) {
            $key = strtolower(trim($_item['text'], ':'));
            $details[$key] = $_item['value'];
        }
    }

    return $details;
}


function get_car_price(DOMXPath $xpath) {
    $items = $xpath->evaluate('//div[@class[contains(.,"listing-detail-header")]]//span[@class[contains(.,"price-text")]]');

    if ($items->length == 0) {
        return null;
    }

    $text = $items->item(0)->textContent;
    $text = str_replace(',', '', $text);
    return floatval($text);
}


function get_car_images(DOMXPath $xpath) {
    $items = $xpath->evaluate('//div[@class[contains(.,"listing-detail-gallery")]]//div[@class[contains(.,"right-images")]]//a[@class[contains(.,"p-popup-image")]]');

    if ($items->length == 0) {
        return null;
    }

    $images_links = [];
    foreach ($items as $item) {
        $images_links[] = $item->getAttribute('href');
    }

    return $images_links;
}


function get_car_data($url) {
    $xpath = get_page($url);
    
    $details = get_car_details($xpath);
    $details['price'] = get_car_price($xpath);
    $details['images'] = get_car_images($xpath);
    $details['url'] = $url;

    return $details;
}


function get_car_dto($details) {
    $dto = [];

    $dto['Condition'] = 'Used';
    $dto['google_product_category'] = '123';
    $dto['store_code'] = 'xpremium';
    $dto['vehicle_fulfillment(option:store_code)'] = 'in_store:premium';

    $dto['brand'] = $details['make'];
    $dto['model'] = $details['model'];
    $dto['year'] = $details['year'];
    $dto['color'] = $details['color'];
    $dto['mileage'] = $details['mileage'];
    $dto['price'] = $details['price'];
    $dto['vin'] = $details['vin'];

    $dto['image_link'] = $details['images'] > 0 ? $details['images'][1] : '';

    $dto['link_template'] = "{$details['url']}?store={$dto['store_code']}";

    return $dto;
}


function get_cars($url) {
    $cars_links = get_all_cars_links($url);

    $cars = [];
    $cur = 1;
    $cnt = count($cars_links);
    foreach ($cars_links as $car_link) {
        echo "get car data: {$cur}/{$cnt} ({$car_link})\n";
        $cur++;

        $details = get_car_data($car_link);
        $cars[] = get_car_dto($details);
    } 

    return $cars;
}

libxml_use_internal_errors(true);

$URL='https://premiumcarsfl.com/listing-list-full/';

$cars = get_cars($URL);

file_put_contents('cars.json', json_encode($cars, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));



