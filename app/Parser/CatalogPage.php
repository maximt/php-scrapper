<?php

namespace Parser;

use Parser\Page;

class CatalogPage extends Page {


    function getCarsLinks(): array {
        $items = $this->xpath->evaluate('//a[@class[contains(.,"listing-image")]]');
        if ($items->length == 0)
            return [];
    
        $car_links = [];
        foreach ($items as $item) {
            $car_links[] = $item->getAttribute('href');
        }
        return $car_links;
    }

}
