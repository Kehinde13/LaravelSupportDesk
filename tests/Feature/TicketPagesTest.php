<?php

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('protects ticket endpoints from guests', function (string $method, string $route) {
    $this->{$method}(route($route))->assertRedirect(route('login'));
})->with([['get', 'tickets.index'], ['get', 'tickets.create'], ['post', 'tickets.store']]);

it('requires verified email for ticket endpoints', function (string $method, string $route) {
    $this->actingAs(User::factory()->unverified()->create())
        ->{$method}(route($route))->assertRedirect(route('verification.notice'));
})->with([['get', 'tickets.index'], ['get', 'tickets.create'], ['post', 'tickets.store']]);

it('shows ticket pages and empty state to verified users', function () {
    $this->actingAs(User::factory()->create());
    $this->get(route('tickets.index'))->assertOk()->assertSee('No tickets yet')->assertSee('Create ticket');
    $this->get(route('tickets.create'))->assertOk()->assertSee('Description')->assertSee('Cancel');
});

it('lists only owned tickets newest first with ten per page', function () {
    $user = User::factory()->create();
    $old = Ticket::factory()->for($user)->create(['title' => 'Old owned ticket', 'created_at' => now()->subDays(2)]);
    Ticket::factory()->count(9)->for($user)->create(['created_at' => now()->subDay()]);
    $new = Ticket::factory()->for($user)->create(['title' => 'Newest owned ticket', 'created_at' => now()]);
    $other = Ticket::factory()->create(['title' => 'Private other ticket']);
    $this->actingAs($user)->get(route('tickets.index'))->assertOk()
        ->assertSee($new->title)->assertDontSee($old->title)->assertDontSee($other->title)
        ->assertViewHas('tickets', fn ($tickets) => $tickets->total() === 11
            && $tickets->count() === 10 && $tickets->first()->id === $new->id);
    $this->get(route('tickets.index', ['page' => 2]))->assertOk()->assertSee($old->title)->assertDontSee($new->title);
});

it('creates an owned open ticket and flashes success despite spoofed fields', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $this->actingAs($user)->post(route('tickets.store'), [
        'title' => 'Printer unavailable', 'description' => 'Cannot print documents.', 'priority' => 'high',
        'user_id' => $other->id, 'status' => 'resolved',
    ])->assertRedirect(route('tickets.index'))->assertSessionHas('success', 'Ticket created successfully.');
    $this->assertDatabaseHas('tickets', [
        'user_id' => $user->id, 'title' => 'Printer unavailable', 'priority' => 'high', 'status' => 'open',
    ]);
    $this->assertDatabaseCount('tickets', 1);
    $this->get(route('tickets.index'))->assertSee('Ticket created successfully.')->assertSee('Printer unavailable');
});

it('returns errors and preserves input without creating invalid tickets', function () {
    $this->actingAs(User::factory()->create())->from(route('tickets.create'))->post(route('tickets.store'), [
        'title' => 'Keep this title', 'description' => '', 'priority' => 'urgent',
    ])->assertRedirect(route('tickets.create'))->assertSessionHasErrors(['description', 'priority'])
        ->assertSessionHasInput('title', 'Keep this title');
    $this->get(route('tickets.create'))->assertSee('Keep this title');
    $this->assertDatabaseCount('tickets', 0);
});

it('enforces policy checks on every ticket endpoint', function (string $method, string $route) {
    Gate::before(fn () => false);
    $this->actingAs(User::factory()->create())->{$method}(route($route))->assertForbidden();
})->with([['get', 'tickets.index'], ['get', 'tickets.create'], ['post', 'tickets.store']]);

it('renders validation errors on the create form', function () {
    $this->actingAs(User::factory()->create());
    $this->withViewErrors(['description' => 'The description field is required.'])
        ->view('tickets.create', ['priorities' => \App\TicketPriority::cases()])
        ->assertSee('Please correct the errors below.')
        ->assertSee('The description field is required.');
});
