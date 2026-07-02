<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Use the in-process array store so the dashboard does not reach out to
    // Redis/Memcached during tests.
    config(['cron.stores' => ['array'], 'cron.cache_key' => 'cron:last-run']);
});

it('renders the deployment status dashboard', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Application')
        ->assertSee('Drivers')
        ->assertSee('Database')
        ->assertSee('Cron')
        ->assertSee('Migrations')
        ->assertSee(app()->version());
});

it('reports the database as connected', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('connected');
});

it('lists ran migrations once the schema exists', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('create_users_table')
        ->assertSee('Ran');
});

it('shows the last cron run recorded in a store', function () {
    Cache::store('array')->forever('cron:last-run', now()->toIso8601String());

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Array')
        ->assertSee(now()->toIso8601String());
});
