<?php 

class Page {
    protected string $url;
    protected DOMXPath $xpath;

    
    function __construct(string $url) {
        $this->url = $url;
        $this->xpath = $this->getXPath($url);
    }


    private static function getXPath(string $url): DOMXPath {
        $html = file_get_contents($url);
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        return new DOMXPath($doc);
    }

}


class StartPage extends Page {


    function getPagesCount(): int {
        $pages = $this->xpath->evaluate('//li/a[@class[contains(.,"page-numbers")]]');
        if ($pages->length == 0)
            return 0;
    
        $max_page = 0;
        foreach($pages as $page) {
            $link = $page->getAttribute('href');
            $_link = explode('/', trim($link, '/'));
            $page = intval(end($_link));
            $max_page = max($max_page, $page);
        }
        return $max_page;
    }
    

    function getPagesLinks(): array {   
        $count = $this->getPagesCount();

        $url = trim($this->url, '/');
        return array_map(function($page) use ($url) {
            return "{$url}/page/{$page}/";
        },range(1, $count));
    }
    
}


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


class CarPage extends Page {


    function getData(): array {
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


class Parser {
    private string $url;
    private StartPage $startPage;
    private array $carsLinksAll = [];
    private array $carsAll = [];


    public function __construct(string $url) {
        $this->url = $url;
        $this->startPage = new StartPage($this->url);
    }


    function getCarsLinksAll(): array {
        if ($this->carsLinksAll)
            return $this->carsLinksAll;

        $pages_links =  $this->startPage->getPagesLinks();

        $page_cur = 0; $pages_cnt = count($pages_links);
        echo "Pages: {$pages_cnt}\n";

        $this->carsLinksAll = [];
        foreach ($pages_links as $page_link) {
            $catalogPage = new CatalogPage($page_link);
            $cars_links = $catalogPage->getCarsLinks();

            $page_cur++; $car_cnt = count($cars_links);
            echo "Page: {$page_cur}/{$pages_cnt}; Cars on page: {$car_cnt}\n";
            
            if ($cars_links)
                $this->carsLinksAll = array_merge($this->carsLinksAll, $cars_links);
        }
        $this->carsLinksAll = array_unique(array_filter($this->carsLinksAll));

        $cars_links_all_cnt = count($this->carsLinksAll);
        echo "Total cars links: {$cars_links_all_cnt}\n";

        return $this->carsLinksAll;
    }


    function getCarsAll(): array {
        if ($this->carsAll)
            return $this->carsAll;

        $cars_links = $this->getCarsLinksAll();
    
        $car_cur = 0; $cars_cnt = count($cars_links);

        $this->carsAll = [];
        foreach ($cars_links as $car_link) {
            $car_cur++;
            echo "Get car data: {$car_cur}/{$cars_cnt} ({$car_link})\n";
            
            $carPage = new CarPage($car_link);
            $this->carsAll[] = $carPage->getData();
        }

        $cars_all_cnt = count($this->carsAll);
        echo "Total cars parsed: {$cars_all_cnt}\n";

        return $this->carsAll;
    }
}