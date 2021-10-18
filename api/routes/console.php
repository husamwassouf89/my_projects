<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('cc', function () {
    // exec('cd dash-tenancy && composer dump-autoload');

    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');

    echo "Mission completed, go go 🌠🌠!";
})->describe('Clean the cache!');


Artisan::command('go', function () {
    // exec('cd dash-tenancy && composer dump-autoload');

    Artisan::call('migrate');
    Artisan::call('passport:install');

    echo "Database is ready, go go 🌠🌠!";
})->describe('Migrate with passport!');


Artisan::command('go-all', function () {
    // exec('cd dash-tenancy && composer dump-autoload');

    Artisan::call('db:wipe');
    Artisan::call('migrate');
    Artisan::call('passport:install');
    Artisan::call('db:seed');
    Artisan::call('db:seed StageSeeder');
    Artisan::call('db:seed GuaranteeSeeder');
    Artisan::call('db:seed QuestionSeeder');

    echo "Database is ready, go go 🌠🌠!";
})->describe('Migrate with passport!');
