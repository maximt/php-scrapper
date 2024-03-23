<?php

namespace Parser;

use Parser\Page;

class CatalogPage extends Page {


    public function getCarsLinks(): array {
        try {
            $items = $this->xpath->evaluate('//a[@class[contains(.,"listing-image")]]');
            if ($items->length == 0)
                return [];
        
            $car_links = [];
            foreach ($items as $item) {
                $car_links[] = $item->getAttribute('href');
            }
            return $car_links;
        } catch (\Exception $e) {
            echo "Error: {$e->getMessage()}\n";
            return [];
        }
}

}
