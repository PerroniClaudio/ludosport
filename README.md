<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Ludosport Management System

Gestione digitale per la community Ludosport: atleti, istruttori, eventi, pagamenti e molto altro in un'unica piattaforma.

## Descrizione

Questo progetto è una web application sviluppata in Laravel per la gestione centralizzata delle attività Ludosport, tra cui:

-   Anagrafica atleti e staff
-   Gestione eventi, iscrizioni e presenze
-   Pagamenti e quote associative
-   Ruoli e permessi (atleta, istruttore, manager, tecnico, ecc.)
-   Reportistica e esportazione dati

## Requisiti

-   PHP >= 8.3
-   Composer
-   Database SQLite (default) o altro supportato da Laravel
-   Node.js, pnpm o npm (per asset frontend)
-   Docker (opzionale, per sviluppo e produzione)

## Installazione

1. Clona la repository:
    ```sh
    git clone <repo-url>
    cd ludosport
    ```
2. Installa le dipendenze PHP:
    ```sh
    composer install
    ```
3. Installa le dipendenze frontend:
    ```sh
    pnpm install
    # oppure
    npm install
    ```
4. Copia il file `.env.example` in `.env` e configura le variabili ambiente.
5. Genera la chiave dell'applicazione:
    ```sh
    php artisan key:generate
    ```
6. Esegui le migrazioni e i seeders:
    ```sh
    php artisan migrate --seed
    ```
7. Avvia il server di sviluppo:
    ```sh
    php artisan serve
    ```

## Struttura principale della codebase

-   `app/` — Logica applicativa (Models, Http, Events, Jobs, ecc.)
-   `routes/` — Definizione delle rotte (web, API, ruoli specifici)
-   `resources/` — Views Blade, asset CSS/JS
-   `database/` — Migrazioni, seeders, factories
-   `docker/` — Configurazioni Docker e ambienti
-   `tests/` — Test automatici (Pest, PHPUnit)

## Comandi utili

-   Avvio ambiente locale: `php artisan serve`
-   Esecuzione test: `./vendor/bin/pest`
-   Compilazione asset: `pnpm run dev` o `npm run dev`
-   Avvio ambiente Docker: `docker-compose -f docker/docker-compose.dev.yml up`

## Contribuire

Contributi sono benvenuti! Apri una issue o una pull request. Consulta la documentazione Laravel per le linee guida generali.

## Licenza

Questo progetto è open-source, distribuito sotto licenza [MIT](https://opensource.org/licenses/MIT).
