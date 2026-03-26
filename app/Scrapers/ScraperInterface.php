<?php

namespace App\Scrapers;

interface ScraperInterface
{
    // Ez szedi ki a listaoldalról a konkrét bulik linkjeit
    public function getEventLinks($driver): array;

    // Ez szedi ki a konkrét buli adatait (ha a JSON-LD nem működne)
    public function extractEventDetails($driver): ?array;
}