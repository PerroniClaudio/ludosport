<?php

namespace App\Config;

use MeiliSearch\Client;

class MeiliSearchConfig
{
    public static function setup(): void
    {
        $client = new Client(env('MEILISEARCH_HOST'), env('MEILISEARCH_KEY'));

        $indices = [
            ['name' => 'users', 'primaryKey' => 'id'],
            ['name' => 'academies', 'primaryKey' => 'id'],
            ['name' => 'schools', 'primaryKey' => 'id'],
            ['name' => 'clans', 'primaryKey' => 'id'],
            // Aggiungi altri indici qui
        ];

        foreach ($indices as $index) {
            try {
                $client->getIndex($index['name']);
            } catch (\Throwable $e) {
                $client->createIndex($index['name'], ['primaryKey' => $index['primaryKey']]);
            }
        }
    }
}