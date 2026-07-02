<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the deployment status dashboard', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Application')
        ->assertSee('Drivers')
        ->assertSee('Database')
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
