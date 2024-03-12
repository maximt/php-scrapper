<?php

namespace Parser;

use DOMXPath;
use DOMDocument;


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
