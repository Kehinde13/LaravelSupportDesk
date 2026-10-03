<?php

use App\Models\User;

it('brands the public home and offers guest authentication actions', function () {
    $this->get(route('home'))->assertOk()->assertSee('SupportDesk Lite')
        ->assertSee('Simple support ticket management')->assertSee('Sign in')->assertSee('Register')
        ->assertSee('href="'.route('login').'"', false)->assertSee('href="'.route('register').'"', false)
        ->assertDontSee('Laravel')->assertDontSee('Laracasts');
});

it('offers the dashboard action on the authenticated home page', function () {
    $this->actingAs(User::factory()->create())->get(route('home'))->assertOk()
        ->assertSee('href="'.route('dashboard').'"', false)
        ->assertDontSee('href="'.route('login').'"', false);
});

it('uses configured identity in guest application chrome', function (string $route) {
    config(['app.name' => 'Custom Support Desk']);
    $this->get(route($route))->assertOk()->assertSee('Custom Support Desk')
        ->assertDontSee('Laravel')->assertDontSee('laravel.com')->assertDontSee('/favicon.ico');
})->with(['home', 'login', 'register']);

it('brands authenticated chrome without starter links', function (string $route) {
    $this->actingAs(User::factory()->create())->get(route($route))->assertOk()
        ->assertSee('SupportDesk Lite')->assertDontSee('Laravel')
        ->assertDontSee('laravel.com')->assertDontSee('livewire-starter-kit');
})->with(['dashboard', 'tickets.index']);
