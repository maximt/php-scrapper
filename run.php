<?php
require_once 'vendor/autoload.php';

libxml_use_internal_errors(true);


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['URL']);

$parser = new Parser\Parser($_ENV['URL']);
$all_cars = $parser->getCarsAll();

$csv = new CSV\CSVWriter('cars.csv');
$csv->write($all_cars);
