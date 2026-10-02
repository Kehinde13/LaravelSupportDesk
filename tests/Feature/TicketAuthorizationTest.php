<?php

use App\Models\Ticket;
use App\Models\User;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;

it('discovers the ticket policy', function () {
    expect(Gate::getPolicyFor(Ticket::class))->toBeInstanceOf(TicketPolicy::class);
});

it('allows owners and denies other users for ticket actions', function (string $ability) {
    $ticket = Ticket::factory()->create();
    $other = User::factory()->create();

    expect(Gate::forUser($ticket->user)->allows($ability, $ticket))->toBeTrue()
        ->and(Gate::forUser($other)->allows($ability, $ticket))->toBeFalse()
        ->and(Gate::forUser(null)->allows($ability, $ticket))->toBeFalse();
})->with(['view', 'update', 'delete']);

it('allows authenticated users to list and create tickets', function (string $ability) {
    expect(Gate::forUser(User::factory()->create())->allows($ability, Ticket::class))->toBeTrue()
        ->and(Gate::forUser(null)->allows($ability, Ticket::class))->toBeFalse();
})->with(['viewAny', 'create']);
