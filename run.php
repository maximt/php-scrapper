<?php
require_once 'vendor/autoload.php';
require_once 'parser.php';
require_once 'csv.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['URL']);

libxml_use_internal_errors(true);

$parser = new Parser($_ENV['URL']);
$all_cars = $parser->getCarsAll();

$csv = new CSVWriter('cars.csv');
$csv->write($all_cars);

