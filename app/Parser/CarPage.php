<?php

namespace Parser;

use DOMElement;
use Parser\Page;


class CarPage extends Page {


    public function getData(): array {
        $details = $this->ParseCarDetails();
        $details['price'] = $this->getCarPrice();
        $details['images'] = $this->getCarImages();
        $details['url'] = $this->url;
        return $details;
    }


    private function ParseCarDetails(): array {
        $items = $this->xpath->evaluate('//*[@id="listing-detail-detail"]/ul/li');
        if ($items->length == 0)
            return [];
    
        $details = [];
        foreach ($items as $item) {
            $_item = $this->parseCarDetailItem($item);
    
            if ($_item['text']) {
                $key = strtolower(trim($_item['text'], ':'));
                $details[$key] = $_item['value'];
            }
        }
        return $details;
    }


    private function getCarPrice(): ?float {
        $items = $this->xpath->evaluate('//div[@class[contains(.,"listing-detail-header")]]//span[@class[contains(.,"price-text")]]');
        if ($items->length == 0)
            return null;
    
        $text = $items->item(0)->textContent;
        $text = str_replace(',', '', $text);
        return floatval($text);
    }
    
    
    private function getCarImages(): array {
        $items = $this->xpath->evaluate('//div[@class[contains(.,"listing-detail-gallery")]]//div[@class[contains(.,"right-images")]]//a[@class[contains(.,"p-popup-image")]]');
        if ($items->length == 0) 
            return [];
    
        $images_links = [];
        foreach ($items as $item) {
            $images_links[] = $item->getAttribute('href');
        }
        return $images_links;
    }


    private static function parseCarDetailItem(DOMElement $car_detail): array {
        $item = [];
        foreach ($car_detail->childNodes as $node) {
            if ($node->nodeName == 'div' && !empty($node->className)) {
                $item[trim($node->className)] = trim($node->textContent);
            }
        }
        return $item;
    }
    
}
