<?php

namespace Parser;

use Parser\Page;


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
