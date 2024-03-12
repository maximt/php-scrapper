<?php

namespace Parser;

use Parser\StartPage;
use Parser\CatalogPage;
use Parser\CarPage;


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
